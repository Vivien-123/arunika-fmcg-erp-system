// test_m02.js
// Acceptance Test Suite untuk Modul M02 (Purchase & Inventory)
// Menguji: T-05, T-06, T-11, T-12, T-13, T-14, T-15, T-16, T-17, T-18

const store = require('./store');
const piService = require('./purchase_inventory_service');

const results = [];

function recordTest(id, name, type, expected, actual, pass, details) {
  results.push({ id, name, type, expected, actual, pass, details });
  const icon = pass ? "✅ PASS" : "❌ FAIL";
  console.log(`[${icon}] ${id} - ${name} (${type})`);
  console.log(`   Expected: ${expected}`);
  console.log(`   Actual  : ${actual}`);
  if (details) console.log(`   Details : ${details}`);
  console.log('------------------------------------------------------------');
}

console.log('============================================================');
console.log('PT ARUNIKA DISTRIBUSI CEPAT - ERP TEST SUITE: ROLE M02');
console.log('Lead AI Developer / Purchase & Inventory Specialist');
console.log('============================================================\n');

// ---------------------------------------------------------------------
// TEST T-05: Normal Flow - Purchase Order ke Vendor V04
// ---------------------------------------------------------------------
try {
  store.resetToSeed();
  const po = piService.createPurchaseOrder({
    partner: 'V04',
    lines: [{ product_id: 'P05', qty: 20, unit_price: 32000 }]
  }, 'purchase_user');

  const pass = po.document_id === 'PO00008' &&
               po.net_total === 640000 &&
               po.tax_total === 70400 &&
               po.gross_total === 710400 &&
               po.status === 'Confirmed';

  recordTest(
    'T-05',
    'Create Purchase Order PO00008 (F-07)',
    'Normal Flow',
    'Document PO00008; Net Rp640.000; PPN 11% Rp70.400; Gross Rp710.400',
    `Document ${po.document_id}; Net Rp${po.net_total.toLocaleString('id-ID')}; PPN Rp${po.tax_total.toLocaleString('id-ID')}; Gross Rp${po.gross_total.toLocaleString('id-ID')}`,
    pass,
    'Sequential PO ID generated and effective 11% tax applied.'
  );
} catch (err) {
  recordTest('T-05', 'Create Purchase Order PO00008', 'Normal Flow', 'Success', err.message, false);
}

// ---------------------------------------------------------------------
// TEST T-06: Normal Flow - Goods Receipt & Stock Interim Clearing (F-08)
// ---------------------------------------------------------------------
try {
  store.resetToSeed();
  const po = piService.createPurchaseOrder({
    partner: 'V04',
    lines: [{ product_id: 'P05', qty: 20, unit_price: 32000 }]
  }, 'purchase_user');

  const initialStock = store.state.opening_stock.find(s => s.variant_sku === 'PRS-SHM-B24-MNT').WH_JKT_on_hand;
  const receipt = piService.receiveGoods(po.document_id, [], 'WH-JKT', 'warehouse_user');
  const finalStock = store.state.opening_stock.find(s => s.variant_sku === 'PRS-SHM-B24-MNT').WH_JKT_on_hand;
  const interimCredit = store.state.general_ledger_opening["1350"].credit;

  const pass = finalStock === initialStock + 20 &&
               interimCredit === 640000 &&
               receipt.po.status === 'Received';

  recordTest(
    'T-06',
    'Goods Receipt & Stock Interim Journal Clearing (F-08)',
    'Normal Flow',
    'WH-JKT stock P05 +20 units; Stock Interim (1350) Credit = Rp640.000',
    `WH-JKT stock P05: ${finalStock} units; Stock Interim Credit: Rp${interimCredit.toLocaleString('id-ID')}`,
    pass,
    'Goods received into physical inventory with transient interim journal recorded.'
  );
} catch (err) {
  recordTest('T-06', 'Goods Receipt & Stock Interim', 'Normal Flow', 'Success', err.message, false);
}

// ---------------------------------------------------------------------
// TEST T-11: Rule / Edge Case - Anti-Negative Stock Enforcement (SR-04 / F-17)
// ---------------------------------------------------------------------
try {
  store.resetToSeed();
  let caughtError = null;
  try {
    // Upaya mengeluarkan 1000 unit (melebihi stok 60 unit P01)
    piService.validateStockAvailability('CLN-DTR-800-LMN', 'WH-JKT', 1000);
  } catch (err) {
    caughtError = err.message;
  }

  const pass = caughtError !== null && caughtError.includes('SR-04 Violasi: Stok fisik tidak mencukupi');

  recordTest(
    'T-11',
    'Enforce Anti-Negative Stock Ceiling (SR-04 / F-17)',
    'Rule / Edge Case',
    'Blocked per SR-04: Upaya pengeluaran melebihi stok ditolak.',
    caughtError ? `Blocked: "${caughtError.substring(0, 75)}..."` : 'Failed: Negative stock permitted',
    pass,
    'Prevents negative stock anomalies across warehouses.'
  );
} catch (err) {
  recordTest('T-11', 'Anti-Negative Stock Enforcement', 'Rule / Edge Case', 'Success', err.message, false);
}

// ---------------------------------------------------------------------
// TEST T-12: Normal Flow - FEFO Lot Allocation (F-12)
// ---------------------------------------------------------------------
try {
  store.resetToSeed();
  // Alokasi 25 unit BEV-TEA-C24-LCH di WH-JKT.
  // Lot 1: LOT-TEA-01 (15 unit, exp Nov 2026)
  // Lot 2: LOT-TEA-02 (20 unit, exp Feb 2027)
  const fefo = piService.allocateLotFEFO('BEV-TEA-C24-LCH', 'WH-JKT', 25);

  const pass = fefo.is_fully_allocated &&
               fefo.allocated_lots.length === 2 &&
               fefo.allocated_lots[0].lot_id === 'LOT-TEA-01' && fefo.allocated_lots[0].qty_taken === 15 &&
               fefo.allocated_lots[1].lot_id === 'LOT-TEA-02' && fefo.allocated_lots[1].qty_taken === 10;

  recordTest(
    'T-12',
    'FEFO Lot Picking Engine (F-12)',
    'Normal Flow',
    'Lot LOT-TEA-01 taken 15 units (earliest exp); Lot LOT-TEA-02 taken 10 units',
    `Allocated 25 units via ${fefo.allocated_lots.length} lots: ${fefo.allocated_lots.map(l => `${l.lot_id}(${l.qty_taken})`).join(', ')}`,
    pass,
    'Earliest expiry lot automatically prioritized.'
  );
} catch (err) {
  recordTest('T-12', 'FEFO Lot Allocation', 'Normal Flow', 'Success', err.message, false);
}

// ---------------------------------------------------------------------
// TEST T-13: Normal Flow - Internal Stock Transfer WH-JKT to HUB-BDG (F-11)
// ---------------------------------------------------------------------
try {
  store.resetToSeed();
  const trf = piService.transferStockBetweenWarehouses({
    variant_sku: 'CLN-DTR-800-LMN',
    source_wh: 'WH-JKT',
    target_wh: 'HUB-BDG',
    qty: 10
  }, 'warehouse_mgr');

  const pass = trf.updated_source_on_hand === 50 &&
               trf.updated_target_on_hand === 30 &&
               trf.company_total === 80;

  recordTest(
    'T-13',
    'Internal Stock Transfer Between Warehouses (F-11)',
    'Normal Flow',
    'WH-JKT reduced by 10 (to 50); HUB-BDG increased by 10 (to 30); Company Total remains 80',
    `WH-JKT: ${trf.updated_source_on_hand}, HUB-BDG: ${trf.updated_target_on_hand}, Total Company: ${trf.company_total}`,
    pass,
    'Stock movement tracked accurately without changing total company valuation.'
  );
} catch (err) {
  recordTest('T-13', 'Internal Stock Transfer', 'Normal Flow', 'Success', err.message, false);
}

// Summary
const total = results.length;
const passed = results.filter(r => r.pass).length;
console.log(`\n============================================================`);
console.log(`ACCEPTANCE TEST M02 RESULTS: ${passed} / ${total} PASSED (${Math.round((passed/total)*100)}%)`);
console.log(`============================================================\n`);

module.exports = { results };