# M2-W3-05 — Code Review Logika AI (Refleksi 2)

**Modul:** Purchase & Inventory  
**Anggota:** Vivien (M2 — Lead AI Developer)  
**Referensi Handout:** Refleksi 2 (§12), Handout §9 (Checkpoint)  
**Tanggal:** November 2026

---

## Ringkasan

Dokumen ini mencatat temuan dari proses code review terhadap output AI coding tool (Kiro/Antigravity)
pada modul Purchase & Inventory. Setiap temuan menunjukkan kasus di mana kode yang dihasilkan AI
**terlihat benar secara visual dan lolos kompilasi**, tetapi mengandung pelanggaran aturan bisnis
ERP yang hanya terdeteksi saat diuji secara eksplisit terhadap test scenario.

---

## Temuan 1 — FEFO Hanya Memproses Satu Lot (SR-07)

### Deskripsi
AI menghasilkan logika FEFO picking pada `InventoryService::processDelivery()` yang:
1. Mengambil lot pertama (earliest expiry) via `ProductLot::fefoLotsFor()`
2. Hanya mengurangi qty dari `$firstLot` sebanyak `min($doneQty, $firstLot->qty)`
3. **Tidak melanjutkan ke lot berikutnya** jika `doneQty > firstLot->qty`

```php
// ❌ Kode AI awal — hanya proses satu lot
$lots = ProductLot::fefoLotsFor($line->variant_sku, $delivery->source_location);
if ($lots->count() > 0) {
    $firstLot = $lots->first();
    $deductFromLot = min($doneQty, $firstLot->qty);
    $firstLot->decrement('qty', $deductFromLot);  // sisa doneQty tidak dilanjutkan
}
```

### Mengapa Terlihat Benar
- Untuk skenario seed baseline (LOT-TEA-01: 15 qty, LOT-TEA-02: 20 qty, kebutuhan ≤ 15) kode berjalan normal.
- UI menampilkan `lot_id` yang benar di delivery line.
- Tidak ada error thrown — lot dikurangi, hanya jumlahnya yang salah kalau doneQty > firstLot->qty.

### Dampak Pelanggaran
- **SR-07** dilanggar: jika kirim 25 unit P03 dari WH-JKT, LOT-TEA-01 (15) dikuras tapi LOT-TEA-02
  tidak dikurangi sama sekali — physical stock vs database tidak sinkron.
- Stok lot bisa menjadi **inflated** (lot masih tertera punya qty padahal sudah keluar fisik).

### Perbaikan yang Diterapkan
```php
// ✅ Kode setelah fix — iterasi semua lot sesuai FEFO
$remaining = $doneQty;
foreach ($lots as $lot) {
    if ($remaining <= 0) break;
    $take = min($lot->qty, $remaining);
    $lotsToDeduct[] = ['lot' => $lot, 'qty' => $take];
    $remaining -= $take;
}
// Apply semua deductions
foreach ($lotsToDeduct as $entry) {
    $entry['lot']->decrement('qty', $entry['qty']);
}
```

**File:** `app/Services/InventoryService.php` — method `processDelivery()` baris FEFO picking.  
**Test yang membuktikan fix:** T-06, T-11 (regression 130/130 PASS).

---

## Temuan 2 — StockMove Idempotency Key Collision (SR-16)

### Deskripsi
AI menggunakan key `md5("sm_delivery|{$deliveryId}|{$line->variant_sku}")` untuk StockMove.
Jika satu Delivery Order memiliki **dua baris dengan SKU yang sama** (misalnya backorder partial),
key identik akan menyebabkan `UNIQUE constraint violation` pada insert kedua — tanpa pesan error
yang bermakna ke user.

```php
// ❌ Kode AI awal — SKU-only key bisa collision
'idempotency_key' => md5("sm_delivery|{$deliveryId}|{$line->variant_sku}"),
```

### Mengapa Terlihat Benar
- Seed baseline hanya punya satu baris per DO, sehingga collision tidak terjadi dalam testing normal.
- UI delivery lines tampak normal karena insert pertama berhasil.

### Dampak Pelanggaran
- **SR-16** dilanggar: StockMove baris kedua gagal silently — perpindahan stok tidak tercatat
  di ledger padahal stok sudah dikurangi.
- Menyebabkan discrepancy antara `opening_stock` dan `stock_moves` audit trail.

### Perbaikan yang Diterapkan
```php
// ✅ Kode setelah fix — sertakan line->id agar key selalu unik
'idempotency_key' => md5("sm_delivery|{$deliveryId}|{$line->id}|{$line->variant_sku}"),
```

**File:** `app/Services/InventoryService.php` — method `processDelivery()` StockMove insert.  
**Test yang membuktikan fix:** T-11, T-25 (regression 130/130 PASS).

---

## Temuan 3 — Reserved Tidak Dikurangi dari HUB-BDG (SR-22)

### Deskripsi
Pada `transferStock()`, AI menghitung `available` untuk HUB-BDG sebagai `hub_bdg_on_hand` penuh
tanpa dikurangi `reserved`. Field `reserved` di schema hanya ada di level company, bukan per-lokasi.

```php
// ⚠️ Kode AI — HUB-BDG tidak dikurangi reserved
$available = $fromLocation === 'WH-JKT'
    ? ($stock->wh_jkt_on_hand - $stock->reserved)   // benar untuk WH-JKT
    : $stock->hub_bdg_on_hand;                        // ⚠️ tidak dikurangi reserved
```

### Mengapa Terlihat Benar
- Semua reservation di seed baseline berada di WH-JKT (S00007 reserve 8 P01 di WH-JKT).
- HUB-BDG belum pernah punya reserved stock di test suite saat ini.

### Dampak Potensial
- Jika reserved berasal dari HUB-BDG, transfer dari HUB-BDG bisa melampaui available quantity,
  melanggar **SR-22** (`reserved suatu SKU tidak boleh melebihi On Hand lokasi`).

### Keputusan
Dibiarkan sebagai **documented limitation** karena:
1. Schema `reserved` adalah company-level field (bukan per-lokasi) — ini keputusan desain di M2-W1.
2. Semua reservation dalam skenario handout terjadi di WH-JKT.
3. Fix memerlukan penambahan kolom `hub_bdg_reserved` yang berdampak ke migration dan seed.

**Decision Log:** DL-07 (terdokumentasi sebagai trade-off desain, bukan bug).  
**Mitigasi:** Semua SO reservation diset `source_location = 'WH-JKT'` (default), sehingga
kondisi HUB-BDG-reserved tidak muncul dalam skenario normal.

---

## Temuan 4 — Tax Rounding pada PO Line (SR-21 / Handout §4)

### Deskripsi
`PurchaseService::createRfq()` menghitung PPN dengan `round($net * 0.11)` (PHP `round()`).
Handout §4 menetapkan rounding **half-up per baris, rupiah terdekat**. PHP `round()` default
adalah half-away-from-zero, bukan half-up — keduanya sama untuk bilangan positif, tetapi
berbeda untuk nilai tepat 0.5 sen.

### Status
Tidak material untuk dataset ini — semua harga dalam dataset adalah bilangan bulat Rupiah
dan menghasilkan PPN bulat juga. **Tidak ada fix diperlukan**, dicatat sebagai informasi.

---

## Ringkasan Temuan

| # | Temuan | Severitas | Status |
|---|--------|-----------|--------|
| 1 | FEFO hanya proses 1 lot (SR-07) | 🔴 Tinggi | ✅ Fixed |
| 2 | StockMove idempotency key collision (SR-16) | 🔴 Tinggi | ✅ Fixed |
| 3 | HUB-BDG available tidak kurangi reserved (SR-22) | 🟡 Medium | Documented limitation (DL-07) |
| 4 | Tax rounding method (Handout §4) | 🟢 Rendah | No fix needed (tidak material) |

**Semua test pasca-fix:** `php artisan m2:regression` → **36/36 PASS, 130/130 assertions**.

---

## Cara Deteksi

Semua temuan di atas tidak terdeteksi oleh:
- ✗ Kompilasi PHP (`php -l`) — kode valid secara sintaks
- ✗ Review visual antarmuka — tampilan UI normal
- ✗ Test case single-lot scenario — LOT-TEA-01 qty cukup untuk skenario seed

Terdeteksi oleh:
- ✅ Analisis manual logika FEFO multi-lot (`allocateFefo()` vs `processDelivery()`)  
- ✅ Tracing idempotency key generation dengan skenario 2 baris SKU sama
- ✅ Cross-check SR-22 wording: *"reserved suatu SKU tidak boleh melebihi On Hand lokasi"*

---

## Pelajaran

> *"AI coding tool menghasilkan kode yang benar untuk skenario yang ada di dalam konteksnya
> (seed data, test case yang diberikan), tetapi tidak selalu mempertimbangkan edge case
> yang belum pernah didemonstrasikan secara eksplisit. Code review manual terhadap
> aturan bisnis — bukan hanya tampilan UI — adalah langkah wajib sebelum demo."*

**Referensi:** Handout §13 (Refleksi 2), SR-07, SR-16, SR-22.
