<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['icon'=>'📌','label'=>'','count'=>null,'href'=>'#','color'=>'gray']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['icon'=>'📌','label'=>'','count'=>null,'href'=>'#','color'=>'gray']); ?>
<?php foreach (array_filter((['icon'=>'📌','label'=>'','count'=>null,'href'=>'#','color'=>'gray']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<?php $colors=['gray'=>'bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100','purple'=>'bg-purple-50 border-purple-200 text-purple-700 hover:bg-purple-100','green'=>'bg-green-50 border-green-200 text-green-700 hover:bg-green-100','red'=>'bg-red-50 border-red-200 text-red-700 hover:bg-red-100','blue'=>'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100'];$cls=$colors[$color]??$colors['gray']; ?>
<a href="<?php echo e($href); ?>" class="inline-flex flex-col items-center px-3 py-1.5 border rounded text-xs transition <?php echo e($cls); ?>">
    <span class="text-base leading-tight"><?php echo e($icon); ?></span>
    <?php if($count!==null): ?><span class="font-bold text-sm leading-tight"><?php echo e($count); ?></span><?php endif; ?>
    <span class="leading-tight"><?php echo e($label); ?></span>
</a>
<?php /**PATH C:\arunika-fmcg-erp-system\resources\views/components/smart-button.blade.php ENDPATH**/ ?>