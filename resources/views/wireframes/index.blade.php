@extends('layouts.app')
@section('title','Wireframe S-01–S-18')
@php
    $currentApp  = 'Wireframes';
    $sidebarMenu = [['label'=>'Semua Wireframe','url'=>route('wireframes'),'icon'=>'🖼️']];
@endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700">Wireframe S-01–S-18</span>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-4">

    <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-800">
        <strong>M3-W1-01</strong> · Draft wireframe S-01 s/d S-18 dengan struktur Odoo Web 17.0 (Search/Filters/Group By · View Switcher · Fields · Table/KPI · Related Docs + Log Notes).
    </div>

    @php
    $screens = [
        ['S-01','Beranda dan App Switcher','Home','Semua','F-01, F-22','M3 ✅',
            [['Aplikasi','Sales · CRM · Purchase · Inventory · Accounting · Invoicing · Employees'],['Role','Sales / Warehouse / Finance / Purchase / HR / Owner']],
            ['Nama Aplikasi','Status Akses','Deskripsi'],
            [['Sales','Diizinkan','Order to Cash'],['Accounting','Dibatasi','Finance only']],
            [],''],
        ['S-02','Contact dan Credit Profile','Sales','Sales, Finance','F-02, F-10','M1',
            [['Customer','C01 — PT Toko Makmur Sejahtera'],['Credit Limit','Rp 2.000.000'],['Open AR','Rp 900.000'],['Terms','14 hari']],
            ['Dokumen','Nilai','Status'],
            [['S00007','Rp 222.000','Confirmed; unbilled']],
            [],''],
        ['S-03','Product dan Varian SKU','Inventory','Sales, Purchase, Warehouse','F-03, F-22','M2',
            [['Variant SKU','P01 / CLN-DTR-800-LMN'],['Sales Price','Rp 25.000'],['Standard Cost','Rp 17.000'],['UoM','pouch']],
            ['Variant SKU','Color','Price'],
            [['P01','Lemon','Rp 25.000'],['P11','Floral','Rp 25.000']],
            [],''],
        ['S-04','CRM Pipeline','CRM','Sales','F-04, F-21','M1',
            [['Lead','L00003'],['Customer','C03 — UD Berkah Niaga'],['Expected Revenue','Rp 500.000'],['Salesperson','E04']],
            ['New','Qualified','Won'],
            [['L00003 — C03','Opportunity C03','Proposal sent']],
            ['New','Qualified','Won'],'New'],
        ['S-05','Quotation / Sales Order','Sales','Sales, Finance','F-05, F-06, F-10, F-22','M1',
            [['Customer','C01'],['Document','S00008'],['Product','P01'],['Qty / Unit Price','10 × Rp 25.000']],
            ['SKU','Qty','Unit Price','Tax','Subtotal'],
            [['P01','10','Rp 25.000','11%','Rp 250.000']],
            ['Draft','Confirmed','Done'],'Draft'],
        ['S-06','Delivery Order','Inventory','Warehouse','F-07, F-08, F-09, F-14, F-19','M2',
            [['Reference','S00008'],['Source','WH-JKT'],['Validator','E01 — Aktif'],['Reservation','10 × P01']],
            ['SKU','Demand','Done','Backorder'],
            [['P01','10','10','0']],
            ['Ready','Done'],'Ready'],
        ['S-07','Stock Overview / Transfers','Inventory','Warehouse','F-07, F-17, F-20','M2',
            [['Location','WH-JKT'],['SKU','P01'],['On Hand','60'],['Reserved','8']],
            ['Location','On Hand','Reserved','Available'],
            [['WH-JKT','60','8','52'],['HUB-BDG','20','0','20']],
            [],''],
        ['S-08','RFQ / Purchase Order','Purchase','Purchase, Finance','F-11, F-13, F-22','M2',
            [['Document','PO00007'],['Vendor','V04 — PT Personal Sentosa'],['Product','P05'],['Qty','20']],
            ['SKU','Ordered','Unit Price','Net'],
            [['P05','20','Rp 32.000','Rp 640.000']],
            ['RFQ','Purchase Order'],'Purchase Order'],
        ['S-09','Receipt dan Quality Acceptance','Inventory','Warehouse, Purchase','F-12, F-19, F-14','M2',
            [['Purchase Order','PO00007'],['Validator','E01'],['Source','V04'],['Destination','WH-JKT']],
            ['SKU','Received','Accepted','Rejected'],
            [['P05','20','15','5']],
            ['Waiting','Done'],'Waiting'],
        ['S-10','Customer Invoice','Invoicing','Finance','F-09, F-14, F-21','M4',
            [['Source SO','S00008'],['Delivered','10 × P01'],['Untaxed Amount','Rp 250.000'],['Tax','Rp 27.500']],
            ['SKU','Invoiced Qty','Untaxed','Gross'],
            [['P01','10','Rp 250.000','Rp 277.500']],
            ['Draft','Posted','Paid'],'Draft'],
        ['S-11','Vendor Bill','Accounting','Finance, Purchase','F-13, F-14, F-21','M4',
            [['Source PO','PO00007'],['Accepted','15 × P05'],['Untaxed','Rp 480.000'],['Tax','Rp 52.800']],
            ['Source Receipt','Accepted','Already Billed','Available'],
            [['PO00007','15','0','15']],
            ['Draft','Posted','Paid'],'Draft'],
        ['S-12','Register Payment','Accounting','Finance','F-15, F-21','M4',
            [['Invoice','INV00008'],['Open Amount','Rp 277.500'],['Payment','Rp 100.000'],['Bank','BANK-IDR']],
            ['Before','Payment','After'],
            [['Rp 277.500','Rp 100.000','Rp 177.500']],
            ['Draft','Posted'],'Draft'],
        ['S-13','Journal Entries / General Ledger','Accounting','Finance, Owner','F-14, F-22','M4',
            [['Source','DO / Invoice / Receipt / Bill'],['Posting State','Posted'],['Currency','IDR'],['Method','Standard Cost / Perpetual']],
            ['Account','Debit','Credit'],
            [['5100 HPP','Rp 170.000','-'],['1300 Persediaan','-','Rp 170.000']],
            [],''],
        ['S-16','Return dan Credit Note','Sales / Accounting','Warehouse, Finance','F-16, F-14, F-21','M4',
            [['Original Invoice','INV00008'],['SKU','P01'],['Return Qty','2'],['Disposition','Layak jual']],
            ['SKU','Qty','Credit Tax','Credit Gross'],
            [['P01','2','Rp 5.500','Rp 55.500']],
            ['Draft','Posted'],'Draft'],
        ['S-17','Operational Dashboard','Reporting','Owner, Finance, Warehouse','F-20, F-10, F-07','M4',
            [['Snapshot','Seed baseline'],['Stock Units','880'],['Stock Value','Rp 23.095.000'],['AR / AP','Rp 1.500.000 / Rp 700.000']],
            ['Open Orders','On Hand','Reserved','Bank'],
            [['1 SO + 1 PO','880','8','Rp 20.000.000']],
            [],''],
        ['S-18','Audit / Related Documents','Reporting','Owner dan pemilik modul','F-21, F-22, F-19','M4',
            [['Document','S00007 / PO00007'],['Actor','User / Employee ID'],['Action','Confirm / Validate / Post'],['Timestamp','2026-10-20 09:00 WIB']],
            ['Reference','Action','Actor','Result'],
            [['PO00007','Confirm','purchase_user','Once'],['S00007','Reserve','warehouse_mgr','8 units']],
            [],''],
    ];
    @endphp

    @foreach($screens as [$sid,$title,$mod,$role,$feat,$owner,$fields,$headers,$rows,$stages,$current])
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gray-50 border-b px-4 py-2 flex items-center justify-between">
            <div>
                <span class="font-mono text-xs text-[#714B67] font-bold">{{ $sid }}</span>
                <span class="ml-2 font-semibold text-sm text-gray-700">{{ $title }}</span>
            </div>
            <div class="text-xs text-gray-400">{{ $mod }} · {{ $role }} · {{ $feat }}</div>
        </div>
        <div class="flex text-xs">
            <div class="w-28 border-r border-gray-100 py-2 flex-shrink-0">
                @foreach(['Overview','Orders / Documents','Reporting','Configuration'] as $item)
                <div class="px-3 py-1 {{ $loop->first ? 'text-[#714B67] font-medium':'text-gray-400' }}">{{ $item }}</div>
                @endforeach
            </div>
            <div class="flex-1 px-4 py-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-gray-700">① {{ $title }}</span>
                    <span class="text-gray-400">Search · Filters · Group By</span>
                </div>
                <div class="flex items-center gap-1 mb-2 text-gray-400">
                    <span>②</span>
                    @foreach(['List','Kanban','Form','Activity'] as $v)
                    <span class="px-1.5 py-0.5 rounded {{ $v==='Form' ? 'bg-[#714B67] text-white':'bg-gray-100' }}">{{ $v }}</span>
                    @endforeach
                    @if($current) <span class="ml-2">| current: <strong>{{ $current }}</strong></span> @endif
                </div>
                @if(count($stages)>0)
                <div class="mb-2"><x-status-bar :stages="$stages" :current="$current" /></div>
                @endif
                @if(count($fields)>0)
                <div class="mb-2">
                    <span class="text-gray-400">③ Fields:</span>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 mt-1 bg-gray-50 rounded p-2">
                        @foreach($fields as [$label,$value])
                        <div><span class="text-gray-400">{{ $label }}</span><div class="font-medium text-gray-800">{{ $value }}</div></div>
                        @endforeach
                    </div>
                </div>
                @endif
                @if(count($headers)>0)
                <div>
                    <span class="text-gray-400">④ Detail / Kanban / KPIs</span>
                    <table class="w-full mt-1 border border-gray-200 rounded">
                        <thead class="bg-gray-100"><tr>@foreach($headers as $h)<th class="text-left px-2 py-1 font-medium text-gray-500">{{ $h }}</th>@endforeach</tr></thead>
                        <tbody>@foreach($rows as $row)<tr class="border-t border-gray-100">@foreach($row as $cell)<td class="px-2 py-1 text-gray-700">{{ $cell }}</td>@endforeach</tr>@endforeach</tbody>
                    </table>
                </div>
                @endif
                <div class="mt-2 pt-2 border-t border-gray-100 flex items-center gap-2 text-gray-400">
                    <span>⑤ Related Docs</span><span>|</span><span>Log Notes</span><span>|</span><span>Audit / History</span>
                </div>
            </div>
        </div>
        <div class="bg-amber-50 border-t border-amber-100 px-4 py-1.5 text-xs text-amber-700">
            ⚠️ <strong>Wireframe {{ $sid }}</strong> · Owner: <strong>{{ $owner }}</strong>
            @if(str_contains($owner,'✅')) · <a href="{{ $sid==='S-14' ? route('employees.index') : route('employees.show','E01') }}" class="underline">→ Lihat implementasi</a> @endif
        </div>
    </div>
    @endforeach

    {{-- S-14 dan S-15 implemented --}}
    <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800 flex items-center gap-3">
        <span>✅</span>
        <div>
            <strong>S-14 Employees dan Departments</strong> · <a href="{{ route('employees.index') }}" class="underline">Buka →</a><br>
            <strong>S-15 Employee Form dan Validator</strong> · <a href="{{ route('employees.show','E01') }}" class="underline">Buka E01 →</a>
        </div>
    </div>

</div>
@endsection
