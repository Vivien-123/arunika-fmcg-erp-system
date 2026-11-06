
<?php $__env->startSection('title','Buat Credit Note'); ?>
<?php $currentApp = 'Accounting'; $sidebarMenu = [
    ['label'=>'Customer Invoices','url'=>route('accounting.invoices.index'),'icon'=>'🧾'],
]; ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto px-2">
    <div class="mb-5">
        <p class="text-xs text-gray-400 mb-1"><a href="<?php echo e(route('accounting.invoices.show', $invoice->id)); ?>" class="hover:underline"><?php echo e($invoice->document_number); ?></a> › Credit Note</p>
        <h1 class="text-xl font-bold text-gray-800">Buat Credit Note</h1>
        <p class="text-xs text-gray-400 mt-0.5">S-16 · F-16 · SR-13: Max qty = invoiced − already credited</p>
    </div>

    <?php if($errors->any()): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-2.5 rounded-lg mb-4">❌ <?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <div class="bg-orange-50 border border-orange-200 rounded-xl px-4 py-3 mb-4 text-xs text-orange-700">
        ⚠ SR-13: Credit Note maksimal qty yang sudah ditagih dan belum dikreditkan.
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
        <h3 class="font-semibold text-gray-700 text-sm mb-3">Batas Qty yang Dapat Dikreditkan</h3>
        <table class="w-full text-xs">
            <thead class="bg-gray-50"><tr>
                <th class="text-left px-3 py-2 text-gray-500">SKU</th>
                <th class="text-right px-3 py-2 text-gray-500">Max Creditable Qty</th>
            </tr></thead>
            <tbody>
                <?php $__currentLoopData = $maxQty; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sku => $qty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-t border-gray-50">
                    <td class="px-3 py-2 font-mono text-gray-600"><?php echo e($sku); ?></td>
                    <td class="px-3 py-2 text-right font-bold <?php echo e($qty > 0 ? 'text-green-600' : 'text-red-400'); ?>"><?php echo e($qty); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <form method="POST" action="<?php echo e(route('accounting.credit-notes.store', $invoice->id)); ?>">
        <?php echo csrf_field(); ?>
        <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(($maxQty[$item->variant_sku] ?? 0) > 0): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-3">
            <p class="font-mono font-semibold text-[#714B67] text-xs mb-3"><?php echo e($item->variant_sku); ?> — <?php echo e($item->product_name); ?></p>
            <input type="hidden" name="return_lines[<?php echo e($i); ?>][variant_sku]" value="<?php echo e($item->variant_sku); ?>">
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="text-[10px] text-gray-400">Return Qty (max <?php echo e($maxQty[$item->variant_sku]); ?>)</label>
                    <input name="return_lines[<?php echo e($i); ?>][qty]" type="number" min="0" max="<?php echo e($maxQty[$item->variant_sku]); ?>" value="0"
                           class="w-full mt-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5">
                </div>
                <div>
                    <label class="text-[10px] text-gray-400">Layak Jual?</label>
                    <select name="return_lines[<?php echo e($i); ?>][is_saleable]" class="w-full mt-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5">
                        <option value="1">Ya (reversal COGS)</option>
                        <option value="0">Tidak</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] text-gray-400">Unit Cost (Rp)</label>
                    <input name="return_lines[<?php echo e($i); ?>][unit_cost]" type="number" value="0"
                           class="w-full mt-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5">
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div class="flex gap-3">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-5 py-2.5 rounded-lg shadow transition">
                Buat & Post Credit Note
            </button>
            <a href="<?php echo e(route('accounting.invoices.show', $invoice->id)); ?>" class="text-xs text-gray-500 hover:text-gray-700 px-4 py-2.5">Batal</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\arunika-fmcg-erp-system\resources\views/accounting/credit-notes/create.blade.php ENDPATH**/ ?>