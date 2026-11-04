# M1-W1-07 · Decision Log — Konflik Spesifikasi SR
**PT Arunika Distribusi Cepat — ERP FMCG | Group 02 · Kelas 24SI2**
**Penyusun:** M1 — Nadine Valencia Cicero | **Status:** DRAFT — Menunggu review tim

> ⚠️ Semua entri berstatus **DRAFT** hingga disetujui dalam rapat Weekly Planning Minggu 2.
> Keputusan yang sudah dicatat mengikat semua anggota (disagree and commit).
> Entri lama tidak dihapus; perubahan keputusan menggunakan entri baru.

---

## Format Wajib Decision Defense (Handout §10, hal. 35)

| Kolom | Isi |
|-------|-----|
| **Masalah / Konflik (SR)** | ID SR yang bertentangan |
| **Opsi Dipertimbangkan** | Minimal dua opsi konkret |
| **Keputusan + F/S** | Opsi yang dipilih + fitur dan layar terdampak |
| **Trade-off & Pihak Dirugikan** | Siapa yang kehilangan apa; mitigasi |
| **Bukti (T)** | Test case yang membuktikan keputusan |
| **Dampak Pembalikan** | Apa yang terjadi jika keputusan dibalik |

---

## DL-01 · SR-01 vs SR-02 — Billing Policy (Invoice Timing)

| Field | Detail |
|-------|--------|
| **ID** | DL-01 |
| **Tanggal** | 4 Nov 2026 (draft M1) |
| **SR** | SR-01 (Owner: tagih saat SO Confirmed) vs SR-02 (Finance: tagih hanya qty Done) |
| **Isu** | Pemilik ingin cash flow cepat (tagih 100% saat SO dikonfirmasi); Finance ingin akurasi (tagih hanya yang sudah dikirim). Konflik langsung: SR-01 menghasilkan invoice atas barang yang belum berpindah tangan. |
| **Opsi A** | Terapkan SR-01 → Invoice dapat diterbitkan 100% saat SO Confirmed, sebelum DO ada. |
| **Opsi B** | Terapkan SR-02 → Invoice hanya untuk qty Done di DO − qty already invoiced. |
| **Opsi C** | Hybrid → Izinkan "Pro-forma Invoice" (non-posting, tidak menambah AR) saat SO Confirmed; Invoice resmi (posting, tambah AR) hanya setelah DO Done. |
| **Keputusan** | **Opsi B (SR-02)** — akurasi akuntansi lebih penting dari kecepatan; over-invoicing melanggar PSAK 72 dan menciptakan AR fiktif. |
| **Fitur + Layar** | F-09 (Customer Invoice berdasarkan delivery) · S-10 |
| **Trade-off & Pihak Dirugikan** | Hendra Widodo (Owner) kehilangan kemampuan tagih instan. **Mitigasi:** Dashboard menampilkan "unbilled confirmed amount" sebagai proyeksi AR; SOP dipercepat: lead time pengiriman maksimal 2 hari kerja. |
| **Bukti (T)** | T-19 (invoice setelah DO Done normal), T-20 (invoice 12 P02 saat baru 7 Done — harus ditolak/dibatasi) |
| **Dampak Pembalikan** | Jika dibalik ke Opsi A: perusahaan menagih barang yang belum/tidak pernah dikirim; risiko sengketa dengan toko B2B, retur masif, dan koreksi pajak PPN keluaran; audit trail putus. |
| **Status** | DRAFT · Disetujui oleh: *menunggu* |

---

## DL-02 · SR-03 vs SR-04 — SO Confirmation vs Negative Stock

| Field | Detail |
|-------|--------|
| **ID** | DL-02 |
| **Tanggal** | 4 Nov 2026 (draft M1) |
| **SR** | SR-03 (Sales: SO dikonfirmasi walau stok tidak cukup) vs SR-04 (Gudang: reservation & DO tidak boleh buat stok negatif) |
| **Isu** | Sales tidak ingin order pernah ditolak karena stok merah; Gudang tidak ingin ada pengiriman fiktif. Keduanya bisa konsisten jika *konfirmasi dokumen* dipisahkan dari *alokasi stok fisik*. |
| **Opsi A** | Blokir konfirmasi SO jika Available < qty ordered (mengutamakan SR-04, mengabaikan SR-03). |
| **Opsi B** | SO dikonfirmasi selalu diizinkan (SR-03); reservation hanya sebesar Available; kekurangan menjadi backorder DO otomatis; DO tidak boleh divalidasi melebihi Available (SR-04). |
| **Keputusan** | **Opsi B** — pisahkan *konfirmasi dokumen* (boleh over-commit) dari *alokasi stok* (tidak boleh negatif). |
| **Fitur + Layar** | F-05 (SO confirmation) · F-07 (reservation) · F-08 (DO backorder) · S-05, S-06 |
| **Trade-off & Pihak Dirugikan** | Sari Prameswari (Gudang) menanggung beban komunikasi backorder. **Mitigasi:** Sistem menampilkan warning "Available insufficient — partial backorder will be created" saat konfirmasi SO; notifikasi otomatis ke Pengadaan untuk PO reorder. |
| **Bukti (T)** | T-11 (DO normal 10 P01), T-12 (12 P02 order, 7 kirim, 5 backorder), T-13 (baseline P01 reservation), T-14 (transfer 53 P01 dengan 8 reserved) |
| **Dampak Pembalikan** | Jika dibalik ke Opsi A: kehilangan potensi omzet fast-moving; pelanggan pindah ke kompetitor. Jika stok negatif diizinkan: `ghost inventory` — selisih fisik vs sistem; staf gudang dituduh mengirim barang fiktif. |
| **Status** | DRAFT · Disetujui oleh: *menunggu* |

---

## DL-03 · SR-05 vs SR-06 — Vendor Bill Basis

| Field | Detail |
|-------|--------|
| **ID** | DL-03 |
| **Tanggal** | 4 Nov 2026 (draft M1) |
| **SR** | SR-05 (Pengadaan: bill untuk seluruh qty PO) vs SR-06 (Finance: bill ≤ accepted receipt qty) |
| **Isu** | Pengadaan ingin proses cepat (tagih per PO); Finance ingin 3-way matching. Jika SR-05 diterapkan, vendor bisa menagih barang yang belum/tidak pernah diterima—termasuk rejected qty. |
| **Opsi A** | Terapkan SR-05 → Vendor Bill diterbitkan berdasarkan PO confirmed qty. |
| **Opsi B** | Terapkan SR-06 → Vendor Bill dibatasi: min(PO qty, accepted receipt qty) − already billed. Rejected qty tidak dapat ditagih. |
| **Keputusan** | **Opsi B (SR-06)** — 3-way matching adalah kontrol keuangan fundamental; membayar barang yang ditolak adalah kerugian langsung bagi perusahaan. |
| **Fitur + Layar** | F-13 (Vendor Bill tiga-arah) · S-11 |
| **Trade-off & Pihak Dirugikan** | Rita Kusuma (Pengadaan) harus menunggu receipt accepted sebelum submit bill. **Mitigasi:** Sistem menampilkan "billable qty" real-time di form PO; receipt dapat dibuat segera setelah barang tiba. |
| **Bukti (T)** | T-21 (bill 15 P05 setelah receipt accepted 15 — normal), T-22 (bill 20 P05 tanpa receipt tambahan — ditolak) |
| **Dampak Pembalikan** | Jika dibalik ke Opsi A: perusahaan membayar vendor untuk qty yang ditolak/belum diterima; AP overstated; arus kas terpengaruh tanpa dasar fisik. |
| **Status** | DRAFT · Disetujui oleh: *menunggu* |

---

## DL-04 · SR-07 vs SR-08 — Discount Authorization

| Field | Detail |
|-------|--------|
| **ID** | DL-04 |
| **Tanggal** | 4 Nov 2026 (draft M1) |
| **SR** | SR-07 (Sales: diskon ≤ 15% bebas tanpa approval) vs SR-08 (Finance: diskon >10% wajib Finance approval) |
| **Isu** | SR-07 mengklaim batas 15% bebas; SR-08 mengklaim batas 10%. Keduanya dari stakeholder berbeda dengan wewenang saling tumpang tindih. Secara implisit SR-07 memperbolehkan diskon 10–15% tanpa approval—bertentangan dengan SR-08. |
| **Opsi A** | Terapkan SR-07 → Diskon bebas hingga 15% tanpa approval. |
| **Opsi B** | Terapkan SR-08 → Diskon 0–10%: bebas, SO langsung Confirmed. Diskon 10.01–15%: status SO → `To Approve`, wajib Finance approval. Diskon >15%: tidak diizinkan. |
| **Opsi C** | Kompromi → Diskon 0–10%: bebas. Diskon 10.01–15%: wajib Finance approval + audit trail wajib. Tidak ada perbedaan substansial dengan Opsi B. |
| **Keputusan** | **Opsi B (SR-08)** — segregation of duties antara Sales (mengusulkan diskon) dan Finance (menyetujui diskon besar) adalah kontrol internal standar. |
| **Fitur + Layar** | F-06 (Diskon dan penguncian harga) · S-05 |
| **Trade-off & Pihak Dirugikan** | Dini Pratiwi (Sales) kehilangan fleksibilitas negosiasi cepat untuk diskon 10–15%. **Mitigasi:** SLA Finance approval = 2 jam kerja; notifikasi badge di dashboard Finance; Owner dapat override dalam kondisi darurat. |
| **Bukti (T)** | T-09 (SO tanpa diskon, normal), T-10 (diskon 15% tanpa Finance approval — diblokir) |
| **Dampak Pembalikan** | Jika dibalik ke Opsi A: margin laba kotor tergerus akibat diskon tak terkontrol; Sales dapat memberi diskon maksimal untuk semua order tanpa oversight; profitabilitas perusahaan terancam. |
| **Status** | DRAFT · Disetujui oleh: *menunggu* |

---

## DL-05 · SR-11 — Credit Limit Enforcement Mode

| Field | Detail |
|-------|--------|
| **ID** | DL-05 |
| **Tanggal** | 4 Nov 2026 (draft M1) |
| **SR** | SR-11: order baru "ditolak" jika `exposure + gross order baru > credit limit` |
| **Isu** | Kata "ditolak" tidak didefinisikan: apakah hard block (SO tidak bisa dikonfirmasi) atau soft warning (peringatan tapi bisa override)? |
| **Formula Exposure (SR-11)** | `Credit Exposure = open_AR_pelanggan + Σ(gross value Confirmed SO yang belum ditagih)` |
| **Contoh Kalkulasi C01** | open_AR = Rp900.000 + gross S00007 = Rp222.000 → Exposure = **Rp1.122.000** |
| **Opsi A** | Hard block — SO Confirmation gagal jika exposure melebihi limit; tidak ada override tanpa aksi eksplisit. |
| **Opsi B** | Soft warning — tampilkan peringatan, Sales/Owner dapat override dengan konfirmasi + alasan audit. |
| **Keputusan** | **Opsi A (Hard block)** — credit limit yang dapat di-bypass tidak efektif sebagai pengaman keuangan. Credit limit sudah ditetapkan per pelanggan berdasarkan risk assessment. |
| **Fitur + Layar** | F-02 (Pelanggan & kredit) · F-10 (Kontrol credit limit) · S-02, S-05 |
| **Trade-off & Pihak Dirugikan** | Dini Pratiwi (Sales) kehilangan fleksibilitas untuk situasi darurat. **Mitigasi:** `owner_user` dapat menaikkan credit limit sementara melalui Customer Configuration + mandatory audit entry; Finance dapat approve peningkatan limit permanen. |
| **Bukti (T)** | T-03 (exposure baseline C01 = Rp1.122.000), T-04 (SO 40 P01 → exposure Rp2.232.000 > limit Rp2.000.000 → DITOLAK), T-09 (SO 10 P01 → exposure Rp1.399.500 < limit → DIIZINKAN) |
| **Dampak Pembalikan** | Jika dibalik ke Opsi B: celah credit loophole — pelanggan dengan limit Rp2.000.000 bisa membuat 5 SO masing-masing Rp1.500.000 sebelum invoice terbit, menghasilkan eksposur kredit tak terlindungi hingga Rp7.500.000+. |
| **Status** | DRAFT · Disetujui oleh: *menunggu* |

---

## DL-06 · SR-17 vs SR-18 — Validator Authorization Architecture

| Field | Detail |
|-------|--------|
| **ID** | DL-06 |
| **Tanggal** | 4 Nov 2026 (draft M1) |
| **SR** | SR-17 (Gudang: validator = Employee Aktif + dept WH + role berwenang + employee ID di audit) vs SR-18 (SDM: Employee terpisah dari User Account; hanya Employee Aktif mapped ke user aktif boleh sign-off; perubahan status efektif segera) |
| **Isu** | SR-17 dan SR-18 saling melengkapi (bukan bertentangan), tetapi memerlukan keputusan arsitektur: bagaimana Employee ↔ User Account di-link, kapan perubahan status Nonaktif efektif, dan bagaimana DO/Receipt sign-off diperiksa. |
| **Keputusan Arsitektur** | Employee entity **terpisah** dari User Account (tabel `employees` vs `users`). Sign-off check pada saat DO/Receipt submit: (1) user login aktif, (2) `users.linked_employee` ada, (3) `employees.status = Aktif`, (4) `employees.department = D-WH`. Check dilakukan **real-time** saat submit, bukan saat login. |
| **Efektivitas Perubahan Status** | Perubahan `employees.status` efektif segera — tidak ada grace period. Jika E01 dinonaktifkan saat DO sedang in-progress, DO tidak dapat di-Done hingga validator diganti. |
| **Fitur + Layar** | F-08 (DO backorder) · F-12 (Receipt accepted/rejected) · F-19 (Employee sign-off) · S-06, S-09, S-15 |
| **Trade-off & Pihak Dirugikan** | Jika HR menonaktifkan employee mid-shift, DO yang sedang dikerjakan oleh employee tersebut tidak bisa diselesaikan. **Mitigasi:** SOP HR — wajib notify Gudang minimal 1 jam sebelum menonaktifkan employee yang sedang bertugas. |
| **Bukti (T)** | T-17 (E01 sign-off receipt normal — OK), T-18 (E03 Nonaktif coba sign-off — DITOLAK), T-29 (employee form E01 Aktif + mapping), T-30 (nonaktifkan E01 → coba validate DO → DITOLAK) |
| **Dampak Pembalikan** | Jika Employee dan User Account disatukan: akun login bisa digunakan atas nama siapa pun; tidak ada kontrol siapa yang benar-benar melakukan sign-off fisik di gudang; pelanggaran SR-17 dan SR-18. |
| **Status** | DRAFT · Disetujui oleh: *menunggu* |

---

## Ringkasan Status Decision Log

| ID | Konflik SR | Status | Target Keputusan Final |
|----|-----------|--------|----------------------|
| DL-01 | SR-01 vs SR-02 | DRAFT | Weekly Planning Minggu 2 |
| DL-02 | SR-03 vs SR-04 | DRAFT | Weekly Planning Minggu 2 |
| DL-03 | SR-05 vs SR-06 | DRAFT | Weekly Planning Minggu 2 |
| DL-04 | SR-07 vs SR-08 | DRAFT | Weekly Planning Minggu 2 |
| DL-05 | SR-11 (ambigu) | DRAFT | Weekly Planning Minggu 2 |
| DL-06 | SR-17 + SR-18 | DRAFT | Weekly Planning Minggu 2 |

> **Aturan:** Setelah dicatat dan disetujui, semua anggota menjalankan keputusan (disagree and commit). Ketidaksetujuan boleh dicatat sebagai minority opinion di baris terpisah tetapi tidak menghentikan implementasi.

---

*Penyusun: M1 · Referensi: Handout §2 (SR), §10 (Decision Defense format) · Review: M2, M4*
