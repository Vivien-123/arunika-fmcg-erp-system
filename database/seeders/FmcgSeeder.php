<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FmcgSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Clear in dependency order (accounting tables first for FK safety)
        DB::table('audit_logs')->delete();
        DB::table('journal_entries')->delete();
        DB::table('invoice_items')->delete();
        DB::table('invoices')->delete();
        DB::table('chart_of_accounts')->delete();
        DB::table('employee_private_records')->delete();
        DB::table('employees')->delete();
        DB::table('users')->delete();
        DB::table('departments')->delete();

        // ── Chart of Accounts (M4-W1-01 · Handout §4) ──────────────────
        DB::table('chart_of_accounts')->insert([
            ['code'=>'1000', 'name'=>'Kas dan Bank',                    'type'=>'Asset',    'normal_balance'=>'Debit',  'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'1100', 'name'=>'Piutang Usaha',                   'type'=>'Asset',    'normal_balance'=>'Debit',  'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'1300', 'name'=>'Persediaan Barang',               'type'=>'Asset',    'normal_balance'=>'Debit',  'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'1350', 'name'=>'Stock Interim / Receipt Clearing','type'=>'Asset',    'normal_balance'=>'Debit',  'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'1400', 'name'=>'PPN Masukan',                     'type'=>'Asset',    'normal_balance'=>'Debit',  'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'1500', 'name'=>'Aset Tetap',                      'type'=>'Asset',    'normal_balance'=>'Debit',  'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'2000', 'name'=>'Utang Usaha',                     'type'=>'Liability','normal_balance'=>'Credit', 'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'2100', 'name'=>'PPN Keluaran',                    'type'=>'Liability','normal_balance'=>'Credit', 'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'2200', 'name'=>'Utang Biaya',                     'type'=>'Liability','normal_balance'=>'Credit', 'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'3000', 'name'=>'Modal / Saldo Awal',              'type'=>'Equity',   'normal_balance'=>'Credit', 'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'4000', 'name'=>'Penjualan',                       'type'=>'Income',   'normal_balance'=>'Credit', 'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'4100', 'name'=>'Retur Penjualan',                 'type'=>'Income',   'normal_balance'=>'Debit',  'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'4200', 'name'=>'Diskon Penjualan',                'type'=>'Income',   'normal_balance'=>'Debit',  'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'5100', 'name'=>'HPP',                             'type'=>'Expense',  'normal_balance'=>'Debit',  'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
            ['code'=>'6100', 'name'=>'Beban Operasional',               'type'=>'Expense',  'normal_balance'=>'Debit',  'is_active'=>1, 'created_at'=>$now, 'updated_at'=>$now],
        ]);

        // ── Opening Balance Journal Entries (Handout §4 · M4-W1-02) ─────
        // Dr Bank 20.000.000 + Dr AR 1.500.000 + Dr Persediaan 23.095.000
        // = Cr AP 700.000 + Cr Modal 43.895.000  (total Rp44.595.000 balanced)
        DB::table('journal_entries')->insert([
            ['invoice_id'=>null, 'journal_type'=>'opening', 'reference'=>'OPENING-BALANCE',
             'idempotency_key'=>md5('opening|OPENING-BALANCE|1000|3000|20000000'),
             'debit_account'=>'1000', 'credit_account'=>'3000', 'amount'=>20000000,
             'description'=>'Opening Balance: Kas dan Bank Rp20.000.000',
             'posted_by'=>'system', 'posted_at'=>$now, 'is_reversed'=>0, 'reversed_by_entry_id'=>null,
             'created_at'=>$now, 'updated_at'=>$now],
            ['invoice_id'=>null, 'journal_type'=>'opening', 'reference'=>'OPENING-BALANCE',
             'idempotency_key'=>md5('opening|OPENING-BALANCE|1100|3000|1500000'),
             'debit_account'=>'1100', 'credit_account'=>'3000', 'amount'=>1500000,
             'description'=>'Opening Balance: Piutang Usaha AR Rp1.500.000 (C01 Rp900.000 + C02 Rp600.000)',
             'posted_by'=>'system', 'posted_at'=>$now, 'is_reversed'=>0, 'reversed_by_entry_id'=>null,
             'created_at'=>$now, 'updated_at'=>$now],
            ['invoice_id'=>null, 'journal_type'=>'opening', 'reference'=>'OPENING-BALANCE',
             'idempotency_key'=>md5('opening|OPENING-BALANCE|1300|3000|23095000'),
             'debit_account'=>'1300', 'credit_account'=>'3000', 'amount'=>23095000,
             'description'=>'Opening Balance: Persediaan Barang Rp23.095.000 (WH-JKT Rp17.315.000 + HUB-BDG Rp5.780.000)',
             'posted_by'=>'system', 'posted_at'=>$now, 'is_reversed'=>0, 'reversed_by_entry_id'=>null,
             'created_at'=>$now, 'updated_at'=>$now],
            ['invoice_id'=>null, 'journal_type'=>'opening', 'reference'=>'OPENING-BALANCE',
             'idempotency_key'=>md5('opening|OPENING-BALANCE|3000|2000|700000'),
             'debit_account'=>'3000', 'credit_account'=>'2000', 'amount'=>700000,
             'description'=>'Opening Balance: Utang Usaha AP Rp700.000 (V04)',
             'posted_by'=>'system', 'posted_at'=>$now, 'is_reversed'=>0, 'reversed_by_entry_id'=>null,
             'created_at'=>$now, 'updated_at'=>$now],
        ]);

        // Departments
        DB::table('departments')->insert([
            ['dept_id'=>'D-WH',  'name'=>'Warehouse',       'manager_employee'=>'E01', 'company'=>'Arunika', 'created_at'=>$now, 'updated_at'=>$now],
            ['dept_id'=>'D-SAL', 'name'=>'Sales',           'manager_employee'=>'E04', 'company'=>'Arunika', 'created_at'=>$now, 'updated_at'=>$now],
            ['dept_id'=>'D-FIN', 'name'=>'Finance',         'manager_employee'=>'E06', 'company'=>'Arunika', 'created_at'=>$now, 'updated_at'=>$now],
            ['dept_id'=>'D-PUR', 'name'=>'Procurement',     'manager_employee'=>'E07', 'company'=>'Arunika', 'created_at'=>$now, 'updated_at'=>$now],
            ['dept_id'=>'D-HR',  'name'=>'Human Resources', 'manager_employee'=>'E08', 'company'=>'Arunika', 'created_at'=>$now, 'updated_at'=>$now],
        ]);

        // Users
        DB::table('users')->insert([
            ['id'=>1, 'name'=>'Sari Prameswari', 'username'=>'warehouse_mgr',  'email'=>'warehouse_mgr@arunika.test',  'password'=>Hash::make('Demo2026!'), 'role'=>'Warehouse', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>2, 'name'=>'Arif Maulana',    'username'=>'warehouse_user', 'email'=>'warehouse_user@arunika.test', 'password'=>Hash::make('Demo2026!'), 'role'=>'Warehouse', 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>3, 'name'=>'Dini Pratiwi',    'username'=>'sales_user',     'email'=>'sales_user@arunika.test',     'password'=>Hash::make('Demo2026!'), 'role'=>'Sales',     'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>4, 'name'=>'Farhan Saputra',  'username'=>'sales_user2',    'email'=>'sales_user2@arunika.test',    'password'=>Hash::make('Demo2026!'), 'role'=>'Sales',     'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>5, 'name'=>'Made Wirawan',    'username'=>'finance_user',   'email'=>'finance_user@arunika.test',   'password'=>Hash::make('Demo2026!'), 'role'=>'Finance',   'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>6, 'name'=>'Rita Kusuma',     'username'=>'purchase_user',  'email'=>'purchase_user@arunika.test',  'password'=>Hash::make('Demo2026!'), 'role'=>'Purchase',  'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>7, 'name'=>'Melati Yusuf',    'username'=>'hr_user',        'email'=>'hr_user@arunika.test',        'password'=>Hash::make('Demo2026!'), 'role'=>'HR',        'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>8, 'name'=>'Hendra Widodo',   'username'=>'owner_user',     'email'=>'owner_user@arunika.test',     'password'=>Hash::make('Demo2026!'), 'role'=>'Owner',     'created_at'=>$now, 'updated_at'=>$now],
        ]);

        // Employees
        DB::table('employees')->insert([
            ['id'=>'E01', 'name'=>'Sari Prameswari', 'department_id'=>'D-WH',  'job_title'=>'Supervisor Gudang',   'location'=>'WH-JKT', 'status'=>'Aktif',    'mapped_user_id'=>1,    'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>'E02', 'name'=>'Arif Maulana',    'department_id'=>'D-WH',  'job_title'=>'Petugas Gudang',      'location'=>'WH-JKT', 'status'=>'Aktif',    'mapped_user_id'=>2,    'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>'E03', 'name'=>'Naya Putri',       'department_id'=>'D-WH',  'job_title'=>'Petugas Gudang',      'location'=>'WH-JKT', 'status'=>'Nonaktif', 'mapped_user_id'=>null, 'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>'E04', 'name'=>'Dini Pratiwi',     'department_id'=>'D-SAL', 'job_title'=>'Sales Lead',          'location'=>'JKT',    'status'=>'Aktif',    'mapped_user_id'=>3,    'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>'E05', 'name'=>'Farhan Saputra',   'department_id'=>'D-SAL', 'job_title'=>'Sales Executive',     'location'=>'JKT',    'status'=>'Aktif',    'mapped_user_id'=>4,    'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>'E06', 'name'=>'Made Wirawan',     'department_id'=>'D-FIN', 'job_title'=>'Finance Officer',     'location'=>'JKT',    'status'=>'Aktif',    'mapped_user_id'=>5,    'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>'E07', 'name'=>'Rita Kusuma',      'department_id'=>'D-PUR', 'job_title'=>'Procurement Officer', 'location'=>'JKT',    'status'=>'Aktif',    'mapped_user_id'=>6,    'created_at'=>$now, 'updated_at'=>$now],
            ['id'=>'E08', 'name'=>'Melati Yusuf',     'department_id'=>'D-HR',  'job_title'=>'HR Administrator',    'location'=>'JKT',    'status'=>'Aktif',    'mapped_user_id'=>7,    'created_at'=>$now, 'updated_at'=>$now],
        ]);

        // Employee Private Records (SR-15 — HR only)
        DB::table('employee_private_records')->insert([
            ['employee_id'=>'E01', 'personal_email'=>'sari.private@example.test', 'emergency_contact'=>'Nila Prameswari', 'private_phone'=>'0812-0000-0101', 'created_at'=>$now, 'updated_at'=>$now],
            ['employee_id'=>'E03', 'personal_email'=>'naya.private@example.test', 'emergency_contact'=>'Laras Putri',     'private_phone'=>'0812-0000-0303', 'created_at'=>$now, 'updated_at'=>$now],
        ]);

        // ── Audit Log Seed (M3-W3-04 · F-21 · S-18) ────────────────────────
        // NOTE: old_values / new_values must be JSON strings when using
        // DB::table()->insert() — Eloquent casts are bypassed here.
        $rows = [];

        // Employee created entries (8 employees)
        foreach (['E01','E02','E03','E04','E05','E06','E07','E08'] as $eid) {
            $rows[] = [
                'document_type'  => 'employee',
                'document_id'    => $eid,
                'action'         => 'created',
                'actor_user'     => 'system',
                'actor_employee' => null,
                'description'    => 'Seeded dari FmcgSeeder',
                'old_values'     => null,
                'new_values'     => null,
                'acted_at'       => $now,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        // S00007 — Sales Order: confirm + reserve (F-21, T-35)
        $rows[] = [
            'document_type'  => 'sales_order',
            'document_id'    => 'S00007',
            'action'         => 'confirm',
            'actor_user'     => 'sales_user',
            'actor_employee' => 'E04',
            'description'    => 'Sales Order S00007 dikonfirmasi. Customer: C01, 8xP01, net Rp200.000, gross Rp222.000. Harga terkunci (SR-12).',
            'old_values'     => '{"status":"draft"}',
            'new_values'     => '{"status":"confirmed"}',
            'acted_at'       => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];
        $rows[] = [
            'document_type'  => 'sales_order',
            'document_id'    => 'S00007',
            'action'         => 'reserve',
            'actor_user'     => 'warehouse_mgr',
            'actor_employee' => 'E01',
            'description'    => 'Reservasi 8 unit P01 di WH-JKT untuk S00007. On Hand 60, Reserved 8, Available 52. (SR-22)',
            'old_values'     => '{"reserved":0,"available":60}',
            'new_values'     => '{"reserved":8,"available":52}',
            'acted_at'       => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        // PO00007 — Purchase Order: confirm (F-21, T-35, T-36)
        $rows[] = [
            'document_type'  => 'purchase_order',
            'document_id'    => 'PO00007',
            'action'         => 'confirm',
            'actor_user'     => 'purchase_user',
            'actor_employee' => 'E07',
            'description'    => 'PO00007 dikonfirmasi. Vendor: V04 PT Personal Sentosa, 20xP05, net Rp640.000, gross est. Rp710.400. Unit price terkunci (SR-21). Related Receipt=0, Bill=0.',
            'old_values'     => '{"status":"rfq"}',
            'new_values'     => '{"status":"confirmed"}',
            'acted_at'       => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        // L00003 — Lead CRM (F-21, T-07, T-08)
        $rows[] = [
            'document_type'  => 'lead',
            'document_id'    => 'L00003',
            'action'         => 'created',
            'actor_user'     => 'sales_user',
            'actor_employee' => 'E04',
            'description'    => 'Lead L00003 dibuat. Customer: C03 UD Berkah Niaga. Expected Revenue: Rp500.000. Salesperson: E04.',
            'old_values'     => null,
            'new_values'     => '{"stage":"new","customer":"C03","revenue":500000}',
            'acted_at'       => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        // Login audit entries (F-01, F-21, T-01, T-02)
        $loginUsers = [
            'sales_user'    => 'Sales',
            'warehouse_mgr' => 'Warehouse',
            'hr_user'       => 'HR',
            'owner_user'    => 'Owner',
        ];
        foreach ($loginUsers as $uname => $role) {
            $rows[] = [
                'document_type'  => 'access_control',
                'document_id'    => $uname,
                'action'         => 'login',
                'actor_user'     => $uname,
                'actor_employee' => null,
                'description'    => "User [{$uname}] login berhasil. Role: {$role}. (F-01, SR-15)",
                'old_values'     => null,
                'new_values'     => null,
                'acted_at'       => $now,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        // E03 Nonaktif — status change (SR-18, T-30)
        $rows[] = [
            'document_type'  => 'employee',
            'document_id'    => 'E03',
            'action'         => 'status_changed',
            'actor_user'     => 'hr_user',
            'actor_employee' => 'E08',
            'description'    => 'Status E03 Naya Putri diubah dari Aktif menjadi Nonaktif. Efektif segera (SR-18). E03 tidak boleh sign-off (F-19, T-18, T-30).',
            'old_values'     => '{"status":"Aktif"}',
            'new_values'     => '{"status":"Nonaktif"}',
            'acted_at'       => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        // T-02 — Access denied: sales_user mencoba Finance page (SR-15, F-21)
        $rows[] = [
            'document_type'  => 'access_control',
            'document_id'    => 'sales_user',
            'action'         => 'access_denied',
            'actor_user'     => 'sales_user',
            'actor_employee' => 'E04',
            'description'    => 'Role [Sales] ditolak akses ke [accounting]. SR-15: Finance page hanya untuk role Finance/Owner. Tidak ada perubahan saldo. (T-02)',
            'old_values'     => null,
            'new_values'     => null,
            'acted_at'       => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        // T-18 — E03 sign-off rejected (SR-17, SR-18)
        $rows[] = [
            'document_type'  => 'receipt',
            'document_id'    => 'RCV-ATTEMPT-001',
            'action'         => 'sign_off_rejected',
            'actor_user'     => 'system',
            'actor_employee' => 'E03',
            'description'    => 'Sign-off ditolak: E03 Naya Putri berstatus Nonaktif (SR-17, SR-18). Hanya Employee Aktif departemen Warehouse dengan mapped user boleh sign-off. (T-18)',
            'old_values'     => null,
            'new_values'     => null,
            'acted_at'       => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        DB::table('audit_logs')->insert($rows);
    }
}
