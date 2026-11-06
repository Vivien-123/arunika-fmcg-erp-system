
<?php $__env->startSection('title','Buat Customer Invoice'); ?>
<?php $currentApp = 'Accounting'; $sidebarMenu = [
    ['label'=>'Customer Invoices','url'=>route('accounting.invoices.index'),'icon'=>'🧾'],
]; ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto px-2">
    <div class="mb-5">
        <p class="text-xs text-gray-400 mb-1"><a href="<?php echo e(route('accounting.invoices.index')); ?>" class="hover:underline">Customer Invoices</a> › Buat Baru</p>
        <h1 class="text-xl font-bold text-gray-800">Buat Customer Invoice</h1>
        <p class="text-xs text-gray-400 mt-0.5">F-09 · SR-02: Invoice hanya untuk qty yang sudah Done di Delivery</p>
    </div>

    <?php if($errors->any()): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-2.5 rounded-lg mb-4">❌ <?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('accounting.invoices.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
            <h3 class="font-semibold text-gray-700 text-sm mb-3">Informasi Invoice</h3>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-gray-500 font-medium">Partner ID *</label>
                    <input name="partner_id" value="<?php echo e(old('partner_id','C01')); ?>" required class="w-full mt-1 text-xs border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                </div>
                <div>
                    <label class="text-xs text-gray-500 font-medium">Partner Name *</label>
                    <input name="partner_name" value="<?php echo e(old('partner_name','PT Toko Makmur Sejahtera')); ?>" required class="w-full mt-1 text-xs border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                </div>
                <div>
                    <label class="text-xs text-gray-500 font-medium">Source SO</label>
                    <input name="source_so" value="<?php echo e(old('source_so','S00008')); ?>" class="w-full mt-1 text-xs border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                </div>
                <div>
                    <label class="text-xs text-gray-500 font-medium">Payment Terms</label>
                    <select name="payment_terms" class="w-full mt-1 text-xs border border-gray-200 rounded-lg px-3 py-2">
                        <option value="NET14" <?php echo e(old('payment_terms')=='NET14'?'selected':''); ?>>NET14 (14 hari)</option>
                        <option value="NET30" <?php echo e(old('payment_terms')=='NET30'?'selected':''); ?>>NET30 (30 hari)</option>
                        <option value="CASH"  <?php echo e(old('payment_terms')=='CASH' ?'selected':''); ?>>Tunai</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
            <h3 class="font-semibold text-gray-700 text-sm mb-1">Baris Invoice</h3>
            <p class="text-[11px] text-orange-500 mb-3">⚠ SR-02: Qty invoice = Delivered Qty − Already Invoiced Qty</p>
            <div id="lines">
                <div class="grid grid-cols-6 gap-2 mb-2 line-row">
                    <input name="lines[0][product_id]"   value="P01"              placeholder="Product ID"    class="text-xs border border-gray-200 rounded-lg px-2 py-1.5" required>
                    <input name="lines[0][variant_sku]"  value="CLN-DTR-800-LMN" placeholder="Variant SKU"   class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 col-span-2" required>
                    <input name="lines[0][product_name]" value="Detergen Cair Lemon" placeholder="Nama Produk" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 col-span-2">
                    <input name="lines[0][unit]"         value="pouch"           placeholder="Unit"          class="text-xs border border-gray-200 rounded-lg px-2 py-1.5">
                </div>
                <div class="grid grid-cols-4 gap-2 mb-4">
                    <div><label class="text-[10px] text-gray-400">Unit Price (Rp)</label>
                    <input name="lines[0][unit_price]"    value="25000"  type="number" class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 mt-0.5" required></div>
                    <div><label class="text-[10px] text-gray-400">Delivered Qty</label>
                    <input name="lines[0][delivered_qty]" value="10"     type="number" class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 mt-0.5" required></div>
                    <div><label class="text-[10px] text-gray-400">Already Invoiced</label>
                    <input name="lines[0][invoiced_qty]"  value="0"      type="number" class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 mt-0.5" required></div>
                    <div><label class="text-[10px] text-gray-400">Delivery Ref</label>
                    <input name="lines[0][delivery_ref]"  value="DO-S00008" class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 mt-0.5"></div>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-[#714B67] hover:bg-[#4a2e47] text-white text-xs font-semibold px-5 py-2.5 rounded-lg shadow transition">
                Simpan sebagai Draft
            </button>
            <a href="<?php echo e(route('accounting.invoices.index')); ?>" class="text-xs text-gray-500 hover:text-gray-700 px-4 py-2.5">Batal</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\arunika-fmcg-erp-system\resources\views/accounting/invoices/create.blade.php ENDPATH**/ ?>