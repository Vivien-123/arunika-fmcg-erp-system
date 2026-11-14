
<?php $__env->startSection('title','Dashboard'); ?>
<?php
    $currentApp = 'Dashboard';
    $user  = auth()->user();
    $menus = $user->allowedMenus();

    $allMenus = [
        'Sales'      => ['url'=>route('sales.orders.index'),        'desc'=>'Quotation → Order to Cash',    'sub'=>'Sales Orders, CRM Pipeline'],
        'CRM'        => ['url'=>route('crm.leads.index'),           'desc'=>'Lead & Opportunity pipeline',  'sub'=>'Leads, Opportunities'],
        'Purchase'   => ['url'=>route('purchase.orders.index'),     'desc'=>'RFQ → Procure to Pay',         'sub'=>'Purchase Orders, Receipts'],
        'Inventory'  => ['url'=>route('inventory.stock'),           'desc'=>'Stok, Transfer & Delivery',    'sub'=>'Stock, DO, GR, Transfer'],
        'Accounting' => ['url'=>route('accounting.invoices.index'), 'desc'=>'Jurnal, Ledger & Pajak',       'sub'=>'Invoice, Bill, Ledger'],
        'Invoicing'  => ['url'=>route('accounting.invoices.index'), 'desc'=>'Invoice & Pembayaran',         'sub'=>'Customer Invoice, Credit Note'],
        'Employees'  => ['url'=>route('employees.index'),           'desc'=>'Pegawai & Departemen',         'sub'=>'Directory, Sign-off Control'],
        'Reporting'  => ['url'=>route('reporting.dashboard'),       'desc'=>'KPI, Audit & Testing',         'sub'=>'Dashboard, Audit, T-01..T-36'],
        'Admin'      => ['url'=>route('admin.business-rules'),      'desc'=>'Business Rules & Role Matrix', 'sub'=>'SR Rules, CheckRole Matrix'],
        'Wireframes' => ['url'=>route('wireframes'),                'desc'=>'S-01 s/d S-18',                'sub'=>'All screen wireframes'],
    ];
    $allowedMenus = array_filter($allMenus, fn($key) => in_array($key, $menus), ARRAY_FILTER_USE_KEY);
?>

<?php $__env->startSection('breadcrumb'); ?>
    <span class="mx-1">/</span>
    <span class="text-gray-600 font-medium">Dashboard</span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('control_panel_extra'); ?>
<?php if($user->role === 'Owner'): ?>
<span class="text-[11px] font-semibold bg-gray-100 text-gray-500 px-2.5 py-1 rounded flex items-center gap-1.5">
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
    Read-only mode
</span>
<?php elseif($user->role === 'MasterAdmin'): ?>
<span class="text-[11px] font-semibold bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded flex items-center gap-1.5">
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
    Master Admin · Full Access
</span>
<?php endif; ?>
<a href="<?php echo e(route('reporting.dashboard')); ?>" class="btn-primary text-xs flex items-center gap-1.5 ml-2">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
    Operational Dashboard
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">

    
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Workspace operasional</h1>
        <p class="text-sm text-gray-400 mt-0.5">Arunika ERP / <?php echo e($user->role); ?> · Pilih modul untuk memulai</p>
    </div>

    
    <div class="panel px-5 py-4 mb-6 flex items-center gap-4">
        <div class="w-10 h-10 bg-[#714B67] rounded-lg flex items-center justify-center text-white font-bold text-base shrink-0">
            <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-800 truncate"><?php echo e($user->name); ?></p>
            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                <span class="inline-flex items-center gap-1 text-[11px] font-mono font-semibold
                    <?php if($user->role==='MasterAdmin'): ?> bg-indigo-100 text-indigo-700
                    <?php elseif($user->role==='Owner'): ?> bg-gray-100 text-gray-500
                    <?php elseif($user->role==='HR'): ?> bg-purple-100 text-purple-700
                    <?php elseif($user->role==='Finance'): ?> bg-green-100 text-green-700
                    <?php elseif($user->role==='Sales'): ?> bg-orange-100 text-orange-700
                    <?php elseif($user->role==='Warehouse'): ?> bg-cyan-100 text-cyan-700
                    <?php else: ?> bg-blue-100 text-blue-700
                    <?php endif; ?>
                    px-2 py-0.5 rounded">
                    <?php echo e($user->role); ?>

                </span>
                <?php if($user->role === 'Owner'): ?>
                <span class="text-[11px] text-gray-400 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Hanya dapat melihat data, tidak dapat mengedit
                </span>
                <?php elseif($user->role === 'MasterAdmin'): ?>
                <span class="text-[11px] text-gray-400 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Akses penuh semua modul — dapat edit dan hapus
                </span>
                <?php endif; ?>
                <span class="text-[11px] text-gray-400">· PT Arunika Distribusi Cepat</span>
            </div>
        </div>
        <div class="text-right hidden sm:block text-xs text-gray-400 shrink-0">
            <p><?php echo e(now()->format('d M Y')); ?></p>
        </div>
    </div>

    
    <div class="mb-2">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Modul Tersedia — <?php echo e(count($allowedMenus)); ?> modul</p>
    </div>

    <?php if(count($allowedMenus) === 0): ?>
    <div class="panel px-6 py-12 text-center">
        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <p class="text-sm font-medium text-gray-500">Tidak ada modul yang tersedia.</p>
        <p class="text-xs text-gray-400 mt-1">Hubungi administrator untuk mendapatkan akses.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <?php $__currentLoopData = $allowedMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e($info['url']); ?>"
           class="panel px-5 py-4 flex flex-col gap-3 hover:border-[#714B67] hover:shadow-sm transition-all group">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-800 group-hover:text-[#714B67] transition-colors"><?php echo e($name); ?></p>
                    <p class="text-xs text-gray-500 mt-0.5 leading-relaxed"><?php echo e($info['desc']); ?></p>
                </div>
                <svg class="w-4 h-4 text-gray-200 group-hover:text-[#714B67] transition-colors shrink-0 mt-0.5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
            <div class="border-t border-gray-100 pt-2.5 flex items-center justify-between">
                <p class="text-[10px] text-gray-400"><?php echo e($info['sub']); ?></p>
                <?php if($user->role === 'Owner'): ?>
                <span class="text-[10px] text-gray-400 flex items-center gap-0.5">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    View only
                </span>
                <?php else: ?>
                <span class="flex items-center gap-1">
                    <span class="dot bg-green-400"></span>
                    <span class="text-[10px] text-green-600">Aktif</span>
                </span>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    
    <?php if(in_array($user->role, ['Owner','MasterAdmin'])): ?>
    <div class="panel overflow-hidden mt-6">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Role Access Matrix</h3>
            <span class="text-[10px] text-gray-400 mono">SR-15 · <?php echo e($user->role); ?> only</span>
        </div>
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-2.5 text-[10px] font-bold text-gray-400 uppercase">Modul</th>
                    <th class="text-left px-5 py-2.5 text-[10px] font-bold text-gray-400 uppercase">Deskripsi</th>
                    <th class="text-left px-5 py-2.5 text-[10px] font-bold text-gray-400 uppercase">Akses</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__currentLoopData = $allowedMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="tr-hover">
                    <td class="px-5 py-2.5 font-semibold text-gray-800"><?php echo e($name); ?></td>
                    <td class="px-5 py-2.5 text-gray-500"><?php echo e($info['desc']); ?></td>
                    <td class="px-5 py-2.5">
                        <?php if($user->role === 'Owner'): ?>
                        <span class="badge badge-gray">View only</span>
                        <?php else: ?>
                        <span class="badge badge-green">Full edit</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\arunika-fmcg-erp-system\resources\views/home.blade.php ENDPATH**/ ?>