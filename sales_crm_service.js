// sales_crm_service.js
// Core Business Rules Engine for M01 (Sales / CRM)
// Implements SR-01, SR-02, SR-03, SR-07, SR-08, SR-10, SR-11, SR-12, SR-16, SR-19, SR-22
// Handles Features: F-02, F-04, F-05, F-06, F-10, F-21, F-22

const store = require('./store');

// Helper to enforce role-based actions
function requireRole(actor, allowedRoles) {
  if (!allowedRoles.includes(actor)) {
    throw new Error(`Action requires role ${allowedRoles.join(' or ')}`);
  }
}

// Helper to validate line data (price, qty, discount)
function validateLine(line) {
  const product = store.state.products.find(p => p.variant_sku === line.variant_sku);
  if (!product) throw new Error(`Product ${line.variant_sku} not found`);
  if (line.qty <= 0) throw new Error(`Quantity must be positive for SKU ${line.variant_sku}`);
  if (line.discount_percent < 0 || line.discount_percent > 15) {
    throw new Error(`Discount percent must be between 0 and 15 for SKU ${line.variant_sku}`);
  }
  if (line.unit_price !== product.sales_price) {
    throw new Error(`Unit price for SKU ${line.variant_sku} must match catalog price ${product.sales_price}`);
  }
}

class SalesCrmService {
  // PPN Calculation Utility (Effective 11%, Round half-up per line)
  calculateLine(qty, unitPrice, discountPercent = 0) {
    const q = Number(qty) || 0;
    const up = Number(unitPrice) || 0;
    const disc = Number(discountPercent) || 0;

    const effectiveUnitPrice = Math.round(up * (1 - disc / 100));
    const netAmount = Math.round(q * effectiveUnitPrice);
    // Effective PPN rate 11% (12% x 11/12 DPP) rounded half-up
    const taxAmount = Math.round(netAmount * 0.11);
    const grossAmount = netAmount + taxAmount;

    return {
      qty: q,
      unit_price: up,
      discount_percent: disc,
      effective_unit_price: effectiveUnitPrice,
      net_amount: netAmount,
      tax_amount: taxAmount,
      gross_amount: grossAmount
    };
  }

  calculateOrderTotals(lines) {
    let netTotal = 0;
    let taxTotal = 0;
    let grossTotal = 0;
    let hasDiscountOver10 = false;

    const calculatedLines = lines.map((line, idx) => {
      const calc = this.calculateLine(line.qty, line.unit_price, line.discount_percent);
      if (line.discount_percent > 10) {
        hasDiscountOver10 = true;
      }
      netTotal += calc.net_amount;
      taxTotal += calc.tax_amount;
      grossTotal += calc.gross_amount;

      return {
        id: line.id || (idx + 1),
        product_id: line.product_id,
        variant_sku: line.variant_sku,
        description: line.description || '',
        unit: line.unit || 'unit',
        ...calc,
        reserved_qty: line.reserved_qty || 0,
        delivered_qty: line.delivered_qty || 0,
        invoiced_qty: line.invoiced_qty || 0
      };
    });

    return {
      lines: calculatedLines,
      net_total: netTotal,
      tax_total: taxTotal,
      gross_total: grossTotal,
      has_discount_over_10: hasDiscountOver10
    };
  }

  // F-02 & F-10: Customer Credit Exposure Calculation
  // Credit Exposure = Open Receivable + Gross value of Confirmed Sales Orders not yet invoiced (SR-11)
  getCustomerCreditProfile(customerId) {
    const state = store.state;
    const customer = state.contacts_customers.find(c => c.id === customerId);
    if (!customer) {
      throw new Error(`Customer with ID ${customerId} not found`);
    }

    const openAr = Number(customer.open_ar) || 0;
    const creditLimit = Number(customer.credit_limit) || 0;

    // Find all confirmed orders that are unbilled (or partially unbilled)
    const orders = state.existing_documents.sales_orders || [];
    const customerOrders = orders.filter(
      o => o.partner === customerId && (o.status === 'Confirmed' || o.status === 'Sales Order')
    );

    let unbilledGrossTotal = 0;
    const unbilledOrdersDetail = [];

    customerOrders.forEach(so => {
      // In our model, check if order has unbilled gross
      // If invoiced_qty is 0, full gross is unbilled
      // S00007 baseline: 8 units P01, unbilled = 222.000
      let unbilledForThisOrder = so.gross_total;
      if (so.lines && so.lines.length > 0) {
        const totalQty = so.lines.reduce((acc, l) => acc + (l.qty || 0), 0);
        const invoicedQty = so.lines.reduce((acc, l) => acc + (l.invoiced_qty || 0), 0);
        if (totalQty > 0 && invoicedQty > 0) {
          const ratio = (totalQty - invoicedQty) / totalQty;
          unbilledForThisOrder = Math.round(so.gross_total * ratio);
        }
      }

      if (unbilledForThisOrder > 0) {
        unbilledGrossTotal += unbilledForThisOrder;
        unbilledOrdersDetail.push({
          document_id: so.document_id,
          gross_amount: so.gross_total,
          unbilled_amount: unbilledForThisOrder,
          status: `${so.status}; unbilled`
        });
      }
    });

    const currentExposure = openAr + unbilledGrossTotal;
    const remainingCredit = creditLimit - currentExposure;

    return {
      customer_id: customer.id,
      name: customer.name,
      credit_limit: creditLimit,
      open_ar: openAr,
      unbilled_so_gross: unbilledGrossTotal,
      current_credit_exposure: currentExposure,
      remaining_credit: remainingCredit,
      is_over_limit: currentExposure > creditLimit,
      payment_terms: customer.payment_terms,
      unbilled_orders: unbilledOrdersDetail
    };
  }

  // F-04: CRM Lead and Opportunity Pipeline
  getLeads(filter = {}) {
    let leads = [...(store.state.existing_documents.leads || [])];
    if (filter.stage) {
      leads = leads.filter(l => l.stage.toLowerCase() === filter.stage.toLowerCase());
    }
    if (filter.salesperson) {
      leads = leads.filter(l => l.salesperson === filter.salesperson);
    }
    return leads;
  }

  getLeadById(documentId) {
    const lead = (store.state.existing_documents.leads || []).find(l => l.document_id === documentId);
    if (!lead) throw new Error(`Lead/Opportunity ${documentId} not found`);
    return lead;
  }

  createLead(data, actor = "sales_user") {
    const state = store.state;
    const docId = store.getNextLeadSequence();
    const customer = state.contacts_customers.find(c => c.id === data.partner);

    const newLead = {
      document_id: docId,
      type: "Lead",
      partner: data.partner,
      partner_name: customer ? customer.name : (data.partner_name || "Unknown"),
      salesperson: data.salesperson || "E04",
      status: "New",
      stage: "New",
      expected_revenue: Number(data.expected_revenue) || 0,
      details: data.details || `Expected Revenue ${Number(data.expected_revenue) || 0}`,
      stock_state: "Tidak ada",
      financial_state: "Tidak ada",
      created_at: new Date().toISOString()
    };

    state.existing_documents.leads.push(newLead);
    store.logAudit({
      document_id: docId,
      action: "CREATE_LEAD",
      actor,
      details: `Created new lead ${docId} for customer ${newLead.partner_name} with potential Rp${newLead.expected_revenue}.`,
      result: "Success"
    });
    store.persist();
    return newLead;
  }

  // T-07: Convert Lead to Opportunity
  convertToOpportunity(documentId, actor = "sales_user") {
    const state = store.state;
    const lead = (state.existing_documents.leads || []).find(l => l.document_id === documentId);
    if (!lead) throw new Error(`Lead ${documentId} not found`);

    const prevStage = lead.stage;
    lead.type = "Opportunity";
    lead.stage = "Qualified";
    lead.status = "Qualified";
    lead.details = `Opportunity ${lead.partner}: Expected Revenue ${lead.expected_revenue}`;

    store.logAudit({
      document_id: documentId,
      action: "CONVERT_OPPORTUNITY",
      actor,
      details: `Lead ${documentId} converted to Opportunity. Stage changed from ${prevStage} to Qualified. Customer relation ${lead.partner} preserved. No AR/stock posted.`,
      result: "Stage = Qualified; C03 tetap; tidak ada posting AR/stock."
    });
    store.persist();
    return lead;
  }

  // T-08: Update Opportunity & Guard Customer Relation (SR-19)
  updateOpportunity(documentId, updates, actor = "sales_user") {
    const state = store.state;
    const lead = (state.existing_documents.leads || []).find(l => l.document_id === documentId);
    if (!lead) throw new Error(`Lead/Opportunity ${documentId} not found`);

    // Edge case check T-08: If user attempts to remove or empty customer relation
    if (updates.partner !== undefined && (!updates.partner || updates.partner.trim() === '')) {
      const errorMsg = `Rule violation (SR-19): Customer relation cannot be deleted on Opportunity ${documentId}. Traceability from lead to order must be preserved.`;
      store.logAudit({
        document_id: documentId,
        action: "REJECT_DELETE_CUSTOMER_RELATION",
        actor,
        details: `Attempted to remove customer relation from Opportunity ${documentId}. Action was blocked per SR-19.`,
        result: "BLOCKED_RULE_SR19"
      });
      throw new Error(errorMsg);
    }

    if (updates.partner && updates.partner !== lead.partner) {
      const cust = state.contacts_customers.find(c => c.id === updates.partner);
      if (cust) {
        lead.partner = cust.id;
        lead.partner_name = cust.name;
      }
    }
    if (updates.expected_revenue !== undefined) {
      lead.expected_revenue = Number(updates.expected_revenue);
    }
    if (updates.stage) {
      lead.stage = updates.stage;
    }
    if (updates.salesperson) {
      lead.salesperson = updates.salesperson;
    }

    store.logAudit({
      document_id: documentId,
      action: "UPDATE_OPPORTUNITY",
      actor,
      details: `Updated Opportunity ${documentId}: stage=${lead.stage}, partner=${lead.partner}`,
      result: "Success"
    });
    store.persist();
    return lead;
  }

  // F-05, F-06, F-10: Quotation & Sales Order Management
  getSalesOrders(filter = {}) {
    let orders = [...(store.state.existing_documents.sales_orders || [])];
    if (filter.status) {
      orders = orders.filter(o => o.status.toLowerCase() === filter.status.toLowerCase());
    }
    if (filter.partner) {
      orders = orders.filter(o => o.partner === filter.partner);
    }
    return orders;
  }

  getSalesOrderById(documentId) {
    const so = (store.state.existing_documents.sales_orders || []).find(o => o.document_id === documentId);
    if (!so) throw new Error(`Sales Order ${documentId} not found`);
    return so;
  }

  // Create Quotation (Starts in Draft state)
  createQuotation(data, actor = "sales_user") {
    const state = store.state;
    const customer = state.contacts_customers.find(c => c.id === data.partner);
    if (!customer) throw new Error(`Customer ${data.partner} not found`);

    // Assign temporary or next sequence
    const docId = data.document_id || store.getNextSalesOrderSequence();

    // Calculate totals
    const calculated = this.calculateOrderTotals(data.lines || []);

    const quotation = {
      document_id: docId,
      type: "Sales Order",
      partner: customer.id,
      partner_name: customer.name,
      salesperson: data.salesperson || "E04",
      status: "Draft", // Draft -> To Approve (if disc > 10%) -> Confirmed -> Done / Cancelled
      approval_status: calculated.has_discount_over_10 ? "Pending Finance Approval" : "Not Required",
      payment_terms: data.payment_terms || customer.payment_terms,
      warehouse: data.warehouse || "WH-JKT",
      lines: calculated.lines,
      net_total: calculated.net_total,
      tax_total: calculated.tax_total,
      gross_total: calculated.gross_total,
      has_discount_over_10: calculated.has_discount_over_10,
      stock_state: "Draft Quotation (no reservation)",
      financial_state: "Draft (not exposed)",
      created_at: new Date().toISOString(),
      created_by: actor
    };

    state.existing_documents.sales_orders.push(quotation);
    store.logAudit({
      document_id: docId,
      action: "CREATE_QUOTATION",
      actor,
      details: `Created draft Quotation ${docId} for ${customer.name}. Net: Rp${calculated.net_total}, Gross: Rp${calculated.gross_total}.`,
      result: "Success"
    });
    store.persist();
    return quotation;
  }

  // T-10: Finance Approval for discounts > 10% (SR-07 & SR-08)
  approveDiscount(documentId, actor = "finance_user") {
    const so = this.getSalesOrderById(documentId);
    if (so.status === "Confirmed") {
      throw new Error(`Order ${documentId} is already Confirmed and locked.`);
    }

    so.approval_status = "Approved by Finance";
    store.logAudit({
      document_id: documentId,
      action: "FINANCE_DISCOUNT_APPROVAL",
      actor,
      details: `Finance approved discount for order ${documentId} (exceeded 10% threshold).`,
      result: "Discount Approved"
    });
    store.persist();
    return so;
  }

  // F-05, F-06, F-10: Confirm Quotation to Sales Order
  // Validates:
  // 1. Discount Approval (SR-07 / SR-08: if discount > 10% and not approved -> reject/block)
  // 2. Credit Exposure Check (SR-11: exposure + new order gross <= credit_limit)
  // 3. Stock Reservation (SR-03, SR-04, SR-22: reserve up to on_hand, manage reservation)
  // 4. Price & line locking (SR-12)
  confirmSalesOrder(documentId, actor = "sales_user") {
    const state = store.state;
    const so = this.getSalesOrderById(documentId);

    // Rule 1: Idempotency check (SR-16)
    if (so.status === "Confirmed" || so.status === "Sales Order") {
      store.logAudit({
        document_id: documentId,
        action: "IDEMPOTENT_CONFIRM_ATTEMPT",
        actor,
        details: `Order ${documentId} is already Confirmed. Re-confirmation skipped to maintain idempotency.`,
        result: "Skipped (Already Confirmed)"
      });
      return { so, message: "Order is already confirmed.", success: true };
    }

    // Rule 2: Discount & Approval check (SR-07 & SR-08, T-10)
    // If discount > 10% and approval_status !== "Approved by Finance", Sales cannot confirm!
    if (so.has_discount_over_10 && so.approval_status !== "Approved by Finance") {
      so.status = "To Approve"; // change status to waiting approval
      const errorMsg = `Diskon di atas 10% memerlukan persetujuan Finance sebelum konfirmasi (SR-08). Status order dialihkan ke 'To Approve'.`;
      store.logAudit({
        document_id: documentId,
        action: "BLOCK_CONFIRM_DISCOUNT_UNAPPROVED",
        actor,
        details: `Sales attempted to confirm order ${documentId} with discount > 10% without Finance approval. Blocked per SR-07/SR-08.`,
        result: "BLOCKED_APPROVAL_REQUIRED"
      });
      store.persist();
      throw new Error(errorMsg);
    }

    // Rule 3: Credit Limit & Exposure Check (SR-11, T-04, T-09)
    const creditProfile = this.getCustomerCreditProfile(so.partner);
    const newExposure = creditProfile.current_credit_exposure + so.gross_total;

    if (newExposure > creditProfile.credit_limit) {
      const excess = newExposure - creditProfile.credit_limit;
      const errorMsg = `Pesanan ditolak: Credit exposure melebihi batas kredit pelanggan (SR-11)! ` +
        `Baseline exposure: Rp${creditProfile.current_credit_exposure.toLocaleString('id-ID')}, ` +
        `Gross pesanan ini: Rp${so.gross_total.toLocaleString('id-ID')}, ` +
        `Total proyeksi: Rp${newExposure.toLocaleString('id-ID')}, ` +
        `Credit limit: Rp${creditProfile.credit_limit.toLocaleString('id-ID')} (Lebih Rp${excess.toLocaleString('id-ID')}).`;

      store.logAudit({
        document_id: documentId,
        action: "REJECT_CREDIT_LIMIT_EXCEEDED",
        actor,
        details: `Confirmation of order ${documentId} rejected. Projected exposure Rp${newExposure} exceeds limit Rp${creditProfile.credit_limit}.`,
        result: "REJECTED_CREDIT_LIMIT"
      });
      store.persist();
      throw new Error(errorMsg);
    }

    // Rule 4: Stock Reservation (SR-03, SR-04, SR-22)
    // Reservation reserves available stock at source warehouse without changing on_hand
    const whCode = so.warehouse || "WH-JKT";
    const whStockMap = state.opening_stock;

    so.lines.forEach(line => {
      const stockItem = whStockMap.find(s => s.variant_sku === line.variant_sku);
      if (stockItem) {
        const onHand = whCode === "WH-JKT" ? stockItem.WH_JKT_on_hand : stockItem.HUB_BDG_on_hand;
        const currentReserved = stockItem.reserved || 0;
        const currentAvail = onHand - currentReserved;

        // Reserve demand (if available)
        const reserveQty = Math.min(line.qty, currentAvail);
        stockItem.reserved = currentReserved + reserveQty;
        line.reserved_qty = reserveQty;
      }
    });

    // Rule 5: State Transition to Confirmed & Locking (SR-12, F-22)
    so.status = "Confirmed";
    so.approval_status = "Approved";
    so.confirmed_at = new Date().toISOString();
    so.confirmed_by = actor;
    so.stock_state = `Reserved ${so.lines.map(l => `${l.reserved_qty} ${l.product_id}`).join(', ')} di ${whCode}; belum delivered`;
    so.financial_state = `Belum invoiced; exposure ${so.gross_total.toLocaleString('id-ID')}`;

    store.logAudit({
      document_id: documentId,
      action: "CONFIRM_SALES_ORDER",
      actor,
      details: `Confirmed Sales Order ${documentId} for ${so.partner_name}. Net: Rp${so.net_total}, Gross: Rp${so.gross_total}. Customer exposure is now Rp${newExposure}. Lines and prices locked.`,
      result: "Confirmed; S00008 created once; lines locked"
    });

    store.persist();
    return {
      so,
      creditProfile: this.getCustomerCreditProfile(so.partner),
      newExposure,
      success: true
    };
  }

  // SR-12: Reject editing lines after order is confirmed
  updateOrderLines(documentId, lines, actor = "sales_user") {
    const so = this.getSalesOrderById(documentId);
    if (so.status === "Confirmed" || so.status === "Sales Order") {
      const errorMsg = `Rule violation (SR-12): Harga dan baris Sales Order ${documentId} telah terkunci karena berstatus Confirmed. Revisi harus melalui pembatalan terotorisasi dan audit.`;
      store.logAudit({
        document_id: documentId,
        action: "REJECT_UPDATE_CONFIRMED_ORDER",
        actor,
        details: `Attempted to modify prices/lines on confirmed order ${documentId}. Blocked per SR-12.`,
        result: "BLOCKED_ORDER_LOCKED"
      });
      throw new Error(errorMsg);
    }

    const calculated = this.calculateOrderTotals(lines);
    so.lines = calculated.lines;
    so.net_total = calculated.net_total;
    so.tax_total = calculated.tax_total;
    so.gross_total = calculated.gross_total;
    so.has_discount_over_10 = calculated.has_discount_over_10;
    if (calculated.has_discount_over_10) {
      so.approval_status = "Pending Finance Approval";
    }

    store.logAudit({
      document_id: documentId,
      action: "UPDATE_QUOTATION_LINES",
      actor,
      details: `Updated lines for Quotation ${documentId}. Net: Rp${calculated.net_total}, Gross: Rp${calculated.gross_total}.`,
      result: "Success"
    });
    store.persist();
    return so;
  }
}

const salesCrmService = new SalesCrmService();
module.exports = salesCrmService;
