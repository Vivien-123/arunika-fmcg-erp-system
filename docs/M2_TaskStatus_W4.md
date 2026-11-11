# M2 — Task Status Update (Minggu 4)

**Anggota:** Vivien  
**Modul:** Purchase / Inventory  
**Progress real:** ~95%  
**Terakhir diperbarui:** November 2026

> File ini adalah pembaruan status tracker yang sebenarnya.
> Kolom "Belum" di spreadsheet tidak mencerminkan kondisi repo — lihat bukti commit dan test di bawah.

---

## Status per Task

### Minggu 1

| ID | Tugas | Status | Bukti |
|----|-------|--------|-------|
| M2-W1-01 | Model data dan seed | ✅ Selesai | `RunFmcgMasterSeed.php`, checksum 34/34 |
| M2-W1-02 | Arsitektur dan stack teknologi | ✅ Selesai | `README.md` §10–12 |
| M2-W1-03 | Skeleton repository dan orkestrasi AI | ✅ Selesai | `docs/AI_WORKFLOW_AND_SKELETON.md` |
| M2-W1-04 | Seed baseline dan checksum | ✅ Selesai | `php artisan fmcg:verify-checksum` → 34/34 |
| M2-W1-05 | Review draft wireframe terkait stok | ✅ Selesai | `docs/WIREFRAME_STOCK_MAPPING_REVIEW.md` |

### Minggu 2

| ID | Tugas | Status | Bukti |
|----|-------|--------|-------|
| M2-W2-01 | Katalog produk dan variant SKU | ✅ Selesai | T-05 PASS — 16 SKU unique |
| M2-W2-02 | Lot FEFO P03 | ✅ Selesai | T-06 PASS — LOT-TEA-01/02/03, FEFO engine |
| M2-W2-03 | Reservation dan Available Stock | ✅ Selesai | T-09, T-13, T-14 PASS — SR-22 live |
| M2-W2-04 | Delivery Order dan backorder | ✅ Selesai | T-11, T-12 PASS — `DeliveryController` live |
| M2-W2-05 | RFQ → Purchase Order | ✅ Selesai | T-15, T-16 PASS — `PurchaseOrderController` live |
| M2-W2-06 | Receipt accepted/rejected | ✅ Selesai | T-17, T-18 PASS — `ReceiptController` live |
| M2-W2-07 | Internal Transfer WH-JKT ↔ HUB-BDG | ✅ Selesai | T-13, T-14 PASS — `transferStock()` live |
| M2-W2-08 | Checkpoint 10 tes normal dan commit | ✅ Selesai | `php artisan m2:w2-checkpoint` → 90/90 |

### Minggu 3

| ID | Tugas | Status | Bukti |
|----|-------|--------|-------|
| M2-W3-01 | Integrasi stok dengan akuntansi | ✅ Selesai | DO→Invoice tombol, GR→Bill tombol, `invoiced_qty`/`billed_qty` update |
| M2-W3-02 | Idempotensi posting stok | ✅ Selesai | T-26, T-36 PASS — SR-16 guard |
| M2-W3-03 | Return stock move | ✅ Selesai | T-31, T-32 PASS — COGS reversal + Credit Note |
| M2-W3-04 | Integrasi sign-off Employee | ✅ Selesai | T-17, T-18, T-29, T-30 PASS — SR-17/18 |
| M2-W3-05 | Code review logika AI | ✅ Selesai | `docs/M2-W3-05_CodeReviewLogikaAI.md` — 2 bug ditemukan dan diperbaiki |

### Minggu 4

| ID | Tugas | Status | Bukti |
|----|-------|--------|-------|
| M2-W4-01 | Run final T-05, T-06, T-11–T-18 | ✅ Selesai | `php artisan m2:regression` → T-01..T-36, 130/130 |
| M2-W4-02 | Perbaikan defect stok dan pembelian | ✅ Selesai | Bug FEFO multi-lot & StockMove key diperbaiki |
| M2-W4-03 | Diagram arsitektur dan data final | ✅ Selesai | README §11 — Mermaid flowchart updated |
| M2-W4-04 | Siap defense modul dan lintas modul | 🔄 Berjalan | Perlu latihan tanya jawab, semua logika siap |

---

## Commands untuk Verifikasi Cepat

```bash
# Reset ke baseline
php artisan db:master-seed

# Checksum baseline (34/34)
php artisan fmcg:verify-checksum

# Full regression T-01 → T-36
php artisan m2:regression

# Week 2 checkpoint saja
php artisan m2:w2-checkpoint

# Week 3 integration
php artisan m2:w3-checkpoint
```

---

## Catatan Bug yang Ditemukan dan Diperbaiki (M2-W4-02)

| Bug | File | Fix |
|-----|------|-----|
| FEFO hanya proses 1 lot (SR-07) | `InventoryService.php` | Iterasi semua lot dalam urutan FEFO |
| StockMove key collision multi-line (SR-16) | `InventoryService.php` | Tambah `line->id` ke idempotency key |

Detail lengkap: `docs/M2-W3-05_CodeReviewLogikaAI.md`
