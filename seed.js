'use strict';

const fs = require('fs');
const path = require('path');
const { initializeDatabase } = require('./database');

const DB_PATH = path.join(__dirname, 'erp.db');

function resetDatabase() {
  // Delete existing database file
  if (fs.existsSync(DB_PATH)) {
    fs.unlinkSync(DB_PATH);
function truncateAndSeed(db) {
  const tables = [
    'journal_entry_lines', 'journal_entries', 'payments', 'invoice_lines', 'invoices',
    'receipt_lines', 'receipts', 'delivery_order_lines', 'delivery_orders',
    'sales_order_lines', 'sales_orders', 'purchase_order_lines', 'purchase_orders',
    'internal_transfer_lines', 'internal_transfers', 'return_order_lines', 'return_orders',
    'stock_moves', 'product_lots', 'stock', 'products', 'contacts', 'employees',
    'users', 'accounts', 'sequences', 'leads', 'audit_log'
  ];
  db.pragma('foreign_keys = OFF');
  for (const t of tables) {
    try { db.exec(`DELETE FROM ${t};`); } catch (_) {}
  }
  // Also remove WAL/SHM files if they exist
  if (fs.existsSync(DB_PATH + '-wal')) fs.unlinkSync(DB_PATH + '-wal');
  if (fs.existsSync(DB_PATH + '-shm')) fs.unlinkSync(DB_PATH + '-shm');
  db.pragma('foreign_keys = ON');
  seedAll(db);
}

function resetDatabase(existingDb) {
  if (existingDb) {
    truncateAndSeed(existingDb);
    return existingDb;
  }
  try {
    if (fs.existsSync(DB_PATH)) fs.unlinkSync(DB_PATH);
    if (fs.existsSync(DB_PATH + '-wal')) fs.unlinkSync(DB_PATH + '-wal');
    if (fs.existsSync(DB_PATH + '-shm')) fs.unlinkSync(DB_PATH + '-shm');
  } catch (_e) {
    // If locked by Windows process, fall back to truncate
    const db = initializeDatabase(DB_PATH);
    truncateAndSeed(db);
    return db;
  }

  const db = initializeDatabase(DB_PATH);
  seedAll(db);
  db.close();
  console.log('Database seeded successfully at', DB_PATH);
  return db;
}

function seedAll(db) {
  const now = new Date().toISOString();

  // ──────────────────────────────────────────────
  // Chart of Accounts
  // ──────────────────────────────────────────────
  const insertAccount = db.prepare('INSERT INTO accounts (code, name, type, balance) VALUES (?, ?, ?, ?)');
  const accounts = [
    ['1000', 'Kas dan Bank',        'asset',     20000000],
    ['1100', 'Piutang Usaha',       'asset',      1500000],
    ['1300', 'Persediaan Barang',   'asset',     23095000],
    ['1350', 'Stock Interim / Clearing', 'asset',       0],
    ['1400', 'PPN Masukan',         'asset',            0],
    ['2000', 'Utang Usaha',         'liability',   700000],
    ['2100', 'PPN Keluaran',        'liability',        0],
    ['3000', 'Modal Awal',          'equity',   43895000],
    ['4000', 'Penjualan',           'revenue',          0],
    ['5100', 'HPP / COGS',          'expense',          0],
  ];
  const insertAccounts = db.transaction(() => {
    for (const a of accounts) insertAccount.run(...a);
  });
  insertAccounts();

  // ──────────────────────────────────────────────
  // Users
  // ──────────────────────────────────────────────
  const insertUser = db.prepare('INSERT INTO users (id, username, password, display_name, role, employee_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)');
  const users = [
    ['owner_user',     'owner_user',     'owner123',     'Owner User',      'owner',     null, 1],
    ['sales_user',     'sales_user',     'sales123',     'Dini Pratiwi',    'sales',     'E04', 1],
    ['sales_user2',    'sales_user2',    'sales123',     'Farhan Saputra',  'sales',     'E05', 1],
    ['finance_user',   'finance_user',   'finance123',   'Made Wirawan',    'finance',   'E06', 1],
    ['purchase_user',  'purchase_user',  'purchase123',  'Rita Kusuma',     'purchase',  'E07', 1],
    ['warehouse_mgr',  'warehouse_mgr',  'warehouse123', 'Sari Prameswari', 'warehouse', 'E01', 1],
    ['warehouse_user', 'warehouse_user', 'warehouse123', 'Arif Maulana',    'warehouse', 'E02', 1],
    ['hr_user',        'hr_user',        'hr123',        'Melati Yusuf',    'hr',        'E08', 1],
  ];
  const insertUsers = db.transaction(() => {
    for (const u of users) insertUser.run(...u);
  });
  insertUsers();

  // ──────────────────────────────────────────────
  // Employees (E01-E08)
  // ──────────────────────────────────────────────
  const insertEmployee = db.prepare(`
    INSERT INTO employees (id, name, department_id, department_name, job_title, work_location, status, personal_email, emergency_contact, private_phone, user_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `);
  const employees = [
    ['E01', 'Sari Prameswari',  'D-WH',  'Warehouse',        'Warehouse Manager',     'WH-JKT', 'Aktif',    'sari@personal.com',   '081234567890', '081234567891', 'warehouse_mgr'],
    ['E02', 'Arif Maulana',     'D-WH',  'Warehouse',        'Petugas Gudang',        'WH-JKT', 'Aktif',    'arif@personal.com',   '081234567892', '081234567893', 'warehouse_user'],
    ['E03', 'Naya Putri',       'D-WH',  'Warehouse',        'Petugas Gudang',        'WH-JKT', 'Nonaktif', 'naya@personal.com',   '081234567894', '081234567895', null],
    ['E04', 'Dini Pratiwi',     'D-SAL', 'Sales',            'Sales Lead',            'JKT',    'Aktif',    'dini@personal.com',   '081234567896', '081234567897', 'sales_user'],
    ['E05', 'Farhan Saputra',   'D-SAL', 'Sales',            'Sales Executive',       'JKT',    'Aktif',    'farhan@personal.com', '081234567898', '081234567899', 'sales_user2'],
    ['E06', 'Made Wirawan',     'D-FIN', 'Finance',          'Finance Officer',       'JKT',    'Aktif',    'made@personal.com',   '081234568900', '081234568901', 'finance_user'],
    ['E07', 'Rita Kusuma',      'D-PUR', 'Procurement',      'Procurement Officer',   'JKT',    'Aktif',    'rita@personal.com',   '081234568902', '081234568903', 'purchase_user'],
    ['E08', 'Melati Yusuf',     'D-HR',  'Human Resources',  'HR Administrator',      'JKT',    'Aktif',    'melati@personal.com', '081234568904', '081234568905', 'hr_user'],
  ];
  const insertEmployees = db.transaction(() => {
    for (const e of employees) insertEmployee.run(...e);
  });
  insertEmployees();

  // ──────────────────────────────────────────────
  // Contacts - Customers (C01-C06)
  // ──────────────────────────────────────────────
  const insertContact = db.prepare(`
    INSERT INTO contacts (id, name, type, city, credit_limit, payment_terms, open_ar, product_category)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
  `);
  const customers = [
    ['C01', 'PT Toko Makmur Sejahtera', 'customer', 'JKT',       2000000, 'Net 14', 900000, null],
    ['C02', 'CV Sinar Harapan',         'customer', 'Bekasi',    1000000, 'Net 14', 600000, null],
    ['C03', 'UD Berkah Niaga',          'customer', 'Depok',     3000000, 'Net 30',      0, null],
    ['C04', 'Toko Aneka Jaya',          'customer', 'Bogor',     1250000, 'Tunai',       0, null],
    ['C05', 'PT Mini Mart Bersama',     'customer', 'Tangerang', 4000000, 'Net 30',      0, null],
    ['C06', 'Toko Karunia',             'customer', 'JKT',        750000, 'Tunai',       0, null],
  ];
  const vendors = [
    ['V01', 'PT Bersih Abadi',      'vendor', 'JKT',       0, 'Net 14', 0, 'Produk Kebersihan'],
    ['V02', 'CV Pangan Rasa',       'vendor', 'Bekasi',    0, 'Net 14', 0, 'Makanan Ringan'],
    ['V03', 'PT Segar Nusantara',   'vendor', 'Tangerang', 0, 'Net 30', 0, 'Minuman'],
    ['V04', 'PT Personal Sentosa',  'vendor', 'Bogor',     0, 'Net 14', 0, 'Perawatan Pribadi'],
  ];
  const insertContacts = db.transaction(() => {
    for (const c of customers) insertContact.run(...c);
    for (const v of vendors)   insertContact.run(...v);
  });
  insertContacts();

  // ──────────────────────────────────────────────
  // Products (P01-P16)
  // ──────────────────────────────────────────────
  const insertProduct = db.prepare(`
    INSERT INTO products (id, sku, name, description, uom, uom_label, sale_price, standard_cost, vendor_id, is_lot_tracked)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `);
  const products = [
    ['P01', 'CLN-DTR-800-LMN', 'Detergen Cair Lemon',       '800 ml',    'pouch',  'pouch',   25000, 17000, 'V01', 0],
    ['P02', 'FOD-MIE-B12-ORI', 'Mie Instan Dus Original',   '12 pcs',    'dus',    'dus',     54000, 37000, 'V02', 0],
    ['P03', 'BEV-TEA-C24-LCH', 'Teh Botol Karton Leci',     '24 botol',  'karton', 'karton',  72000, 51000, 'V03', 1],
    ['P04', 'CLN-DIS-750-JER', 'Sabun Cuci Piring Jeruk',   '750 ml',    'botol',  'botol',   20000, 13000, 'V01', 0],
    ['P05', 'PRS-SHM-B24-MNT', 'Sampo Sachet Dus Mentol',   '24 sachet', 'dus',    'dus',     48000, 32000, 'V04', 0],
    ['P06', 'PRS-SPR-100-ROS', 'Sabun Mandi Mawar',         '100 ml',    'botol',  'botol',   14000,  9000, 'V04', 0],
    ['P07', 'SNK-BIS-B10-CHO', 'Biskuit Cokelat Dus',       '10 pak',    'dus',    'dus',     62000, 44000, 'V02', 0],
    ['P08', 'SNK-WFR-B12-VNL', 'Wafer Vanila Dus',          '12 pak',    'dus',    'dus',     52000, 36000, 'V02', 0],
    ['P09', 'BEV-COF-B20-ORI', 'Kopi Sachet Dus',           '20 sachet', 'dus',    'dus',     68000, 48000, 'V03', 0],
    ['P10', 'CLN-FLR-900-PIN', 'Pembersih Lantai Pine',     '900 ml',    'botol',  'botol',   28000, 18000, 'V01', 0],
    ['P11', 'CLN-DTR-800-FLR', 'Detergen Cair Floral',      '800 ml',    'pouch',  'pouch',   25000, 17000, 'V01', 0],
    ['P12', 'BEV-TEA-C24-JSM', 'Teh Botol Karton Melati',   '24 botol',  'karton', 'karton',  72000, 51000, 'V03', 0],
    ['P13', 'FOD-MIE-B12-SPC', 'Mie Instan Dus Pedas',      '12 pcs',    'dus',    'dus',     54000, 37000, 'V02', 0],
    ['P14', 'PRS-TOO-120-HRB', 'Pasta Gigi Herbal',         '120 g',     'tube',   'tube',    19000, 12000, 'V04', 0],
    ['P15', 'SNK-CRK-B08-BBQ', 'Kerupuk BBQ Dus',           '8 pak',     'dus',    'dus',     47000, 30000, 'V02', 0],
    ['P16', 'PRS-SHM-B24-ALV', 'Sampo Sachet Dus Aloe',     '24 sachet', 'dus',    'dus',     48000, 32000, 'V04', 0],
  ];
  const insertProducts = db.transaction(() => {
    for (const p of products) insertProduct.run(...p);
  });
  insertProducts();

  // ──────────────────────────────────────────────
  // Stock (Initial)
  // ──────────────────────────────────────────────
  const insertStock = db.prepare('INSERT INTO stock (product_id, location_id, on_hand, reserved) VALUES (?, ?, ?, ?)');

  // WH-JKT stock quantities
  // WH-JKT stock quantities from page 8 dataset
  const whJkt = {
    P01: 60, P02: 50, P03: 35, P04: 40, P05: 30, P06: 25,
    P07: 35, P08: 30, P09: 40, P10: 45, P11: 55, P12: 30,
    P13: 50, P14: 35, P15: 50, P16: 55,
    P01: 60, P02: 40, P03: 35, P04: 50, P05: 30, P06: 80,
    P07: 25, P08: 30, P09: 20, P10: 45, P11: 55, P12: 40,
    P13: 35, P14: 65, P15: 30, P16: 25,
  };
  // HUB-BDG stock quantities
  // HUB-BDG stock quantities from page 8 dataset
  const hubBdg = {
    P01: 20, P02: 10, P03: 15, P04: 10, P05: 15, P06: 10,
    P07: 10, P08: 10, P09: 10, P10: 15, P11: 20, P12: 15,
    P13: 10, P14: 15, P15: 15, P16: 15,
    P01: 20, P02: 10, P03: 15, P04: 20, P05: 10, P06: 20,
    P07: 10, P08: 10, P09: 10, P10: 15, P11: 15, P12: 10,
    P13: 10, P14: 20, P15: 10, P16: 10,
  };

  const insertStockAll = db.transaction(() => {
    for (const [pid, qty] of Object.entries(whJkt)) {
      // P01 at WH-JKT has reserved=8 for S00007
      const reserved = pid === 'P01' ? 8 : 0;
      insertStock.run(pid, 'WH-JKT', qty, reserved);
    }
    for (const [pid, qty] of Object.entries(hubBdg)) {
      insertStock.run(pid, 'HUB-BDG', qty, 0);
    }
  });
  insertStockAll();

  // ──────────────────────────────────────────────
  // Product Lots for P03
  // ──────────────────────────────────────────────
  const insertLot = db.prepare('INSERT INTO product_lots (id, lot_number, product_id, location_id, quantity, expiry_date) VALUES (?, ?, ?, ?, ?, ?)');
  const insertLots = db.transaction(() => {
    insertLot.run('LOT-TEA-01', 'LOT-TEA-01', 'P03', 'WH-JKT',  15, '2026-11-15');
    insertLot.run('LOT-TEA-02', 'LOT-TEA-02', 'P03', 'WH-JKT',  20, '2027-02-28');
    insertLot.run('LOT-TEA-03', 'LOT-TEA-03', 'P03', 'HUB-BDG', 15, '2027-03-15');
  });
  insertLots();

  // ──────────────────────────────────────────────
  // Sequences
  // ──────────────────────────────────────────────
  const insertSeq = db.prepare('INSERT INTO sequences (prefix, next_val, pad_length) VALUES (?, ?, ?)');
  const sequences = [
    ['S',    8, 5],
    ['P',    8, 6],
    ['DO',   1, 5],
    ['RCV',  1, 5],
    ['INV',  8, 5],
    ['BILL', 1, 5],
    ['PAY',  1, 5],
    ['CN',   1, 5],
    ['IT',   1, 5],
    ['L',    4, 5],
    ['RET',  1, 5],
    ['JE',   2, 5],
  ];
  const insertSequences = db.transaction(() => {
    for (const s of sequences) insertSeq.run(...s);
  });
  insertSequences();

  // ──────────────────────────────────────────────
  // Existing Open Documents
  // ──────────────────────────────────────────────

  // 1. Lead L00003
  db.prepare(`
    INSERT INTO leads (id, sequence_number, contact_id, contact_name, title, expected_revenue, status, type, created_at, updated_at, created_by)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `).run('L00003', 'L00003', 'C03', 'UD Berkah Niaga', 'Potential Bulk Order', 500000, 'new', 'lead', now, now, 'sales_user');

  // 2. Sales Order S00007
  const soDate = '2026-10-15';
  db.prepare(`
    INSERT INTO sales_orders (id, sequence_number, contact_id, status, date_order, discount_pct, needs_finance_approval, finance_approved, finance_approved_by, total_untaxed, total_tax, total_gross, created_at, updated_at, created_by)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `).run('S00007', 'S00007', 'C01', 'confirmed', soDate, 0, 0, 0, null, 200000, 22000, 222000, now, now, 'sales_user');

  db.prepare(`
    INSERT INTO sales_order_lines (order_id, product_id, quantity, unit_price, discount_pct, subtotal, tax_amount, total, delivered_qty, invoiced_qty)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `).run('S00007', 'P01', 8, 25000, 0, 200000, 22000, 222000, 0, 0);

  // 3. Purchase Order P000007
  // 3. Purchase Order PO00007 (also referred to as P000007 in some parts of spec)
  const poDate = '2026-10-10';
  db.prepare(`
    INSERT INTO purchase_orders (id, sequence_number, vendor_id, status, date_order, total_untaxed, total_tax, total_gross, created_at, updated_at, created_by)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `).run('P000007', 'P000007', 'V04', 'confirmed', poDate, 640000, 70400, 710400, now, now, 'purchase_user');
  `).run('PO00007', 'PO00007', 'V04', 'confirmed', poDate, 640000, 70400, 710400, now, now, 'purchase_user');

  db.prepare(`
    INSERT INTO purchase_order_lines (order_id, product_id, quantity, unit_price, subtotal, tax_amount, total, received_qty, accepted_qty, rejected_qty, billed_qty)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `).run('P000007', 'P05', 20, 32000, 640000, 70400, 710400, 0, 0, 0, 0);
  `).run('PO00007', 'P05', 20, 32000, 640000, 70400, 710400, 0, 0, 0, 0);

  // ──────────────────────────────────────────────
  // Opening Journal Entry JE00001
  // ──────────────────────────────────────────────
  db.prepare(`
    INSERT INTO journal_entries (id, entry_date, source_document, description, is_posted, created_at)
    VALUES (?, ?, ?, ?, ?, ?)
  `).run('JE00001', now.split('T')[0], 'Opening Balance', 'Saldo awal pembukaan', 1, now);

  const insertJELine = db.prepare(`
    INSERT INTO journal_entry_lines (journal_entry_id, account_code, account_name, debit, credit)
    VALUES (?, ?, ?, ?, ?)
  `);
  const jeLines = db.transaction(() => {
    insertJELine.run('JE00001', '1000', 'Kas dan Bank',        20000000, 0);
    insertJELine.run('JE00001', '1100', 'Piutang Usaha',        1500000, 0);
    insertJELine.run('JE00001', '1300', 'Persediaan Barang',   23095000, 0);
    insertJELine.run('JE00001', '2000', 'Utang Usaha',                0, 700000);
    insertJELine.run('JE00001', '3000', 'Modal Awal',                 0, 43895000);
  });
  jeLines();

  // ──────────────────────────────────────────────
  // Verification
  // ──────────────────────────────────────────────
  const whJktTotal = db.prepare("SELECT SUM(on_hand) as total FROM stock WHERE location_id = 'WH-JKT'").get();
  const hubBdgTotal = db.prepare("SELECT SUM(on_hand) as total FROM stock WHERE location_id = 'HUB-BDG'").get();
  const whJktValue = db.prepare(`
    SELECT SUM(s.on_hand * p.standard_cost) as value 
    FROM stock s JOIN products p ON s.product_id = p.id 
    WHERE s.location_id = 'WH-JKT'
  `).get();
  const hubBdgValue = db.prepare(`
    SELECT SUM(s.on_hand * p.standard_cost) as value 
    FROM stock s JOIN products p ON s.product_id = p.id 
    WHERE s.location_id = 'HUB-BDG'
  `).get();

  console.log('--- Stock Verification ---');
  console.log(`WH-JKT: ${whJktTotal.total} units, value = ${whJktValue.value}`);
  console.log(`HUB-BDG: ${hubBdgTotal.total} units, value = ${hubBdgValue.value}`);
  console.log(`Grand total: ${whJktTotal.total + hubBdgTotal.total} units, value = ${whJktValue.value + hubBdgValue.value}`);

  if (whJktTotal.total !== 665) console.error('ERROR: WH-JKT total should be 665, got', whJktTotal.total);
  if (hubBdgTotal.total !== 215) console.error('ERROR: HUB-BDG total should be 215, got', hubBdgTotal.total);
  if (whJktValue.value !== 17315000) console.error('ERROR: WH-JKT value should be 17315000, got', whJktValue.value);
  if (hubBdgValue.value !== 5780000) console.error('ERROR: HUB-BDG value should be 5780000, got', hubBdgValue.value);
  if (whJktValue.value + hubBdgValue.value !== 23095000) console.error('ERROR: Grand total value should be 23095000');

  console.log('--- Seed complete ---');
}

module.exports = { resetDatabase };
module.exports = { resetDatabase, truncateAndSeed };

// Run directly if this is the main script
if (require.main === module) {
  resetDatabase();
}
