# M1-W1-02 · Team Agreement — Referensi Repository
**PT Arunika Distribusi Cepat — ERP FMCG | Group 02 · Kelas 24SI2**
**Penyusun:** M1 — Nadine Valencia Cicero | **Status:** Selesai ✅

---

## Catatan

Team Agreement lengkap tersedia dalam format spreadsheet di:
- **Berkas sumber:** `Group02-24SI2-FMCG-TeamAgreement.xlsx` (diserahkan secara terpisah)
- **Versi:** 1.0 · Berlaku sejak: 24 Oktober 2026

Dokumen ini merangkum poin-poin operasional utama untuk keperluan referensi cepat di dalam repository.

---

## Ritme Rapat

| Rapat | Frekuensi | Jadwal | Durasi | Output Wajib |
|-------|-----------|--------|--------|--------------|
| Weekly Planning | Awal tiap minggu | Sabtu 19.00 WIB | 45 mnt | Tugas + tenggat per anggota |
| Check-in async | 2× per minggu | Rabu & Minggu ≤20.00 | 5 mnt | Pesan check-in di grup |
| Integration & Test | Tiap minggu (mulai Minggu 2) | Kamis 19.00 WIB | 60 mnt | Hasil tes expected vs actual |
| Checkpoint Review | Akhir tiap minggu | Kamis 20.00 WIB | 30 mnt | Checklist checkpoint |

**Kuorum:** Minimal 3 dari 4 anggota hadir.

---

## Aturan Commit & Repository (Ringkasan G-01 s.d. G-13)

| ID | Aturan Kunci |
|----|-------------|
| G-01 | Satu repository bersama; README memuat cara menjalankan, diagram, kepemilikan modul |
| G-02 | Setiap anggota commit dari akun Git sendiri |
| G-03 | Format commit: `[Mx][F-xx] jenis: deskripsi singkat` |
| G-04 | Branch main selalu stabil; kerja di branch `feature/F-xx-nama` |
| G-05 | Setiap PR ditinjau minimal 1 anggota lain |
| G-07 | Seed hanya diubah lewat script seed yang di-versi; checksum wajib diverifikasi |
| G-10 | Tidak commit secret/API key; gunakan `.env` yang di-ignore |
| G-12 | Penamaan berkas: `Group02_24SI2_FMCG_ItemName` |

---

## Definition of Done (Ringkasan)

Sebuah fitur F-xx dinyatakan Selesai jika:
1. Semua kriteria Given/When/Then F-xx (minimal 2) terpenuhi
2. SR-xx terkait diterapkan di logika server dan diuji (expected vs actual)
3. Stok tidak pernah negatif; posting idempotent; nomor dokumen unik
4. Audit tercatat: actor, timestamp, aksi, dokumen
5. Efek lintas modul benar (stok ↔ jurnal ↔ AR/AP ↔ pajak)
6. Data tersimpan setelah refresh; fitur berjalan di URL publik
7. T-xx terkait dijalankan dan dicatat di Test Results
8. Commit sudah di-merge lewat PR yang ditinjau
9. Minimal dua anggota dapat menjelaskan fitur tersebut

---

## Eskalasi Keterlambatan (Ringkasan)

| Tingkat | Pemicu | Tindakan |
|---------|--------|----------|
| 0 — Pemberitahuan dini | Perkiraan tidak selesai tepat waktu | Beri tahu grup ≥24 jam sebelum tenggat |
| 1 — Pengingat | Melewati tenggat >24 jam tanpa kabar | Pengingat oleh M1; balasan ≤12 jam |
| 2 — Rencana pemulihan | Terlambat >48 jam atau hambatan teknis >4 jam | Rapat singkat 15 menit |
| 3 — Redistribusi | Terlambat >72 jam atau checkpoint terancam | Voting redistribusi tugas |
| 4 — Eskalasi ke dosen | Tidak berkontribusi/tidak responsif | M1 kirim ringkasan tertulis ke dosen |

---

*Untuk dokumen lengkap lihat: `Group02-24SI2-FMCG-TeamAgreement.xlsx`*
