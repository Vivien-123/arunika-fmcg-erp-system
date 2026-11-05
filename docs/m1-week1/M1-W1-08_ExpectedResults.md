# M1-W1-08 · Expected Results — T-03, T-04, T-07 s.d. T-10
**PT Arunika Distribusi Cepat — ERP FMCG | Group 02 · Kelas 24SI2**
**Penyusun:** M1 — Nadine Valencia Cicero | **Status:** Selesai ✅

---

## Catatan Penting

- Semua kalkulasi dimulai dari **seed baseline bersih** kecuali dinyatakan "(fixture)".
- PPN dihitung per baris dengan tarif efektif 11% (12% × DPP nilai lain 11/12), pembulatan half-up.
- Formula: `PPN = floor(net_amount × 0.11 + 0.5)` → Rp250.000 × 11% = **Rp27.500**.
- Seluruh amount tersimpan sebagai integer Rupiah (no decimals).

---

## T-03 · Normal Flow — Contact & Credit Profile C01

**Feature:** F-02 · **Related:** F-10 · **Screen:** S-02

### Kondisi Awal (Seed Baseline)

| Parameter | Nilai | Sumber |
|-----------|-------|--------|
| Credit Limit C01 | Rp2.000.000 | `contacts_customers`: C01.credit_limit |
| Open AR C01 | Rp900.000 | Saldo pembukaan: piutang C01 |
| S00007 (8 × P01, Confirmed, belum invoiced) | — | `existing_documents` |
| Gross S00007 | 8 × Rp25.000 = Rp200.000 net + 11% = **Rp222.000** | Handout §4 |
| Gross S00007 crosscheck | Rp200.000 × 1.11 = Rp222.000 ✅ | — |

### Kalkulasi Expected

```
Credit Exposure C01 = open_AR + Σ(gross Confirmed SO belum ditagih)
                    = Rp900.000 + Rp222.000
                    = Rp1.122.000

Sisa kredit tersedia = Credit Limit − Exposure
                     = Rp2.000.000 − Rp1.122.000
                     = Rp878.000
```

### Expected Result

| Field | Nilai yang Diharapkan |
|-------|----------------------|
| Credit Limit | Rp2.000.000 |
| Open AR | Rp900.000 |
| Confirmed unbilled (S00007) | Rp222.000 |
| **Current Exposure** | **Rp1.122.000** |
| Available Credit | Rp878.000 |
| Mutasi stok | Tidak ada |
| Jurnal keuangan | Tidak ada |
| Status SO | Tidak berubah |

**Bukti wajib:** Screenshot form Customer C01 menampilkan exposure Rp1.122.000.

---

## T-04 · Rule/Edge Case — Credit Limit Enforcement C01

**Feature:** F-02 · **Related:** F-10 · **Screen:** S-02
**Keputusan berlaku:** DL-05 (hard block, Opsi A)

### Skenario

C01 ingin membuat SO baru: **40 × P01** (Detergen Cair Lemon @ Rp25.000).

### Kalkulasi Expected

```
SO baru:
  Net    = 40 × Rp25.000 = Rp1.000.000
  PPN    = floor(1.000.000 × 0.11 + 0.5) = Rp110.000
  Gross  = Rp1.110.000

Proyeksi exposure jika dikonfirmasi:
  Baseline exposure C01  = Rp1.122.000
  Gross SO baru          = Rp1.110.000
  Total projected        = Rp2.232.000

Apakah melebihi credit limit?
  Rp2.232.000 > Rp2.000.000 → YA (excess Rp232.000)
```

### Expected Result (berdasarkan DL-05 Opsi A — Hard Block)

| Field | Nilai yang Diharapkan |
|-------|----------------------|
| Baseline exposure sebelum | Rp1.122.000 |
| Proyeksi exposure | Rp2.232.000 |
| Credit Limit | Rp2.000.000 |
| **Hasil konfirmasi** | **DITOLAK — hard block** |
| Pesan error | `"Credit limit exceeded: projected exposure Rp2.232.000 > limit Rp2.000.000 (SR-11)"` |
| Status SO setelah penolakan | Draft / tidak berubah |
| Exposure C01 setelah penolakan | Rp1.122.000 (tidak berubah) |
| Mutasi stok | Tidak ada |
| Audit log | Tercatat: percobaan konfirmasi ditolak, actor, timestamp |

**Bukti wajib:** Screenshot pesan validasi + laporan exposure C01.

> **Jika tim memilih Opsi B (soft warning):** SO dapat dikonfirmasi dengan warning; exposure menjadi Rp2.232.000; catat sebagai deviation dari rekomendasi M1 di Decision Log DL-05.

---

## T-07 · Normal Flow — Konversi Lead L00003 ke Opportunity

**Feature:** F-04 · **Related:** F-21 · **Screen:** S-04

### Kondisi Awal (Seed Baseline)

| Parameter | Nilai | Sumber |
|-----------|-------|--------|
| Lead ID | L00003 | `existing_documents` |
| Customer | C03 — UD Berkah Niaga | — |
| Stage awal | New | — |
| Expected Revenue | Rp500.000 | — |
| Salesperson | E04 (Dini Pratiwi) | — |
| Financial state | Tidak ada | — |
| Stock state | Tidak ada | — |

### Expected Result

| Field | Sebelum Konversi | Setelah Konversi |
|-------|-----------------|-----------------|
| Stage | New | **Qualified** |
| Customer | C03 | C03 (tidak berubah) |
| Expected Revenue | Rp500.000 | Rp500.000 (tidak berubah) |
| Salesperson | E04 | E04 (tidak berubah) |
| Mutasi stok | Tidak ada | Tidak ada |
| Jurnal keuangan | Tidak ada | Tidak ada |
| AR | Rp1.500.000 | Rp1.500.000 (tidak berubah) |
| Bank | Rp20.000.000 | Rp20.000.000 (tidak berubah) |
| Related documents | — | Link ke pipeline tersedia |
| Audit log | — | `action: convert_to_opportunity; actor: sales_user` |

**Bukti wajib:** Screenshot before (stage: New) dan after (stage: Qualified) + related documents link.

---

## T-08 · Rule/Edge Case — Proteksi Relasi Customer pada Opportunity

**Feature:** F-04 · **Related:** F-21 · **Screen:** S-04
**Keputusan berlaku:** SR-19 (traceability wajib)

### Skenario

Operator (sales_user) mencoba menghapus/mengosongkan field `Customer` pada Opportunity L00003 yang sudah berstatus Qualified.

### Expected Result (berdasarkan SR-19)

| Field | Nilai yang Diharapkan |
|-------|----------------------|
| **Hasil operasi** | **DITOLAK** |
| Pesan error | `"Rule violation (SR-19): Customer relation cannot be deleted on Opportunity L00003"` |
| Customer setelah penolakan | C03 (tidak berubah) |
| Stage | Qualified (tidak berubah) |
| Audit log | `action: reject_delete_customer; document: L00003; actor: sales_user; timestamp` |
| Financial/stock | Tidak ada mutasi |

> **Dasar keputusan:** SR-19 mewajibkan setiap angka dashboard dapat ditelusuri ke dokumen → Opportunity tanpa customer tidak dapat dikonversi ke Quotation dan rantai O2C putus.

**Bukti wajib:** Screenshot penolakan + audit log + aturan relasi.

---

## T-09 · Normal Flow — Quotation 10 P01 untuk C01 (Tanpa Diskon)

**Feature:** F-05 · **Related:** F-06, F-10, F-22 · **Screen:** S-05

### Kondisi Awal (Seed Baseline)

| Parameter | Nilai | Sumber |
|-----------|-------|--------|
| Customer | C01 — PT Toko Makmur Sejahtera | — |
| Produk | P01 (CLN-DTR-800-LMN) | — |
| Sales Price P01 | Rp25.000 | `products.sales_price` |
| Qty | 10 | — |
| Diskon | 0% | — |
| Nomor SO terakhir | S00007 | `existing_documents` |
| Baseline exposure C01 | Rp1.122.000 | Hasil T-03 |

### Kalkulasi Expected

```
Line item:
  Unit Price  = Rp25.000
  Qty         = 10
  Diskon      = 0%
  Subtotal    = 10 × Rp25.000 = Rp250.000 (net)
  PPN         = floor(250.000 × 0.11 + 0.5) = Rp27.500
  Gross       = Rp250.000 + Rp27.500 = Rp277.500

Nomor dokumen:
  Sequence setelah S00007 → S00008

Exposure C01 setelah konfirmasi:
  Baseline  = Rp1.122.000
  SO baru   = Rp277.500 (gross)
  Total     = Rp1.399.500

Credit check:
  Rp1.399.500 < Rp2.000.000 (limit) → DIIZINKAN

Reservation stok:
  P01 WH-JKT: Reserved naik dari 8 → 18
  P01 WH-JKT: Available turun dari 52 → 42
  P01 On Hand: tetap 60 (tidak berubah)
```

### Expected Result

| Field | Nilai yang Diharapkan |
|-------|----------------------|
| Nomor dokumen | **S00008** |
| Status | Confirmed |
| Net/Subtotal | **Rp250.000** |
| PPN | **Rp27.500** |
| Gross/Total | **Rp277.500** |
| Exposure C01 | **Rp1.399.500** |
| P01 WH-JKT On Hand | 60 (tidak berubah) |
| P01 WH-JKT Reserved | 18 (bertambah 10) |
| P01 WH-JKT Available | 42 |
| Harga P01 | Terkunci (read-only setelah Confirmed) |
| Jurnal keuangan | Tidak ada (belum ada DO) |
| AR | Rp1.500.000 (tidak berubah — belum invoice) |
| Audit log | `action: confirmed; SO: S00008; actor: sales_user` |

**Bukti wajib:** Screenshot Quotation → SO Confirmed + subtotal + PPN + gross + exposure C01.

---

## T-10 · Rule/Edge Case — Diskon 15% Tanpa Finance Approval

**Feature:** F-05 · **Related:** F-06, F-12 · **Screen:** S-05
**Keputusan berlaku:** DL-04 (SR-08 Opsi B)

### Skenario

Sales (sales_user) mengajukan diskon **15%** pada line P01 di Quotation baru untuk C01 (10 unit), kemudian mencoba konfirmasi langsung tanpa approval Finance.

### Kalkulasi Jika Diskon 15% Diterapkan

```
Unit Price P01 = Rp25.000
Diskon         = 15% = Rp3.750
Net per unit   = Rp25.000 − Rp3.750 = Rp21.250
Subtotal 10u   = 10 × Rp21.250 = Rp212.500
PPN            = floor(212.500 × 0.11 + 0.5) = Rp23.375
Gross          = Rp212.500 + Rp23.375 = Rp235.875
```

### Expected Result — Langkah per Langkah

| Langkah | Aksi | Expected Behavior |
|---------|------|-------------------|
| 1 | Sales isi diskon 15% pada Quotation | Sistem menerima input (masih di Quotation/Draft) |
| 2 | Sales klik "Confirm" | **DIBLOKIR** — `"Discount > 10% requires Finance approval (SR-07/SR-08)"` |
| 3 | Status SO setelah langkah 2 | Berubah ke **`To Approve`** (bukan Confirmed, bukan Draft) |
| 4 | Audit log | `action: discount_approval_requested; discount: 15%; actor: sales_user` |
| 5 | Finance (finance_user) membuka approval request | Melihat detail SO: 10 P01, diskon 15%, gross Rp235.875 |
| 6 | Finance klik "Approve" | Status → **Confirmed**; diskon terkunci (read-only); audit: `approved_by: finance_user` |
| 7 | Alternatif: Finance klik "Reject" | Status kembali ke Draft; Sales dinotifikasi; diskon dapat diubah |
| 8 | Coba edit harga setelah Confirmed (langkah 6) | **DITOLAK** — `"Cannot edit confirmed SO lines (SR-12)"` |

### Expected Result Ringkasan (Jalur Happy Path setelah Approval)

| Field | Nilai |
|-------|-------|
| Diskon | 15% |
| Net setelah diskon | Rp212.500 |
| PPN | Rp23.375 |
| Gross | Rp235.875 |
| Status akhir | Confirmed |
| Harga/diskon | Terkunci permanen |
| Audit trail | 3 entri: request → approved → confirmed |

**Bukti wajib:** Screenshot (a) pesan blokir saat konfirmasi, (b) approval request di Finance dashboard, (c) SO Confirmed setelah approval, (d) edit ditolak + Decision Log DL-04.

---

## Ringkasan Expected Results

| Test | Tipe | Expected Key Result | SR Berlaku |
|------|------|--------------------|-----------:|
| T-03 | Normal | Exposure C01 = Rp**1.122.000** | SR-11 |
| T-04 | Rule | SO 40 P01 **DITOLAK** (exposure Rp2.232.000 > limit Rp2.000.000) | SR-11 |
| T-07 | Normal | L00003 stage = **Qualified**; C03 tetap; no stock/AR move | SR-19 |
| T-08 | Rule | Hapus customer L00003 **DITOLAK**; C03 tetap; audit log terisi | SR-19 |
| T-09 | Normal | S**00008**; net Rp250.000; PPN Rp27.500; gross Rp277.500; exposure Rp**1.399.500** | SR-03, SR-11, SR-12 |
| T-10 | Rule | Konfirmasi diskon 15% **DIBLOKIR** → status `To Approve` → Finance approve → Confirmed & locked | SR-07, SR-08, SR-12 |

---

*Penyusun: M1 · Referensi: Handout §4 (dataset), §7 (test cases) · Review: M4*
