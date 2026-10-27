<?php $__env->startSection('title','Beranda'); ?>
<?php
    $currentApp  = 'Home';
    $sidebarMenu = [
        ['label'=>'Overview',   'url'=>route('home'),       'icon'=>'🏠'],
        ['label'=>'Wireframes', 'url'=>route('wireframes'), 'icon'=>'🖼️'],
    ];
    $user  = auth()->user();
    $menus = $user->allowedMenus();
    $allMenus = [
        'Sales'      => ['icon'=>'🛒','desc'=>'Order to Cash',       'url'=>'#','color'=>'bg-orange-50 border-orange-200'],
        'CRM'        => ['icon'=>'📋','desc'=>'Lead & Opportunity',   'url'=>'#','color'=>'bg-yellow-50 border-yellow-200'],
        'Purchase'   => ['icon'=>'📦','desc'=>'Procure to Pay',       'url'=>'#','color'=>'bg-blue-50 border-blue-200'],
        'Inventory'  => ['icon'=>'🏭','desc'=>'Stok & Transfer',      'url'=>'#','color'=>'bg-cyan-50 border-cyan-200'],
        'Accounting' => ['icon'=>'📊','desc'=>'Jurnal & Ledger',      'url'=>'#','color'=>'bg-green-50 border-green-200'],
        'Invoicing'  => ['icon'=>'🧾','desc'=>'Invoice & Pembayaran', 'url'=>'#','color'=>'bg-emerald-50 border-emerald-200'],
        'Employees'  => ['icon'=>'👤','desc'=>'Pegawai & Departemen', 'url'=>route('employees.index'),'color'=>'bg-purple-50 border-purple-200'],
        'Reporting'  => ['icon'=>'📈','desc'=>'KPI & Dashboard',      'url'=>'#','color'=>'bg-pink-50 border-pink-200'],
        'Wireframes' => ['icon'=>'🖼️','desc'=>'S-01 s/d S-18',       'url'=>route('wireframes'),'color'=>'bg-gray-50 border-gray-200'],
    ];
?>

<?php $__env->startSection('breadcrumb'); ?>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700 font-medium">Beranda</span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4 mb-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-[#714B67] rounded-xl flex items-center justify-center text-white font-bold text-xl">A</div>
        <div>
            <h2 class="font-semibold text-gray-800 text-base">Selamat datang, <?php echo e($user->name); ?></h2>
            <p class="text-xs text-gray-500">Role: <span class="font-mono bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded"><?php echo e($user->role); ?></span> · PT Arunika Distribusi Cepat</p>
        </div>
    </div>

    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Aplikasi Tersedia</h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-6">
        <?php $__currentLoopData = $allMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $allowed = in_array($name, $menus); ?>
        <?php if($allowed): ?>
        <a href="<?php echo e($info['url']); ?>" class="flex flex-col items-center p-4 rounded-xl border-2 <?php echo e($info['color']); ?> hover:shadow-md transition text-center">
            <span class="text-3xl mb-1"><?php echo e($info['icon']); ?></span>
            <span class="font-semibold text-sm text-gray-800"><?php echo e($name); ?></span>
            <span class="text-xs text-gray-500 mt-0.5"><?php echo e($info['desc']); ?></span>
            <span class="text-[10px] mt-1.5 bg-green-100 text-green-700 px-1.5 py-0.5 rounded-full">Diizinkan</span>
        </a>
        <?php else: ?>
        <div class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 bg-gray-50 opacity-50 cursor-not-allowed text-center">
            <span class="text-3xl mb-1"><?php echo e($info['icon']); ?></span>
            <span class="font-semibold text-sm text-gray-500"><?php echo e($name); ?></span>
            <span class="text-xs text-gray-400 mt-0.5"><?php echo e($info['desc']); ?></span>
            <span class="text-[10px] mt-1.5 bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full">Dibatasi</span>
        </div>
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b">
            <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Ringkasan Akses (SR-15)</h3>
        </div>
        <table class="w-full text-xs">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Aplikasi</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Status</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $allMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $allowed = in_array($name, $menus); ?>
                <tr class="border-b last:border-0 hover:bg-gray-50">
                    <td class="px-4 py-2 flex items-center gap-2"><span><?php echo e($info['icon']); ?></span><span class="font-medium"><?php echo e($name); ?></span></td>
                    <td class="px-4 py-2">
                        <?php if($allowed): ?>
                            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span><span class="text-green-700">Diizinkan</span></span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span><span class="text-red-600">Dibatasi</span></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-2 text-gray-500"><?php echo e($info['desc']); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\ASUS\OneDrive\Desktop\Coolyeah Sem 4\arunika-erp\resources\views/home.blade.php ENDPATH**/ ?>