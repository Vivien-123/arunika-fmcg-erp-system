# M1-W1-05 · Klasifikasi SR-01 s.d. SR-22
**PT Arunika Distribusi Cepat — ERP FMCG | Group 02 · Kelas 24SI2**
**Penyusun:** M1 — Nadine Valencia Cicero | **Status:** Selesai ✅

---

## Keterangan Kolom

| Kolom | Isi |
|-------|-----|
| **Status** | `Definitif` = dapat diterapkan langsung · `Konflik` = bertentangan dengan SR lain · `Ambigu` = tidak bisa diterapkan literal |
| **Konflik dengan** | SR lain yang tumpang tindih atau bertentangan |
| **Prioritas** | `Must` = wajib selesai · `Should` = setelah Must · `Optional` = tidak menggantikan Must |
| **Decision Log** | ID entri di dokumen Decision Log (M1-W1-07) |

---

## Tabel Klasifikasi

| ID | Stakeholder | Aturan Inti | Prioritas | Status | Konflik dengan | Decision Log | Catatan Implementasi |
|----|------------|-------------|-----------|--------|----------------|-------------|---------------------|
| **SR-01** | Hendra Widodo (Owner) | Invoice boleh diterbitkan saat SO Confirmed, sebelum pengiriman | Must | ⚠️ **Konflik** | SR-02 | DL-01 | Ditolak literal. Ikuti keputusan DL-01. |
| **SR-02** | Made Wirawan (Finance) | Invoice hanya untuk qty Done di DO minus qty sudah ditagih | Must | ⚠️ **Konflik** | SR-01 | DL-01 | **DIPILIH.** Invoicing Policy = Delivered Quantities. |
| **SR-03** | Dini Pratiwi (Sales) | SO dapat dikonfirmasi walau Available tidak cukup (janji kirim ≤ 3 hari) | Must | ⚠️ **Konflik** | SR-04 | DL-02 | Konfirmasi SO diizinkan; reservation dibatasi hingga Available. |
| **SR-04** | Sari Prameswari (Gudang) | Reservation & DO tidak boleh membuat Available/On Hand negatif | Must | ⚠️ **Konflik** | SR-03 | DL-02 | **DIPILIH.** Guard di `OpeningStock::reserve()`. |
| **SR-05** | Rita Kusuma (Pengadaan) | Vendor Bill boleh untuk seluruh qty PO walau receipt belum lengkap | Should | ⚠️ **Konflik** | SR-06 | DL-03 | Ditolak literal. Ikuti keputusan DL-03. |
| **SR-06** | Made Wirawan (Finance) | Vendor Bill maksimal accepted receipt qty yang belum ditagih | Must | ⚠️ **Konflik** | SR-05 | DL-03 | **DIPILIH.** 3-way match: PO × Receipt × Bill. |
| **SR-07** | Dini Pratiwi (Sales) | Sales boleh beri diskon ≤ 15% tanpa approval saat Quotation | Must | ⚠️ **Konflik** | SR-08 | DL-04 | Batas diskon bebas = 10%; 10–15% wajib approval Finance. |
| **SR-08** | Made Wirawan (Finance) | Diskon >10% wajib Finance approval; harga & diskon terkunci setelah Confirmed | Must | ⚠️ **Konflik** | SR-07 | DL-04 | **DIPILIH.** Workflow approval: status → `To Approve` jika diskon >10%. |
| **SR-09** | Bagus Hartanto (Ops) | P03 per lot; WH-JKT pakai FEFO; lot kedaluwarsa dilarang dikirim; tanggal ref 20-10-2026 | Must | ✅ **Definitif** | — | — | FEFO sort `expiry_date ASC`; `isExpired()` guard sebelum alokasi DO. |
| **SR-10** | Rita Kusuma (Pengadaan) | Setiap variant SKU unik; harga/cost/unit mengikuti baris variant | Must | ✅ **Definitif** | — | — | UNIQUE constraint pada `variant_sku`; 16 SKU, tidak ada duplikat. |
| **SR-11** | Made Wirawan (Finance) | Credit exposure = open AR + gross confirmed SO belum ditagih; SO baru ditolak jika melebihi limit | Must | 🔶 **Ambigu** | — | DL-05 | "Ditolak" = hard block (Opsi A dipilih). Lihat DL-05. |
| **SR-12** | Dini Pratiwi (Sales) | Harga dan diskon SO terkunci setelah Confirmed; revisi hanya via pembatalan terotorisasi | Must | ✅ **Definitif** | — | — | Field `price` & `discount` read-only setelah status = Confirmed. |
| **SR-13** | Hendra Widodo (Owner) | Return barang layak jual: tambah On Hand + balik COGS; Credit Note ≤ qty ditagih & belum dikreditkan | Must | ✅ **Definitif** | — | — | Return stock move + credit note dengan ceiling: `invoiced_qty − already_credited`. |
| **SR-14** | Sari Prameswari (Gudang) | Internal Transfer: WH-JKT − qty, HUB-BDG + qty; nilai total/revenue/pajak tidak berubah | Must | ✅ **Definitif** | — | — | Transfer antar-lokasi; tidak memicu jurnal AR/AP/PPN. |
| **SR-15** | Melati Yusuf (SDM) | User non-HR hanya lihat nama, dept, jabatan, status; data privat hanya HR | Must | ✅ **Definitif** | — | — | API-level access control; bukan hanya hide kolom UI. T-28. |
| **SR-16** | Made Wirawan (Finance) | Dokumen bernomor unik; posting idempotent (DO, Receipt, Invoice, Bill, Payment) | Must | ✅ **Definitif** | — | — | Status guard: jika sudah Posted/Done tolak re-post. Sequence auto-increment. |
| **SR-17** | Sari Prameswari (Gudang) | DO & Receipt wajib validator: Employee Aktif, dept Warehouse, role berwenang; employee ID di audit | Must | ⚠️ **Konflik** | SR-18 | DL-06 | Saling melengkapi SR-18. Lihat arsitektur DL-06. |
| **SR-18** | Melati Yusuf (SDM) | Employee terpisah dari User Account; hanya Employee Aktif yang di-mapped ke user aktif boleh sign-off | Must | ⚠️ **Konflik** | SR-17 | DL-06 | **Bukan konflik—saling melengkapi.** `Employee::canSignOff()` cek real-time. |
| **SR-19** | Bagus Hartanto (Ops) | Dashboard hitung Open Orders, Available Stock, unpaid AR/AP, On Hand; tiap angka bisa ditelusuri | Should | ✅ **Definitif** | — | — | KPI hanya dari dokumen Posted/Done; draft tidak dihitung. |
| **SR-20** | Made Wirawan (Finance) | Partial Payment ≤ outstanding Invoice/Bill; saldo turun sebesar nilai bayar; stok & pajak tidak berubah | Must | ✅ **Definitif** | — | — | Validasi `amount <= outstanding` sebelum posting. |
| **SR-21** | Rita Kusuma (Pengadaan) | Unit price PO terkunci setelah Confirmed; Receipt mencatat ordered, received, accepted, rejected terpisah | Must | ✅ **Definitif** | — | — | Field `unit_price` read-only setelah PO Confirmed; 4 kolom qty di receipt. |
| **SR-22** | Sari Prameswari (Gudang) | Available = On Hand − Reserved; reservation tidak mengubah On Hand/valuation; reserved ≤ On Hand lokasi | Must | ✅ **Definitif** | — | — | Computed field; guard `reserved <= on_hand` per lokasi. |

---

## Ringkasan Statistik

| Kategori | Jumlah SR |
|----------|-----------|
| ✅ Definitif (dapat diterapkan langsung) | 12 |
| ⚠️ Konflik aktif (butuh keputusan manajerial) | 8 (4 pasang: 01/02, 03/04, 05/06, 07/08, 17/18*) |
| 🔶 Ambigu (butuh klarifikasi definisi) | 1 (SR-11) |
| **Total** | **22** |

> *SR-17/18 dikategorikan konflik pada tabel namun sesungguhnya saling melengkapi—memerlukan keputusan arsitektur, bukan pilihan satu atau lainnya.*

---

## Konflik yang Harus Diselesaikan Sebelum Minggu 2

> ⚠️ **KRITIS:** Keputusan berikut harus final sebelum M2 dan M4 mulai implementasi.

1. **DL-01 (SR-01/02)** — Billing policy: Delivered qty vs SO qty
2. **DL-02 (SR-03/04)** — SO confirmation vs negative stock prevention
3. **DL-03 (SR-05/06)** — Vendor Bill basis: PO qty vs accepted receipt qty
4. **DL-04 (SR-07/08)** — Discount threshold: 15% bebas vs 10% dengan approval
5. **DL-05 (SR-11)** — Credit enforcement: hard block vs soft warning

---

*Penyusun: M1 · Referensi: Handout §2 (SR-01 s.d. SR-22) · Review: M2, M4*
