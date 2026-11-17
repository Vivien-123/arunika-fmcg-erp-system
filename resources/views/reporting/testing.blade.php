@extends('layouts.app')
@section('title', 'Daftar Skenario Pengujian')
@php $currentApp = 'Pengujian'; @endphp

@section('breadcrumb')
    <span class="mx-1">/</span><span class="text-gray-600 font-medium">Skenario Pengujian</span>
@endsection

@section('content')
<div class="max-w-5xl">

    {{-- Page header --}}
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">Skenario Pengujian</h1>
    </div>

    {{-- Filter bar --}}
    <div class="panel mb-4">
        <div class="px-4 py-3 flex items-center gap-2 flex-wrap border-b border-gray-100">
            <input type="text" id="testSearch" oninput="applyTestFilters()" placeholder="Cari skenario..." class="filter-input w-48">
            <select id="testStatus" onchange="applyTestFilters()" class="filter-input">
                <option value="">Semua status</option>
                <option value="not_run">Belum dijalankan</option>
            </select>
            <select id="testGroup" onchange="applyTestFilters()" class="filter-input">
                <option value="none">Kelompok: Tanpa grup</option>
                <option value="module">Kelompok: Modul</option>
                <option value="category">Kelompok: Kategori</option>
            </select>
            <select id="testColumns" onchange="toggleTestColumn()" class="filter-input">
                <option value="all">Kolom: Semua</option>
                <option value="basic">Kolom: Ringkas</option>
            </select>
            <button type="button" onclick="exportTestingCsv()" class="filter-input text-gray-500 hover:bg-gray-50 transition">Export CSV</button>
            <span class="ml-auto text-[11px] text-gray-400">
                <strong id="testVisibleCount" class="text-gray-700">{{ $total }}</strong> skenario tampil ·
                <strong id="testSelectedCount" class="text-[#714B67]">0</strong> dipilih
            </span>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-4 gap-3 mb-5">
        <div class="panel px-4 py-3">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide">Lulus</p>
            <p class="text-3xl font-bold text-green-600 mt-1 tnum">{{ $passed }}</p>
        </div>
        <div class="panel px-4 py-3">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide">Gagal</p>
            <p class="text-3xl font-bold text-red-500 mt-1 tnum">{{ $failed }}</p>
        </div>
        <div class="panel px-4 py-3">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide">Terblokir</p>
            <p class="text-3xl font-bold text-yellow-500 mt-1 tnum">{{ $blocked }}</p>
        </div>
        <div class="panel px-4 py-3">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide">Belum dijalankan</p>
            <p class="text-3xl font-bold text-gray-400 mt-1 tnum">{{ $total - $passed }}</p>
        </div>
    </div>

    {{-- Progress --}}
    <div class="panel px-5 py-4 mb-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-gray-700">{{ $total }} skenario menunggu pengujian</span>
            <span class="text-xs text-gray-400">{{ $passed }} / {{ $total }} dijalankan</span>
        </div>
        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-[#714B67] rounded-full transition-all duration-500" style="width: {{ round($passed/$total*100) }}%"></div>
        </div>
    </div>

    {{-- Table --}}
    @php
        $moduleColors = ['M1'=>'badge-yellow','M2'=>'badge-blue','M3'=>'badge-purple','M4'=>'badge-green'];
        $statusConfig = [
            'pass'    => ['class'=>'badge-green',  'label'=>'Pass'],
            'fail'    => ['class'=>'badge-red',    'label'=>'Failed'],
            'blocked' => ['class'=>'badge-yellow', 'label'=>'Blocked'],
            'not_run' => ['class'=>'badge-gray',   'label'=>'Belum dijalankan'],
        ];
    @endphp

    <div class="panel overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Daftar Skenario</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-5 py-2.5 w-8">
                            <input type="checkbox" id="testSelectAll" onchange="toggleAllTests(this)"
                                   class="w-3.5 h-3.5 accent-[#714B67] cursor-pointer" title="Pilih semua">
                        </th>
                        <th class="text-left px-2 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide w-20">Test ID</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Skenario</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide w-20">Modul</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide test-col-expected">Hasil yang diharapkan</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide w-24">Status</th>
                    </tr>
                </thead>
                <tbody id="testRows" class="divide-y divide-gray-50">
                    @foreach($tests as $test)
                    @php
                        $sc = $statusConfig[$test['status']] ?? $statusConfig['not_run'];
                        $mc = $moduleColors[$test['module']] ?? 'badge-gray';
                        $expectedMap = [
                            'T-01'=>'Modul terotorisasi terlihat','T-02'=>'Akses ditolak',
                            'T-03'=>'C01 / C02 sesuai baseline','T-04'=>'Block bila order > 878.000',
                            'T-05'=>'16 SKU; data lengkap dibutuhkan','T-06'=>'Lot expiry terdekat dipilih',
                            'T-07'=>'Lead tercatat dengan owner','T-08'=>'Relasi sumber dipertahankan',
                            'T-09'=>'SO S00008 net Rp250.000, gross Rp277.500','T-10'=>'Finance approval diperlukan',
                            'T-11'=>'Qty tervalidasi; stok konsisten','T-12'=>'Backorder terbentuk',
                            'T-13'=>'On Hand 60 Reserved 8 Available 52','T-14'=>'Transfer blocked bila reserved',
                            'T-15'=>'PO net Rp640.000, gross Rp710.400','T-16'=>'Price edit ditolak',
                            'T-17'=>'WH P05=45, stock +Rp480.000','T-18'=>'Sign-off ditolak (E03 Nonaktif)',
                            'T-19'=>'Invoice net Rp250.000, gross Rp277.500','T-20'=>'Qty capped at delivered',
                            'T-21'=>'Bill net Rp480.000, AP Rp1.232.800','T-22'=>'Bill blocked bila qty melebihi receipt',
                            'T-23'=>'Outstanding Rp177.500 setelah bayar','T-24'=>'Overpayment ditolak',
                            'T-25'=>'Dr HPP = Cr Persediaan Rp170.000','T-26'=>'Double post diblokir',
                            'T-27'=>'5 dept, 8 employee, Warehouse 3','T-28'=>'Data privat hanya HR',
                            'T-29'=>'E01 boleh sign-off, E03 tidak','T-30'=>'Sign-off blok setelah Nonaktif',
                            'T-31'=>'Credit note Rp55.500, stok kembali','T-32'=>'CN qty ceiling ditegakkan',
                            'T-33'=>'On Hand 880, AR 1.5jt, AP 700k','T-34'=>'Draft tidak masuk KPI',
                            'T-35'=>'PO00007 Receipt=0, Bill=0','T-36'=>'Re-validate idempotent',
                        ];
                        $expected = $expectedMap[$test['id']] ?? $test['name'];
                    @endphp
                    <tr class="tr-hover test-row"
                        data-id="{{ $test['id'] }}"
                        data-name="{{ $test['name'] }}"
                        data-status="{{ $test['status'] }}"
                        data-module="{{ $test['module'] }}"
                        data-category="{{ $test['category'] }}"
                        data-expected="{{ $expected }}">
                        <td class="px-5 py-3">
                            <input type="checkbox" class="w-3.5 h-3.5 accent-[#714B67] cursor-pointer test-select"
                                   onchange="updateTestSelectedCount()">
                        </td>
                        <td class="px-2 py-3 font-mono font-bold text-[#714B67] text-[11px]">{{ $test['id'] }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $test['name'] }}</td>
                        <td class="px-4 py-3"><span class="badge {{ $mc }}">{{ $test['module'] }}</span></td>
                        <td class="px-4 py-3 text-gray-500 test-col-expected">{{ $expected }}</td>
                        <td class="px-4 py-3"><span class="badge {{ $sc['class'] }}">{{ $sc['label'] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function testRows() {
    return Array.from(document.querySelectorAll('#testRows tr.test-row'));
}

function applyTestFilters() {
    const term   = (document.getElementById('testSearch')?.value || '').toLowerCase().trim();
    const status = document.getElementById('testStatus')?.value || '';
    const group  = document.getElementById('testGroup')?.value || 'none';

    let visible = 0;

    testRows().forEach(function (row) {
        const haystack = (row.dataset.id + ' ' + row.dataset.name + ' ' + row.dataset.category).toLowerCase();
        let show = !term || haystack.includes(term);
        if (status && row.dataset.status !== status) show = false;

        row.classList.toggle('hidden', !show);
        if (show) visible++;
    });

    // Bersihkan header grup lama
    document.querySelectorAll('#testRows tr.test-group-header').forEach(h => h.remove());

    if (group !== 'none') {
        const header = group === 'module' ? 'Modul' : 'Kategori';

        // Urutkan baris berdasarkan grup
        const sorted = testRows().sort((a, b) => {
            const av = group === 'module' ? a.dataset.module : a.dataset.category;
            const bv = group === 'module' ? b.dataset.module : b.dataset.category;
            return av.localeCompare(bv) || a.dataset.id.localeCompare(b.dataset.id);
        });

        const tbody = document.getElementById('testRows');
        let currentKey = null;

        sorted.forEach(function (row) {
            const key = group === 'module' ? row.dataset.module : row.dataset.category;

            if (key !== currentKey) {
                currentKey = key;
                const groupRow = document.createElement('tr');
                groupRow.className = 'test-group-header bg-gray-50';
                groupRow.innerHTML = '<td colspan="6" class="px-5 py-1.5 text-[10px] font-bold uppercase tracking-wide text-[#714B67]">'
                    + header + ': ' + key + '</td>';
                if (row.classList.contains('hidden')) groupRow.dataset.hiddenGroup = '1';
                tbody.appendChild(groupRow);
            }

            tbody.appendChild(row);
        });

        // Sembunyikan header grup yang seluruh anggotanya tersaring
        document.querySelectorAll('#testRows tr.test-group-header').forEach(function (headerRow) {
            let next = headerRow.nextElementSibling;
            let hasVisible = false;
            while (next && !next.classList.contains('test-group-header')) {
                if (next.classList.contains('test-row') && !next.classList.contains('hidden')) hasVisible = true;
                next = next.nextElementSibling;
            }
            headerRow.classList.toggle('hidden', !hasVisible);
        });
    }

    const counter = document.getElementById('testVisibleCount');
    if (counter) counter.textContent = visible;
}

function toggleTestColumn() {
    const mode = document.getElementById('testColumns')?.value || 'all';
    document.querySelectorAll('.test-col-expected').forEach(function (cell) {
        cell.classList.toggle('hidden', mode === 'basic');
    });
}

function toggleAllTests(master) {
    testRows().forEach(function (row) {
        if (row.classList.contains('hidden')) return;
        const box = row.querySelector('.test-select');
        if (box) box.checked = master.checked;
    });
    updateTestSelectedCount();
}

function updateTestSelectedCount() {
    const selected = testRows().filter(r => r.querySelector('.test-select')?.checked);
    const counter  = document.getElementById('testSelectedCount');
    if (counter) counter.textContent = selected.length;

    const master = document.getElementById('testSelectAll');
    const visibleRows = testRows().filter(r => !r.classList.contains('hidden'));
    if (master) {
        master.checked = visibleRows.length > 0 && visibleRows.every(r => r.querySelector('.test-select')?.checked);
    }
}

function exportTestingCsv() {
    const selected = testRows().filter(r => r.querySelector('.test-select')?.checked);
    const rows     = selected.length > 0
        ? selected
        : testRows().filter(r => !r.classList.contains('hidden'));

    const escape = value => '"' + String(value).replace(/"/g, '""') + '"';

    const lines = [['Test ID', 'Skenario', 'Modul', 'Kategori', 'Expected result', 'Status'].map(escape).join(',')];
    rows.forEach(function (row) {
        lines.push([
            row.dataset.id,
            row.dataset.name,
            row.dataset.module,
            row.dataset.category,
            row.dataset.expected,
            row.dataset.status,
        ].map(escape).join(','));
    });

    const blob = new Blob(['\uFEFF' + lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'arunika-integration-tests-' + new Date().toISOString().slice(0, 10) + '.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
}
</script>
@endsection
