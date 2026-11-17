@extends('layouts.app')
@section('title','Akses dan Keamanan Pegawai')
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
    <span class="text-gray-700 font-medium">Akses dan Keamanan</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-[#714B67] to-[#4a2e47] rounded-2xl px-8 py-7">
        <div class="absolute -right-8 -top-8 w-40 h-40 bg-white/5 rounded-full"></div>
        <div class="absolute -right-2 bottom-0 w-24 h-24 bg-white/5 rounded-full"></div>
        <h1 class="text-white font-bold text-xl z-10 relative">Akses Data dan Validasi Gudang</h1>
    </div>

    {{-- 3-column overview --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Private employee data --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-purple-50 border-b border-purple-100 flex items-center gap-2">
                <span class="text-lg">🔐</span>
                <h2 class="font-bold text-purple-800 text-sm">Privasi Data</h2>
            </div>
            <div class="px-5 py-4 space-y-3 text-xs">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="font-semibold text-gray-600 mb-1">Kebijakan</p>
                    <p class="text-gray-500">Informasi pribadi hanya dapat dilihat oleh pengguna HR.</p>
                </div>
                <div class="space-y-2">
                    <div class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <p class="text-gray-600">Data pribadi tidak ditampilkan kepada pengguna di luar HR.</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <p class="text-gray-600">Akses yang ditolak dicatat dalam riwayat aktivitas.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Warehouse validation --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-blue-50 border-b border-blue-100 flex items-center gap-2">
                <span class="text-lg">✍️</span>
                <h2 class="font-bold text-blue-800 text-sm">Validasi Gudang</h2>
            </div>
            <div class="px-5 py-4 space-y-3 text-xs">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="font-semibold text-gray-600 mb-1">Aturan</p>
                    <p class="text-gray-500">Pengiriman dan penerimaan barang harus divalidasi karyawan gudang yang aktif.</p>
                    <p class="text-gray-500 mt-1">Perubahan status karyawan berlaku pada validasi berikutnya.</p>
                </div>
                <div class="space-y-2">
                    <div class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <p class="text-gray-600">Karyawan gudang aktif dengan akun terhubung dapat memvalidasi.</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-red-500 mt-0.5">✗</span>
                        <p class="text-gray-600">Karyawan nonaktif tidak dapat memvalidasi.</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-red-500 mt-0.5">✗</span>
                        <p class="text-gray-600">Karyawan di luar bagian gudang tidak dapat memvalidasi.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Role-based access --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-orange-50 border-b border-orange-100 flex items-center gap-2">
                <span class="text-lg">🛡️</span>
                <h2 class="font-bold text-orange-800 text-sm">Akses Berdasarkan Peran</h2>
            </div>
            <div class="px-5 py-4 space-y-3 text-xs">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="font-semibold text-gray-600 mb-1">Modul yang tersedia</p>
                    <div class="space-y-1 mt-1">
                        @php
                        $roleMenus = [
                            'Sales'     => ['Sales','CRM'],
                            'Warehouse' => ['Inventory'],
                            'Finance'   => ['Accounting','Invoicing','Reporting'],
                            'Purchase'  => ['Purchase','Inventory'],
                            'HR'        => ['Employees'],
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
                    <p class="font-semibold text-gray-600">Perlindungan akses</p>
                    <div class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <p class="text-gray-600">Pengguna harus masuk sebelum membuka aplikasi.</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <p class="text-gray-600">Halaman terbatas hanya dapat dibuka oleh peran yang berwenang.</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <p class="text-gray-600">Menu menyesuaikan akses setiap pengguna.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cross-module integration --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                🔗 Alur Validasi Pegawai dan Gudang
            </h2>
        </div>
        <div class="p-5">
            {{-- Flow diagram --}}
            <div class="flex items-center gap-2 flex-wrap text-xs mb-5">
                @php
                $flow = [
                    ['label'=>'Employee Active', 'color'=>'bg-purple-100 text-purple-700 border-purple-200'],
                    ['arrow'=>true],
                    ['label'=>'Validator Auth', 'color'=>'bg-blue-100 text-blue-700 border-blue-200'],
                    ['arrow'=>true],
                    ['label'=>'Receipt / Delivery Sign-off', 'color'=>'bg-green-100 text-green-700 border-green-200'],
                    ['arrow'=>true],
                    ['label'=>'Pembaruan Stok + Riwayat', 'color'=>'bg-orange-100 text-orange-700 border-orange-200'],
                ];
                @endphp
                @foreach($flow as $step)
                @if(isset($step['arrow']))
                <span class="text-gray-300 text-xl font-bold">→</span>
                @else
                <div class="flex flex-col items-center px-4 py-2.5 rounded-xl border {{ $step['color'] }} text-center">
                    <span class="font-bold text-[11px]">{{ $step['label'] }}</span>
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                    $crossModuleData = [
                        ['modul'=>'Inventory','trigger'=>'Pengiriman divalidasi', 'check'=>'Karyawan gudang aktif', 'audit'=>'Validator dan waktu tercatat'],
                        ['modul'=>'Inventory','trigger'=>'Penerimaan divalidasi', 'check'=>'Karyawan gudang aktif', 'audit'=>'Validator dan waktu tercatat'],
                        ['modul'=>'Inventory','trigger'=>'Validasi oleh karyawan nonaktif', 'check'=>'Akses ditolak', 'audit'=>'Penolakan tercatat'],
                        ['modul'=>'HR','trigger'=>'Status karyawan diperbarui', 'check'=>'Akses HR', 'audit'=>'Perubahan status tercatat'],
                        ['modul'=>'Akses','trigger'=>'Pengguna masuk', 'check'=>'Akun tervalidasi', 'audit'=>'Aktivitas masuk tercatat'],
                        ['modul'=>'Akses','trigger'=>'Membuka halaman terbatas', 'check'=>'Peran diperiksa', 'audit'=>'Akses ditolak tercatat'],
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
                        <th class="text-left px-4 py-2.5 text-gray-500 font-semibold text-[10px] uppercase">Skenario</th>
                        <th class="text-left px-4 py-2.5 text-gray-500 font-semibold text-[10px] uppercase">Pilihan</th>
                        <th class="text-left px-4 py-2.5 text-gray-500 font-semibold text-[10px] uppercase">Keputusan</th>
                        <th class="text-left px-4 py-2.5 text-gray-500 font-semibold text-[10px] uppercase">Dampak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                    $decisions = [
                        [
                            'scenario' => 'Melindungi data pribadi pegawai',
                            'opts'     => 'Sembunyikan data di tampilan atau batasi akses di server',
                            'dec'      => 'Akses dibatasi di server dan hanya diberikan kepada HR.',
                            'trade'    => 'Perlindungan lebih kuat dengan pengelolaan akses yang lebih ketat.',
                        ],
                        [
                            'scenario' => 'Menentukan petugas validasi gudang',
                            'opts'     => 'Periksa status aktif saja atau status, bagian, dan akun',
                            'dec'      => 'Validasi hanya dapat dilakukan karyawan gudang aktif dengan akun terhubung.',
                            'trade'    => 'Pemeriksaan lebih ketat; karyawan bagian lain tidak dapat memvalidasi.',
                        ],
                        [
                            'scenario' => 'Menerapkan perubahan status pegawai',
                            'opts'     => 'Terapkan pada login berikutnya atau saat validasi berikutnya',
                            'dec'      => 'Perubahan status diperiksa saat proses validasi.',
                            'trade'    => 'Akses yang tidak lagi memenuhi syarat segera ditolak.',
                        ],
                    ];
                    @endphp
                    @foreach($decisions as $d)
                    <tr class="hover:bg-gray-50/60 align-top">
                        <td class="px-4 py-3 text-gray-700 font-semibold max-w-xs">{{ $d['scenario'] }}</td>
                        <td class="px-4 py-3 text-gray-600 max-w-xs">{{ $d['opts'] }}</td>
                        <td class="px-4 py-3 text-gray-700 font-medium max-w-xs">{{ $d['dec'] }}</td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs">{{ $d['trade'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Live data check --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
        <h2 class="font-bold text-gray-800 text-sm mb-4">📊 Ringkasan Data Kepegawaian</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @php
            $liveStats = [
                ['label'=>'Total Pegawai', 'value'=> \App\Models\Employee::count(), 'icon'=>'👥'],
                ['label'=>'Pegawai Aktif', 'value'=> \App\Models\Employee::where('status','Aktif')->count(), 'icon'=>'🟢'],
                ['label'=>'Pegawai Nonaktif', 'value'=> \App\Models\Employee::where('status','Nonaktif')->count(), 'icon'=>'🔴'],
                ['label'=>'Bagian Warehouse', 'value'=> \App\Models\Employee::where('department_id','D-WH')->count(), 'icon'=>'🏭'],
                ['label'=>'Petugas Validasi', 'value'=> \App\Models\Employee::where('status','Aktif')->where('department_id','D-WH')->whereNotNull('mapped_user_id')->count(), 'icon'=>'✍️'],
                ['label'=>'Data Pribadi', 'value'=> \App\Models\EmployeePrivateRecord::count(), 'icon'=>'🔐'],
                ['label'=>'Akun Pengguna', 'value'=> \App\Models\User::count(), 'icon'=>'👤'],
                ['label'=>'Aktivitas Tercatat', 'value'=> \App\Models\AuditLog::count(), 'icon'=>'📋'],
            ];
            @endphp
            @foreach($liveStats as $stat)
            <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-base">{{ $stat['icon'] }}</span>
                </div>
                <p class="text-2xl font-black text-gray-800">{{ $stat['value'] }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ $stat['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
