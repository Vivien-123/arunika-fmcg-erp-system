
<?php $__env->startSection('title','General Ledger'); ?>
<?php $currentApp = 'Accounting'; $sidebarMenu = [
    ['label'=>'Customer Invoices','url'=>route('accounting.invoices.index'),'icon'=>'🧾'],
    ['label'=>'Vendor Bills',     'url'=>route('accounting.bills.index'),   'icon'=>'📄'],
    ['label'=>'General Ledger',   'url'=>route('accounting.ledger.index'),  'icon'=>'📊'],
    ['label'=>'Chart of Accounts','url'=>route('accounting.coa.index'),     'icon'=>'📋'],
]; ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto px-2">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-800">General Ledger / Journal Entries</h1>
        <p class="text-xs text-gray-400 mt-0.5">S-13 · F-14 · SR-16: Setiap entry idempotent</p>
    </div>

    
    <form method="GET" class="flex gap-2 mb-4 flex-wrap">
        <input name="reference" value="<?php echo e(request('reference')); ?>" placeholder="Referensi..." class="text-xs border border-gray-200 rounded-lg px-3 py-2">
        <select name="journal_type" class="text-xs border border-gray-200 rounded-lg px-3 py-2">
            <option value="">Semua Tipe</option>
            <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($t); ?>" <?php echo e(request('journal_type')==$t?'selected':''); ?>><?php echo e(strtoupper($t)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <input name="date_from" type="date" value="<?php echo e(request('date_from')); ?>" class="text-xs border border-gray-200 rounded-lg px-3 py-2">
        <input name="date_to"   type="date" value="<?php echo e(request('date_to')); ?>"   class="text-xs border border-gray-200 rounded-lg px-3 py-2">
        <button class="bg-gray-100 hover:bg-gray-200 text-xs px-3 py-2 rounded-lg transition">Filter</button>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-xs">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-semibold">Referensi</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-semibold">Tipe</th>
                    <th class="text-center px-4 py-3 text-gray-500 font-semibold">Debit</th>
                    <th class="text-center px-4 py-3 text-gray-500 font-semibold">Kredit</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-semibold">Amount</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-semibold">Keterangan</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-semibold">Posted</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50/60 transition <?php echo e($e->is_reversed ? 'opacity-50 line-through' : ''); ?>">
                    <td class="px-4 py-2.5 font-mono text-[#714B67] font-semibold"><?php echo e($e->reference); ?></td>
                    <td class="px-4 py-2.5">
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold
                            <?php echo e($e->journal_type=='sales'   ? 'bg-blue-100 text-blue-700'   :
                              ($e->journal_type=='purchase'? 'bg-orange-100 text-orange-700':
                              ($e->journal_type=='payment' ? 'bg-green-100 text-green-700' :
                              ($e->journal_type=='stock'   ? 'bg-purple-100 text-purple-700':
                                                             'bg-gray-100 text-gray-600')))); ?>">
                            <?php echo e(strtoupper($e->journal_type)); ?>

                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-center font-mono text-blue-700 font-semibold"><?php echo e($e->debit_account); ?></td>
                    <td class="px-4 py-2.5 text-center font-mono text-green-700 font-semibold"><?php echo e($e->credit_account); ?></td>
                    <td class="px-4 py-2.5 text-right font-mono font-semibold">Rp<?php echo e(number_format($e->amount,0,',','.')); ?></td>
                    <td class="px-4 py-2.5 text-gray-500 max-w-xs truncate"><?php echo e($e->description); ?></td>
                    <td class="px-4 py-2.5 text-gray-400 whitespace-nowrap"><?php echo e($e->posted_at?->format('d/m H:i')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Belum ada journal entries.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4"><?php echo e($entries->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\arunika-fmcg-erp-system\resources\views/accounting/ledger/index.blade.php ENDPATH**/ ?>