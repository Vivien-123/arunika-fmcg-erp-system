# M1-W1-11 · Review Seed Baseline & Checksum Verification
**PT Arunika Distribusi Cepat — ERP FMCG | Group 02 · Kelas 24SI2**
**Penyusun:** M1 — Nadine Valencia Cicero | **Verifikasi teknis:** M2, M4 | **Status:** Selesai ✅

---

## Tujuan

Memverifikasi bahwa seed baseline yang diimplementasikan oleh M2 sesuai dengan angka kontrol di Handout §4, serta mendokumentasikan keputusan format SKU kanonis.

---

## A. Angka Kontrol Wajib (Handout §4)

| Metrik | Nilai Expected | Sumber Handout |
|--------|---------------|----------------|
| Total SKU Produk | 16 variant SKU unik | §4 — products table |
| On Hand Perusahaan (total) | **880 unit** | §4 — opening_stock TOTAL |
| On Hand WH-JKT | **665 unit** | §4 — opening_stock WH-JKT column |
| On Hand HUB-BDG | **215 unit** | §4 — opening_stock HUB-BDG column |
| Nilai Persediaan Standar (total) | **Rp23.095.000** | §4 — opening_stock standard_value |
| Nilai WH-JKT | **Rp17.315.000** | §4 — dihitung dari 16 SKU |
| Nilai HUB-BDG | **Rp5.780.000** | §4 — dihitung dari 16 SKU |
| Open AR (Piutang Usaha) | **Rp1.500.000** | §4 — C01: Rp900.000 + C02: Rp600.000 |
| Open AP (Utang Usaha) | **Rp700.000** | §4 — V04: Rp700.000 |
| Bank | **Rp20.000.000** | §4 — saldo pembukaan |
| Jurnal Opening Debit | **Rp44.595.000** | Dr Bank + Dr AR + Dr Persediaan |
| Jurnal Opening Credit | **Rp44.595.000** | Cr AP + Cr Modal |
| Reserved (dari S00007) | **8 unit P01 di WH-JKT** | §4 — S00007 seed |
| Total Lot P03 (BEV-TEA-C24-LCH) | **50 unit** (sudah termasuk dalam opening_stock, jangan double count) | §4 — product_lots |

---

## B. Verifikasi Jurnal Opening

```
Dr Bank (1000)          Rp20.000.000
Dr AR (1100)            Rp 1.500.000  ← C01: 900k + C02: 600k
Dr Persediaan (1300)    Rp23.095.000
─────────────────────────────────────
Total Debit             Rp44.595.000

Cr AP (2000)            Rp   700.000  ← V04: 700k
Cr Modal (3000)         Rp43.895.000
─────────────────────────────────────
Total Credit            Rp44.595.000

Status: BALANCED ✅
```

---

## C. Verifikasi Opening Stock per SKU

| ID | Variant SKU | WH-JKT | HUB-BDG | Total | Cost | Value |
|----|------------|--------|---------|-------|------|-------|
| P01 | CLN-DTR-800-LMN | 60 | 20 | 80 | 17.000 | 1.360.000 |
| P02 | FOD-MIE-B12-ORI | 40 | 10 | 50 | 37.000 | 1.850.000 |
| P03 | BEV-TEA-C24-LCH | 35 | 15 | 50 | 51.000 | 2.550.000 |
| P04 | CLN-DIS-750-JER | 50 | 20 | 70 | 13.000 | 910.000 |
| P05 | PRS-SHM-B24-MNT | 30 | 10 | 40 | 32.000 | 1.280.000 |
| P06 | PRS-SPR-100-ROS | 80 | 20 | 100 | 9.000 | 900.000 |
| P07 | SNK-BIS-B10-CHO | 25 | 10 | 35 | 44.000 | 1.540.000 |
| P08 | SNK-WFR-B12-VNL | 30 | 10 | 40 | 36.000 | 1.440.000 |
| P09 | BEV-COF-B20-ORI | 20 | 10 | 30 | 48.000 | 1.440.000 |
| P10 | CLN-FLR-900-PIN | 45 | 15 | 60 | 18.000 | 1.080.000 |
| P11 | CLN-DTR-800-FLR | 55 | 15 | 70 | 17.000 | 1.190.000 |
| P12 | BEV-TEA-C24-JSM | 40 | 10 | 50 | 51.000 | 2.550.000 |
| P13 | FOD-MIE-B12-SPC | 35 | 10 | 45 | 37.000 | 1.665.000 |
| P14 | PRS-TOO-120-HRB | 65 | 20 | 85 | 12.000 | 1.020.000 |
| P15 | SNK-CRK-B08-BBQ | 30 | 10 | 40 | 30.000 | 1.200.000 |
| P16 | PRS-SHM-B24-ALV | 25 | 10 | 35 | 32.000 | 1.120.000 |
| **TOTAL** | — | **665** | **215** | **880** | — | **23.095.000** |

---

## D. Verifikasi Lot P03 (BEV-TEA-C24-LCH)

| Lot ID | Lokasi | Qty | Expiry Date | FEFO Rank |
|--------|--------|-----|------------|-----------|
| LOT-TEA-01 | WH-JKT | 15 | 2026-11-15 | 1 (earliest) |
| LOT-TEA-02 | WH-JKT | 20 | 2027-02-28 | 2 |
| LOT-TEA-03 | HUB-BDG | 15 | 2027-03-15 | 3 |
| **TOTAL** | — | **50** | — | — |

> ✅ Total lot P03 = 50 = opening_stock P03 company_total = 50. **Tidak ada double count.**
> Tanggal referensi FEFO: 20-10-2026. LOT-TEA-01 sudah kedaluwarsa pada tanggal uji? **Tidak** (exp: 2026-11-15 > 2026-10-20). Lot yang expired sebelum 20-10-2026 tidak ada.

---

## E. Keputusan Format SKU Kanonis

**Masalah:** Handout §4 tabel `products` menggunakan format SKU tanpa tanda penghubung antar segmen (contoh: `CLNDTR-800-LMN`), sedangkan tabel `vendor_pricelist` menggunakan format dengan tanda penghubung lengkap (contoh: `CLN-DTR-800-LMN`).

**Keputusan M1 (koordinasi dengan M2):**
- **Format kanonis yang diadopsi:** `CLN-DTR-800-LMN` (dengan tanda penghubung lengkap, sesuai `vendor_pricelist`)
- **Alasan:** Format ini lebih konsisten, mudah dibaca, dan sesuai dengan kolom `variant_sku` di `vendor_pricelist` yang merupakan referensi lintas-tabel.
- **Implementasi:** Semua 16 SKU di tabel `products` dan `opening_stock` menggunakan format ini.
- **Catatan:** Perbedaan format di halaman 7 handout (tabel products vs vendor_pricelist) adalah inkonsistensi penulisan di dokumen handout, bukan perbedaan data.

---

## F. Existing Documents Seed Baseline

| Document ID | Type | Partner | Status | Details | Stock State | Financial State |
|-------------|------|---------|--------|---------|-------------|----------------|
| L00003 | Lead | C03 | New | Expected Revenue Rp500.000 | — | — |
| S00007 | Sales Order | C01 | Confirmed | 8 × P01; net Rp200.000; gross Rp222.000 | Reserved 8 P01 di WH-JKT; belum delivered | Belum invoiced; exposure Rp222.000 |
| PO00007 | Purchase Order | V04 | Confirmed | 20 × P05; net Rp640.000; gross est. Rp710.400 | 0 received / 0 accepted | 0 billed / 0 paid |

---

## G. Checklist Verifikasi Seed

| Check | Expected | Status |
|-------|----------|--------|
| Total SKU | 16 | ✅ |
| Total On Hand | 880 unit | ✅ |
| WH-JKT On Hand | 665 unit | ✅ |
| HUB-BDG On Hand | 215 unit | ✅ |
| Nilai Persediaan | Rp23.095.000 | ✅ |
| Nilai WH-JKT | Rp17.315.000 | ✅ |
| Nilai HUB-BDG | Rp5.780.000 | ✅ |
| Open AR | Rp1.500.000 | ✅ |
| Open AP | Rp700.000 | ✅ |
| Bank | Rp20.000.000 | ✅ |
| Jurnal Opening Balanced | Rp44.595.000 | ✅ |
| Reserved S00007 | 8 P01 di WH-JKT | ✅ |
| P01 WH Available | 60 − 8 = 52 | ✅ |
| Lot P03 total | 50 (= opening_stock, no double) | ✅ |
| SKU kanonis dengan tanda penghubung | 16 SKU | ✅ |
| `php artisan db:master-seed` → 34/34 assertions | PASS | ✅ (laporan M2) |
| `node verify_checksum.js` → 100% | PASS | ✅ (laporan M2) |

---

## H. Cara Reset ke Seed Baseline

```bash
# Reset basis data ke kondisi awal (untuk setiap T-xx independen):
php artisan db:master-seed

# Verifikasi checksum:
php artisan fmcg:verify-checksum

# Alternatif (Node.js layer):
node verify_checksum.js
```

> **Aturan fixture (Handout §4):** Setiap T-xx mulai dari seed bersih kecuali tes itu menyatakan "Setelah T-xx (fixture)". Hasil tes lain tidak diakumulasikan secara diam-diam.

---

*Penyusun: M1 · Verifikasi teknis: M2 (seed implementor), M4 (QA) · Referensi: Handout §4, §9*
