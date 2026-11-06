<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PT Arunika ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F5F5F5] flex items-center justify-center min-h-screen">
<div class="w-full max-w-sm">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-[#714B67] rounded-xl mb-3">
            <span class="text-white text-3xl font-bold">A</span>
        </div>
        <h1 class="text-xl font-bold text-gray-800">PT Arunika Distribusi Cepat</h1>
        <p class="text-sm text-gray-500 mt-1">ERP System</p>
    </div>

    <div class="bg-white rounded-xl shadow-md px-8 py-6">
        <h2 class="text-base font-semibold text-gray-700 mb-4">Masuk ke Akun Anda</h2>

        <?php if($errors->any()): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 text-xs rounded px-3 py-2 mb-4">
            <?php echo e($errors->first()); ?>

        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('login.post')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Username / Email</label>
                <input type="text" name="username" value="<?php echo e(old('username')); ?>" required autofocus
                       placeholder="contoh: sales_user"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#714B67]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Password</label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#714B67]">
            </div>
            <button type="submit" class="w-full bg-[#714B67] hover:bg-[#5c3d56] text-white font-semibold rounded-lg py-2 text-sm transition">
                Masuk
            </button>
        </form>
    </div>

    <div class="mt-4 bg-white rounded-xl shadow-sm px-6 py-4 text-xs">
        <p class="font-semibold text-gray-600 mb-2">Akun Uji (password: <code class="bg-gray-100 px-1 rounded">Demo2026!</code>)</p>
        <table class="w-full text-gray-600">
            <thead><tr class="border-b"><th class="text-left py-0.5 font-medium">Username</th><th class="text-left py-0.5 font-medium">Role</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = [['sales_user','Sales'],['warehouse_mgr','Warehouse'],['warehouse_user','Warehouse'],['finance_user','Finance'],['purchase_user','Purchase'],['hr_user','HR'],['owner_user','Owner']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$u,$r]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-b last:border-0">
                    <td class="py-0.5 font-mono"><?php echo e($u); ?></td>
                    <td class="py-0.5"><?php echo e($r); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\arunika-fmcg-erp-system\resources\views/auth/login.blade.php ENDPATH**/ ?>