# DECISION LOG & DEFENSE MATRIX — ROLE M01
**PT Arunika Distribusi Cepat — Proyek ERP FMCG (Odoo Web 17.0 Reference)**  
**Peran:** M01 — Product Owner, Project Manager & Business Analyst  
**Kepemilikan Modul:** Sales & CRM  
**Rubrik Penilaian Terkait:** Aturan & Keputusan Bisnis (25%), Defense & Presentasi (20%)  

---

## 1. Executive Summary & Identifikasi Ketegangan Bisnis (Trade-offs)
Sebagai **Product Owner & Business Analyst (M01)**, tugas utama adalah menengahi ketegangan kepentingan antarpemangku kepentingan (*stakeholders*) di PT Arunika Distribusi Cepat:
1. **Pemilik Perusahaan (Hendra Widodo)** menginginkan arus kas secepat mungkin dengan menagih 100% di muka begitu order dikonfirmasi (SR-01) dan retur yang serentak membalik nilai finansial serta fisik (SR-13).
2. **Manajer Keuangan (Made Wirawan)** menerapkan prinsip kehati-hatian akuntansi: barang belum sampai tidak boleh ditagih (SR-02), diskon besar (>10%) wajib persetujuan Finance (SR-08), plafon kredit dihitung komprehensif (SR-11), pembayaran bertahap (SR-20), dan posting dokumen harus idempoten (SR-16).
3. **Lead Sales (Dini Pratiwi)** menuntut fleksibilitas komersial: konfirmasi order meski stok merah dengan janji delivery 3 hari (SR-03), diskon nego hingga 15% (SR-07), serta harga terkunci agar tidak berubah di belakang layar (SR-12).
4. **Supervisor Gudang (Sari Prameswari)** menolak keras stok negatif (SR-04), mewajibkan pemisahan tegas antara On-Hand dan Reserved (SR-22), serta audit penandatangan aktif (SR-17).
5. **HR Administrator (Melati Yusuf)** menuntut perlindungan data privasi pegawai (SR-15) dan pemisahan akun login dari entitas pegawai (SR-18).
6. **Operasional (Bagus Hartanto)** menuntut ketertelusuran dokumen dari prospek hingga pelunasan (SR-19).

---

## 2. Tabel Resmi Format Wajib Decision Defense (Sesuai Halaman 35 Handout)

| Masalah / Konflik (SR) | Opsi Dipertimbangkan | Keputusan + F/S | Trade-off & Pihak Dirugikan | Bukti Uji (T) | Dampak Jika Keputusan Dibalik |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **SR-01 vs SR-02**<br>Saat Pembuatan Invoice Penjualan: Saat SO Confirmed vs Selesai Dikirim (*Delivered*) | **Opsi A:** Terbitkan Customer Invoice 100% saat SO Confirmed (keinginan Hendra).<br>**Opsi B:** Customer Invoice hanya dapat mencakup kuantitas berstatus Done di Delivery Order dikurangi yang sudah ditagih (keinginan Made). | **Keputusan: Opsi B**<br>Modul Sales & Invoicing.<br>Fitur: `F-09`, Layar: `S-10`.<br>Invoicing Policy: *Delivered Quantities*. | **Pihak Dirugikan:** Hendra Widodo (Owner). Arus kas formal penagihan tidak bisa ditarik sebelum barang fisik diterima toko B2B.<br>**Mitigasi:** Penetapan batas kredit ketat (SR-11), percepatan lead time kirim WH-JKT & HUB-BDG max 2 hari, dan opsi pembayaran DP untuk toko berisiko tinggi. | **T-12, T-19, T-20** | **Jika Dibalik (Opsi A):** Perusahaan menerbitkan faktur pajak dan menagih piutang atas barang yang berpotensi ditolak/rusak di jalan. Menimbulkan sengketa komersial, retur pajak masif, dan pelanggaran PSAK 72. |
| **SR-03 vs SR-04**<br>Konfirmasi Order Saat Stok Kurang vs Larangan Stok Negatif Gudang | **Opsi A:** Blokir konfirmasi SO jika available < demand.<br>**Opsi B:** SO boleh dikonfirmasi dengan komitmen kirim 3 hari (SR-03), namun Delivery Order tidak boleh divalidasi melebihi Available On-Hand fisik dan tidak boleh menyebabkan stok negatif (SR-04 & SR-22). Sisanya menjadi backorder. | **Keputusan: Opsi B**<br>Modul Sales & Inventory.<br>Fitur: `F-07`, `F-08`, Layar: `S-05`, `S-06`.<br>Backorder management. | **Pihak Dirugikan:** Tim Sales & CS. Harus mengelola komunikasi janji kirim parsial dan backorder ke pelanggan.<br>**Mitigasi:** Notifikasi otomatis ke bagian Pengadaan (PO otomatis via reordering rule) saat order parsial terjadi. | **T-11, T-12, T-14** | **Jika Dibalik (Opsi A):** Kehilangan potensi omzet FMCG fast-moving.<br>**Jika Dibalik (Stok Negatif):** Gudang mengalami *ghost inventory*, selisih fisik vs sistem, dan staf gudang dituduh mengirim barang fiktif. |
| **SR-07 vs SR-08**<br>Wewenang Diskon Sales 15% vs Batas Approval Finance 10% | **Opsi A:** Sales bebas konfirmasi diskon hingga 15% tanpa campur tangan pihak manapun.<br>**Opsi B:** Sales berhak mengajukan diskon hingga 15% di tahap Quotation (Draft). Jika diskon ≤ 10%, SO auto-confirm. Jika diskon 10.01%–15%, status SO beralih ke `To Approve` (wajib persetujuan role Finance) sebelum sah menjadi Confirmed. | **Keputusan: Opsi B**<br>Modul Sales & CRM.<br>Fitur: `F-06`, Layar: `S-05`.<br>Dual-control / Segregation of Duties. | **Pihak Dirugikan:** Dini Pratiwi (Sales Lead). Proses penutupan order dengan diskon besar membutuhkan jeda waktu menunggu review Finance.<br>**Mitigasi:** SLA persetujuan Finance maksimal 2 jam kerja; notifikasi badge di dashboard Finance. | **T-09, T-10** | **Jika Dibalik (Opsi A):** Margin laba kotor tergerus drastis akibat diskon tak terkontrol oleh sales demi mengejar target komisi volume, membahayakan profitabilitas perusahaan. |
| **SR-11**<br>Kontrol Plafon Kredit (Credit Exposure Ceiling) | **Opsi A:** Exposure hanya menghitung invoice yang sudah terbit (Open AR).<br>**Opsi B:** Formula komprehensif: `Credit Exposure = Open AR + Gross Value Confirmed SO Belum Ditagih`. Jika Exposure + Order Baru > Limit, order **DITOLAK**. | **Keputusan: Opsi B**<br>Modul Sales / Contacts.<br>Fitur: `F-02`, `F-10`, Layar: `S-02`, `S-05`. | **Pihak Dirugikan:** Pelanggan B2B (misal C01) dan Sales. Pesanan tertahan jika limit terlampaui.<br>**Mitigasi:** Fitur pembayaran parsial segera (F-15) dan display real-time indikator persentase sisa limit pada layar pemesanan. | **T-03, T-04** | **Jika Dibalik (Opsi A):** Terjadi celah *credit loophole*: pelanggan dengan limit Rp2.000.000 bisa melakukan 5 order berturut-turut masing-masing Rp1.500.000 sebelum invoice terbit, menimbulkan eksposur kredit tak terlindungi hingga Rp7.500.000! |
| **SR-12**<br>Penguncian Harga & Baris Setelah Konfirmasi (*Price Locking*) | **Opsi A:** Baris dan harga dapat diubah kapan saja di form SO.<br>**Opsi B:** Begitu status berubah menjadi `Confirmed`, harga satuan, diskon, dan kuantitas baris terkunci (*read-only*). Revisi hanya melalui pembatalan terotorisasi dengan jejak audit lengkap. | **Keputusan: Opsi B**<br>Modul Sales.<br>Fitur: `F-05`, `F-22`, Layar: `S-05`.<br>Immutable confirmed line. | **Pihak Dirugikan:** Sales yang ceroboh melakukan salah input data harus meminta pembatalan resmi.<br>**Mitigasi:** Peringatan konfirmasi visual dan fitur duplikasi order cepat (*Duplicate Order*). | **T-09, T-10** | **Jika Dibalik (Opsi A):** Risiko manipulasi harga sepihak setelah disepakati pelanggan; selisih antara nilai PO pelanggan, SO, Delivery Order, dan Invoice. |
| **SR-15 & SR-18**<br>Pemisahan Akun User vs Pegawai & Akses Data Pribadi | **Opsi A:** Satukan entitas User dan Employee; tampilkan seluruh kolom kontak pribadi di form direktori.<br>**Opsi B:** Entitas User Account dipisahkan dari Employee. Kolom sensitif (email pribadi, nomor HP pribadi, kontak darurat) hanya dapat dibaca oleh role HR. Role Sales, Warehouse, Finance hanya melihat nama, departemen, dan jabatan. | **Keputusan: Opsi B**<br>Modul Employees & Core.<br>Fitur: `F-01`, `F-18`, Layar: `S-01`, `S-14`, `S-15`. | **Pihak Dirugikan:** Karyawan non-HR yang ingin mencari kontak darurat rekan kerja secara langsung.<br>**Mitigasi:** Kontak operasional kantor (misal extension internal) tetap disediakan publik. | **T-01, T-02, T-28** | **Jika Dibalik (Opsi A):** Pelanggaran privasi data pribadi pegawai (UU PDP No. 27/2022) dan risiko akun sistem digunakan oleh sembarang nama pegawai tanpa pemetaan kredensial. |
| **SR-19**<br>Integritas Relasi Pelanggan pada Pipeline CRM | **Opsi A:** Relasi pelanggan pada Opportunity boleh dikosongkan/dihapus.<br>**Opsi B:** Validasi ketat: Opportunity yang telah mencapai tahap *Qualified* dilarang menghapus relasi pelanggan. Setiap kesempatan bisnis harus terhubung ke partner B2B terdaftar. | **Keputusan: Opsi B**<br>Modul CRM & Sales.<br>Fitur: `F-04`, `F-21`, Layar: `S-04`. | **Pihak Dirugikan:** Sales yang ingin mencatat prospek anonim tanpa mendaftarkan kontak B2B.<br>**Mitigasi:** Quick Create Contact langsung dari pop-up form Opportunity. | **T-07, T-08** | **Jika Dibalik (Opsi A):** Terputusnya rantai audit (*audit trail break*); timbul *orphan opportunities* yang tidak dapat dikonversi menjadi Quotation resmi. |
| **SR-22**<br>Definisi Stok: On-Hand vs Reserved | **Opsi A:** Konfirmasi order langsung mengurangi On-Hand fisik dan memicu jurnal stok.<br>**Opsi B:** `Available = On Hand - Reserved`. Konfirmasi order hanya menambah alokasi `Reserved` tanpa mengurangi On-Hand fisik dan tanpa mencatat jurnal HPP sampai barang dikeluarkan di DO. | **Keputusan: Opsi B**<br>Modul Sales & Inventory.<br>Fitur: `F-07`, Layar: `S-06`, `S-07`. | **Pihak Dirugikan:** Tidak ada kerugian bisnis; ini menjamin integritas stok ganda (fisik vs komitmen). | **T-11, T-13** | **Jika Dibalik (Opsi A):** Jurnal HPP dan pengurangan persediaan tercatat sebelum barang dikirim. Jika pengiriman batal, laporan keuangan menghasilkan *false valuation*. |

---

## 3. Rincian Eksekusi & Bukti Pengujian Wajib M01

### Test T-03: Normal Flow — Verifikasi Baseline Credit Exposure C01
- **Situasi Uji:** Data pelanggan `C01` dibuka pada seed baseline bersih.
- **Kondisi Awal:**
  - Plafon Kredit (*Credit Limit*): Rp2.000.000
  - Piutang Terbuka (*Open AR*): Rp900.000
  - Pesanan Terkonfirmasi Belum Ditagih: Dokumen `S00007` (8 unit P01, Gross Rp222.000)
- **Rumus Handout (SR-11):**
  $$\text{Credit Exposure} = \text{Open AR} + \text{Gross Unbilled Confirmed SO}$$
  $$\text{Credit Exposure} = 900.000 + 222.000 = \text{Rp1.122.000}$$
- **Hasil Pengujian:**
  - Expected: Credit exposure = Rp1.122.000; Sisa limit = Rp878.000.
  - Actual: `Limit: Rp2.000.000 | Open AR: Rp900.000 | S00007: Rp222.000 | Exposure: Rp1.122.000`.
  - Status: **PASSED (100%)**.

### Test T-04: Rule / Edge Case — Penolakan Pesanan Melebihi Plafon Kredit (SR-11)
- **Situasi Uji:** Pelanggan `C01` mencoba memesan 40 unit `P01` (Detergen Cair Lemon @ Rp25.000).
- **Perhitungan Finansial:**
  - Net: $40 \times 25.000 = \text{Rp1.000.000}$
  - PPN 11% (Non-mewah): $1.000.000 \times 11\% = \text{Rp110.000}$
  - Gross Order Baru: $\text{Rp1.110.000}$
  - Proyeksi Exposure: $1.122.000 + 1.110.000 = \text{Rp2.232.000}$
  - Batas Plafon: $\text{Rp2.000.000}$ (Eksedensi Rp232.000).
- **Hasil Pengujian:**
  - Expected: Sistem memblokir konfirmasi pesanan dengan pesan validasi aturan SR-11. Saldo exposure C01 tetap Rp1.122.000. Jejak penolakan tercatat di audit log.
  - Actual: Muncul error validasi `"Pesanan ditolak: Credit exposure melebihi batas kredit pelanggan (SR-11)!"`, order tetap berstatus Draft/ditolak, exposure C01 tidak bertambah.
  - Status: **PASSED (100%)**.

### Test T-07: Normal Flow — Konversi Lead L00003 Menjadi Opportunity (SR-19)
- **Situasi Uji:** Prospek `L00003` milik `C03` (UD Berkah Niaga) dikonversi menjadi Opportunity dengan potensi Rp500.000.
- **Hasil Pengujian:**
  - Expected: Tahap pipeline berubah dari `New` ke `Qualified`. Relasi pelanggan `C03` tetap terhubung. Tidak ada jurnal keuangan atau mutasi stok yang terbentuk.
  - Actual: Stage = `Qualified`, Partner = `C03`, Expected Revenue = `Rp500.000`, Stock State = `Tidak ada`.
  - Status: **PASSED (100%)**.

### Test T-08: Rule / Edge Case — Proteksi Integritas Relasi Pelanggan (SR-19)
- **Situasi Uji:** Staf sales mencoba mengosongkan/menghapus atribut pelanggan (`partner`) pada Opportunity `L00003`.
- **Hasil Pengujian:**
  - Expected: Sistem menolak operasi penghapusan dan mencatat upaya pelanggaran ke audit log.
  - Actual: Error `"Rule violation (SR-19): Customer relation cannot be deleted on Opportunity L00003"`. Partner tetap `C03`. Audit log mencatat kode aksi `REJECT_DELETE_CUSTOMER_RELATION`.
  - Status: **PASSED (100%)**.

### Test T-09: Normal Flow — Quotation 10 P01 untuk C01 Sampai Confirmed (SR-03, SR-12, SR-22)
- **Situasi Uji:** Membuat Quotation 10 unit `P01` (@ Rp25.000) tanpa diskon untuk `C01`, lalu melakukan konfirmasi dari baseline seed.
- **Perhitungan:**
  - Net Total: $10 \times 25.000 = \text{Rp250.000}$
  - PPN Efektif 11%: $\text{round}(250.000 \times 11\%) = \text{Rp27.500}$
  - Gross Total: $\text{Rp277.500}$
  - Nomor Dokumen Otomatis: `S00008` (mengikuti sekuens setelah S00007)
  - Pemutakhiran Exposure C01: $1.122.000 + 277.500 = \text{Rp1.399.500}$ ($\le \text{Rp2.000.000}$)
  - Reservasi Stok WH-JKT: P01 bertambah dari 8 menjadi 18 unit (Available tersisa $60 - 18 = 42$).
- **Hasil Pengujian:**
  - Expected: Terbit dokumen `S00008`, Net Rp250.000, PPN Rp27.500, Gross Rp277.500, Exposure C01 menjadi Rp1.399.500, Status = `Confirmed`, baris terkunci.
  - Actual: Semua nilai identik secara presisi.
  - Status: **PASSED (100%)**.

### Test T-10: Rule / Edge Case — Alur Approval Diskon 15% (SR-07 & SR-08) & Penguncian Harga (SR-12)
- **Situasi Uji:** Sales mengajukan diskon 15% pada baris `P01` dan mencoba melakukan konfirmasi langsung tanpa approval Finance.
- **Hasil Pengujian:**
  - Langkah 1 (Sales Confirm): Diblokir sistem! Status order dialihkan ke `To Approve` dengan pesan peringatan approval.
  - Langkah 2 (Finance Review): User dengan role `finance_user` menekan tombol *Approve Discount*. Status approval menjadi `Approved by Finance`.
  - Langkah 3 (Final Confirmation): Sales mengonfirmasi order -> Status menjadi `Confirmed`.
  - Langkah 4 (Uji Immutability SR-12): Dicoba mengedit kuantitas atau harga satuan pada order Confirmed -> Diblokir dengan pesan error SR-12.
  - Status: **PASSED (100%)**.

---

## 4. Panduan Presentasi 15 Menit + 10 Menit Tanya Jawab (Role M01)

### Alokasi Waktu Presentasi:
1. **0:00 - 2:00 (Konteks & Masalah PT Arunika FMCG):**  
   Jelaskan profil perusahaan distribusi B2B, volume pesanan harian, tantangan data terpisah (chat & spreadsheet), serta friksi antara pemilik, sales, keuangan, dan gudang.
2. **2:00 - 4:00 (Dampak Finansial & Operasional Jika Dibiarkan):**  
   Tunjukkan angka riil dari dataset: Stok Rp23.095.000 dan Piutang Rp1.500.000. Jelaskan bahaya *credit exposure loophole* jika SR-11 tidak diterapkan (risiko piutang macet grosir) dan bahaya *over-invoicing* sebelum barang sampai (SR-01 vs SR-02).
3. **4:00 - 6:00 (Solusi Modul Sales & CRM serta Demo Aplikasi):**  
   Tampilkan layar live Odoo 17 Web:
   - Screen `S-02`: Profil kredit pelanggan C01 dengan grafik exposure interaktif.
   - Screen `S-04`: Kanban board pipeline dari prospek hingga penawaran.
   - Screen `S-05`: Pembuatan penawaran, perhitungan PPN otomatis, dan smart buttons.
4. **6:00 - 11:00 (Decision Defense — Inti Pertahanan M01):**  
   Paparkan tabel Decision Defense di atas dengan percaya diri. Fokuskan pada resolusi konflik **SR-01 vs SR-02** (mengapa memilih delivered quantity), **SR-03 vs SR-04** (manajemen backorder tanpa stok negatif), dan **SR-07 vs SR-08** (pembatasan diskon 10% vs 15%).
5. **11:00 - 12:00 (Hasil Pengujian & Refleksi Penggunaan AI):**  
   Tunjukkan hasil `npm test` yang 100% PASS (T-03, T-04, T-07, T-08, T-09, T-10). Ungkapkan contoh refleksi AI (misal AI sempat mengizinkan konfirmasi diskon 15% tanpa mengecek role Finance, yang kemudian diperbaiki melalui guard logika di backend).
6. **12:00 - 15:00 (Rekomendasi Strategis — Now / Next / Later):**  
   - *Now:* Otomasi trigger reorder saat reservasi mencapai batas minimum safety stock.
   - *Next:* Integrasi WhatsApp API untuk notifikasi persetujuan diskon instan ke Finance Officer.
   - *Later:* Dynamic credit scoring berbasis histori ketepatan pembayaran invoice sebelumnya.
7. **15:00 - 25:00 (Sesi Tanya Jawab Dosen):**  
   Siap menjawab seluruh pertanyaan lintas modul (misal kaitan Sales Order dengan reservasi di Gudang dan pembentukan piutang di Akuntansi).
