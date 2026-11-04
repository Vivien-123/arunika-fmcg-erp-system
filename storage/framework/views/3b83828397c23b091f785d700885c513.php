
<?php $__env->startSection('title','Audit — '.$docId); ?>
<?php
    $currentApp  = 'Reporting';
    $sidebarMenu = [
        ['label' => 'Audit Log',   'url' => route('reporting.audit'),  'icon' => '📋'],
        ['label' => 'Dashboard',   'url' => route('home'),             'icon' => '📈'],
    ];

    $actionColors = [
        'created'               => 'bg-gray-100 text-gray-600',
        'login'                 => 'bg-blue-100 text-blue-700',
        'logout'                => 'bg-gray-100 text-gray-500',
        'status_changed'        => 'bg-yellow-100 text-yellow-700',
        'sign_off_authorized'   => 'bg-green-100 text-green-700',
        'sign_off_rejected'     => 'bg-red-100 text-red-700',
        'access_denied'         => 'bg-red-100 text-red-700',
        'private_access_denied' => 'bg-orange-100 text-orange-700',
        'confirm'               => 'bg-blue-100 text-blue-700',
        'validate'              => 'bg-green-100 text-green-700',
        'post'                  => 'bg-purple-100 text-purple-700',
        'payment'               => 'bg-emerald-100 text-emerald-700',
        'return'                => 'bg-rose-100 text-rose-700',
        'rejected_edit'         => 'bg-red-100 text-red-600',
        'double_post_blocked'   => 'bg-red-100 text-red-700',
    ];

    /* Type label mapping */
    $typeLabels = [
        'employee'       => '👤 Employee',
        'access_control' => '🔐 Access Control',
        'purchase_order' => '📦 Purchase Order',
        'sales_order'    => '🛒 Sales Order',
        'delivery'       => '🚚 Delivery',
        'receipt'        => '📥 Receipt',
        'customer_invoice' => '🧾 Customer Invoice',
        'vendor_bill'    => '📄 Vendor Bill',
        'payment'        => '💰 Payment',
        'credit_note'    => '↩️ Credit Note',
        'transfer'       => '🔄 Transfer',
    ];
?>

<?php $__env->startSection('breadcrumb'); ?>
    <span class="mx-1 text-gray-400">›</span>
    <a href="<?php echo e(route('reporting.audit')); ?>" class="hover:text-[#714B67]">Audit Log</a>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700 font-medium font-mono"><?php echo e($docId); ?></span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('smart_buttons'); ?>
    
    <a href="<?php echo e(route('reporting.audit')); ?>?document_ref=<?php echo e(urlencode($docId)); ?>"
       class="inline-flex flex-col items-center px-3 py-1.5 border rounded text-xs bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100 transition">
        <span class="text-base">📋</span>
        <span class="font-bold text-sm"><?php echo e($logs->count()); ?></span>
        <span>Log Entries</span>
    </a>

    <?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relType => $relCount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <span class="inline-flex flex-col items-center px-3 py-1.5 border rounded text-xs
        <?php echo e($relCount > 0 ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-gray-50 border-gray-200 text-gray-400'); ?>">
        <span class="text-base"><?php echo e($relCount > 0 ? '🔗' : '○'); ?></span>
        <span class="font-bold text-sm"><?php echo e($relCount); ?></span>
        <span class="capitalize"><?php echo e(str_replace('_', ' ', $relType)); ?></span>
    </span>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('status_bar'); ?>
    <?php
        $lastLog   = $logs->first();
        $firstLog  = $logs->last();
        $statusMap = [
            'created'  => 'Draft',
            'confirm'  => 'Confirmed',
            'validate' => 'Done',
            'post'     => 'Posted',
            'payment'  => 'Paid',
        ];
        $lastStatus = $lastLog ? ($statusMap[$lastLog->action] ?? ucfirst($lastLog->action)) : 'Unknown';
        $stages     = ['Draft', 'Confirmed', 'Done / Posted', 'Paid'];
    ?>
    <?php if (isset($component)) { $__componentOriginaleefe123c501def433fd4cb72e48804d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleefe123c501def433fd4cb72e48804d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-bar','data' => ['stages' => $stages,'current' => $lastStatus]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('status-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stages),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($lastStatus)]); ?>
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

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto space-y-4">

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-[#714B67] flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                📋
            </div>
            <div>
                <h2 class="font-bold text-gray-800 text-base font-mono"><?php echo e($docId); ?></h2>
                <p class="text-xs text-gray-500">
                    <?php echo e($typeLabels[$docType] ?? ucfirst(str_replace('_', ' ', $docType))); ?>

                    · <?php echo e($logs->count()); ?> log entries
                </p>
            </div>
        </div>
        <div class="px-6 py-4 grid grid-cols-2 gap-4 text-sm">
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Document</label>
                <p class="font-mono font-semibold text-gray-800"><?php echo e($docId); ?></p>
            </div>
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Tipe Dokumen</label>
                <p class="text-gray-700"><?php echo e($typeLabels[$docType] ?? ucfirst(str_replace('_', ' ', $docType))); ?></p>
            </div>
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Aksi Terakhir</label>
                <?php if($lastLog): ?>
                <?php $cls = $actionColors[$lastLog->action] ?? 'bg-gray-100 text-gray-600'; ?>
                <span class="inline-block px-2 py-0.5 rounded text-xs font-mono font-semibold <?php echo e($cls); ?>">
                    <?php echo e($lastLog->action); ?>

                </span>
                <?php else: ?>
                <span class="text-gray-400 italic">—</span>
                <?php endif; ?>
            </div>
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Timestamp Terakhir</label>
                <p class="font-mono text-gray-700">
                    <?php echo e($lastLog ? \Carbon\Carbon::parse($lastLog->acted_at)->format('d/m/Y H:i:s') : '—'); ?>

                </p>
            </div>
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Actor (User)</label>
                <p class="font-mono text-gray-700"><?php echo e($lastLog?->actor_user ?? '—'); ?></p>
            </div>
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Actor (Employee ID)</label>
                <p class="font-mono text-gray-700"><?php echo e($lastLog?->actor_employee ?? '—'); ?></p>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
            <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                ④ Riwayat Aksi — <?php echo e($docId); ?>

                <span class="font-normal text-gray-400 ml-1">(F-21 · SR-16 · SR-19)</span>
            </h3>
            <span class="text-xs text-gray-400"><?php echo e($logs->count()); ?> entri</span>
        </div>
        <table class="w-full text-xs">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Timestamp</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Reference</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Aksi</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Actor</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Employee</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Hasil / Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $cls = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-600'; ?>
                <tr class="border-b last:border-0 hover:bg-gray-50">
                    <td class="px-4 py-2 font-mono text-gray-500 whitespace-nowrap">
                        <?php echo e(\Carbon\Carbon::parse($log->acted_at)->format('d/m/Y H:i:s')); ?>

                    </td>
                    <td class="px-4 py-2 font-mono font-semibold text-[#714B67]"><?php echo e($log->document_id); ?></td>
                    <td class="px-4 py-2">
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-mono font-semibold <?php echo e($cls); ?>">
                            <?php echo e($log->action); ?>

                        </span>
                    </td>
                    <td class="px-4 py-2 font-mono text-gray-700"><?php echo e($log->actor_user); ?></td>
                    <td class="px-4 py-2 font-mono text-gray-500"><?php echo e($log->actor_employee ?? '—'); ?></td>
                    <td class="px-4 py-2 text-gray-600 max-w-xs"><?php echo e($log->description ?: '—'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-400 italic">
                        Tidak ada log untuk dokumen ini.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <?php if(count($related)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
            ⑤ Related Documents — Smart Buttons (F-21 · T-35)
        </h4>
        <div class="flex flex-wrap gap-3">
            <?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relType => $relCount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="inline-flex flex-col items-center px-4 py-2 border rounded-lg text-xs
                <?php echo e($relCount > 0 ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-gray-50 border-gray-200 text-gray-400'); ?>">
                <span class="font-bold text-lg"><?php echo e($relCount); ?></span>
                <span class="capitalize font-medium"><?php echo e(str_replace('_', ' ', $relType)); ?></span>
                <?php if($relCount === 0): ?>
                <span class="text-[10px] text-gray-400 mt-0.5">Belum dibuat</span>
                <?php else: ?>
                <span class="text-[10px] text-blue-500 mt-0.5">Lihat →</span>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <p class="text-[10px] text-gray-400 mt-3 italic">
            T-35: PO00007 → Receipt = 0, Bill = 0 (seed baseline, AP Rp700.000 tetap).
            T-36: Re-validasi setelah Done → diblokir idempotent (SR-16).
        </p>
    </div>
    <?php endif; ?>

    
    <?php
        $logsWithDiff = $logs->filter(fn($l) => $l->old_values || $l->new_values);
    ?>
    <?php if($logsWithDiff->isNotEmpty()): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
            Perubahan Nilai (Before / After)
        </h4>
        <div class="space-y-2">
            <?php $__currentLoopData = $logsWithDiff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="border border-gray-100 rounded-lg px-3 py-2 text-xs">
                <div class="flex items-center gap-2 mb-1">
                    <?php $cls = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-600'; ?>
                    <span class="font-mono text-gray-400">
                        <?php echo e(\Carbon\Carbon::parse($log->acted_at)->format('d/m/Y H:i')); ?>

                    </span>
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-mono <?php echo e($cls); ?>">
                        <?php echo e($log->action); ?>

                    </span>
                    <span class="text-gray-500">oleh <strong><?php echo e($log->actor_user); ?></strong></span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <?php if($log->old_values): ?>
                    <div class="bg-red-50 rounded px-2 py-1">
                        <p class="text-[10px] font-semibold text-red-600 uppercase mb-1">Before</p>
                        <?php $__currentLoopData = $log->old_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p class="font-mono text-red-700"><?php echo e($k); ?>: <?php echo e($v); ?></p>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php endif; ?>
                    <?php if($log->new_values): ?>
                    <div class="bg-green-50 rounded px-2 py-1">
                        <p class="text-[10px] font-semibold text-green-600 uppercase mb-1">After</p>
                        <?php $__currentLoopData = $log->new_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p class="font-mono text-green-700"><?php echo e($k); ?>: <?php echo e($v); ?></p>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="text-xs">
        <a href="<?php echo e(route('reporting.audit')); ?>" class="text-[#714B67] hover:underline">
            ← Kembali ke Audit Log
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\ASUS Vivobook\arunika-fmcg-erp-system\arunika-fmcg-erp-system\resources\views/reporting/audit-show.blade.php ENDPATH**/ ?>