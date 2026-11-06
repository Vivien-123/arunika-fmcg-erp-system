<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['logs'=>collect(),'documentType'=>'','documentId'=>'']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['logs'=>collect(),'documentType'=>'','documentId'=>'']); ?>
<?php foreach (array_filter((['logs'=>collect(),'documentType'=>'','documentId'=>'']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<div id="log-notes" class="mt-3">
    <div class="flex items-center justify-between mb-2">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Log Notes & Audit</h4>
        <span class="text-xs text-gray-400"><?php echo e($logs->count()); ?> entri</span>
    </div>
    <?php if($logs->isEmpty()): ?>
    <p class="text-xs text-gray-400 italic">Belum ada log.</p>
    <?php else: ?>
    <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
        <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $colors=['created'=>'bg-gray-100 text-gray-600','status_changed'=>'bg-yellow-100 text-yellow-700','sign_off_authorized'=>'bg-green-100 text-green-700','sign_off_rejected'=>'bg-red-100 text-red-700','access_denied'=>'bg-red-100 text-red-700','private_access_denied'=>'bg-orange-100 text-orange-700','login'=>'bg-blue-100 text-blue-700','logout'=>'bg-gray-100 text-gray-500'];$cls=$colors[$log->action]??'bg-gray-100 text-gray-600'; ?>
        <div class="flex gap-2 text-xs border-l-2 border-gray-200 pl-2">
            <div class="flex-shrink-0 text-gray-400 w-32"><?php echo e(\Carbon\Carbon::parse($log->acted_at)->format('d/m/Y H:i')); ?></div>
            <div class="flex-1">
                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-mono <?php echo e($cls); ?>"><?php echo e($log->action); ?></span>
                <span class="text-gray-500 ml-1">oleh <strong><?php echo e($log->actor_employee??$log->actor_user); ?></strong></span>
                <?php if($log->description): ?><p class="text-gray-600 mt-0.5"><?php echo e($log->description); ?></p><?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\arunika-fmcg-erp-system\resources\views/components/log-notes.blade.php ENDPATH**/ ?>