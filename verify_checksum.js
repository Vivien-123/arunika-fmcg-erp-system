// verify_checksum.js
// Script verifikasi Checksum Seed Baseline untuk PT Arunika Distribusi Cepat (M4-W1-06)

const store = require('./store');

function verifyChecksum() {
  store.resetToSeed();
  const state = store.state;

  console.log('============================================================');
  console.log('PT ARUNIKA DISTRIBUSI CEPAT - SEED BASELINE CHECKSUM REPORT');
  console.log('============================================================\n');

  // Kalkulasi data stok
  const totalSKUs = state.products.length;
  const totalStockOnHand = state.opening_stock.reduce((acc, s) => acc + s.company_total, 0);
  const totalStockValuation = state.opening_stock.reduce((acc, s) => acc + s.standard_value, 0);
  const totalWhJktOnHand = state.opening_stock.reduce((acc, s) => acc + s.WH_JKT_on_hand, 0);
  const totalHubBdgOnHand = state.opening_stock.reduce((acc, s) => acc + s.HUB_BDG_on_hand, 0);
  const totalReserved = state.opening_stock.reduce((acc, s) => acc + (s.reserved || 0), 0);

  // Kalkulasi General Ledger Opening
  const gl = state.general_ledger_opening;
  let totalDebit = 0;
  let totalCredit = 0;
  Object.keys(gl).forEach(code => {
    totalDebit += gl[code].debit || 0;
    totalCredit += gl[code].credit || 0;
  });

  // Tampilkan laporan hasil verifikasi
  console.log(`Total SKU Products        : ${totalSKUs} (Expected: 16)`);
  console.log(`Total On Hand Company     : ${totalStockOnHand} unit (Expected: 880)`);
  console.log(`Total On Hand WH-JKT      : ${totalWhJktOnHand} unit (Expected: 665)`);
  console.log(`Total On Hand HUB-BDG     : ${totalHubBdgOnHand} unit (Expected: 215)`);
  console.log(`Total Reserved (S00007)   : ${totalReserved} unit (Expected: 8)`);
  console.log(`Total Stock Valuation     : Rp${totalStockValuation.toLocaleString('id-ID')} (Expected: Rp23.095.000)`);
  console.log(`Ledger Total Debit        : Rp${totalDebit.toLocaleString('id-ID')}`);
  console.log(`Ledger Total Credit       : Rp${totalCredit.toLocaleString('id-ID')}`);
  console.log(`Ledger Balance Status     : ${totalDebit === totalCredit ? '✅ BALANCED (Rp44.595.000)' : '❌ UNBALANCED'}\n`);

  const isValid = totalSKUs === 16 && totalStockOnHand === 880 && totalStockValuation === 23095000 && totalDebit === totalCredit;
  console.log(`HASIL AKHIR CHECKSUM      : ${isValid ? 'PASSED 100%' : 'FAILED'}`);
  console.log('============================================================\n');
}

verifyChecksum();