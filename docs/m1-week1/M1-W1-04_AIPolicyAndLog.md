# M1-W1-04 · Kebijakan Penggunaan AI dan Catatan AI
**PT Arunika Distribusi Cepat — ERP FMCG | Group 02 · Kelas 24SI2**
**Penyusun:** M1 — Nadine Valencia Cicero | **Status:** Selesai ✅

---

## A. Kebijakan Penggunaan AI (Ringkasan)

Berdasarkan Handout §8 dan §13, tim Group 02 menetapkan kebijakan berikut:

| ID | Aturan | Penanggung Jawab |
|----|--------|-----------------|
| A-01 | Semua alat AI yang dipakai didaftarkan di tabel B sebelum dipakai | M1 |
| A-02 | Input ke AI menggunakan wireframe S-xx + acceptance criteria F-xx + SR terkait; prompt penting disimpan di log | M2 (orkestrasi), semua |
| A-03 | Output AI ditinjau logikanya (bukan hanya tampilan) sebelum commit: cek terhadap SR, angka dataset, dan edge case | Penulis + peninjau PR |
| A-04 | Output yang tampak benar tetapi melanggar aturan dicatat sebagai AI-trap di Log AI | Semua; M1 merapikan |
| A-05 | Kode AI tidak boleh meniru kode/aset Odoo dan tidak boleh melewatkan seed/aturan di handout | M2 |
| A-06 | Tidak berbagi kode, prompt, atau solusi jadi dengan kelompok lain | Semua |
| A-07 | Setiap anggota harus bisa menjelaskan bagian mana pun dari aplikasi secara langsung | Semua |
| A-08 | Hanya data fiktif handout yang dimasukkan ke alat AI; tidak ada data pribadi nyata atau secret | Semua |

---

## B. Daftar Alat AI yang Diungkapkan

| No. | Alat AI (nama + versi/model) | Digunakan oleh | Digunakan untuk / catatan |
|-----|------------------------------|---------------|--------------------------|
| 1 | Kiro (Antigravity / Amazon) | M1 (utama), semua anggota sesuai kebutuhan | Generate kode modul dari wireframe + acceptance criteria; review SR; generate dokumen spesifikasi |
| 2 | — | — | *Tambahkan baris bila ada alat lain* |

> **Catatan:** Alat yang dipakai tetapi tidak tercantum dianggap tidak diungkapkan.

---

## C. Log Penggunaan AI (Template Terisi)

| No. | Tanggal | Anggota | Alat AI | Tujuan / Ringkasan Prompt | Dipakai? | Hasil Review Logika (SR yang dicek) | Link Commit / PR |
|-----|---------|---------|---------|--------------------------|----------|-------------------------------------|-----------------|
| 1 | 4 Nov 2026 | M1 | Kiro | Generate 5 dokumen spesifikasi Minggu 1: SR classification, traceability matrix, decision log, expected results, acceptance criteria BDD | Ya | SR-01/02, SR-03/04, SR-05/06, SR-07/08, SR-11, SR-16, SR-17/18 semua dicek terhadap handout; kalkulasi T-03/T-09 diverifikasi manual | *docs: M1-W1 spec package* |
| 2 | *dd Mmm 2026* | *Mx* | *Kiro* | *Isi sesuai penggunaan aktual* | *Ya/Sebagian/Tidak* | *SR yang dicek* | *link* |

---

## D. AI-Trap Log (Output Tampak Benar tetapi Melanggar Aturan)

| ID | Tanggal | Modul | SR/F Terkait | Apa yang Tampak Benar | T-xx | Aturan yang Dilanggar & Cara Mendeteksi | Perbaikan & Tes Regresi |
|----|---------|-------|-------------|----------------------|------|----------------------------------------|------------------------|
| AT-01 | *Akan diisi saat ditemukan* | Sales | SR-08; F-06 | *Contoh: tombol Confirm mengizinkan diskon 15% tanpa approval Finance — UI mulus, total benar* | T-10 | *SR-08: diskon >10% harus ada approval. Deteksi: cek status SO setelah confirm 15%* | *Guard if discount > 10% → status = To Approve; ulangi T-10* |
| AT-02 | — | — | — | — | — | — | — |

> Template AI-trap log ini akan diisi sepanjang proyek sebagai bahan Refleksi 2 dan slide hasil tes.

---

## E. Protokol Review Output AI

Setiap kode yang dihasilkan AI wajib melalui checklist berikut sebelum commit:

- [ ] Tidak ada raw SQL kecuali PRAGMA di master seed
- [ ] Pesan exception mencantumkan `SR-XX:`
- [ ] `AuditLog::record()` dipanggil pada setiap transisi status
- [ ] Tidak ada journal entry tanpa `idempotency_key` check
- [ ] Amount tersimpan sebagai integer Rupiah (no decimals)
- [ ] PPN dihitung per baris: `floor(net × 0.11 + 0.5)`
- [ ] Kalkulasi diverifikasi manual terhadap angka di handout

---

*Kebijakan ini berlaku sejak persetujuan Team Agreement. Dikelola oleh M1.*
