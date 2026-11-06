<?php $__env->startSection('title','Employee — '.$employee->name); ?>
<?php
    $currentApp  = 'Employees';
    $sidebarMenu = [
        ['label'=>'Semua Pegawai','url'=>route('employees.index'),'icon'=>'👥'],
        ['label'=>'Departemen',   'url'=>route('employees.index').'?view=dept','icon'=>'🏢'],
    ];
?>

<?php $__env->startSection('breadcrumb'); ?>
    <span class="mx-1 text-gray-400">›</span>
    <a href="<?php echo e(route('employees.index')); ?>" class="hover:text-[#714B67]">Employees</a>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700"><?php echo e($employee->name); ?></span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('status_bar'); ?>
    <?php if (isset($component)) { $__componentOriginaleefe123c501def433fd4cb72e48804d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleefe123c501def433fd4cb72e48804d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-bar','data' => ['stages' => ['Draft','Aktif','Nonaktif'],'current' => $employee->status==='Aktif' ? 'Aktif' : 'Nonaktif']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Draft','Aktif','Nonaktif']),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($employee->status==='Aktif' ? 'Aktif' : 'Nonaktif')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaleefe123c501def433fd4cb72e48804d5)): ?>
<?php $attributes = $__attributesOriginaleefe123c501def433fd4cb72e48804d5; ?>
<?php unset($__attributesOriginaleefe123c501def433fd4cb72e48804d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaleefe123c501def433fd4cb72e48804d5)): ?>
<?php $component = $__componentOriginaleefe123c501def433fd4cb72e48804d5; ?>
<?php unset($__componentOriginaleefe123c501def433fd4cb72e48804d5); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('smart_buttons'); ?>
    <?php if (isset($component)) { $__componentOriginale49e4b6f111759f4b7afba027a52d01a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale49e4b6f111759f4b7afba027a52d01a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.smart-button','data' => ['icon' => '📋','label' => 'Audit Log','count' => $auditCount,'href' => '#log-notes','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('smart-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => '📋','label' => 'Audit Log','count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($auditCount),'href' => '#log-notes','color' => 'gray']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale49e4b6f111759f4b7afba027a52d01a)): ?>
<?php $attributes = $__attributesOriginale49e4b6f111759f4b7afba027a52d01a; ?>
<?php unset($__attributesOriginale49e4b6f111759f4b7afba027a52d01a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale49e4b6f111759f4b7afba027a52d01a)): ?>
<?php $component = $__componentOriginale49e4b6f111759f4b7afba027a52d01a; ?>
<?php unset($__componentOriginale49e4b6f111759f4b7afba027a52d01a); ?>
<?php endif; ?>
    <?php if($employee->canSignOff()): ?>
        <?php if (isset($component)) { $__componentOriginale49e4b6f111759f4b7afba027a52d01a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale49e4b6f111759f4b7afba027a52d01a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.smart-button','data' => ['icon' => '✅','label' => 'Bisa Sign-off','href' => '#','color' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('smart-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => '✅','label' => 'Bisa Sign-off','href' => '#','color' => 'green']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale49e4b6f111759f4b7afba027a52d01a)): ?>
<?php $attributes = $__attributesOriginale49e4b6f111759f4b7afba027a52d01a; ?>
<?php unset($__attributesOriginale49e4b6f111759f4b7afba027a52d01a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale49e4b6f111759f4b7afba027a52d01a)): ?>
<?php $component = $__componentOriginale49e4b6f111759f4b7afba027a52d01a; ?>
<?php unset($__componentOriginale49e4b6f111759f4b7afba027a52d01a); ?>
<?php endif; ?>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginale49e4b6f111759f4b7afba027a52d01a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale49e4b6f111759f4b7afba027a52d01a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.smart-button','data' => ['icon' => '🚫','label' => 'Tidak Sign-off','href' => '#','color' => 'red']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('smart-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => '🚫','label' => 'Tidak Sign-off','href' => '#','color' => 'red']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale49e4b6f111759f4b7afba027a52d01a)): ?>
<?php $attributes = $__attributesOriginale49e4b6f111759f4b7afba027a52d01a; ?>
<?php unset($__attributesOriginale49e4b6f111759f4b7afba027a52d01a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale49e4b6f111759f4b7afba027a52d01a)): ?>
<?php $component = $__componentOriginale49e4b6f111759f4b7afba027a52d01a; ?>
<?php unset($__componentOriginale49e4b6f111759f4b7afba027a52d01a); ?>
<?php endif; ?>
    <?php endif; ?>
    <?php if($isHR && $employee->privateRecord): ?>
        <?php if (isset($component)) { $__componentOriginale49e4b6f111759f4b7afba027a52d01a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale49e4b6f111759f4b7afba027a52d01a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.smart-button','data' => ['icon' => '🔐','label' => 'Data Privat','href' => '#private','color' => 'purple']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('smart-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => '🔐','label' => 'Data Privat','href' => '#private','color' => 'purple']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale49e4b6f111759f4b7afba027a52d01a)): ?>
<?php $attributes = $__attributesOriginale49e4b6f111759f4b7afba027a52d01a; ?>
<?php unset($__attributesOriginale49e4b6f111759f4b7afba027a52d01a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale49e4b6f111759f4b7afba027a52d01a)): ?>
<?php $component = $__componentOriginale49e4b6f111759f4b7afba027a52d01a; ?>
<?php unset($__componentOriginale49e4b6f111759f4b7afba027a52d01a); ?>
<?php endif; ?>
    <?php endif; ?>
    <?php if (isset($component)) { $__componentOriginale49e4b6f111759f4b7afba027a52d01a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale49e4b6f111759f4b7afba027a52d01a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.smart-button','data' => ['icon' => '🏢','label' => ''.e($employee->department?->name).'','href' => ''.e(route('employees.index')).'?dept='.e($employee->department_id).'','color' => 'blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('smart-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => '🏢','label' => ''.e($employee->department?->name).'','href' => ''.e(route('employees.index')).'?dept='.e($employee->department_id).'','color' => 'blue']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale49e4b6f111759f4b7afba027a52d01a)): ?>
<?php $attributes = $__attributesOriginale49e4b6f111759f4b7afba027a52d01a; ?>
<?php unset($__attributesOriginale49e4b6f111759f4b7afba027a52d01a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale49e4b6f111759f4b7afba027a52d01a)): ?>
<?php $component = $__componentOriginale49e4b6f111759f4b7afba027a52d01a; ?>
<?php unset($__componentOriginale49e4b6f111759f4b7afba027a52d01a); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-4">

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full flex items-center justify-center text-white font-bold text-2xl flex-shrink-0 <?php echo e($employee->status==='Aktif' ? 'bg-[#714B67]':'bg-gray-400'); ?>">
                <?php echo e(strtoupper(substr($employee->name,0,1))); ?>

            </div>
            <div class="flex-1">
                <h2 class="text-lg font-bold text-gray-800"><?php echo e($employee->name); ?></h2>
                <p class="text-sm text-gray-500"><?php echo e($employee->job_title); ?></p>
                <div class="mt-1 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full <?php echo e($employee->status==='Aktif' ? 'bg-green-100 text-green-700':'bg-red-100 text-red-600'); ?>">
                        <span class="w-1.5 h-1.5 rounded-full inline-block <?php echo e($employee->status==='Aktif' ? 'bg-green-500':'bg-red-400'); ?>"></span>
                        <?php echo e($employee->status); ?>

                    </span>
                    <span class="text-xs text-gray-400 font-mono"><?php echo e($employee->id); ?></span>
                </div>
            </div>
            <?php if($isHR): ?>
            <form method="POST" action="<?php echo e(route('employees.toggleStatus',$employee->id)); ?>"
                  onsubmit="return confirm('Ubah status <?php echo e($employee->name); ?>?')">
                <?php echo csrf_field(); ?>
                <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border transition <?php echo e($employee->status==='Aktif' ? 'border-red-300 text-red-600 hover:bg-red-50':'border-green-300 text-green-600 hover:bg-green-50'); ?>">
                    <?php echo e($employee->status==='Aktif' ? '🔴 Nonaktifkan':'🟢 Aktifkan'); ?>

                </button>
            </form>
            <?php endif; ?>
        </div>

        <div class="px-6 py-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Employee ID</label><p class="font-mono font-semibold text-gray-800"><?php echo e($employee->id); ?></p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Nama Lengkap</label><p class="text-gray-800"><?php echo e($employee->name); ?></p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Departemen</label><p><a href="<?php echo e(route('employees.index')); ?>?dept=<?php echo e($employee->department_id); ?>" class="text-[#714B67] hover:underline"><?php echo e($employee->department?->name ?? $employee->department_id); ?></a></p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Jabatan</label><p class="text-gray-800"><?php echo e($employee->job_title); ?></p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Lokasi</label><p class="text-gray-800"><?php echo e($employee->location); ?></p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Status</label><p class="<?php echo e($employee->status==='Aktif' ? 'text-green-700 font-semibold':'text-red-600 font-semibold'); ?>"><?php echo e($employee->status); ?></p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Mapped User</label>
                    <p class="text-gray-800">
                        <?php if($employee->mappedUser): ?> <span class="font-mono"><?php echo e($employee->mappedUser->username); ?></span> <span class="text-xs text-gray-400">(<?php echo e($employee->mappedUser->role); ?>)</span>
                        <?php else: ?> <span class="text-gray-400 italic">— Tidak ada</span> <?php endif; ?>
                    </p>
                </div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Otorisasi Sign-off</label>
                    <?php if($employee->canSignOff()): ?> <p class="text-green-700 font-semibold text-sm">✅ Bisa sign-off gudang</p>
                    <?php else: ?> <p class="text-red-600 text-sm">🚫 Tidak bisa <?php if($employee->status==='Nonaktif'): ?>(Nonaktif)<?php elseif($employee->department_id!=='D-WH'): ?>(Bukan Warehouse)<?php else: ?><?php endif; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <?php if($isHR): ?>
    <div id="private" class="bg-white rounded-xl shadow-sm border border-purple-200 overflow-hidden">
        <div class="px-6 py-3 border-b border-purple-100 bg-purple-50 flex items-center gap-2">
            <span>🔐</span>
            <h3 class="text-sm font-semibold text-purple-700">Data Privat Pegawai</h3>
            <span class="text-xs text-purple-500 ml-auto">Hanya role HR (SR-15)</span>
        </div>
        <div class="px-6 py-4">
            <?php if($privateRecord): ?>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Email Pribadi</label><p><?php echo e($privateRecord->personal_email); ?></p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Kontak Darurat</label><p><?php echo e($privateRecord->emergency_contact); ?></p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Telepon Pribadi</label><p><?php echo e($privateRecord->private_phone); ?></p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Access Role</label><p class="text-purple-700 font-semibold">HR only</p></div>
            </div>
            <?php else: ?>
            <p class="text-sm text-gray-400 italic">Tidak ada data privat untuk pegawai ini.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-gray-50 rounded-xl border border-gray-200 px-6 py-4 text-sm text-gray-500 flex items-center gap-2">
        <span>🔒</span>
        <span>Data pribadi hanya dapat diakses oleh role <strong>HR</strong>. (SR-15)</span>
    </div>
    <?php endif; ?>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4">
        <?php if (isset($component)) { $__componentOriginalf0cb44ac341a1bf07b4859fea2a0f4bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0cb44ac341a1bf07b4859fea2a0f4bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.related-docs','data' => ['docs' => []]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('related-docs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['docs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf0cb44ac341a1bf07b4859fea2a0f4bc)): ?>
<?php $attributes = $__attributesOriginalf0cb44ac341a1bf07b4859fea2a0f4bc; ?>
<?php unset($__attributesOriginalf0cb44ac341a1bf07b4859fea2a0f4bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf0cb44ac341a1bf07b4859fea2a0f4bc)): ?>
<?php $component = $__componentOriginalf0cb44ac341a1bf07b4859fea2a0f4bc; ?>
<?php unset($__componentOriginalf0cb44ac341a1bf07b4859fea2a0f4bc); ?>
<?php endif; ?>
        <p class="text-xs text-gray-400 italic mt-2">Related documents akan muncul setelah modul lain diimplementasi.</p>
    </div>

    
    <div id="log-notes" class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4">
        <?php if (isset($component)) { $__componentOriginalb8ff04b5998c69c86e3ee4da1e14e878 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb8ff04b5998c69c86e3ee4da1e14e878 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.log-notes','data' => ['logs' => $auditLogs,'documentType' => 'employee','documentId' => ''.e($employee->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('log-notes'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['logs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($auditLogs),'documentType' => 'employee','documentId' => ''.e($employee->id).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb8ff04b5998c69c86e3ee4da1e14e878)): ?>
<?php $attributes = $__attributesOriginalb8ff04b5998c69c86e3ee4da1e14e878; ?>
<?php unset($__attributesOriginalb8ff04b5998c69c86e3ee4da1e14e878); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb8ff04b5998c69c86e3ee4da1e14e878)): ?>
<?php $component = $__componentOriginalb8ff04b5998c69c86e3ee4da1e14e878; ?>
<?php unset($__componentOriginalb8ff04b5998c69c86e3ee4da1e14e878); ?>
<?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\arunika-fmcg-erp-system\resources\views/employees/show.blade.php ENDPATH**/ ?>