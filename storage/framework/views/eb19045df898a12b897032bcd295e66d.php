<?php $__env->startSection('title','Audit / Related Documents'); ?>
<?php
    $currentApp  = 'Reporting';
    $sidebarMenu = [
        ['label' => 'Audit Log', 'url' => route('reporting.audit'), 'icon' => '📋'],
        ['label' => 'Beranda',   'url' => route('home'),            'icon' => '🏠'],
    ];

    $actionColors = [
        'created'               => ['bg'=>'bg-gray-100',   'text'=>'text-gray-600',   'dot'=>'bg-gray-400'],
        'login'                 => ['bg'=>'bg-blue-100',   'text'=>'text-blue-700',   'dot'=>'bg-blue-400'],
        'logout'                => ['bg'=>'bg-slate-100',  'text'=>'text-slate-500',  'dot'=>'bg-slate-400'],
        'status_changed'        => ['bg'=>'bg-yellow-100', 'text'=>'text-yellow-700', 'dot'=>'bg-yellow-400'],
        'sign_off_authorized'   => ['bg'=>'bg-green-100',  'text'=>'text-green-700',  'dot'=>'bg-green-500'],
        'sign_off_rejected'     => ['bg'=>'bg-red-100',    'text'=>'text-red-700',    'dot'=>'bg-red-500'],
        'access_denied'         => ['bg'=>'bg-red-100',    'text'=>'text-red-700',    'dot'=>'bg-red-500'],
        'private_access_denied' => ['bg'=>'bg-orange-100', 'text'=>'text-orange-700', 'dot'=>'bg-orange-500'],
        'confirm'               => ['bg'=>'bg-blue-100',   'text'=>'text-blue-700',   'dot'=>'bg-blue-500'],
        'reserve'               => ['bg'=>'bg-indigo-100', 'text'=>'text-indigo-700', 'dot'=>'bg-indigo-400'],
        'validate'              => ['bg'=>'bg-green-100',  'text'=>'text-green-700',  'dot'=>'bg-green-500'],
        'post'                  => ['bg'=>'bg-purple-100', 'text'=>'text-purple-700', 'dot'=>'bg-purple-500'],
        'payment'               => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','dot'=>'bg-emerald-500'],
        'return'                => ['bg'=>'bg-rose-100',   'text'=>'text-rose-700',   'dot'=>'bg-rose-400'],
        'rejected_edit'         => ['bg'=>'bg-red-100',    'text'=>'text-red-600',    'dot'=>'bg-red-400'],
        'double_post_blocked'   => ['bg'=>'bg-red-100',    'text'=>'text-red-700',    'dot'=>'bg-red-500'],
    ];

    $typeIcons = [
        'employee'         => '👤',
        'access_control'   => '🔐',
        'purchase_order'   => '📦',
        'sales_order'      => '🛒',
        'delivery'         => '🚚',
        'receipt'          => '📥',
        'customer_invoice' => '🧾',
        'vendor_bill'      => '📄',
        'payment'          => '💰',
        'credit_note'      => '↩️',
        'transfer'         => '🔄',
        'lead'             => '📋',
    ];

    $hasActiveFilters = count($filters) > 0;
?>

<?php $__env->startSection('breadcrumb'); ?>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-500">Reporting</span>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700 font-medium">Audit Trail</span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('view_switcher'); ?>
    <span class="px-2 py-1 text-xs bg-[#714B67] text-white">☰ List</span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('control_panel_extra'); ?>

<div class="flex items-center gap-2">
    <?php if($hasActiveFilters): ?>
    
    <div class="flex items-center gap-1.5 flex-wrap">
        <?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 text-[11px] font-semibold px-2 py-0.5 rounded-full border border-purple-200">
            <?php echo e(str_replace('_',' ',$key)); ?>: <?php echo e($val); ?>

        </span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <a href="<?php echo e(route('reporting.audit')); ?>"
       class="flex items-center gap-1 text-xs text-red-500 hover:text-red-700 border border-red-200 bg-red-50 rounded-lg px-2.5 py-1 transition hover:bg-red-100">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        Reset
    </a>
    <?php endif; ?>
    <button onclick="openFilterModal()"
            class="ms-auto flex items-center gap-2 bg-[#714B67] hover:bg-[#5c3d56] text-white text-xs font-semibold px-4 py-1.5 rounded-lg shadow transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        Filter
        <?php if($hasActiveFilters): ?>
        <span class="bg-white text-[#714B67] text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center">
            <?php echo e(count($filters)); ?>

        </span>
        <?php endif; ?>
    </button>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-5">

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <?php
            $kpis = [
                ['label'=>'Total Entri',      'value'=>number_format($totalCount),    'icon'=>'📋', 'color'=>'from-violet-500 to-purple-600'],
                ['label'=>'Hasil Filter',     'value'=>number_format($filteredCount), 'icon'=>'🔍', 'color'=>'from-[#714B67] to-[#4a2e47]'],
                ['label'=>'Dokumen Unik',     'value'=>\App\Models\AuditLog::query()->distinct()->count('document_id'), 'icon'=>'📄', 'color'=>'from-blue-500 to-indigo-600'],
                ['label'=>'Jenis Aksi',       'value'=>count($distinctActions),       'icon'=>'⚡', 'color'=>'from-orange-400 to-amber-500'],
            ];
        ?>
        <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="relative overflow-hidden bg-white rounded-2xl shadow-sm border border-gray-100 px-5 py-4">
            <div class="absolute -right-3 -top-3 w-16 h-16 bg-gradient-to-br <?php echo e($kpi['color']); ?> opacity-10 rounded-full"></div>
            <p class="text-[11px] text-gray-400 uppercase tracking-wider font-semibold mb-1"><?php echo e($kpi['label']); ?></p>
            <div class="flex items-end gap-2">
                <p class="text-2xl font-black text-gray-800"><?php echo e($kpi['value']); ?></p>
                <span class="text-xl mb-0.5"><?php echo e($kpi['icon']); ?></span>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
            <div>
                <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 bg-[#714B67] rounded-md flex items-center justify-center text-white text-xs">📋</span>
                    Audit Trail
                    <span class="text-xs font-normal text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">F-21 · SR-16 · SR-19</span>
                </h3>
                <p class="text-xs text-gray-400 mt-0.5 ml-8">Rekam jejak semua aksi, actor, dan timestamp</p>
            </div>
            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                <?php echo e($logs->total()); ?> entri
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="text-left px-4 py-3 text-gray-400 font-semibold uppercase tracking-wide text-[10px]">Waktu</th>
                        <th class="text-left px-4 py-3 text-gray-400 font-semibold uppercase tracking-wide text-[10px]">Dokumen</th>
                        <th class="text-left px-4 py-3 text-gray-400 font-semibold uppercase tracking-wide text-[10px]">Tipe</th>
                        <th class="text-left px-4 py-3 text-gray-400 font-semibold uppercase tracking-wide text-[10px]">Actor</th>
                        <th class="text-left px-4 py-3 text-gray-400 font-semibold uppercase tracking-wide text-[10px]">Aksi</th>
                        <th class="text-left px-4 py-3 text-gray-400 font-semibold uppercase tracking-wide text-[10px]">Keterangan</th>
                        <th class="text-center px-4 py-3 text-gray-400 font-semibold uppercase tracking-wide text-[10px]"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $ac     = $actionColors[$log->action] ?? ['bg'=>'bg-gray-100','text'=>'text-gray-600','dot'=>'bg-gray-400'];
                        $typeIc = $typeIcons[$log->document_type] ?? '📁';
                    ?>
                    <tr class="hover:bg-purple-50/30 transition-colors duration-150 group">
                        
                        <td class="px-4 py-3 whitespace-nowrap">
                            <p class="font-mono text-gray-700 text-[11px]"><?php echo e(\Carbon\Carbon::parse($log->acted_at)->format('d/m/Y')); ?></p>
                            <p class="font-mono text-gray-400 text-[10px]"><?php echo e(\Carbon\Carbon::parse($log->acted_at)->format('H:i:s')); ?></p>
                        </td>
                        
                        <td class="px-4 py-3">
                            <a href="<?php echo e(route('reporting.audit.show', [$log->document_type, $log->document_id])); ?>"
                               class="font-mono font-bold text-[#714B67] hover:text-[#4a2e47] text-xs group-hover:underline">
                                <?php echo e($log->document_id); ?>

                            </a>
                        </td>
                        
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-[10px] font-medium px-2 py-0.5 rounded-full">
                                <?php echo e($typeIc); ?> <?php echo e(str_replace('_',' ',$log->document_type)); ?>

                            </span>
                        </td>
                        
                        <td class="px-4 py-3">
                            <p class="font-mono text-gray-700 text-[11px]"><?php echo e($log->actor_user); ?></p>
                            <?php if($log->actor_employee): ?>
                            <p class="text-[10px] text-gray-400 font-mono"><?php echo e($log->actor_employee); ?></p>
                            <?php endif; ?>
                        </td>
                        
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 <?php echo e($ac['bg']); ?> <?php echo e($ac['text']); ?> text-[10px] font-bold px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($ac['dot']); ?> inline-block"></span>
                                <?php echo e($log->action); ?>

                            </span>
                        </td>
                        
                        <td class="px-4 py-3 max-w-xs">
                            <p class="text-gray-500 text-[11px] leading-relaxed line-clamp-2" title="<?php echo e($log->description); ?>">
                                <?php echo e($log->description ?: '—'); ?>

                            </p>
                        </td>
                        
                        <td class="px-4 py-3 text-center">
                            <a href="<?php echo e(route('reporting.audit.show', [$log->document_type, $log->document_id])); ?>"
                               class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 hover:bg-[#714B67] hover:text-white text-gray-500 transition-colors duration-200 group/btn"
                               title="Lihat detail">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="py-20 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center text-3xl">🔍</div>
                                <p class="text-gray-500 font-semibold text-sm">Tidak ada data ditemukan</p>
                                <p class="text-gray-400 text-xs">Coba ubah filter pencarian Anda</p>
                                <?php if($hasActiveFilters): ?>
                                <a href="<?php echo e(route('reporting.audit')); ?>"
                                   class="mt-1 text-xs text-[#714B67] font-semibold hover:underline">
                                    ← Reset semua filter
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <?php if($logs->hasPages()): ?>
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <span class="text-xs text-gray-400">
                Menampilkan <span class="font-semibold text-gray-600"><?php echo e($logs->firstItem()); ?>–<?php echo e($logs->lastItem()); ?></span>
                dari <span class="font-semibold text-gray-600"><?php echo e($logs->total()); ?></span> entri
            </span>
            <div class="flex items-center gap-1">
                <?php if($logs->onFirstPage()): ?>
                    <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 text-xs cursor-not-allowed">‹</span>
                <?php else: ?>
                    <a href="<?php echo e($logs->previousPageUrl()); ?>"
                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 text-xs hover:bg-gray-100 hover:border-gray-300 transition">‹</a>
                <?php endif; ?>
                <?php $__currentLoopData = $logs->getUrlRange(max(1,$logs->currentPage()-1), min($logs->lastPage(),$logs->currentPage()+1)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($url); ?>"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition
                          <?php echo e($page == $logs->currentPage()
                             ? 'bg-[#714B67] text-white border border-[#714B67]'
                             : 'border border-gray-200 text-gray-600 hover:bg-gray-100'); ?>">
                    <?php echo e($page); ?>

                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($logs->hasMorePages()): ?>
                    <a href="<?php echo e($logs->nextPageUrl()); ?>"
                       class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 text-xs hover:bg-gray-100 hover:border-gray-300 transition">›</a>
                <?php else: ?>
                    <span class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-300 text-xs cursor-not-allowed">›</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    
    <div class="flex items-center gap-3 text-[10px] text-gray-300 pb-2">
        <span>S-18</span><span>·</span><span>F-21</span><span>·</span>
        <span>SR-16 idempotent</span><span>·</span><span>SR-19 traceability</span><span>·</span>
        <span>T-35 related docs</span><span>·</span><span>T-36 re-validate block</span>
    </div>
</div>



<div id="filterModal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     onclick="closeFilterModalOutside(event)">

    
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

    
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-modal">

        
        <div class="bg-gradient-to-r from-[#714B67] to-[#4a2e47] px-6 py-5 flex items-center justify-between">
            <div>
                <h2 class="text-white font-bold text-base flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Filter Audit Log
                </h2>
                <p class="text-white/60 text-xs mt-0.5">Sempitkan hasil berdasarkan kriteria berikut</p>
            </div>
            <button onclick="closeFilterModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/20 hover:bg-white/30 text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        
        <form method="GET" action="<?php echo e(route('reporting.audit')); ?>" class="px-6 py-5 space-y-4">

            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        🔖 Nomor Dokumen
                    </label>
                    <input type="text" name="document_ref"
                           value="<?php echo e($filters['document_ref'] ?? ''); ?>"
                           placeholder="Contoh: S00007, PO00007"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#714B67]/30 focus:border-[#714B67] bg-gray-50 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        📁 Tipe Dokumen
                    </label>
                    <select name="document_type"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#714B67]/30 focus:border-[#714B67] bg-gray-50 transition appearance-none">
                        <option value="">Semua Tipe</option>
                        <?php $__currentLoopData = $distinctTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($dt); ?>" <?php echo e(($filters['document_type'] ?? '') === $dt ? 'selected' : ''); ?>>
                                <?php echo e($typeIcons[$dt] ?? '📁'); ?> <?php echo e(str_replace('_',' ',ucfirst($dt))); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        👤 Actor / Employee ID
                    </label>
                    <input type="text" name="actor"
                           value="<?php echo e($filters['actor'] ?? ''); ?>"
                           placeholder="Contoh: sales_user, E01"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#714B67]/30 focus:border-[#714B67] bg-gray-50 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        ⚡ Jenis Aksi
                    </label>
                    <select name="action"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#714B67]/30 focus:border-[#714B67] bg-gray-50 transition appearance-none">
                        <option value="">Semua Aksi</option>
                        <?php $__currentLoopData = $distinctActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($act); ?>" <?php echo e(($filters['action'] ?? '') === $act ? 'selected' : ''); ?>>
                                <?php echo e($act); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    📅 Rentang Waktu
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <div class="relative">
                        <input type="date" name="date_from"
                               value="<?php echo e($filters['date_from'] ?? ''); ?>"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#714B67]/30 focus:border-[#714B67] bg-gray-50 transition">
                        <span class="absolute left-3 top-2.5 text-gray-400 text-[10px] -mt-5 pointer-events-none font-medium">Dari</span>
                    </div>
                    <div class="relative">
                        <input type="date" name="date_to"
                               value="<?php echo e($filters['date_to'] ?? ''); ?>"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#714B67]/30 focus:border-[#714B67] bg-gray-50 transition">
                        <span class="absolute left-3 top-2.5 text-gray-400 text-[10px] -mt-5 pointer-events-none font-medium">Sampai</span>
                    </div>
                </div>
            </div>

            
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wide">Filter Cepat</label>
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" onclick="setDocType('purchase_order')"
                            class="text-[11px] px-3 py-1 rounded-full border border-gray-200 text-gray-600 hover:border-[#714B67] hover:text-[#714B67] hover:bg-purple-50 transition">
                        📦 Purchase Order
                    </button>
                    <button type="button" onclick="setDocType('sales_order')"
                            class="text-[11px] px-3 py-1 rounded-full border border-gray-200 text-gray-600 hover:border-[#714B67] hover:text-[#714B67] hover:bg-purple-50 transition">
                        🛒 Sales Order
                    </button>
                    <button type="button" onclick="setDocType('access_control')"
                            class="text-[11px] px-3 py-1 rounded-full border border-gray-200 text-gray-600 hover:border-[#714B67] hover:text-[#714B67] hover:bg-purple-50 transition">
                        🔐 Access Control
                    </button>
                    <button type="button" onclick="setAction('access_denied')"
                            class="text-[11px] px-3 py-1 rounded-full border border-red-200 text-red-500 hover:bg-red-50 transition">
                        ⛔ Access Denied
                    </button>
                    <button type="button" onclick="setAction('sign_off_rejected')"
                            class="text-[11px] px-3 py-1 rounded-full border border-red-200 text-red-500 hover:bg-red-50 transition">
                        🚫 Sign-off Rejected
                    </button>
                </div>
            </div>

            
            <div class="border-t border-gray-100"></div>

            
            <div class="flex items-center justify-between gap-3">
                <a href="<?php echo e(route('reporting.audit')); ?>"
                   class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700 font-medium transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset Filter
                </a>
                <div class="flex gap-2">
                    <button type="button" onclick="closeFilterModal()"
                            class="px-4 py-2 text-xs font-semibold text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 text-xs font-bold text-white bg-gradient-to-r from-[#714B67] to-[#4a2e47] rounded-xl hover:opacity-90 transition shadow-sm flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.animate-modal {
    animation: modalIn 0.2s ease-out both;
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(0.95) translateY(-8px); }
    to   { opacity: 1; transform: scale(1)    translateY(0);    }
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<script>
function openFilterModal() {
    const modal = document.getElementById('filterModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeFilterModal() {
    const modal = document.getElementById('filterModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

function closeFilterModalOutside(e) {
    if (e.target === document.getElementById('filterModal')) {
        closeFilterModal();
    }
}

// Keyboard ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeFilterModal();
});

// Quick preset helpers
function setDocType(val) {
    document.querySelector('[name="document_type"]').value = val;
}
function setAction(val) {
    document.querySelector('[name="action"]').value = val;
}

// Auto-open jika ada filter aktif (supaya user tau filter sedang berjalan)
// Tidak auto-open agar UX clean — filter badge di header sudah cukup
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\arunika-fmcg-erp-system\resources\views/reporting/audit.blade.php ENDPATH**/ ?>