// purchase_inventory_service.js
// Service Layer untuk Modul M02: Purchase & Inventory (PT Arunika Distribusi Cepat)
// Memenuhi Fitur: F-03, F-07, F-08, F-11, F-12, F-17

const store = require('./store');

class PurchaseInventoryService {

  // -------------------------------------------------------------------
  // F-03: Katalog Produk & Status Stok Real-time
  // -------------------------------------------------------------------
  getProductsWithStock() {
    return store.state.products.map(p => {
      const stock = store.state.opening_stock.find(s => s.variant_sku === p.variant_sku);
      const whJkt = stock ? stock.WH_JKT_on_hand : 0;
      const hubBdg = stock ? stock.HUB_BDG_on_hand : 0;
      const reserved = stock ? stock.reserved : 0;
      const total = stock ? stock.company_total : 0;
      const availableWhJkt = whJkt - reserved;

      return {
        ...p,
        wh_jkt_on_hand: whJkt,
        hub_bdg_on_hand: hubBdg,
        reserved_qty: reserved,
        company_total: total,
        available_wh_jkt: availableWhJkt
      };
    });
  }

  // -------------------------------------------------------------------
  // F-17: Enforcement Anti-Negative Stock (SR-04)
  // -------------------------------------------------------------------
  validateStockAvailability(variantSku, locationCode, requestedQty) {
    const stock = store.state.opening_stock.find(s => s.variant_sku === variantSku);
    if (!stock) {
      throw new Error(`SKU ${variantSku} tidak ditemukan dalam sistem persediaan.`);
    }

    let onHand = 0;
    if (locationCode === 'WH-JKT') {
      onHand = stock.WH_JKT_on_hand;
    } else if (locationCode === 'HUB-BDG') {
      onHand = stock.HUB_BDG_on_hand;
    } else {
      throw new Error(`Kode lokasi gudang '${locationCode}' tidak valid.`);
    }

    const available = onHand - (locationCode === 'WH-JKT' ? stock.reserved : 0);

    if (requestedQty > available) {
      store.logAudit({
        document_id: "STOCK_CHECK",
        action: "REJECT_NEGATIVE_STOCK",
        actor: "system",
        details: `Upaya mengeluarkan ${requestedQty} unit ${variantSku} di ${locationCode} ditolak. Stok tersedia: ${available} (On-hand: ${onHand}, Reserved: ${stock.reserved}).`,
        result: "BLOCKED_NEGATIVE_STOCK_PREVENTED"
      });
      throw new Error(`Aturan Bisnis SR-04 Violasi: Stok fisik tidak mencukupi di ${locationCode}! Diminta: ${requestedQty}, Tersedia: ${available} (On-hand: ${onHand}). Transaksi dibatalkan untuk mencegah stok negatif.`);
    }

    return true;
  }

  // -------------------------------------------------------------------
  // F-12: Engine Pemilihan Lot Berdasarkan FEFO (First Expired, First Out)
  // -------------------------------------------------------------------
  allocateLotFEFO(variantSku, locationCode, qtyRequested) {
    const lots = store.state.product_lots.filter(l =>
      l.variant_sku === variantSku &&
      l.location === locationCode &&
      l.qty > 0
    );

    if (lots.length === 0) {
      return {
        allocated_lots: [],
        remaining_qty_unallocated: qtyRequested,
        is_fully_allocated: false
      };
    }

    // Urutkan berdasarkan expiry_date terdekat (FEFO)
    const sortedLots = [...lots].sort((a, b) => new Date(a.expiry_date) - new Date(b.expiry_date));

    let needed = qtyRequested;
    const allocation = [];

    for (const lot of sortedLots) {
      if (needed <= 0) break;
      const take = Math.min(lot.qty, needed);
      allocation.push({
        lot_id: lot.lot_id,
        expiry_date: lot.expiry_date,
        qty_taken: take
      });
      needed -= take;
    }

    return {
      allocated_lots: allocation,
      remaining_qty_unallocated: needed,
      is_fully_allocated: needed === 0
    };
  }

  // -------------------------------------------------------------------
  // F-07: Pembelian (Purchase Order) & Pricelist Vendor Validation
  // -------------------------------------------------------------------
  createPurchaseOrder({ partner, warehouse = 'WH-JKT', lines = [] }, actor = 'purchase_user') {
    const vendor = store.state.contacts_vendors.find(v => v.id === partner);
    if (!vendor) {
      throw new Error(`Vendor dengan ID '${partner}' tidak ditemukan.`);
    }

    if (!lines || lines.length === 0) {
      throw new Error("Purchase Order wajib memiliki minimal 1 baris produk.");
    }

    const processedLines = lines.map((line, index) => {
      const product = store.state.products.find(p => p.id === line.product_id || p.variant_sku === line.variant_sku);
      if (!product) {
        throw new Error(`Produk '${line.product_id || line.variant_sku}' tidak valid.`);
      }

      // Validasi pricelist vendor
      const pricelist = store.state.vendor_pricelist.find(vp =>
        vp.vendor_id === partner && vp.variant_sku === product.variant_sku
      );

      const unitPrice = line.unit_price || (pricelist ? pricelist.unit_price : product.cost);
      const qty = line.qty;

      if (qty <= 0) {
        throw new Error("Kuantitas pemesanan harus lebih besar dari 0.");
      }

      const netAmount = qty * unitPrice;
      const taxRate = 0.11; // Effective PPN 11%
      const taxAmount = Math.round(netAmount * taxRate);
      const grossAmount = netAmount + taxAmount;

      return {
        id: index + 1,
        product_id: product.id,
        variant_sku: product.variant_sku,
        description: line.description || `${product.name} ${product.size}`,
        qty,
        unit: product.unit,
        unit_price: unitPrice,
        net_amount: netAmount,
        tax_rate: taxRate,
        tax_amount: taxAmount,
        gross_amount: grossAmount,
        received_qty: 0,
        accepted_qty: 0,
        rejected_qty: 0,
        billed_qty: 0
      };
    });

    const netTotal = processedLines.reduce((acc, l) => acc + l.net_amount, 0);
    const taxTotal = processedLines.reduce((acc, l) => acc + l.tax_amount, 0);
    const grossTotal = netTotal + taxTotal;

    const poId = this.getNextPoSequence();

    const po = {
      document_id: poId,
      type: "Purchase Order",
      partner,
      partner_name: vendor.name,
      status: "Confirmed",
      warehouse,
      lines: processedLines,
      net_total: netTotal,
      tax_total: taxTotal,
      gross_total: grossTotal,
      details: `${processedLines.reduce((acc, l) => acc + l.qty, 0)} items dari ${vendor.name}`,
      stock_state: "0 received / 0 accepted",
      financial_state: "0 billed / 0 paid",
      created_at: new Date().toISOString(),
      created_by: actor
    };

    store.state.existing_documents.purchase_orders.push(po);

    store.logAudit({
      document_id: poId,
      action: "CREATE_PURCHASE_ORDER",
      actor,
      details: `Membuat PO ${poId} untuk vendor ${vendor.name}. Net: Rp${netTotal.toLocaleString('id-ID')}, Gross: Rp${grossTotal.toLocaleString('id-ID')}.`,
      result: "Confirmed"
    });

    store.persist();
    return po;
  }

  getNextPoSequence() {
    const pos = store.state.existing_documents.purchase_orders || [];
    let maxNum = 7; // Baseline PO00007
    pos.forEach(po => {
      const match = po.document_id.match(/PO(\d+)/i);
      if (match) {
        const num = parseInt(match[1], 10);
        if (num > maxNum) maxNum = num;
      }
    });
    return `PO${String(maxNum + 1).padStart(5, '0')}`;
  }

  // -------------------------------------------------------------------
  // F-08: Penerimaan Barang (Goods Receipt) & Akuntansi Interim
  // -------------------------------------------------------------------
  receiveGoods(poId, receiptLines = [], warehouseCode = 'WH-JKT', actor = 'warehouse_user') {
    const pos = store.state.existing_documents.purchase_orders || [];
    const po = pos.find(p => p.document_id === poId);

    if (!po) {
      throw new Error(`Purchase Order '${poId}' tidak ditemukan.`);
    }

    if (po.status === 'Received' || po.status === 'Done') {
      throw new Error(`Purchase Order '${poId}' sudah diproses penerimaannya sebelumnya.`);
    }

    let totalValuationAdded = 0;

    po.lines.forEach(line => {
      const inputLine = receiptLines.find(r => r.product_id === line.product_id || r.variant_sku === line.variant_sku);
      const qtyToReceive = inputLine ? inputLine.qty_received : line.qty;

      if (qtyToReceive > 0) {
        line.received_qty += qtyToReceive;
        line.accepted_qty += qtyToReceive;

        // Update stok gudang
        const stock = store.state.opening_stock.find(s => s.variant_sku === line.variant_sku);
        if (stock) {
          if (warehouseCode === 'WH-JKT') {
            stock.WH_JKT_on_hand += qtyToReceive;
          } else if (warehouseCode === 'HUB-BDG') {
            stock.HUB_BDG_on_hand += qtyToReceive;
          }
          stock.company_total += qtyToReceive;
          stock.standard_value += (qtyToReceive * stock.standard_cost);
        }

        totalValuationAdded += (qtyToReceive * line.unit_price);
      }
    });

    po.status = "Received";
    po.stock_state = `Goods Received di ${warehouseCode}`;

    // Jurnal Interim Akuntansi: Debit Persediaan (1300), Kredit Stock Interim (1350)
    if (store.state.general_ledger_opening["1300"]) {
      store.state.general_ledger_opening["1300"].debit += totalValuationAdded;
    }
    if (store.state.general_ledger_opening["1350"]) {
      store.state.general_ledger_opening["1350"].credit += totalValuationAdded;
    }

    store.logAudit({
      document_id: poId,
      action: "GOODS_RECEIPT",
      actor,
      details: `Penerimaan fisik barang untuk ${poId} di ${warehouseCode}. Stok bertambah. Jurnal clearing Rp${totalValuationAdded.toLocaleString('id-ID')} dicatat ke Stock Interim (1350).`,
      result: "Goods Received & Stock Updated"
    });

    store.persist();
    return {
      po,
      valuation_added: totalValuationAdded,
      journal_entry: {
        debit_account: "1300 - Persediaan Barang",
        credit_account: "1350 - Stock Interim / Receipt Clearing",
        amount: totalValuationAdded
      }
    };
  }

  // -------------------------------------------------------------------
  // F-11: Mutasi / Transfer Antar Gudang (Internal Stock Transfer)
  // -------------------------------------------------------------------
  transferStockBetweenWarehouses({ variant_sku, source_wh, target_wh, qty }, actor = 'warehouse_mgr') {
    if (source_wh === target_wh) {
      throw new Error("Gudang asal dan gudang tujuan transfer tidak boleh sama.");
    }

    if (qty <= 0) {
      throw new Error("Kuantitas transfer harus lebih besar dari 0.");
    }

    // SR-04 Anti-Negative Stock Check pada gudang asal
    this.validateStockAvailability(variant_sku, source_wh, qty);

    const stock = store.state.opening_stock.find(s => s.variant_sku === variant_sku);

    // Eksekusi mutasi
    if (source_wh === 'WH-JKT') {
      stock.WH_JKT_on_hand -= qty;
      stock.HUB_BDG_on_hand += qty;
    } else if (source_wh === 'HUB-BDG') {
      stock.HUB_BDG_on_hand -= qty;
      stock.WH_JKT_on_hand += qty;
    }

    // Company total tetap sama!
    const transferId = `TRF-${Date.now().toString().slice(-6)}`;

    store.logAudit({
      document_id: transferId,
      action: "STOCK_TRANSFER",
      actor,
      details: `Transfer ${qty} unit SKU ${variant_sku} dari ${source_wh} ke ${target_wh}. Total stok perusahaan tidak berubah (${stock.company_total} unit).`,
      result: "Transfer Completed"
    });

    store.persist();

    return {
      transfer_id: transferId,
      variant_sku,
      source_wh,
      target_wh,
      qty_transferred: qty,
      updated_source_on_hand: source_wh === 'WH-JKT' ? stock.WH_JKT_on_hand : stock.HUB_BDG_on_hand,
      updated_target_on_hand: target_wh === 'HUB-BDG' ? stock.HUB_BDG_on_hand : stock.WH_JKT_on_hand,
      company_total: stock.company_total
    };
  }
}

module.exports = new PurchaseInventoryService();