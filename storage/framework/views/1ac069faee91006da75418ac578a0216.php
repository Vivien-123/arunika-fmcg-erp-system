
<?php $__env->startSection('title','Vendor Bills'); ?>
<?php $currentApp = 'Accounting'; $sidebarMenu = [
    ['label'=>'Customer Invoices','url'=>route('accounting.invoices.index'),'icon'=>'🧾'],
    ['label'=>'Vendor Bills',     'url'=>route('accounting.bills.index'),   'icon'=>'📄'],
    ['label'=>'General Ledger',   'url'=>route('accounting.ledger.index'),  'icon'=>'📊'],
    ['label'=>'Chart of Accounts','url'=>route('accounting.coa.index'),     'icon'=>'📋'],
]; ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto px-2">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Vendor Bills</h1>
            <p class="text-xs text-gray-400 mt-0.5">S-11 · F-13 · SR-06: 3-Way Match PO + Receipt + Bill</p>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 text-xs px-4 py-2.5 rounded-lg mb-4">✅ <?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-xs">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-semibold">No. Dokumen</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-semibold">Vendor</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-semibold">Source PO</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-semibold">Net</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-semibold">Gross</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-semibold">Outstanding</th>
                    <th class="text-center px-4 py-3 text-gray-500 font-semibold">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50/60 transition">
                    <td class="px-4 py-3 font-mono font-semibold text-[#714B67]"><?php echo e($inv->document_number); ?></td>
                    <td class="px-4 py-3 text-gray-700"><?php echo e($inv->partner_name); ?></td>
                    <td class="px-4 py-3 font-mono text-gray-500"><?php echo e($inv->source_document_id ?? '-'); ?></td>
                    <td class="px-4 py-3 text-right">Rp<?php echo e(number_format($inv->untaxed_amount,0,',','.')); ?></td>
                    <td class="px-4 py-3 text-right font-semibold">Rp<?php echo e(number_format($inv->gross_amount,0,',','.')); ?></td>
                    <td class="px-4 py-3 text-right <?php echo e($inv->amount_outstanding > 0 ? 'text-red-600 font-semibold' : 'text-green-600'); ?>">
                        Rp<?php echo e(number_format($inv->amount_outstanding,0,',','.')); ?>

                    </td>
                    <td class="px-4 py-3 text-center">
                        <?php $colors = ['draft'=>'bg-yellow-100 text-yellow-700','posted'=>'bg-blue-100 text-blue-700','paid'=>'bg-green-100 text-green-700']; ?>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold <?php echo e($colors[$inv->status] ?? 'bg-gray-100 text-gray-600'); ?>">
                            <?php echo e(strtoupper($inv->status)); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="<?php echo e(route('accounting.bills.show', $inv->id)); ?>" class="text-[#714B67] hover:underline font-medium">Lihat →</a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Belum ada vendor bill.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4"><?php echo e($invoices->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\arunika-fmcg-erp-system\resources\views/accounting/bills/index.blade.php ENDPATH**/ ?>