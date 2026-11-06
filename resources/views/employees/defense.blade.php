@extends('layouts.app')
@section('title','Defense Prep — Modul Employees')
@php
    $currentApp  = 'Employees';
    $sidebarMenu = [
        ['label'=>'Semua Pegawai', 'url'=>route('employees.index'),   'icon'=>'👥'],
        ['label'=>'Defense Prep',  'url'=>route('employees.defense'), 'icon'=>'🛡️'],
    ];
@endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-400">›</span>
    <a href="{{ route('employees.index') }}" class="hover:text-[#714B67]">Employees</a>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700 font-medium">Defense Prep — M3-W4-04</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-[#714B67] to-[#4a2e47] rounded-2xl px-8 py-7">
        <div class="absolute -right-8 -top-8 w-40 h-40 bg-white/5 rounded-full"></div>
        <div class="absolute -right-2 bottom-0 w-24 h-24 bg-white/5 rounded-full"></div>
        <p class="text-white/70 text-xs uppercase tracking-widest mb-1 z-10 relative">M3-W4-04 · Siap Defense Modul Employees</p>
        <h1 class="text-white font-bold text-xl z-10 relative">Kontrol Akses, Sign-off & Lintas Modul</h1>
        <p class="text-white/60 text-xs mt-2 z-10 relative">SR-15 · SR-17 · SR-18 · F-18 · F-19 · F-21 · T-27–T-30</p>
    </div>

    {{-- 3-column overview --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- SR-15: Data Privat --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-purple-50 border-b border-purple-100 flex items-center gap-2">
                <span class="text-lg">🔐</span>
                <h2 class="font-bold text-purple-800 text-sm">SR-15 · Privasi Data</h2>
            </div>
            <div class="px-5 py-4 space-y-3 text-xs">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="font-semibold text-gray-600 mb-1">Stakeholder</p>
                    <p class="text-gray-500">Melati Yusuf — SDM</p>
                    <p class="text-gray-400 italic mt-1">"Data pribadi pegawai tidak boleh ditampilkan ke seluruh pengguna."</p>
                </div>
                <div class="space-y-2">
                    <p class="font-semibold text-gray-600">Implementasi</p>
                    <div class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <p class="text-gray-600"><code class="bg-gray-100 px-1 rounded">EmployeeController::show()</code> cek <code class="bg-gray-100 px-1 rounded">$isHR = auth()->user()->isHR()</code></p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <p class="text-gray-600">Jika bukan HR → <code class="bg-gray-100 px-1 rounded">$privateRecord = null</code>, blok view tidak render</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <p class="text-gray-600">Data privat (email, phone, emergency) disimpan di tabel terpisah <code class="bg-gray-100 px-1 rounded">employee_private_records</code></p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <p class="text-gray-600">Akses non-HR + <code class="bg-gray-100 px-1 rounded">?private</code> param → dicatat sebagai <code class="bg-gray-100 px-1 rounded">private_access_denied</code> di audit</p>
                    </div>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                    <p class="font-semibold text-green-700 text-[11px]">T-28 PASS ✓</p>
                    <p class="text-green-600 text-[11px] mt-0.5">sales_user → personal_email tidak tampil. hr_user → data privat lengkap.</p>
                </div>
            </div>
        </div>

        {{-- SR-17 + SR-18: Sign-off --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-blue-50 border-b border-blue-100 flex items-center gap-2">
                <span class="text-lg">✍️</span>
                <h2 class="font-bold text-blue-800 text-sm">SR-17/18 · Sign-off Control</h2>
            </div>
            <div class="px-5 py-4 space-y-3 text-xs">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="font-semibold text-gray-600 mb-1">Aturan</p>
                    <p class="text-gray-500">Delivery & Receipt memerlukan: Employee Aktif + Dept Warehouse + mapped_user aktif.</p>
                    <p class="text-gray-500 mt-1">Data Employee terpisah dari User Account. Perubahan status efektif segera.</p>
                </div>
                <div class="space-y-2">
                    <p class="font-semibold text-gray-600">Implementasi canSignOff()</p>
                    <div class="bg-gray-900 rounded-lg px-3 py-2.5 font-mono text-[10px] text-green-400 leading-relaxed">
                        return $this->status === 'Aktif'<br>
                        &nbsp;&nbsp;&amp;&amp; $this->department_id === 'D-WH'<br>
                        &nbsp;&amp;&amp; $this->mapped_user_id !== null;
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <p class="text-gray-600">E01 Sari Prameswari (Aktif + D-WH + mapped) → <strong>BOLEH sign-off</strong></p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-red-500 mt-0.5">✗</span>
                        <p class="text-gray-600">E03 Naya Putri (Nonaktif + no mapped) → <strong>DITOLAK</strong></p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-red-500 mt-0.5">✗</span>
                        <p class="text-gray-600">E04 Dini Pratiwi (D-SAL) → <strong>DITOLAK</strong> (bukan Warehouse)</p>
                    </div>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                    <p class="font-semibold text-green-700 text-[11px]">T-29, T-30 PASS ✓</p>
                    <p class="text-green-600 text-[11px] mt-0.5">E01 toggle Nonaktif → validateSignOff → 422 Nonaktif. Efektif segera.</p>
                </div>
            </div>
        </div>

        {{-- F-01: Role-based Menu --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-orange-50 border-b border-orange-100 flex items-center gap-2">
                <span class="text-lg">🛡️</span>
                <h2 class="font-bold text-orange-800 text-sm">F-01 · Role-based Access</h2>
            </div>
            <div class="px-5 py-4 space-y-3 text-xs">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="font-semibold text-gray-600 mb-1">App Switcher Per Role</p>
                    <div class="space-y-1 mt-1">
                        @php
                        $roleMenus = [
                            'Sales'     => ['Sales','CRM'],
                            'Warehouse' => ['Inventory'],
                            'Finance'   => ['Accounting','Invoicing','Reporting'],
                            'Purchase'  => ['Purchase','Inventory'],
                            'HR'        => ['Employees','Wireframes'],
                            'Owner'     => ['Semua (9 modul)'],
                        ];
                        @endphp
                        @foreach($roleMenus as $role => $menus)
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded w-20 text-center">{{ $role }}</span>
                            <span class="text-gray-500">{{ implode(', ', $menus) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="space-y-2">
                    <p class="font-semibold text-gray-600">Middleware Stack</p>
                    <div class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <p class="text-gray-600"><code class="bg-gray-100 px-1 rounded">auth</code> → redirect ke login jika belum login</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <p class="text-gray-600"><code class="bg-gray-100 px-1 rounded">role:Finance,Owner</code> → 403 + audit log</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <p class="text-gray-600"><code class="bg-gray-100 px-1 rounded">User::allowedMenus()</code> → hanya tampilkan menu izin</p>
                    </div>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                    <p class="font-semibold text-green-700 text-[11px]">T-01, T-02 PASS ✓</p>
                    <p class="text-green-600 text-[11px] mt-0.5">sales_user: Sales+CRM saja. Finance 403. Saldo tidak berubah.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Cross-module integration --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                🔗 Integrasi Lintas Modul — Employees ↔ Inventory ↔ Audit
            </h2>
            <p class="text-xs text-gray-400 mt-1">F-19 · SR-17 · SR-18 · F-21</p>
        </div>
        <div class="p-5">
            {{-- Flow diagram --}}
            <div class="flex items-center gap-2 flex-wrap text-xs mb-5">
                @php
                $flow = [
                    ['label'=>'Employee Active', 'sub'=>'status=Aktif, D-WH', 'color'=>'bg-purple-100 text-purple-700 border-purple-200'],
                    ['arrow'=>true],
                    ['label'=>'Validator Auth', 'sub'=>'canSignOff()=true', 'color'=>'bg-blue-100 text-blue-700 border-blue-200'],
                    ['arrow'=>true],
                    ['label'=>'Receipt / Delivery Sign-off', 'sub'=>'actor_employee=E01', 'color'=>'bg-green-100 text-green-700 border-green-200'],
                    ['arrow'=>true],
                    ['label'=>'Stock Move + Audit', 'sub'=>'F-21, SR-16', 'color'=>'bg-orange-100 text-orange-700 border-orange-200'],
                ];
                @endphp
                @foreach($flow as $step)
                @if(isset($step['arrow']))
                <span class="text-gray-300 text-xl font-bold">→</span>
                @else
                <div class="flex flex-col items-center px-4 py-2.5 rounded-xl border {{ $step['color'] }} text-center">
                    <span class="font-bold text-[11px]">{{ $step['label'] }}</span>
                    <span class="text-[10px] opacity-75 mt-0.5">{{ $step['sub'] }}</span>
                </div>
                @endif
                @endforeach
            </div>

            {{-- Data table lintas modul --}}
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-y border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2 text-gray-500 font-semibold text-[10px] uppercase">Modul</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-semibold text-[10px] uppercase">Trigger</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-semibold text-[10px] uppercase">Employee Check</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-semibold text-[10px] uppercase">Audit Tercatat</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-semibold text-[10px] uppercase">SR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                    $crossModuleData = [
                        ['modul'=>'Inventory','trigger'=>'Delivery Done', 'check'=>'canSignOff() → E01 Aktif, D-WH', 'audit'=>'sign_off_authorized + actor_employee=E01', 'sr'=>'SR-17'],
                        ['modul'=>'Inventory','trigger'=>'Receipt Validate', 'check'=>'canSignOff() → E01 Aktif, D-WH', 'audit'=>'receipt validated + employee_id=E01', 'sr'=>'SR-17'],
                        ['modul'=>'Inventory','trigger'=>'E03 sign-off attempt', 'check'=>'canSignOff()=false (Nonaktif)', 'audit'=>'sign_off_rejected + reason', 'sr'=>'SR-18'],
                        ['modul'=>'HR','trigger'=>'Toggle Status E01 → Nonaktif', 'check'=>'isHR() guard (role:HR,Owner)', 'audit'=>'status_changed old=Aktif new=Nonaktif', 'sr'=>'SR-18'],
                        ['modul'=>'Auth','trigger'=>'Login berhasil', 'check'=>'allowedMenus() per role', 'audit'=>'login + role', 'sr'=>'SR-15'],
                        ['modul'=>'Auth','trigger'=>'Akses Finance page by Sales', 'check'=>'CheckRole middleware → 403', 'audit'=>'access_denied + path', 'sr'=>'SR-15'],
                    ];
                    @endphp
                    @foreach($crossModuleData as $row)
                    <tr class="hover:bg-gray-50/60">
                        <td class="px-4 py-2.5">
                            <span class="font-semibold text-[#714B67] bg-purple-50 px-2 py-0.5 rounded text-[10px]">{{ $row['modul'] }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-gray-700 font-medium">{{ $row['trigger'] }}</td>
                        <td class="px-4 py-2.5 text-gray-600 font-mono text-[10px]">{{ $row['check'] }}</td>
                        <td class="px-4 py-2.5 text-gray-500 font-mono text-[10px]">{{ $row['audit'] }}</td>
                        <td class="px-4 py-2.5">
                            <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded">{{ $row['sr'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Decision Log ringkas --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                📝 Decision Log — Konflik & Keputusan Modul Employees
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-gray-500 font-semibold text-[10px] uppercase">Konflik SR</th>
                        <th class="text-left px-4 py-2.5 text-gray-500 font-semibold text-[10px] uppercase">Opsi yang Dipertimbangkan</th>
                        <th class="text-left px-4 py-2.5 text-gray-500 font-semibold text-[10px] uppercase">Keputusan</th>
                        <th class="text-left px-4 py-2.5 text-gray-500 font-semibold text-[10px] uppercase">Trade-off & Pihak Terdampak</th>
                        <th class="text-left px-4 py-2.5 text-gray-500 font-semibold text-[10px] uppercase">Bukti T</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                    $decisions = [
                        [
                            'sr'    => 'SR-15 vs usability',
                            'opts'  => 'A: Sembunyikan kolom di view saja (cepat). B: Pisah tabel + API-level guard (aman)',
                            'dec'   => 'Opsi B dipilih. Data privat di tabel employee_private_records terpisah, hanya di-query jika isHR()=true',
                            'trade' => 'Non-HR tidak bisa lihat walau inspect HTML. Trade-off: lebih kompleks. Terdampak: semua non-HR tidak bisa lihat data privat',
                            'test'  => 'T-28',
                        ],
                        [
                            'sr'    => 'SR-17 vs SR-18',
                            'opts'  => 'A: Cek hanya status Employee. B: Cek status + dept + mapped_user (lebih ketat)',
                            'dec'   => 'Opsi B: canSignOff() = status=Aktif AND dept=D-WH AND mapped_user≠null. Saling melengkapi, bukan pilih salah satu',
                            'trade' => 'Lebih ketat. Pegawai aktif di dept lain tidak bisa sign-off gudang. Terdampak: HR/Finance tidak bisa validasi penerimaan barang',
                            'test'  => 'T-29, T-30',
                        ],
                        [
                            'sr'    => 'SR-18 efektif segera',
                            'opts'  => 'A: Revoke saat next login. B: Revoke segera real-time (cek DB tiap request)',
                            'dec'   => 'Opsi B dipilih. canSignOff() query DB langsung, tidak ada cache. Toggle status langsung efektif',
                            'trade' => 'Performa: 1 query extra per validasi. Terdampak: HR perlu hati-hati — toggle status langsung block semua operasi gudang',
                            'test'  => 'T-30',
                        ],
                    ];
                    @endphp
                    @foreach($decisions as $d)
                    <tr class="hover:bg-gray-50/60 align-top">
                        <td class="px-4 py-3">
                            <span class="font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded text-[10px] font-mono">{{ $d['sr'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 max-w-xs">{{ $d['opts'] }}</td>
                        <td class="px-4 py-3 text-gray-700 font-medium max-w-xs">{{ $d['dec'] }}</td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs">{{ $d['trade'] }}</td>
                        <td class="px-4 py-3">
                            <span class="font-mono font-bold text-[#714B67] text-[11px]">{{ $d['test'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Live data check --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
        <h2 class="font-bold text-gray-800 text-sm mb-4">📊 Live Data Check — Seed Baseline</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @php
            $liveStats = [
                ['label'=>'Total Pegawai', 'value'=> \App\Models\Employee::count(), 'target'=>'8', 'icon'=>'👥'],
                ['label'=>'Pegawai Aktif', 'value'=> \App\Models\Employee::where('status','Aktif')->count(), 'target'=>'7', 'icon'=>'🟢'],
                ['label'=>'Pegawai Nonaktif', 'value'=> \App\Models\Employee::where('status','Nonaktif')->count(), 'target'=>'1', 'icon'=>'🔴'],
                ['label'=>'Dept Warehouse', 'value'=> \App\Models\Employee::where('department_id','D-WH')->count(), 'target'=>'3', 'icon'=>'🏭'],
                ['label'=>'Bisa Sign-off', 'value'=> \App\Models\Employee::where('status','Aktif')->where('department_id','D-WH')->whereNotNull('mapped_user_id')->count(), 'target'=>'2', 'icon'=>'✍️'],
                ['label'=>'Punya Private Record', 'value'=> \App\Models\EmployeePrivateRecord::count(), 'target'=>'2', 'icon'=>'🔐'],
                ['label'=>'User Accounts', 'value'=> \App\Models\User::count(), 'target'=>'8', 'icon'=>'👤'],
                ['label'=>'Audit Log Entries', 'value'=> \App\Models\AuditLog::count(), 'target'=>'19+', 'icon'=>'📋'],
            ];
            @endphp
            @foreach($liveStats as $stat)
            @php $ok = is_numeric($stat['target']) ? $stat['value'] == $stat['target'] : true; @endphp
            <div class="bg-gray-50 rounded-xl p-3 border {{ $ok ? 'border-green-200' : 'border-red-200' }}">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-base">{{ $stat['icon'] }}</span>
                    <span class="text-[10px] font-bold {{ $ok ? 'text-green-600' : 'text-red-500' }}">
                        {{ $ok ? '✓' : '✗' }} target: {{ $stat['target'] }}
                    </span>
                </div>
                <p class="text-2xl font-black text-gray-800">{{ $stat['value'] }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ $stat['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
