<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * FmcgSeeder — Dataset lengkap sesuai PDF Handout §4 (Hal 6-10)
 * Urutan insert mengikuti dependency FK.
 */
class FmcgSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ── Truncate semua tabel (urutan FK-safe) ────────────────────────
        DB::table('audit_logs')->delete();
        DB::table('sales_order_lines')->delete();
        DB::table('sales_orders')->delete();
        DB::table('leads')->delete();
        DB::table('invoice_items')->delete();
        DB::table('invoices')->delete();
        DB::table('journal_entries')->delete();
        DB::table('employee_private_records')->delete();
        DB::table('employees')->delete();
        DB::table('users')->delete();
        DB::table('departments')->delete();
        DB::table('product_lots')->delete();
        DB::table('opening_stock')->delete();
        DB::table('vendor_pricelist')->delete();
        DB::table('products')->delete();
        DB::table('contacts_vendors')->delete();
        DB::table('contacts_customers')->delete();
        DB::table('warehouses_locations')->delete();
        DB::table('tax_rates')->delete();
        DB::table('payment_terms')->delete();
        DB::table('chart_of_accounts')->delete();

        // ── Payment Terms ────────────────────────────────────────────────
        DB::table('payment_terms')->insert([
            ['code'=>'CASH',  'description'=>'Tunai',            'due_days'=>0,  'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'NET14', 'description'=>'14 hari kalender', 'due_days'=>14, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'NET30', 'description'=>'30 hari kalender', 'due_days'=>30, 'created_at'=>$now, 'updated_at'=>$now],
        ]);

        // ── Tax Rates ─────────────────────────────────────────────────────
        DB::table('tax_rates')->insert([
            ['code'=>'PPN-NONLUX', 'formal_rate'=>'12%', 'tax_base_factor'=>'11/12',
             'effective_rate'=>11.00, 'rounding'=>'Per baris, rupiah terdekat',
             'scope'=>'16 SKU simulasi', 'created_at'=>$now, 'updated_at'=>$now],
        ]);

        // ── Chart of Accounts ─────────────────────────────────────────────
        DB::table('chart_of_accounts')->insert([
            ['code'=>'1000','name'=>'Kas dan Bank',                'type'=>'Asset',        'normal_balance'=>'Debit',  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'1100','name'=>'Piutang Usaha',               'type'=>'Asset',        'normal_balance'=>'Debit',  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'1300','name'=>'Persediaan Barang',           'type'=>'Asset',        'normal_balance'=>'Debit',  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'1350','name'=>'Stock Interim / Receipt Clearing','type'=>'Asset',    'normal_balance'=>'Debit',  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'1400','name'=>'PPN Masukan',                 'type'=>'Asset',        'normal_balance'=>'Debit',  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'1500','name'=>'Aset Tetap',                  'type'=>'Asset',        'normal_balance'=>'Debit',  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'2000','name'=>'Utang Usaha',                 'type'=>'Liability',    'normal_balance'=>'Credit', 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'2100','name'=>'PPN Keluaran',                'type'=>'Liability',    'normal_balance'=>'Credit', 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'2200','name'=>'Utang Biaya',                 'type'=>'Liability',    'normal_balance'=>'Credit', 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'3000','name'=>'Modal / Saldo Awal',          'type'=>'Equity',       'normal_balance'=>'Credit', 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'4000','name'=>'Penjualan',                   'type'=>'Income',       'normal_balance'=>'Credit', 'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'4100','name'=>'Retur Penjualan',             'type'=>'Contra Income','normal_balance'=>'Debit',  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'4200','name'=>'Diskon Penjualan',            'type'=>'Contra Income','normal_balance'=>'Debit',  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'5100','name'=>'HPP',                         'type'=>'Expense',      'normal_balance'=>'Debit',  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'6100','name'=>'Beban Operasional',           'type'=>'Expense',      'normal_balance'=>'Debit',  'is_active'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Warehouse Locations ────────────────────────────────────────────
        DB::table('warehouses_locations')->insert([
            ['code'=>'WH-JKT',  'name'=>'Gudang Utama Arunika', 'city'=>'Jakarta', 'usage'=>'Internal', 'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'HUB-BDG', 'name'=>'Hub Distribusi Bandung','city'=>'Bandung', 'usage'=>'Internal', 'created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Customers ─────────────────────────────────────────────────────
        DB::table('contacts_customers')->insert([
            ['id'=>'C01','name'=>'PT Toko Makmur Sejahtera','type'=>'B2B','city'=>'Jakarta', 'credit_limit'=>2000000,'payment_terms'=>'NET14','open_ar'=>900000, 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'C02','name'=>'CV Sinar Harapan',        'type'=>'B2B','city'=>'Bekasi',  'credit_limit'=>1000000,'payment_terms'=>'NET14','open_ar'=>600000, 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'C03','name'=>'UD Berkah Niaga',          'type'=>'B2B','city'=>'Depok',   'credit_limit'=>3000000,'payment_terms'=>'NET30','open_ar'=>0,      'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'C04','name'=>'Toko Aneka Jaya',          'type'=>'B2B','city'=>'Bogor',   'credit_limit'=>1250000,'payment_terms'=>'CASH', 'open_ar'=>0,      'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'C05','name'=>'PT Mini Mart Bersama',     'type'=>'B2B','city'=>'Tangerang','credit_limit'=>4000000,'payment_terms'=>'NET30','open_ar'=>0,      'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'C06','name'=>'Toko Karunia',             'type'=>'B2B','city'=>'Jakarta', 'credit_limit'=>750000, 'payment_terms'=>'CASH', 'open_ar'=>0,      'created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Vendors ────────────────────────────────────────────────────────
        DB::table('contacts_vendors')->insert([
            ['id'=>'V01','name'=>'PT Bersih Abadi',    'city'=>'Jakarta',   'payment_terms'=>'NET14','category'=>'Produk kebersihan', 'open_ap'=>0,      'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'V02','name'=>'CV Pangan Rasa',     'city'=>'Bekasi',    'payment_terms'=>'NET14','category'=>'Makanan ringan',    'open_ap'=>0,      'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'V03','name'=>'PT Segar Nusantara', 'city'=>'Tangerang', 'payment_terms'=>'NET30','category'=>'Minuman',           'open_ap'=>0,      'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'V04','name'=>'PT Personal Sentosa','city'=>'Bogor',     'payment_terms'=>'NET14','category'=>'Perawatan pribadi', 'open_ap'=>700000, 'created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Products (16 SKU) ──────────────────────────────────────────────
        DB::table('products')->insert([
            ['id'=>'P01','variant_sku'=>'CLN-DTR-800-LMN','name'=>'Detergen Cair Lemon',      'size'=>'800 ml',   'color'=>'Lemon',   'unit'=>'pouch', 'sales_price'=>25000,'cost'=>17000,'default_vendor'=>'V01','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P02','variant_sku'=>'FOD-MIE-B12-ORI','name'=>'Mie Instan Dus Original',  'size'=>'12 pcs',   'color'=>'Original','unit'=>'dus',   'sales_price'=>54000,'cost'=>37000,'default_vendor'=>'V02','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P03','variant_sku'=>'BEV-TEA-C24-LCH','name'=>'Teh Botol Karton Leci',    'size'=>'24 botol', 'color'=>'Leci',    'unit'=>'karton','sales_price'=>72000,'cost'=>51000,'default_vendor'=>'V03','track_lot'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P04','variant_sku'=>'CLN-DIS-750-JER','name'=>'Sabun Cuci Piring Jeruk',  'size'=>'750 ml',   'color'=>'Jeruk',   'unit'=>'botol', 'sales_price'=>20000,'cost'=>13000,'default_vendor'=>'V01','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P05','variant_sku'=>'PRS-SHM-B24-MNT','name'=>'Sampo Sachet Dus Mentol',  'size'=>'24 sachet','color'=>'Mentol',  'unit'=>'dus',   'sales_price'=>48000,'cost'=>32000,'default_vendor'=>'V04','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P06','variant_sku'=>'PRS-SPR-100-ROS','name'=>'Sabun Mandi Mawar',         'size'=>'100 ml',   'color'=>'Mawar',   'unit'=>'botol', 'sales_price'=>14000,'cost'=>9000, 'default_vendor'=>'V04','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P07','variant_sku'=>'SNK-BIS-B10-CHO','name'=>'Biskuit Cokelat Dus',       'size'=>'10 pak',   'color'=>'Cokelat', 'unit'=>'dus',   'sales_price'=>62000,'cost'=>44000,'default_vendor'=>'V02','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P08','variant_sku'=>'SNK-WFR-B12-VNL','name'=>'Wafer Vanila Dus',          'size'=>'12 pak',   'color'=>'Vanila',  'unit'=>'dus',   'sales_price'=>52000,'cost'=>36000,'default_vendor'=>'V02','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P09','variant_sku'=>'BEV-COF-B20-ORI','name'=>'Kopi Sachet Dus',           'size'=>'20 sachet','color'=>'Original','unit'=>'dus',   'sales_price'=>68000,'cost'=>48000,'default_vendor'=>'V03','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P10','variant_sku'=>'CLN-FLR-900-PIN','name'=>'Pembersih Lantai Pine',     'size'=>'900 ml',   'color'=>'Pine',    'unit'=>'botol', 'sales_price'=>28000,'cost'=>18000,'default_vendor'=>'V01','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P11','variant_sku'=>'CLN-DTR-800-FLR','name'=>'Detergen Cair Floral',      'size'=>'800 ml',   'color'=>'Floral',  'unit'=>'pouch', 'sales_price'=>25000,'cost'=>17000,'default_vendor'=>'V01','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P12','variant_sku'=>'BEV-TEA-C24-JSM','name'=>'Teh Botol Karton Melati',   'size'=>'24 botol', 'color'=>'Melati',  'unit'=>'karton','sales_price'=>72000,'cost'=>51000,'default_vendor'=>'V03','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P13','variant_sku'=>'FOD-MIE-B12-SPC','name'=>'Mie Instan Dus Pedas',      'size'=>'12 pcs',   'color'=>'Pedas',   'unit'=>'dus',   'sales_price'=>54000,'cost'=>37000,'default_vendor'=>'V02','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P14','variant_sku'=>'PRS-TOO-120-HRB','name'=>'Pasta Gigi Herbal',          'size'=>'120 g',    'color'=>'Herbal',  'unit'=>'tube',  'sales_price'=>19000,'cost'=>12000,'default_vendor'=>'V04','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P15','variant_sku'=>'SNK-CRK-B08-BBQ','name'=>'Kerupuk BBQ Dus',            'size'=>'8 pak',    'color'=>'BBQ',     'unit'=>'dus',   'sales_price'=>47000,'cost'=>30000,'default_vendor'=>'V02','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'P16','variant_sku'=>'PRS-SHM-B24-ALV','name'=>'Sampo Sachet Dus Aloe',     'size'=>'24 sachet','color'=>'Aloe',    'unit'=>'dus',   'sales_price'=>48000,'cost'=>32000,'default_vendor'=>'V04','track_lot'=>0,'created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Vendor Pricelist ───────────────────────────────────────────────
        DB::table('vendor_pricelist')->insert([
            ['vendor_id'=>'V01','variant_sku'=>'CLN-DTR-800-LMN','unit'=>'pouch', 'unit_price'=>17000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V02','variant_sku'=>'FOD-MIE-B12-ORI','unit'=>'dus',   'unit_price'=>37000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V03','variant_sku'=>'BEV-TEA-C24-LCH','unit'=>'karton','unit_price'=>51000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V01','variant_sku'=>'CLN-DIS-750-JER','unit'=>'botol', 'unit_price'=>13000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V04','variant_sku'=>'PRS-SHM-B24-MNT','unit'=>'dus',   'unit_price'=>32000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V04','variant_sku'=>'PRS-SPR-100-ROS','unit'=>'botol', 'unit_price'=>9000, 'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V02','variant_sku'=>'SNK-BIS-B10-CHO','unit'=>'dus',   'unit_price'=>44000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V02','variant_sku'=>'SNK-WFR-B12-VNL','unit'=>'dus',   'unit_price'=>36000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V03','variant_sku'=>'BEV-COF-B20-ORI','unit'=>'dus',   'unit_price'=>48000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V01','variant_sku'=>'CLN-FLR-900-PIN','unit'=>'botol', 'unit_price'=>18000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V01','variant_sku'=>'CLN-DTR-800-FLR','unit'=>'pouch', 'unit_price'=>17000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V03','variant_sku'=>'BEV-TEA-C24-JSM','unit'=>'karton','unit_price'=>51000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V02','variant_sku'=>'FOD-MIE-B12-SPC','unit'=>'dus',   'unit_price'=>37000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V04','variant_sku'=>'PRS-TOO-120-HRB','unit'=>'tube',  'unit_price'=>12000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V02','variant_sku'=>'SNK-CRK-B08-BBQ','unit'=>'dus',   'unit_price'=>30000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['vendor_id'=>'V04','variant_sku'=>'PRS-SHM-B24-ALV','unit'=>'dus',   'unit_price'=>32000,'min_qty'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Opening Stock ──────────────────────────────────────────────────
        // Control baseline: On Hand 880 unit, nilai Rp23.095.000
        // WH-JKT: Rp17.315.000 | HUB-BDG: Rp5.780.000
        // S00007 reserved: 8 P01 di WH-JKT
        DB::table('opening_stock')->insert([
            ['variant_sku'=>'CLN-DTR-800-LMN','wh_jkt_on_hand'=>60,'wh_jkt_reserved'=>8, 'hub_bdg_on_hand'=>20,'company_total'=>80, 'standard_cost'=>17000,'standard_value'=>1360000,'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'FOD-MIE-B12-ORI','wh_jkt_on_hand'=>40,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>10,'company_total'=>50, 'standard_cost'=>37000,'standard_value'=>1850000,'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'BEV-TEA-C24-LCH','wh_jkt_on_hand'=>35,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>15,'company_total'=>50, 'standard_cost'=>51000,'standard_value'=>2550000,'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'CLN-DIS-750-JER','wh_jkt_on_hand'=>50,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>20,'company_total'=>70, 'standard_cost'=>13000,'standard_value'=>910000, 'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'PRS-SHM-B24-MNT','wh_jkt_on_hand'=>30,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>10,'company_total'=>40, 'standard_cost'=>32000,'standard_value'=>1280000,'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'PRS-SPR-100-ROS','wh_jkt_on_hand'=>80,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>20,'company_total'=>100,'standard_cost'=>9000, 'standard_value'=>900000, 'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'SNK-BIS-B10-CHO','wh_jkt_on_hand'=>25,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>10,'company_total'=>35, 'standard_cost'=>44000,'standard_value'=>1540000,'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'SNK-WFR-B12-VNL','wh_jkt_on_hand'=>30,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>10,'company_total'=>40, 'standard_cost'=>36000,'standard_value'=>1440000,'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'BEV-COF-B20-ORI','wh_jkt_on_hand'=>20,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>10,'company_total'=>30, 'standard_cost'=>48000,'standard_value'=>1440000,'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'CLN-FLR-900-PIN','wh_jkt_on_hand'=>45,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>15,'company_total'=>60, 'standard_cost'=>18000,'standard_value'=>1080000,'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'CLN-DTR-800-FLR','wh_jkt_on_hand'=>55,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>15,'company_total'=>70, 'standard_cost'=>17000,'standard_value'=>1190000,'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'BEV-TEA-C24-JSM','wh_jkt_on_hand'=>40,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>10,'company_total'=>50, 'standard_cost'=>51000,'standard_value'=>2550000,'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'FOD-MIE-B12-SPC','wh_jkt_on_hand'=>35,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>10,'company_total'=>45, 'standard_cost'=>37000,'standard_value'=>1665000,'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'PRS-TOO-120-HRB','wh_jkt_on_hand'=>65,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>20,'company_total'=>85, 'standard_cost'=>12000,'standard_value'=>1020000,'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'SNK-CRK-B08-BBQ','wh_jkt_on_hand'=>30,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>10,'company_total'=>40, 'standard_cost'=>30000,'standard_value'=>1200000,'created_at'=>$now,'updated_at'=>$now],
            ['variant_sku'=>'PRS-SHM-B24-ALV','wh_jkt_on_hand'=>25,'wh_jkt_reserved'=>0, 'hub_bdg_on_hand'=>10,'company_total'=>35, 'standard_cost'=>32000,'standard_value'=>1120000,'created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Product Lots (P03 FEFO) ────────────────────────────────────────
        DB::table('product_lots')->insert([
            ['lot_id'=>'LOT-TEA-01','variant_sku'=>'BEV-TEA-C24-LCH','location'=>'WH-JKT', 'qty'=>15,'expiry_date'=>'2026-11-15','selection_policy'=>'FEFO','created_at'=>$now,'updated_at'=>$now],
            ['lot_id'=>'LOT-TEA-02','variant_sku'=>'BEV-TEA-C24-LCH','location'=>'WH-JKT', 'qty'=>20,'expiry_date'=>'2027-02-28','selection_policy'=>'FEFO','created_at'=>$now,'updated_at'=>$now],
            ['lot_id'=>'LOT-TEA-03','variant_sku'=>'BEV-TEA-C24-LCH','location'=>'HUB-BDG','qty'=>15,'expiry_date'=>'2027-03-15','selection_policy'=>'FEFO','created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Departments ────────────────────────────────────────────────────
        DB::table('departments')->insert([
            ['dept_id'=>'D-WH', 'name'=>'Warehouse',       'manager_employee'=>'E01','company'=>'Arunika','created_at'=>$now,'updated_at'=>$now],
            ['dept_id'=>'D-SAL','name'=>'Sales',           'manager_employee'=>'E04','company'=>'Arunika','created_at'=>$now,'updated_at'=>$now],
            ['dept_id'=>'D-FIN','name'=>'Finance',         'manager_employee'=>'E06','company'=>'Arunika','created_at'=>$now,'updated_at'=>$now],
            ['dept_id'=>'D-PUR','name'=>'Procurement',     'manager_employee'=>'E07','company'=>'Arunika','created_at'=>$now,'updated_at'=>$now],
            ['dept_id'=>'D-HR', 'name'=>'Human Resources', 'manager_employee'=>'E08','company'=>'Arunika','created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Users ─────────────────────────────────────────────────────────
        // Pre-hashed bcrypt(cost=10) untuk "Demo2026!" — tidak panggil Hash::make() agar seeder cepat
        $pw = '$2y$10$t25TPbhNxXRWYwEiFAJnI.a.8ZwTlGVt.PgTuZb9tWG7hSrxNan8u';
        DB::table('users')->insert([
            ['id'=>1,'name'=>'Sari Prameswari','username'=>'warehouse_mgr', 'email'=>'warehouse_mgr@arunika.test', 'password'=>$pw,'role'=>'Warehouse','created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'name'=>'Arif Maulana',   'username'=>'warehouse_user','email'=>'warehouse_user@arunika.test','password'=>$pw,'role'=>'Warehouse','created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'name'=>'Dini Pratiwi',   'username'=>'sales_user',    'email'=>'sales_user@arunika.test',    'password'=>$pw,'role'=>'Sales',    'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'name'=>'Farhan Saputra', 'username'=>'sales_user2',   'email'=>'sales_user2@arunika.test',   'password'=>$pw,'role'=>'Sales',    'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,'name'=>'Made Wirawan',   'username'=>'finance_user',  'email'=>'finance_user@arunika.test',  'password'=>$pw,'role'=>'Finance',  'created_at'=>$now,'updated_at'=>$now],
            ['id'=>6,'name'=>'Rita Kusuma',    'username'=>'purchase_user', 'email'=>'purchase_user@arunika.test', 'password'=>$pw,'role'=>'Purchase', 'created_at'=>$now,'updated_at'=>$now],
            ['id'=>7,'name'=>'Melati Yusuf',   'username'=>'hr_user',       'email'=>'hr_user@arunika.test',       'password'=>$pw,'role'=>'HR',       'created_at'=>$now,'updated_at'=>$now],
            ['id'=>8,'name'=>'Hendra Widodo',  'username'=>'owner_user',    'email'=>'owner_user@arunika.test',    'password'=>$pw,'role'=>'Owner',    'created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Employees ─────────────────────────────────────────────────────
        DB::table('employees')->insert([
            ['id'=>'E01','name'=>'Sari Prameswari','department_id'=>'D-WH', 'job_title'=>'Supervisor Gudang',  'location'=>'WH-JKT','status'=>'Aktif',   'mapped_user_id'=>1,   'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'E02','name'=>'Arif Maulana',   'department_id'=>'D-WH', 'job_title'=>'Petugas Gudang',     'location'=>'WH-JKT','status'=>'Aktif',   'mapped_user_id'=>2,   'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'E03','name'=>'Naya Putri',      'department_id'=>'D-WH', 'job_title'=>'Petugas Gudang',     'location'=>'WH-JKT','status'=>'Nonaktif','mapped_user_id'=>null,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'E04','name'=>'Dini Pratiwi',    'department_id'=>'D-SAL','job_title'=>'Sales Lead',         'location'=>'JKT',   'status'=>'Aktif',   'mapped_user_id'=>3,   'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'E05','name'=>'Farhan Saputra',  'department_id'=>'D-SAL','job_title'=>'Sales Executive',    'location'=>'JKT',   'status'=>'Aktif',   'mapped_user_id'=>4,   'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'E06','name'=>'Made Wirawan',    'department_id'=>'D-FIN','job_title'=>'Finance Officer',    'location'=>'JKT',   'status'=>'Aktif',   'mapped_user_id'=>5,   'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'E07','name'=>'Rita Kusuma',     'department_id'=>'D-PUR','job_title'=>'Procurement Officer','location'=>'JKT',   'status'=>'Aktif',   'mapped_user_id'=>6,   'created_at'=>$now,'updated_at'=>$now],
            ['id'=>'E08','name'=>'Melati Yusuf',    'department_id'=>'D-HR', 'job_title'=>'HR Administrator',   'location'=>'JKT',   'status'=>'Aktif',   'mapped_user_id'=>7,   'created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Employee Private Records (SR-15) ──────────────────────────────
        DB::table('employee_private_records')->insert([
            ['employee_id'=>'E01','personal_email'=>'sari.private@example.test','emergency_contact'=>'Nila Prameswari','private_phone'=>'0812-0000-0101','created_at'=>$now,'updated_at'=>$now],
            ['employee_id'=>'E03','personal_email'=>'naya.private@example.test','emergency_contact'=>'Laras Putri',    'private_phone'=>'0812-0000-0303','created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Existing Documents (dari PDF §4 halaman 10) ───────────────────
        // L00003 — Lead (New)
        DB::table('leads')->insert([
            ['id'=>'L00003','partner_id'=>'C03','partner_name'=>'UD Berkah Niaga','stage'=>'new',
             'salesperson_id'=>'sales_user','salesperson_name'=>'Dini Pratiwi',
             'expected_revenue'=>500000,'notes'=>null,'created_by'=>'sales_user',
             'created_at'=>$now,'updated_at'=>$now],
        ]);

        // S00007 — Sales Order (Confirmed), 8 x P01, reserved
        DB::table('sales_orders')->insert([
            ['id'=>'S00007','customer_id'=>'C01','customer_name'=>'PT Toko Makmur Sejahtera',
             'status'=>'confirmed',
             'discount_approval_status'=>'not_required',
             'discount_approved_by'=>null,'discount_approved_at'=>null,
             'untaxed_amount'=>200000,'tax_amount'=>22000,'gross_amount'=>222000,
             'payment_terms'=>'NET14','order_date'=>'2026-10-20',
             'delivery_promise_date'=>'2026-10-23',
             'created_by'=>'sales_user','confirmed_by'=>'sales_user',
             'confirmed_at'=>$now,'lead_id'=>null,
             'created_at'=>$now,'updated_at'=>$now],
        ]);

        DB::table('sales_order_lines')->insert([
            ['sales_order_id'=>'S00007','product_id'=>'P01','variant_sku'=>'CLN-DTR-800-LMN',
             'product_name'=>'Detergen Cair Lemon','unit'=>'pouch',
             'quantity'=>8,'unit_price'=>25000,'discount_percent'=>0.00,'is_locked'=>1,
             'net_amount'=>200000,'tax_amount'=>22000,'gross_amount'=>222000,
             'delivered_qty'=>0,'invoiced_qty'=>0,'reserved_qty'=>8,'source_location'=>'WH-JKT',
             'created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Opening Journal Entries (saldo pembukaan, bukan T-xx) ─────────
        DB::table('journal_entries')->insert([
            ['invoice_id'=>null,'journal_type'=>'opening','reference'=>'OPN-2026',
             'description'=>'Saldo pembukaan Bank Rp20.000.000',
             'debit_account'=>'1000','credit_account'=>'3000','amount'=>20000000,
             'posted_at'=>$now,'posted_by'=>'system','idempotency_key'=>'OPN-BANK-001',
             'is_reversed'=>0,'reversed_by_entry_id'=>null,
             'created_at'=>$now,'updated_at'=>$now],
            ['invoice_id'=>null,'journal_type'=>'opening','reference'=>'OPN-2026',
             'description'=>'Saldo pembukaan AR C01+C02 Rp1.500.000',
             'debit_account'=>'1100','credit_account'=>'3000','amount'=>1500000,
             'posted_at'=>$now,'posted_by'=>'system','idempotency_key'=>'OPN-AR-001',
             'is_reversed'=>0,'reversed_by_entry_id'=>null,
             'created_at'=>$now,'updated_at'=>$now],
            ['invoice_id'=>null,'journal_type'=>'opening','reference'=>'OPN-2026',
             'description'=>'Saldo pembukaan Persediaan Rp23.095.000',
             'debit_account'=>'1300','credit_account'=>'3000','amount'=>23095000,
             'posted_at'=>$now,'posted_by'=>'system','idempotency_key'=>'OPN-INV-001',
             'is_reversed'=>0,'reversed_by_entry_id'=>null,
             'created_at'=>$now,'updated_at'=>$now],
            ['invoice_id'=>null,'journal_type'=>'opening','reference'=>'OPN-2026',
             'description'=>'Saldo pembukaan AP V04 Rp700.000',
             'debit_account'=>'3000','credit_account'=>'2000','amount'=>700000,
             'posted_at'=>$now,'posted_by'=>'system','idempotency_key'=>'OPN-AP-001',
             'is_reversed'=>0,'reversed_by_entry_id'=>null,
             'created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Audit Log ─────────────────────────────────────────────────────
        $rows = [];

        foreach (['E01','E02','E03','E04','E05','E06','E07','E08'] as $eid) {
            $rows[] = ['document_type'=>'employee','document_id'=>$eid,'action'=>'created',
                'actor_user'=>'system','actor_employee'=>null,'description'=>'Seeded dari FmcgSeeder',
                'old_values'=>null,'new_values'=>null,'acted_at'=>$now,'created_at'=>$now,'updated_at'=>$now];
        }

        $rows[] = ['document_type'=>'sales_order','document_id'=>'S00007','action'=>'confirm',
            'actor_user'=>'sales_user','actor_employee'=>'E04',
            'description'=>'S00007 dikonfirmasi. C01, 8xP01, net Rp200.000, gross Rp222.000. Reservasi 8 P01 di WH-JKT.',
            'old_values'=>'{"status":"draft"}','new_values'=>'{"status":"confirmed"}',
            'acted_at'=>$now,'created_at'=>$now,'updated_at'=>$now];

        $rows[] = ['document_type'=>'sales_order','document_id'=>'S00007','action'=>'reserve',
            'actor_user'=>'warehouse_mgr','actor_employee'=>'E01',
            'description'=>'Reservasi 8 unit CLN-DTR-800-LMN di WH-JKT. On Hand 60, Reserved 8, Available 52.',
            'old_values'=>'{"reserved":0,"available":60}','new_values'=>'{"reserved":8,"available":52}',
            'acted_at'=>$now,'created_at'=>$now,'updated_at'=>$now];

        $rows[] = ['document_type'=>'purchase_order','document_id'=>'PO00007','action'=>'confirm',
            'actor_user'=>'purchase_user','actor_employee'=>'E07',
            'description'=>'PO00007 dikonfirmasi. V04, 20xP05, net Rp640.000, gross est. Rp710.400. Harga terkunci (SR-21). Receipt=0, Bill=0.',
            'old_values'=>'{"status":"rfq"}','new_values'=>'{"status":"confirmed"}',
            'acted_at'=>$now,'created_at'=>$now,'updated_at'=>$now];

        $rows[] = ['document_type'=>'lead','document_id'=>'L00003','action'=>'created',
            'actor_user'=>'sales_user','actor_employee'=>'E04',
            'description'=>'Lead L00003 dibuat. C03 UD Berkah Niaga. Expected Revenue Rp500.000.',
            'old_values'=>null,'new_values'=>'{"stage":"new","customer":"C03","revenue":500000}',
            'acted_at'=>$now,'created_at'=>$now,'updated_at'=>$now];

        foreach (['sales_user'=>'Sales','warehouse_mgr'=>'Warehouse','hr_user'=>'HR','owner_user'=>'Owner'] as $u => $r) {
            $rows[] = ['document_type'=>'access_control','document_id'=>$u,'action'=>'login',
                'actor_user'=>$u,'actor_employee'=>null,
                'description'=>"User [{$u}] login berhasil. Role: {$r}.",
                'old_values'=>null,'new_values'=>null,'acted_at'=>$now,'created_at'=>$now,'updated_at'=>$now];
        }

        $rows[] = ['document_type'=>'employee','document_id'=>'E03','action'=>'status_changed',
            'actor_user'=>'hr_user','actor_employee'=>'E08',
            'description'=>'Status E03 Naya Putri diubah dari Aktif menjadi Nonaktif. Efektif segera (SR-18).',
            'old_values'=>'{"status":"Aktif"}','new_values'=>'{"status":"Nonaktif"}',
            'acted_at'=>$now,'created_at'=>$now,'updated_at'=>$now];

        $rows[] = ['document_type'=>'access_control','document_id'=>'sales_user','action'=>'access_denied',
            'actor_user'=>'sales_user','actor_employee'=>'E04',
            'description'=>'Role [Sales] ditolak akses ke [accounting]. SR-15. Saldo tidak berubah. (T-02)',
            'old_values'=>null,'new_values'=>null,'acted_at'=>$now,'created_at'=>$now,'updated_at'=>$now];

        $rows[] = ['document_type'=>'receipt','document_id'=>'RCV-ATTEMPT-001','action'=>'sign_off_rejected',
            'actor_user'=>'system','actor_employee'=>'E03',
            'description'=>'Sign-off ditolak: E03 berstatus Nonaktif (SR-17, SR-18). (T-18)',
            'old_values'=>null,'new_values'=>null,'acted_at'=>$now,'created_at'=>$now,'updated_at'=>$now];

        DB::table('audit_logs')->insert($rows);
    }
}
