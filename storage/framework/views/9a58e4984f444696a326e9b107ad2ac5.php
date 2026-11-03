<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['stages'=>[],'current'=>'']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['stages'=>[],'current'=>'']); ?>
<?php foreach (array_filter((['stages'=>[],'current'=>'']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<div class="flex items-center gap-0 text-xs select-none">
    <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i=>$stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $isDone=array_search($stage,$stages)<array_search($current,$stages);$isCurrent=$stage===$current; ?>
    <?php if($i>0): ?><div class="w-4 flex items-center justify-center text-gray-300">›</div><?php endif; ?>
    <span class="px-3 py-1 rounded <?php if($isCurrent): ?> bg-[#714B67] text-white font-semibold <?php elseif($isDone): ?> bg-green-100 text-green-700 <?php else: ?> bg-gray-100 text-gray-400 <?php endif; ?>">
        <?php if($isDone): ?><span class="mr-1">✓</span><?php endif; ?><?php echo e($stage); ?>

    </span>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\arunika-fmcg-erp-system\resources\views/components/status-bar.blade.php ENDPATH**/ ?>