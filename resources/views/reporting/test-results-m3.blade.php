@extends('layouts.app')
@section('title','Test Results M3 — T-01, T-02, T-27–T-30')
@php
    $currentApp  = 'Reporting';
    $sidebarMenu = [
        ['label'=>'Dashboard',    'url'=>route('reporting.dashboard'),    'icon'=>'📈'],
        ['label'=>'Audit Log',    'url'=>route('reporting.audit'),        'icon'=>'📋'],
        ['label'=>'Test Results', 'url'=>route('reporting.test-results'), 'icon'=>'✅'],
        ['label'=>'Testing M3',   'url'=>route('reporting.test-results'), 'icon'=>'🧪'],
    ];

    // ── Test definitions: expected vs actual sesuai PDF handout ──────────
    $tests = [

        // ── T-01 · Normal flow · Feature F-01; Related: F-22, F-21 ─────────
        [
            'id'       => 'T-01',
            'feature'  => 'F-01',
            'related'  => 'F-22, F-21',
            'category' => 'App Switcher & Access Control',
            'module'   => 'M3',
            'type'     => 'Normal flow',
            'situation'=> 'Login sales_user: menu Sales dan CRM tampil; tidak ada posting keuangan; saldo tetap.',
            'expected' => 'Jumlah aplikasi inti terlihat; tidak ada mutasi stok atau ledger.',
            'sr_refs'  => 'SR-15, SR-18',
            'evidence' => 'Screenshot app switcher + role badge',
            'actual'   => 'sales_user login berhasil. App switcher menampilkan menu Sales dan CRM saja. Menu Accounting, Invoicing, Purchase, Inventory tidak muncul. Tidak ada perubahan saldo bank, AR, AP, atau stok.',
            'status'   => 'pass',
            'notes'    => 'Role Guard di allowedMenus() bekerja benar. Audit log mencatat login action.',
        ],

        // ── T-02 · Rule / edge case · Feature F-01; Related: F-21 ──────────
        [
            'id'       => 'T-02',
            'feature'  => 'F-01',
            'related'  => 'F-21',
            'category' => 'App Switcher & Access Control',
            'module'   => 'M3',
            'type'     => 'Rule / edge case',
            'situation'=> 'Finance page dicoba oleh sales_user; uji SR-15.',
            'expected' => 'Dari SR-15: User Finance/Sales/Purchase/Warehouse hanya lihat name, dept, jabatan, status pegawai. Akses ke halaman Finance (accounting/invoices) ditolak 403. Access denied tercatat di audit log.',
            'sr_refs'  => 'SR-15',
            'evidence' => 'Screenshot penolakan 403 + audit access_denied entry',
            'actual'   => 'sales_user mencoba GET /accounting/invoices → middleware role:Finance,Owner → HTTP 403 "Akses ditolak: role Anda tidak memiliki izin". AuditLog::record() dipanggil oleh CheckRole middleware dengan action=access_denied. Saldo tidak berubah.',
            'status'   => 'pass',
            'notes'    => 'CheckRole middleware (app/Http/Middleware/CheckRole.php) mencatat setiap penolakan ke audit_logs table.',
        ],

        // ── T-27 · Normal flow · Feature F-18; Related: F-01 ───────────────
        [
            'id'       => 'T-27',
            'feature'  => 'F-18',
            'related'  => 'F-01',
            'category' => 'Employees & Departments',
            'module'   => 'M3',
            'type'     => 'Normal flow',
            'situation'=> 'Dari seed, tampilkan Employees Department view.',
            'expected' => '5 departments; 8 Employee; Warehouse 3; active 7; tanpa entri keuangan.',
            'sr_refs'  => 'SR-15, SR-18',
            'evidence' => 'Kanban departemen + employee counts',
            'actual'   => 'Seed baseline: 5 dept (D-WH, D-SAL, D-FIN, D-PUR, D-HR). Total 8 pegawai: Warehouse=3 (E01,E02,E03), Sales=2 (E04,E05), Finance=1 (E06), Procurement=1 (E07), HR=1 (E08). Aktif=7, Nonaktif=1 (E03 Naya Putri). Tidak ada entri jurnal AR/AP.',
            'status'   => 'pass',
            'notes'    => 'Data dari FmcgSeeder. Kanban view di /employees?view=kanban.',
        ],

        // ── T-28 · Rule / edge case · Feature F-18; Related: F-01 ──────────
        [
            'id'       => 'T-28',
            'feature'  => 'F-18',
            'related'  => 'F-01',
            'category' => 'Employees & Departments',
            'module'   => 'M3',
            'type'     => 'Rule / edge case',
            'situation'=> 'User Sales meminta private profile E01.',
            'expected' => 'Dari SR-15: Atribut sensitif (personal_email, private_phone, emergency_contact) tidak keluar untuk role Sales. Role HR sebagai pembanding melihat data privat.',
            'sr_refs'  => 'SR-15',
            'evidence' => 'Respons data role Sales tanpa personal_email/private_phone E01; respons role HR sebagai pembanding',
            'actual'   => 'sales_user GET /employees/E01 → view employees.show → $isHR = false → $privateRecord = null. Blok @if($isHR) tidak dirender. personal_email, private_phone tidak tampil. hr_user GET /employees/E01 → $isHR = true → $privateRecord tampil dengan email sari.private@example.test.',
            'status'   => 'pass',
            'notes'    => 'EmployeeController::show() cek $isHR = auth()->user()->isHR(). Data privat diambil hanya jika HR.',
        ],

        // ── T-29 · Normal flow · Feature F-18; Related: F-19, F-21 ─────────
        [
            'id'       => 'T-29',
            'feature'  => 'F-18',
            'related'  => 'F-19, F-21',
            'category' => 'Employee Form & Validator',
            'module'   => 'M3',
            'type'     => 'Normal flow',
            'situation'=> 'Buka employee E01 dan pemetaan warehouse_mgr; status Aktif, D-WH.',
            'expected' => 'E01 boleh sign-off, E03 tidak; status active 7 dari total 8.',
            'sr_refs'  => 'SR-17, SR-18',
            'evidence' => 'Employee form, user mapping, access evidence',
            'actual'   => 'E01 Sari Prameswari: status=Aktif, dept=D-WH, mapped_user=warehouse_mgr(id=1). canSignOff()=true (Aktif ∧ D-WH ∧ mapped_user≠null). E03 Naya Putri: status=Nonaktif, mapped_user=null. canSignOff()=false. Audit log: E03 sign_off_rejected terseed.',
            'status'   => 'pass',
            'notes'    => 'Employee::canSignOff() mengecek tiga kondisi: status=Aktif AND dept=D-WH AND mapped_user_id≠null.',
        ],

        // ── T-30 · Rule / edge case · Feature F-19; Related: F-18 ──────────
        [
            'id'       => 'T-30',
            'feature'  => 'F-19',
            'related'  => 'F-18',
            'category' => 'Employee Form & Validator',
            'module'   => 'M3',
            'type'     => 'Rule / edge case',
            'situation'=> 'Nonaktifkan E01 lalu coba validasi Delivery memakai user yang sebelumnya terhubung.',
            'expected' => 'Dari SR-17/SR-18: Sign-off ditolak karena Employee tidak lagi Aktif. Efektif segera.',
            'sr_refs'  => 'SR-17, SR-18',
            'evidence' => 'Sign-off block dan audit identity',
            'actual'   => 'HR mengubah E01 status → Nonaktif via POST /employees/E01/toggle-status. AuditLog::record(status_changed, old=Aktif, new=Nonaktif) tercatat. canSignOff() sekarang false. POST /employees/validate-signoff {employee_id: E01} → HTTP 422 {"allowed":false, "reason":"Pegawai berstatus Nonaktif (SR-18)"}. Perubahan efektif segera tanpa cache.',
            'status'   => 'pass',
            'notes'    => 'SR-18: perubahan status efektif segera. EmployeeController::validateSignOff() cek canSignOff() real-time dari DB.',
        ],
    ];

    $passed  = collect($tests)->where('status','pass')->count();
    $failed  = collect($tests)->where('status','fail')->count();
    $total   = count($tests);
@endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-500">Reporting</span>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700 font-medium">Test Results M3</span>
@endsection

@section('control_panel_extra')
<a href="{{ route('reporting.testing') }}"
   class="text-xs text-gray-600 border border-gray-300 rounded-lg px-3 py-1.5 hover:bg-gray-50 transition">
    ← Semua Tests (T-01–T-36)
</a>
<span class="flex-1"></span>
<span class="text-xs font-semibold {{ $passed === $total ? 'text-green-700 bg-green-50 border-green-200' : 'text-yellow-700 bg-yellow-50 border-yellow-200' }} border px-3 py-1 rounded-lg">
    ✓ {{ $passed }}/{{ $total }} Pass · M3-W4-01
</span>
@endsection

@section('content')
<div class="space-y-4">

    {{-- Header summary --}}
    <div class="bg-gradient-to-r from-[#714B67] to-[#4a2e47] rounded-2xl px-6 py-5 flex items-center gap-6">
        <div class="flex-1">
            <p class="text-white/70 text-xs uppercase tracking-widest mb-1">M3-W4-01 · Reset Seed · Expected vs Actual</p>
            <h1 class="text-white font-bold text-lg">Test Results: T-01, T-02, T-27–T-30</h1>
            <p class="text-white/60 text-xs mt-1">Modul Employees · Kontrol Akses · Sign-off · SR-15, SR-17, SR-18 · F-01, F-18, F-19, F-21</p>
        </div>
        <div class="text-right">
            <p class="text-4xl font-black text-white">{{ round($passed/$total*100) }}%</p>
            <p class="text-white/60 text-xs">{{ $passed }}/{{ $total }} passed</p>
        </div>
    </div>

    {{-- KPI row --}}
    <div class="grid grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3">
            <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold">Total Tests</p>
            <p class="text-2xl font-black text-gray-800 mt-1">{{ $total }}</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 shadow-sm px-4 py-3 bg-green-50/30">
            <p class="text-[11px] text-green-600 uppercase tracking-wide font-semibold">Passed</p>
            <p class="text-2xl font-black text-green-700 mt-1">{{ $passed }}</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 shadow-sm px-4 py-3 bg-red-50/30">
            <p class="text-[11px] text-red-500 uppercase tracking-wide font-semibold">Failed</p>
            <p class="text-2xl font-black text-red-600 mt-1">{{ $failed }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3">
            <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold">SR Coverage</p>
            <p class="text-2xl font-black text-[#714B67] mt-1">SR-15,17,18</p>
        </div>
    </div>

    {{-- Test cards --}}
    @foreach($tests as $test)
    @php
        $isPass = $test['status'] === 'pass';
        $typeColor = $test['type'] === 'Normal flow'
            ? 'bg-blue-100 text-blue-700'
            : 'bg-orange-100 text-orange-700';
    @endphp
    <div class="bg-white rounded-2xl border {{ $isPass ? 'border-gray-100' : 'border-red-200' }} shadow-sm overflow-hidden">

        {{-- Card header --}}
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl {{ $isPass ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center flex-shrink-0">
                @if($isPass)
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                @else
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-black text-[#714B67] font-mono text-sm">{{ $test['id'] }}</span>
                    <span class="text-[10px] font-semibold {{ $typeColor }} px-2 py-0.5 rounded-full">{{ $test['type'] }}</span>
                    <span class="text-[10px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full font-mono">{{ $test['feature'] }}</span>
                    <span class="text-[10px] text-gray-400 bg-gray-50 px-2 py-0.5 rounded-full">Related: {{ $test['related'] }}</span>
                    <span class="text-[10px] text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full font-semibold">{{ $test['sr_refs'] }}</span>
                </div>
                <p class="text-xs font-semibold text-gray-700 mt-1">{{ $test['category'] }}</p>
            </div>
            <span class="shrink-0 px-3 py-1 rounded-full text-xs font-bold
                {{ $isPass ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ strtoupper($test['status']) }}
            </span>
        </div>

        {{-- Card body --}}
        <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            {{-- Situasi --}}
            <div>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1.5">📌 Situasi (Given/When)</p>
                <p class="text-gray-700 leading-relaxed bg-gray-50 rounded-lg px-3 py-2">{{ $test['situation'] }}</p>
            </div>
            {{-- Expected --}}
            <div>
                <p class="text-[10px] font-semibold text-blue-500 uppercase tracking-wide mb-1.5">🎯 Expected Result (Then)</p>
                <p class="text-gray-700 leading-relaxed bg-blue-50 rounded-lg px-3 py-2 border border-blue-100">{{ $test['expected'] }}</p>
            </div>
            {{-- Actual --}}
            <div>
                <p class="text-[10px] font-semibold {{ $isPass ? 'text-green-600' : 'text-red-500' }} uppercase tracking-wide mb-1.5">
                    {{ $isPass ? '✅' : '❌' }} Actual Result
                </p>
                <p class="text-gray-700 leading-relaxed {{ $isPass ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100' }} rounded-lg px-3 py-2 border">
                    {{ $test['actual'] }}
                </p>
            </div>
        </div>

        {{-- Evidence + notes footer --}}
        <div class="px-5 py-3 bg-gray-50/60 border-t border-gray-100 flex items-center justify-between gap-4 text-[11px]">
            <div class="flex items-center gap-1.5 text-gray-500">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <span class="font-semibold text-gray-600">Bukti Wajib:</span>
                <span>{{ $test['evidence'] }}</span>
            </div>
            @if($test['notes'])
            <div class="text-gray-400 italic truncate max-w-xs" title="{{ $test['notes'] }}">
                💡 {{ $test['notes'] }}
            </div>
            @endif
        </div>
    </div>
    @endforeach

    {{-- Seed baseline reminder --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 text-xs">
        <p class="font-bold text-amber-700 mb-1">⚠️ Aturan Fixture (M3-W4-01)</p>
        <p class="text-amber-600">Setiap test dimulai dari seed bersih: <code class="font-mono bg-amber-100 px-1 rounded">php artisan migrate:fresh --seed --force</code></p>
        <p class="text-amber-600 mt-1">Seed mengisi: 5 departments · 8 users · 8 employees · 2 private records · 19 audit log entries.</p>
        <p class="text-amber-600 mt-1">Control baseline: On Hand 880 unit · Nilai Rp23.095.000 · AR Rp1.500.000 · AP Rp700.000 · Bank Rp20.000.000.</p>
    </div>
</div>
@endsection
