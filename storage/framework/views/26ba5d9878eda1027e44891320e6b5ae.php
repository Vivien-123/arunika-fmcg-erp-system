<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['docs'=>[]]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['docs'=>[]]); ?>
<?php foreach (array_filter((['docs'=>[]]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<?php if(count($docs)>0): ?>
<div class="mt-3">
    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Related Documents</h4>
    <div class="flex flex-wrap gap-2">
        <?php $__currentLoopData = $docs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e($doc['url']??'#'); ?>" class="inline-flex items-center gap-1.5 border border-gray-200 rounded px-2 py-1 text-xs bg-white hover:bg-gray-50 text-gray-700 transition">
            <span class="text-gray-400">📄</span>
            <span class="font-mono font-semibold"><?php echo e($doc['id']); ?></span>
            <span class="text-gray-500"><?php echo e($doc['type']); ?></span>
            <?php if(isset($doc['status'])): ?><span class="text-[10px] px-1 rounded <?php if(in_array($doc['status'],['Done','Posted','Paid'])): ?> bg-green-100 text-green-700 <?php elseif($doc['status']==='Confirmed'): ?> bg-blue-100 text-blue-700 <?php else: ?> bg-gray-100 text-gray-500 <?php endif; ?>"><?php echo e($doc['status']); ?></span><?php endif; ?>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endif; ?>
<?php /**PATH C:\arunika-fmcg-erp-system\resources\views/components/related-docs.blade.php ENDPATH**/ ?>