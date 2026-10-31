// accounting_invoicing_service.js
// Service Layer untuk Modul M4: Accounting & Invoicing (PT Arunika Distribusi Cepat)
//
// SR refs: SR-01, SR-02, SR-05, SR-06, SR-13, SR-16, SR-20
// Feature refs: F-09, F-13, F-14, F-15, F-16, F-22
//
// Decision Log (konflik SR yang diselesaikan):
//   SR-01 vs SR-02 : SR-02 wins — invoice hanya untuk qty Done di Delivery Order.
//   SR-05 vs SR-06 : SR-06 wins — vendor bill dibatasi accepted receipt qty.

'use strict';

const store = require('./store');

// ── COA Code Constants (Handout §4) ─────────────────────────────────────────
const COA = {
  BANK        : '1000',
  AR          : '1100',
  INVENTORY   : '1300',
  INTERIM     : '1350',
  VAT_INPUT   : '1400',
  AP          : '2000',
  VAT_OUTPUT  : '2100',
  EQUITY      : '3000',
  SALES       : '4000',
  SALES_RETURN: '4100',
  COGS        : '5100',
};

// ── PPN Tax Logic ─────────────────────────────────────────────────────────────

/**
 * Hitung PPN efektif 11% (formal 12% × DPP 11/12) dengan pembulatan half-up per baris.
 * Handout §4: Rp250.000 × 11% = Rp27.500 (exact).
 * @param {number} netAmount  integer IDR
 * @returns {number}
 */
function calcTax(netAmount) {
  return Math.round(netAmount * 0.11);
}

/**
 * Hitung satu baris invoice: qty × effective_unit_price + PPN 11%.
 * @param {number} qty
 * @param {number} unitPrice
 * @param {number} discountPct  0–15
 * @returns {{ qty, unitPrice, discountPct, effectivePrice, netAmount, taxAmount, grossAmount }}
 */
function calcLine(qty, unitPrice, discountPct = 0) {
  const q              = Math.round(qty)       || 0;
  const up             = Math.round(unitPrice) || 0;
  const disc           = Number(discountPct)   || 0;
  const effectivePrice = Math.round(up * (1 - disc / 100));
  const netAmount      = q * effectivePrice;
  const taxAmount      = calcTax(netAmount);
  return {
    qty,
    unitPrice    : up,
    discountPct  : disc,
    effectivePrice,
    netAmount,
    taxAmount,
    grossAmount  : netAmount + taxAmount,
  };
}

// ── Document Sequence ─────────────────────────────────────────────────────────

function nextDocumentNumber(type) {
  if (!store.state.sequences) store.state.sequences = {};
  if (!store.state.sequences[type]) store.state.sequences[type] = 0;
  store.state.sequences[type]++;

  const prefix = {
    'customer_invoice': 'INV',
    'vendor_bill'     : 'BILL',
    'credit_note'     : 'CN',
    'payment'         : 'PAY',
  }[type] || 'DOC';

  return `${prefix}-${String(store.state.sequences[type]).padStart(5, '0')}`;
}

// ── Idempotency Guard (SR-16) ─────────────────────────────────────────────────

function buildIdempotencyKey(journalType, reference, debitAcc, creditAcc, amount) {
  return `${journalType}|${reference}|${debitAcc}|${creditAcc}|${amount}`;
}

// ── Internal Journal Posting ──────────────────────────────────────────────────

function postJournalEntry({ invoiceDocNumber, journalType, reference, debitAcc, creditAcc, amount, description, postedBy }) {
  if (!store.state.journal_entries) store.state.journal_entries = [];

  const idemKey = buildIdempotencyKey(journalType, reference, debitAcc, creditAcc, amount);

  // SR-16: idempotency — return existing entry without creating duplicate
  const existing = store.state.journal_entries.find(e => e.idempotency_key === idemKey);
  if (existing) {
    return { ...existing, _idempotent_skip: true };
  }

  const entry = {
    id               : store.state.journal_entries.length + 1,
    invoice_doc_number: invoiceDocNumber || null,
    journal_type     : journalType,
    reference,
    idempotency_key  : idemKey,
    debit_account    : debitAcc,
    credit_account   : creditAcc,
    amount,
    description      : description || '',
    posted_by        : postedBy || 'system',
    posted_at        : new Date().toISOString(),
    is_reversed      : false,
  };

  store.state.journal_entries.push(entry);
  store.persist();
  return entry;
}

// ═══════════════════════════════════════════════════════════════════════════════
// AccountingInvoicingService
// ═══════════════════════════════════════════════════════════════════════════════

class AccountingInvoicingService {

  // ── Tax Utilities (public, used by tests) ─────────────────────────────────
  calcTax(netAmount)                        { return calcTax(netAmount); }
  calcLine(qty, unitPrice, discountPct = 0) { return calcLine(qty, unitPrice, discountPct); }

  // ── F-09: Create Customer Invoice ─────────────────────────────────────────

  /**
   * Buat Customer Invoice dari Delivery Order yang sudah Done.
   *
   * Decision Log SR-01 vs SR-02:
   *   SR-02 menang → invoiceable = deliveredQty − alreadyInvoicedQty.
   *
   * @param {Object} params
   * @param {string} params.partnerId
   * @param {string} params.partnerName
   * @param {string} params.sourceSo
   * @param {string} [params.paymentTerms]
   * @param {string} params.createdBy
   * @param {Array}  params.lines  [{productId, variantSku, productName, unit, unitPrice, deliveredQty, invoicedQty}]
   */
  createCustomerInvoice({ partnerId, partnerName, sourceSo, paymentTerms = 'NET14', createdBy, lines = [] }) {
    if (!store.state.invoices) store.state.invoices = [];

    let netTotal = 0;
    let taxTotal = 0;
    const computedLines = [];

    for (const line of lines) {
      const invoiceableQty = Math.max(0, (line.deliveredQty || 0) - (line.invoicedQty || 0));
      if (invoiceableQty <= 0) continue;

      const calc = calcLine(invoiceableQty, line.unitPrice || 0, line.discountPct || 0);
      netTotal += calc.netAmount;
      taxTotal += calc.taxAmount;
      computedLines.push({ ...line, ...calc, quantity: invoiceableQty });
    }

    if (computedLines.length === 0) {
      throw new Error(
        'SR-02 Enforcement: Tidak ada kuantitas yang sudah terkirim untuk ditagih. ' +
        'Customer Invoice hanya dapat dibuat berdasarkan qty Done di Delivery Order.'
      );
    }

    const grossTotal = netTotal + taxTotal;
    const docNumber  = nextDocumentNumber('customer_invoice');
    const termDays   = { 'CASH': 0, 'NET14': 14, 'NET30': 30 }[paymentTerms] ?? 14;
    const now        = new Date();
    const dueDate    = new Date(now.getTime() + termDays * 86400000).toISOString().split('T')[0];

    const invoice = {
      id                  : store.state.invoices.length + 1,
      document_number     : docNumber,
      invoice_type        : 'customer_invoice',
      status              : 'draft',
      partner_id          : partnerId,
      partner_name        : partnerName,
      partner_type        : 'customer',
      source_document_id  : sourceSo || null,
      source_document_type: 'sales_order',
      original_invoice_id : null,
      untaxed_amount      : netTotal,
      tax_amount          : taxTotal,
      gross_amount        : grossTotal,
      amount_paid         : 0,
      amount_outstanding  : grossTotal,
      tax_code            : 'PPN-NONLUX',
      tax_rate            : 0.11,
      payment_terms       : paymentTerms,
      invoice_date        : now.toISOString().split('T')[0],
      due_date            : dueDate,
      created_by          : createdBy,
      posted_by           : null,
      posted_at           : null,
      lines               : computedLines,
    };

    store.state.invoices.push(invoice);
    store.logAudit({
      document_id: docNumber,
      action     : 'INVOICE_CREATED',
      actor      : createdBy,
      details    : `Customer Invoice ${docNumber} dibuat. Partner: ${partnerId}, Net: Rp${netTotal}, PPN: Rp${taxTotal}, Gross: Rp${grossTotal}. Source: ${sourceSo}. SR-02 applied.`,
      result     : 'Status: draft',
    });
    store.persist();
    return invoice;
  }

  // ── F-13: Create Vendor Bill (3-Way Match) ────────────────────────────────

  /**
   * Buat Vendor Bill dengan 3-way matching: PO + Accepted Receipt + Prior Bills.
   *
   * Decision Log SR-05 vs SR-06:
   *   SR-06 menang → billableQty = acceptedQty − alreadyBilledQty.
   *
   * @param {Object} params
   * @param {string} params.partnerId
   * @param {string} params.partnerName
   * @param {string} params.sourcePo
   * @param {string} [params.paymentTerms]
   * @param {string} params.createdBy
   * @param {Array}  params.lines  [{productId, variantSku, productName, unit, unitPrice, orderedQty, acceptedQty, alreadyBilled}]
   */
  createVendorBill({ partnerId, partnerName, sourcePo, paymentTerms = 'NET14', createdBy, lines = [] }) {
    if (!store.state.invoices) store.state.invoices = [];

    let netTotal = 0;
    let taxTotal = 0;
    const computedLines = [];

    for (const line of lines) {
      const billableQty = Math.max(0, (line.acceptedQty || 0) - (line.alreadyBilled || 0));
      if (billableQty <= 0) continue;

      const calc = calcLine(billableQty, line.unitPrice || 0);
      netTotal += calc.netAmount;
      taxTotal += calc.taxAmount;
      computedLines.push({ ...line, ...calc, quantity: billableQty });
    }

    if (computedLines.length === 0) {
      throw new Error(
        'SR-06 Enforcement: Tidak ada kuantitas accepted receipt yang belum ditagih. ' +
        'Vendor Bill dibatasi PO + accepted receipt + tagihan sebelumnya (3-way match).'
      );
    }

    const grossTotal = netTotal + taxTotal;
    const docNumber  = nextDocumentNumber('vendor_bill');
    const termDays   = { 'CASH': 0, 'NET14': 14, 'NET30': 30 }[paymentTerms] ?? 14;
    const now        = new Date();
    const dueDate    = new Date(now.getTime() + termDays * 86400000).toISOString().split('T')[0];

    const bill = {
      id                  : store.state.invoices.length + 1,
      document_number     : docNumber,
      invoice_type        : 'vendor_bill',
      status              : 'draft',
      partner_id          : partnerId,
      partner_name        : partnerName,
      partner_type        : 'vendor',
      source_document_id  : sourcePo || null,
      source_document_type: 'purchase_order',
      original_invoice_id : null,
      untaxed_amount      : netTotal,
      tax_amount          : taxTotal,
      gross_amount        : grossTotal,
      amount_paid         : 0,
      amount_outstanding  : grossTotal,
      tax_code            : 'PPN-NONLUX',
      tax_rate            : 0.11,
      payment_terms       : paymentTerms,
      invoice_date        : now.toISOString().split('T')[0],
      due_date            : dueDate,
      created_by          : createdBy,
      posted_by           : null,
      posted_at           : null,
      lines               : computedLines,
    };

    store.state.invoices.push(bill);
    store.logAudit({
      document_id: docNumber,
      action     : 'BILL_CREATED',
      actor      : createdBy,
      details    : `Vendor Bill ${docNumber} dibuat (3-way match). Partner: ${partnerId}, PO: ${sourcePo}, Net: Rp${netTotal}, PPN: Rp${taxTotal}, Gross: Rp${grossTotal}. SR-06 applied.`,
      result     : 'Status: draft',
    });
    store.persist();
    return bill;
  }

  // ── F-14: Post Invoice → Automated Journal Entries ───────────────────────

  /**
   * Post invoice (draft → posted) + catat Journal Entries otomatis.
   *
   * SR-16: Idempotent — jika sudah posted, throw error.
   *
   * Journal patterns:
   *   Customer Invoice : Dr 1100 AR / Cr 4000 Sales (net) + Dr 1100 AR / Cr 2100 PPN (tax)
   *   Vendor Bill      : Dr 1300 Inv / Cr 2000 AP (net)   + Dr 1400 PPN / Cr 2000 AP (tax)
   *   Credit Note      : Dr 4100 Retur / Cr 1100 AR (net) + Dr 2100 PPN / Cr 1100 AR (tax)
   *
   * @param {string|number} invoiceIdOrDocNumber
   * @param {string}        postedBy
   * @returns {{ invoice, entries }}
   */
  postInvoice(invoiceIdOrDocNumber, postedBy) {
    if (!store.state.invoices) store.state.invoices = [];
    if (!store.state.journal_entries) store.state.journal_entries = [];

    const invoice = store.state.invoices.find(
      inv => inv.id === invoiceIdOrDocNumber || inv.document_number === invoiceIdOrDocNumber
    );

    if (!invoice) {
      throw new Error(`Invoice/Bill dengan ID/DocNumber '${invoiceIdOrDocNumber}' tidak ditemukan.`);
    }

    // SR-16: double posting guard
    if (invoice.status !== 'draft') {
      throw new Error(
        `SR-16 Enforcement: Invoice ${invoice.document_number} sudah diposting ` +
        `(status: ${invoice.status}). Double posting dilarang.`
      );
    }

    const ref     = invoice.document_number;
    const entries = [];

    if (invoice.invoice_type === 'customer_invoice') {
      entries.push(postJournalEntry({
        invoiceDocNumber: ref, journalType: 'sales', reference: ref,
        debitAcc: COA.AR, creditAcc: COA.SALES, amount: invoice.untaxed_amount,
        description: `Penjualan - ${ref} (net)`, postedBy,
      }));
      entries.push(postJournalEntry({
        invoiceDocNumber: ref, journalType: 'sales', reference: ref + '-TAX',
        debitAcc: COA.AR, creditAcc: COA.VAT_OUTPUT, amount: invoice.tax_amount,
        description: `PPN Keluaran - ${ref}`, postedBy,
      }));

    } else if (invoice.invoice_type === 'vendor_bill') {
      entries.push(postJournalEntry({
        invoiceDocNumber: ref, journalType: 'purchase', reference: ref,
        debitAcc: COA.INVENTORY, creditAcc: COA.AP, amount: invoice.untaxed_amount,
        description: `Pembelian - ${ref} (net)`, postedBy,
      }));
      entries.push(postJournalEntry({
        invoiceDocNumber: ref, journalType: 'purchase', reference: ref + '-TAX',
        debitAcc: COA.VAT_INPUT, creditAcc: COA.AP, amount: invoice.tax_amount,
        description: `PPN Masukan - ${ref}`, postedBy,
      }));

    } else if (invoice.invoice_type === 'credit_note') {
      entries.push(postJournalEntry({
        invoiceDocNumber: ref, journalType: 'sales', reference: ref,
        debitAcc: COA.SALES_RETURN, creditAcc: COA.AR, amount: invoice.untaxed_amount,
        description: `Retur Penjualan - ${ref} (net)`, postedBy,
      }));
      entries.push(postJournalEntry({
        invoiceDocNumber: ref, journalType: 'sales', reference: ref + '-TAX',
        debitAcc: COA.VAT_OUTPUT, creditAcc: COA.AR, amount: invoice.tax_amount,
        description: `PPN reversal - ${ref}`, postedBy,
      }));
    }

    invoice.status    = 'posted';
    invoice.posted_by = postedBy;
    invoice.posted_at = new Date().toISOString();

    store.logAudit({
      document_id: ref,
      action     : 'INVOICE_POSTED',
      actor      : postedBy,
      details    : `Invoice ${ref} diposting. Net: Rp${invoice.untaxed_amount}, PPN: Rp${invoice.tax_amount}, Gross: Rp${invoice.gross_amount}. SR-16: idempotent.`,
      result     : 'Status: posted',
    });
    store.persist();

    return { invoice, entries };
  }

  // ── Post Stock Move (F-14: Delivery COGS & Receipt Interim) ─────────────

  /**
   * Catat jurnal stock move.
   * Delivery Done    : Dr 5100 HPP / Cr 1300 Persediaan
   * Receipt Accepted : Dr 1300 Persediaan / Cr 1350 Stock Interim
   *
   * @param {'delivery'|'receipt'} moveType
   * @param {string} reference
   * @param {number} costAmount  standard_cost × qty (integer IDR)
   * @param {string} postedBy
   */
  postStockMove(moveType, reference, costAmount, postedBy) {
    if (moveType === 'delivery') {
      return postJournalEntry({
        invoiceDocNumber: null, journalType: 'stock', reference,
        debitAcc: COA.COGS, creditAcc: COA.INVENTORY, amount: costAmount,
        description: `COGS Delivery ${reference}`, postedBy,
      });
    } else {
      return postJournalEntry({
        invoiceDocNumber: null, journalType: 'stock', reference,
        debitAcc: COA.INVENTORY, creditAcc: COA.INTERIM, amount: costAmount,
        description: `Penerimaan Barang ${reference}`, postedBy,
      });
    }
  }

  // ── F-15: Register Partial Payment (SR-20) ────────────────────────────────

  /**
   * Catat pembayaran parsial terhadap invoice/bill yang sudah posted.
   *
   * SR-20: amount ≤ outstanding. Overpayment ditolak.
   *
   * @param {string|number} invoiceIdOrDocNumber
   * @param {number}        amount
   * @param {string}        paymentRef
   * @param {string}        postedBy
   * @returns {{ invoice, entry }}
   */
  registerPayment(invoiceIdOrDocNumber, amount, paymentRef, postedBy) {
    if (!store.state.invoices) store.state.invoices = [];

    const invoice = store.state.invoices.find(
      inv => inv.id === invoiceIdOrDocNumber || inv.document_number === invoiceIdOrDocNumber
    );

    if (!invoice) {
      throw new Error(`Invoice/Bill '${invoiceIdOrDocNumber}' tidak ditemukan.`);
    }

    if (invoice.status !== 'posted') {
      throw new Error(
        `Pembayaran hanya dapat dilakukan pada Invoice/Bill dengan status 'posted'. Status saat ini: ${invoice.status}.`
      );
    }

    const outstanding = invoice.amount_outstanding;

    // SR-20: tolak overpayment
    if (amount > outstanding) {
      throw new Error(
        `SR-20 Enforcement: Pembayaran Rp${amount} melebihi saldo outstanding Rp${outstanding}. Overpayment ditolak.`
      );
    }

    let debitAcc, creditAcc;
    if (invoice.invoice_type === 'customer_invoice' || invoice.invoice_type === 'credit_note') {
      debitAcc  = COA.BANK;
      creditAcc = COA.AR;
    } else {
      debitAcc  = COA.AP;
      creditAcc = COA.BANK;
    }

    const entry = postJournalEntry({
      invoiceDocNumber: invoice.document_number,
      journalType: 'payment', reference: paymentRef,
      debitAcc, creditAcc, amount,
      description: `Pembayaran ${invoice.document_number} - ${paymentRef}`,
      postedBy,
    });

    invoice.amount_paid        += amount;
    invoice.amount_outstanding  = Math.max(0, invoice.amount_outstanding - amount);
    invoice.status              = invoice.amount_outstanding <= 0 ? 'paid' : 'posted';

    store.logAudit({
      document_id: invoice.document_number,
      action     : 'PAYMENT_REGISTERED',
      actor      : postedBy,
      details    : `Pembayaran Rp${amount} dicatat untuk ${invoice.document_number}. Saldo baru: Rp${invoice.amount_outstanding}. SR-20 applied.`,
      result     : `Status: ${invoice.status}`,
    });
    store.persist();

    return { invoice, entry };
  }

  // ── F-16: Create Credit Note (SR-13) ─────────────────────────────────────

  /**
   * Buat Credit Note terhadap Customer Invoice yang sudah posted.
   *
   * SR-13: returnQty ≤ invoicedQty − alreadyCreditedQty.
   *        Return barang layak jual membalik COGS.
   *
   * @param {string|number} originalInvoiceIdOrDoc
   * @param {Array}  returnLines  [{variantSku, qty, isSaleable, unitCost}]
   * @param {string} createdBy
   */
  createCreditNote(originalInvoiceIdOrDoc, returnLines, createdBy) {
    if (!store.state.invoices) store.state.invoices = [];

    const original = store.state.invoices.find(
      inv => inv.id === originalInvoiceIdOrDoc || inv.document_number === originalInvoiceIdOrDoc
    );

    if (!original) {
      throw new Error(`Invoice '${originalInvoiceIdOrDoc}' tidak ditemukan.`);
    }

    if (original.status !== 'posted') {
      throw new Error('Credit Note hanya dapat dibuat dari Invoice yang sudah diposting.');
    }

    // Compute already credited per SKU
    const alreadyCredited = {};
    for (const inv of store.state.invoices) {
      if (inv.invoice_type === 'credit_note' &&
          inv.original_invoice_id === original.id &&
          inv.status === 'posted') {
        for (const line of (inv.lines || [])) {
          alreadyCredited[line.variantSku] = (alreadyCredited[line.variantSku] || 0) + line.quantity;
        }
      }
    }

    let netTotal = 0;
    let taxTotal = 0;
    const computedLines = [];

    for (const ret of returnLines) {
      const origLine  = (original.lines || []).find(l => l.variantSku === ret.variantSku);
      if (!origLine) throw new Error(`SKU ${ret.variantSku} tidak ada di invoice ${original.document_number}.`);

      const alreadyCred = alreadyCredited[ret.variantSku] || 0;
      const maxAllow    = origLine.quantity - alreadyCred;

      // SR-13 ceiling check
      if (ret.qty > maxAllow) {
        throw new Error(
          `SR-13 Enforcement: Return qty ${ret.qty} untuk SKU ${ret.variantSku} melebihi batas ` +
          `yang dapat dikreditkan (${maxAllow}). Hanya qty yang sudah ditagih dan belum dikreditkan yang boleh diretur.`
        );
      }

      const calc = calcLine(ret.qty, origLine.unitPrice || 0);
      netTotal  += calc.netAmount;
      taxTotal  += calc.taxAmount;
      computedLines.push({
        productId  : origLine.productId || '',
        variantSku : ret.variantSku,
        productName: origLine.productName || ret.variantSku,
        unit       : origLine.unit || 'unit',
        ...calc,
        quantity   : ret.qty,
        unitPrice  : origLine.unitPrice || 0,
        isSaleable : ret.isSaleable || false,
        unitCost   : ret.unitCost   || 0,
      });
    }

    if (computedLines.length === 0) {
      throw new Error('Tidak ada baris return yang valid.');
    }

    const grossTotal = netTotal + taxTotal;
    const docNumber  = nextDocumentNumber('credit_note');

    const cn = {
      id                  : store.state.invoices.length + 1,
      document_number     : docNumber,
      invoice_type        : 'credit_note',
      status              : 'draft',
      partner_id          : original.partner_id,
      partner_name        : original.partner_name,
      partner_type        : 'customer',
      source_document_id  : original.source_document_id,
      source_document_type: 'sales_order',
      original_invoice_id : original.id,
      untaxed_amount      : netTotal,
      tax_amount          : taxTotal,
      gross_amount        : grossTotal,
      amount_paid         : 0,
      amount_outstanding  : grossTotal,
      tax_code            : 'PPN-NONLUX',
      tax_rate            : 0.11,
      payment_terms       : original.payment_terms,
      invoice_date        : new Date().toISOString().split('T')[0],
      due_date            : null,
      created_by          : createdBy,
      posted_by           : null,
      posted_at           : null,
      lines               : computedLines,
    };

    store.state.invoices.push(cn);

    // SR-13: reverse COGS for saleable returns
    for (const line of computedLines) {
      if (line.isSaleable && line.unitCost > 0) {
        const reversalAmt = line.quantity * line.unitCost;
        postJournalEntry({
          invoiceDocNumber: docNumber,
          journalType: 'stock',
          reference  : `COGS-REVERSAL-${docNumber}-${line.variantSku}`,
          debitAcc   : COA.INVENTORY,
          creditAcc  : COA.COGS,
          amount     : reversalAmt,
          description: `COGS Reversal retur layak jual - ${docNumber}`,
          postedBy   : createdBy,
        });
      }
    }

    store.logAudit({
      document_id: docNumber,
      action     : 'CREDIT_NOTE_CREATED',
      actor      : createdBy,
      details    : `Credit Note ${docNumber} dibuat dari ${original.document_number}. Net: Rp${netTotal}, PPN: Rp${taxTotal}, Gross: Rp${grossTotal}. SR-13 ceiling applied.`,
      result     : 'Status: draft',
    });
    store.persist();
    return cn;
  }

  // ── 3-Way Match Validator ─────────────────────────────────────────────────

  /**
   * Validasi 3-way match untuk Vendor Bill.
   * @returns {{ isValid, violations, billableQty, ... }}
   */
  validateThreeWayMatch({ poId, variantSku, orderedQty, acceptedQty, alreadyBilled, requestBillQty }) {
    const violations  = [];
    const billableQty = Math.max(0, acceptedQty - alreadyBilled);

    if (acceptedQty <= 0) {
      violations.push(`Receipt belum ada atau accepted qty = 0 untuk ${variantSku} pada ${poId}.`);
    }

    if (requestBillQty > billableQty) {
      violations.push(
        `SR-06: Request bill qty ${requestBillQty} melebihi billable qty ` +
        `${billableQty} (accepted ${acceptedQty} − already billed ${alreadyBilled}).`
      );
    }

    if (requestBillQty > orderedQty) {
      violations.push(`SR-05/SR-21: Request bill qty ${requestBillQty} melebihi ordered qty ${orderedQty} pada PO ${poId}.`);
    }

    return { poId, variantSku, orderedQty, acceptedQty, alreadyBilled, billableQty, requestBillQty, isValid: violations.length === 0, violations };
  }

  // ── Journal Balance Check ─────────────────────────────────────────────────

  /**
   * Verifikasi total debit = total kredit dari semua journal entries di store.
   */
  verifyJournalBalance() {
    const entries = store.state.journal_entries || [];
    const active  = entries.filter(e => !e.is_reversed);

    const debits  = {};
    const credits = {};
    for (const e of active) {
      debits[e.debit_account]   = (debits[e.debit_account]   || 0) + e.amount;
      credits[e.credit_account] = (credits[e.credit_account] || 0) + e.amount;
    }

    const totalDebit  = Object.values(debits).reduce((s, v) => s + v, 0);
    const totalCredit = Object.values(credits).reduce((s, v) => s + v, 0);

    return { balanced: totalDebit === totalCredit, totalDebit, totalCredit, entryCount: active.length, debits, credits };
  }

  // ── Dashboard helpers (T-33, T-34) ───────────────────────────────────────

  /**
   * Ambil KPI untuk Operational Dashboard (F-20, SR-19).
   * Hanya menghitung dokumen 'posted' — draft tidak menggelembungkan balance.
   */
  getDashboardKPIs() {
    const invoices = store.state.invoices || [];
    const posted   = invoices.filter(inv => inv.status === 'posted' || inv.status === 'paid');

    const totalAR = posted
      .filter(inv => inv.invoice_type === 'customer_invoice')
      .reduce((s, inv) => s + inv.amount_outstanding, 0);

    const totalAP = posted
      .filter(inv => inv.invoice_type === 'vendor_bill')
      .reduce((s, inv) => s + inv.amount_outstanding, 0);

    return {
      total_ar     : 1500000 + totalAR,   // opening AR Rp1.500.000
      total_ap     : 700000  + totalAP,   // opening AP Rp700.000
      open_invoices: invoices.filter(inv => inv.invoice_type === 'customer_invoice' && inv.status === 'posted').length,
      open_bills   : invoices.filter(inv => inv.invoice_type === 'vendor_bill'      && inv.status === 'posted').length,
    };
  }
}

module.exports = new AccountingInvoicingService();
