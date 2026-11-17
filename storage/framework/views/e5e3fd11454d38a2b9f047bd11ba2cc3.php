
<?php $__env->startSection('title','Dashboard'); ?>
<?php
    $currentApp = 'Dashboard';
    $user  = auth()->user();
    $menus = $user->allowedMenus();

    $allMenus = [
        'Sales'      => ['url'=>route('sales.orders.index'),        'desc'=>'Quotation → Order to Cash',    'icon'=>'🛍️', 'class'=>'module-sales'],
        'CRM'        => ['url'=>route('crm.leads.index'),           'desc'=>'Lead & Opportunity pipeline',  'icon'=>'🤝', 'class'=>'module-crm'],
        'Purchase'   => ['url'=>route('purchase.orders.index'),     'desc'=>'RFQ → Procure to Pay',         'icon'=>'🛒', 'class'=>'module-purchase'],
        'Inventory'  => ['url'=>route('inventory.stock'),           'desc'=>'Pantau stok dan pergerakan barang', 'icon'=>'📦', 'class'=>'module-inventory'],
        'Accounting' => ['url'=>route('accounting.invoices.index'), 'desc'=>'Kelola transaksi keuangan',         'icon'=>'💰', 'class'=>'module-accounting'],
        'Employees'  => ['url'=>route('employees.index'),           'desc'=>'Pegawai & Departemen',         'icon'=>'👥', 'class'=>'module-employees'],
        'Reporting'  => ['url'=>route('reporting.dashboard'),       'desc'=>'KPI & Stock Operasional',      'icon'=>'📊', 'class'=>'module-reporting'],
        'Admin'      => ['url'=>route('admin.business-rules'),      'desc'=>'Pengaturan aplikasi',          'icon'=>'⚙️', 'class'=>'module-admin'],
    ];
    $allowedMenus = array_filter($allMenus, fn($key) => in_array($key, $menus), ARRAY_FILTER_USE_KEY);
?>

<?php $__env->startSection('breadcrumb'); ?>
    <span class="mx-1">/</span>
    <span class="text-gray-600 font-medium">Dashboard</span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('control_panel_extra'); ?>
<?php if(in_array('Reporting', $menus)): ?>
<a href="<?php echo e(route('reporting.dashboard')); ?>" class="dashboard-report-button">
    <span aria-hidden="true">📈</span>
    Dashboard Operasional
</a>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-shell">
    <section class="dashboard-hero">
        <div class="dashboard-hero-orbit dashboard-hero-orbit-one"></div>
        <div class="dashboard-hero-orbit dashboard-hero-orbit-two"></div>
        <div class="dashboard-hero-content">
            <div class="dashboard-hero-topline">
                <span class="dashboard-hero-brand"><span class="dashboard-hero-brand-mark">A</span> ARUNIKA ERP</span>
                <span class="dashboard-hero-date"><?php echo e(now()->format('l, d M Y')); ?></span>
            </div>
            <h1>Selamat datang,<br><span><?php echo e(Str::words($user->name, 2, '')); ?></span></h1>
            <div class="dashboard-hero-profile">
                <span class="dashboard-hero-avatar"><?php echo e(strtoupper(substr($user->name, 0, 1))); ?></span>
                <span class="dashboard-hero-role"><?php echo e($user->role); ?></span>
            </div>
        </div>
        <div class="dashboard-hero-art" aria-hidden="true">
            <div class="dashboard-art-ring"></div>
            <div class="dashboard-art-core">A</div>
            <span class="dashboard-art-spark dashboard-art-spark-one">✦</span>
            <span class="dashboard-art-spark dashboard-art-spark-two">✦</span>
            <span class="dashboard-art-spark dashboard-art-spark-three">✧</span>
        </div>
    </section>

    <div class="dashboard-section-heading">
        <div>
            <span class="dashboard-section-kicker">ARUNIKA WORKSPACE</span>
            <h2>Aplikasi Anda</h2>
        </div>
        <span class="dashboard-section-decoration" aria-hidden="true">✦</span>
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
    <div class="dashboard-module-grid">
        <?php $__currentLoopData = $allowedMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $moduleClass = $info['class'] ?? 'module-default'; ?>
        <a href="<?php echo e($info['url']); ?>"
           class="dashboard-module-card <?php echo e($moduleClass); ?>">
            <span class="dashboard-module-glow" aria-hidden="true"></span>
            <span class="dashboard-module-icon" aria-hidden="true"><?php echo e($info['icon']); ?></span>
            <span class="dashboard-module-arrow" aria-hidden="true">↗</span>
            <span class="dashboard-module-copy">
                <span class="dashboard-module-title"><?php echo e($name); ?></span>
                <span class="dashboard-module-description"><?php echo e($info['desc']); ?></span>
            </span>
            <span class="dashboard-module-bottom">
                <span class="dashboard-module-open">Buka aplikasi</span>
                <span class="dashboard-module-arrow-small" aria-hidden="true">→</span>
            </span>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\arunika-fmcg-erp-system\resources\views/home.blade.php ENDPATH**/ ?>