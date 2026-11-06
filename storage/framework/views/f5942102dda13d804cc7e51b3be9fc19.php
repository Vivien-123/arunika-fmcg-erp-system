
<?php $__env->startSection('title', $invoice->document_number); ?>
<?php $currentApp = 'Accounting'; $sidebarMenu = [
    ['label'=>'Customer Invoices','url'=>route('accounting.invoices.index'),'icon'=>'🧾'],
    ['label'=>'Vendor Bills',     'url'=>route('accounting.bills.index'),   'icon'=>'📄'],
    ['label'=>'General Ledger',   'url'=>route('accounting.ledger.index'),  'icon'=>'📊'],
]; ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto px-2">
    
    <?php if(session('success')): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 text-xs px-4 py-2.5 rounded-lg mb-4">✅ <?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-2.5 rounded-lg mb-4">❌ <?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    
    <div class="flex items-start justify-between mb-5">
        <div>
            <p class="text-xs text-gray-400 mb-1"><a href="<?php echo e(route('accounting.invoices.index')); ?>" class="hover:underline">Customer Invoices</a> › <?php echo e($invoice->document_number); ?></p>
            <h1 class="text-xl font-bold text-gray-800"><?php echo e($invoice->document_number); ?></h1>
            <p class="text-xs text-gray-500 mt-0.5"><?php echo e($invoice->partner_name); ?> · <?php echo e($invoice->invoice_date?->format('d/m/Y')); ?></p>
        </div>
        <div class="flex gap-2 items-center">
            <?php $colors = ['draft'=>'bg-yellow-100 text-yellow-700','posted'=>'bg-blue-100 text-blue-700','paid'=>'bg-green-100 text-green-700']; ?>
            <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo e($colors[$invoice->status] ?? 'bg-gray-100 text-gray-600'); ?>">
                <?php echo e(strtoupper($invoice->status)); ?>

            </span>
            <?php if($invoice->status === 'draft'): ?>
            <form method="POST" action="<?php echo e(route('accounting.invoices.post', $invoice->id)); ?>">
                <?php echo csrf_field(); ?>
                <button class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-1.5 rounded-lg transition">Post Invoice</button>
            </form>
            <?php endif; ?>
            <?php if($invoice->status === 'posted'): ?>
            <a href="<?php echo e(route('accounting.payments.create', $invoice->id)); ?>" class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-4 py-1.5 rounded-lg transition">Bayar</a>
            <a href="<?php echo e(route('accounting.credit-notes.create', $invoice->id)); ?>" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-4 py-1.5 rounded-lg transition">Credit Note</a>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3 shadow-sm">
            <p class="text-[10px] text-gray-400 uppercase tracking-wide">Net (DPP)</p>
            <p class="text-base font-bold text-gray-800 mt-0.5">Rp<?php echo e(number_format($invoice->untaxed_amount,0,',','.')); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3 shadow-sm">
            <p class="text-[10px] text-gray-400 uppercase tracking-wide">PPN 11%</p>
            <p class="text-base font-bold text-gray-800 mt-0.5">Rp<?php echo e(number_format($invoice->tax_amount,0,',','.')); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3 shadow-sm">
            <p class="text-[10px] text-gray-400 uppercase tracking-wide">Gross / Outstanding</p>
            <p class="text-base font-bold text-gray-800 mt-0.5">Rp<?php echo e(number_format($invoice->gross_amount,0,',','.')); ?></p>
            <?php if($invoice->amount_outstanding > 0): ?>
            <p class="text-[10px] text-red-500 font-semibold mt-0.5">Sisa: Rp<?php echo e(number_format($invoice->amount_outstanding,0,',','.')); ?></p>
            <?php else: ?>
            <p class="text-[10px] text-green-600 font-semibold mt-0.5">LUNAS</p>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/80">
            <h3 class="font-semibold text-gray-700 text-sm">Baris Invoice</h3>
        </div>
        <table class="w-full text-xs">
            <thead class="bg-gray-50"><tr>
                <th class="text-left px-4 py-2 text-gray-500">SKU</th>
                <th class="text-left px-4 py-2 text-gray-500">Produk</th>
                <th class="text-right px-4 py-2 text-gray-500">Qty</th>
                <th class="text-right px-4 py-2 text-gray-500">Harga Satuan</th>
                <th class="text-right px-4 py-2 text-gray-500">Net</th>
                <th class="text-right px-4 py-2 text-gray-500">PPN</th>
                <th class="text-right px-4 py-2 text-gray-500">Gross</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50/60">
                    <td class="px-4 py-2 font-mono text-gray-600"><?php echo e($item->variant_sku); ?></td>
                    <td class="px-4 py-2 text-gray-700"><?php echo e($item->product_name); ?></td>
                    <td class="px-4 py-2 text-right"><?php echo e($item->quantity); ?> <?php echo e($item->unit); ?></td>
                    <td class="px-4 py-2 text-right">Rp<?php echo e(number_format($item->unit_price,0,',','.')); ?></td>
                    <td class="px-4 py-2 text-right">Rp<?php echo e(number_format($item->net_amount,0,',','.')); ?></td>
                    <td class="px-4 py-2 text-right text-gray-500">Rp<?php echo e(number_format($item->tax_amount,0,',','.')); ?></td>
                    <td class="px-4 py-2 text-right font-semibold">Rp<?php echo e(number_format($item->gross_amount,0,',','.')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    
    <?php if($invoice->journalEntries->count() > 0): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/80">
            <h3 class="font-semibold text-gray-700 text-sm">Journal Entries (F-14)</h3>
        </div>
        <table class="w-full text-xs">
            <thead class="bg-gray-50"><tr>
                <th class="text-left px-4 py-2 text-gray-500">Referensi</th>
                <th class="text-left px-4 py-2 text-gray-500">Debit</th>
                <th class="text-left px-4 py-2 text-gray-500">Kredit</th>
                <th class="text-right px-4 py-2 text-gray-500">Amount</th>
                <th class="text-left px-4 py-2 text-gray-500">Keterangan</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__currentLoopData = $invoice->journalEntries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $je): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50/60">
                    <td class="px-4 py-2 font-mono text-gray-600"><?php echo e($je->reference); ?></td>
                    <td class="px-4 py-2 text-blue-600 font-semibold"><?php echo e($je->debit_account); ?></td>
                    <td class="px-4 py-2 text-green-600 font-semibold"><?php echo e($je->credit_account); ?></td>
                    <td class="px-4 py-2 text-right font-mono">Rp<?php echo e(number_format($je->amount,0,',','.')); ?></td>
                    <td class="px-4 py-2 text-gray-500"><?php echo e($je->description); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    
    <?php if($auditLogs->count() > 0): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/80">
            <h3 class="font-semibold text-gray-700 text-sm">Log Audit (F-21)</h3>
        </div>
        <div class="divide-y divide-gray-50">
            <?php $__currentLoopData = $auditLogs->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="px-4 py-3 flex items-start gap-3">
                <span class="text-base">📝</span>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-700"><?php echo e($log->action); ?> <span class="font-normal text-gray-400">oleh <?php echo e($log->actor_user); ?></span></p>
                    <p class="text-[11px] text-gray-500 mt-0.5"><?php echo e($log->description); ?></p>
                </div>
                <span class="text-[10px] text-gray-400 whitespace-nowrap"><?php echo e($log->acted_at->format('d/m H:i')); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\arunika-fmcg-erp-system\resources\views/accounting/invoices/show.blade.php ENDPATH**/ ?>