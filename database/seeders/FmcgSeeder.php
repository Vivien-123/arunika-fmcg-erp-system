<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FmcgSeeder extends Seeder
{
    public function run(): void
    {
        // Clear in dependency order
        DB::table('audit_logs')->delete();
        DB::table('employee_private_records')->delete();
        DB::table('employees')->delete();
        DB::table('users')->delete();
        DB::table('departments')->delete();

        // Departments
        DB::table('departments')->insert([
            ['dept_id'=>'D-WH',  'name'=>'Warehouse',       'manager_employee'=>'E01', 'company'=>'Arunika'],
            ['dept_id'=>'D-SAL', 'name'=>'Sales',           'manager_employee'=>'E04', 'company'=>'Arunika'],
            ['dept_id'=>'D-FIN', 'name'=>'Finance',         'manager_employee'=>'E06', 'company'=>'Arunika'],
            ['dept_id'=>'D-PUR', 'name'=>'Procurement',     'manager_employee'=>'E07', 'company'=>'Arunika'],
            ['dept_id'=>'D-HR',  'name'=>'Human Resources', 'manager_employee'=>'E08', 'company'=>'Arunika'],
        ]);

        // Users
        DB::table('users')->insert([
            ['id'=>1,'name'=>'Sari Prameswari','username'=>'warehouse_mgr', 'email'=>'warehouse_mgr@arunika.test', 'password'=>Hash::make('Demo2026!'),'role'=>'Warehouse'],
            ['id'=>2,'name'=>'Arif Maulana',   'username'=>'warehouse_user','email'=>'warehouse_user@arunika.test','password'=>Hash::make('Demo2026!'),'role'=>'Warehouse'],
            ['id'=>3,'name'=>'Dini Pratiwi',   'username'=>'sales_user',    'email'=>'sales_user@arunika.test',    'password'=>Hash::make('Demo2026!'),'role'=>'Sales'],
            ['id'=>4,'name'=>'Farhan Saputra', 'username'=>'sales_user2',   'email'=>'sales_user2@arunika.test',   'password'=>Hash::make('Demo2026!'),'role'=>'Sales'],
            ['id'=>5,'name'=>'Made Wirawan',   'username'=>'finance_user',  'email'=>'finance_user@arunika.test',  'password'=>Hash::make('Demo2026!'),'role'=>'Finance'],
            ['id'=>6,'name'=>'Rita Kusuma',    'username'=>'purchase_user', 'email'=>'purchase_user@arunika.test', 'password'=>Hash::make('Demo2026!'),'role'=>'Purchase'],
            ['id'=>7,'name'=>'Melati Yusuf',   'username'=>'hr_user',       'email'=>'hr_user@arunika.test',       'password'=>Hash::make('Demo2026!'),'role'=>'HR'],
            ['id'=>8,'name'=>'Hendra Widodo',  'username'=>'owner_user',    'email'=>'owner_user@arunika.test',    'password'=>Hash::make('Demo2026!'),'role'=>'Owner'],
        ]);

        // Employees
        DB::table('employees')->insert([
            ['id'=>'E01','name'=>'Sari Prameswari','department_id'=>'D-WH', 'job_title'=>'Supervisor Gudang',   'location'=>'WH-JKT','status'=>'Aktif',   'mapped_user_id'=>1],
            ['id'=>'E02','name'=>'Arif Maulana',   'department_id'=>'D-WH', 'job_title'=>'Petugas Gudang',      'location'=>'WH-JKT','status'=>'Aktif',   'mapped_user_id'=>2],
            ['id'=>'E03','name'=>'Naya Putri',      'department_id'=>'D-WH', 'job_title'=>'Petugas Gudang',      'location'=>'WH-JKT','status'=>'Nonaktif','mapped_user_id'=>null],
            ['id'=>'E04','name'=>'Dini Pratiwi',    'department_id'=>'D-SAL','job_title'=>'Sales Lead',          'location'=>'JKT',   'status'=>'Aktif',   'mapped_user_id'=>3],
            ['id'=>'E05','name'=>'Farhan Saputra',  'department_id'=>'D-SAL','job_title'=>'Sales Executive',     'location'=>'JKT',   'status'=>'Aktif',   'mapped_user_id'=>4],
            ['id'=>'E06','name'=>'Made Wirawan',    'department_id'=>'D-FIN','job_title'=>'Finance Officer',     'location'=>'JKT',   'status'=>'Aktif',   'mapped_user_id'=>5],
            ['id'=>'E07','name'=>'Rita Kusuma',     'department_id'=>'D-PUR','job_title'=>'Procurement Officer', 'location'=>'JKT',   'status'=>'Aktif',   'mapped_user_id'=>6],
            ['id'=>'E08','name'=>'Melati Yusuf',    'department_id'=>'D-HR', 'job_title'=>'HR Administrator',    'location'=>'JKT',   'status'=>'Aktif',   'mapped_user_id'=>7],
        ]);

        // Employee Private Records (SR-15 — HR only)
        DB::table('employee_private_records')->insert([
            ['employee_id'=>'E01','personal_email'=>'sari.private@example.test', 'emergency_contact'=>'Nila Prameswari','private_phone'=>'0812-0000-0101'],
            ['employee_id'=>'E03','personal_email'=>'naya.private@example.test', 'emergency_contact'=>'Laras Putri',    'private_phone'=>'0812-0000-0303'],
        ]);

        // Audit log seed entries
        $now  = now();
        $rows = [];
        foreach (['E01','E02','E03','E04','E05','E06','E07','E08'] as $eid) {
            $rows[] = ['document_type'=>'employee','document_id'=>$eid,'action'=>'created','actor_user'=>'system','actor_employee'=>null,'description'=>'Seeded','old_values'=>null,'new_values'=>null,'acted_at'=>$now,'created_at'=>$now,'updated_at'=>$now];
        }
        DB::table('audit_logs')->insert($rows);
    }
}
