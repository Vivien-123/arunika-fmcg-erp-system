# M1-W1-09 · Acceptance Criteria BDD — F-01 s.d. F-22
**PT Arunika Distribusi Cepat — ERP FMCG | Group 02 · Kelas 24SI2**
**Penyusun:** M1 — Nadine Valencia Cicero | **Status:** Selesai ✅

---

## Format

Setiap fitur memiliki minimal **2 skenario BDD**:
- ✅ **Skenario positif (happy path)** — alur normal yang harus berhasil
- ⛔ **Skenario negatif/edge case** — aturan bisnis yang harus menegakkan penolakan

> Format: **Given** [kondisi awal] **When** [aksi pengguna] **Then** [hasil yang diharapkan]

---

## F-01 · Beranda Aplikasi dan Kontrol Peran
*SR: SR-15, SR-18 · Tests: T-01, T-02, T-27, T-28*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F01-01 | ✅ | **Given** `sales_user` berhasil login, **When** membuka App Switcher di beranda, **Then** menu Sales dan CRM tampil; menu Accounting, Finance, dan Jurnal tidak tampil; tidak ada mutasi stok atau ledger. |
| AC-F01-02 | ⛔ | **Given** `sales_user` sedang login, **When** mencoba mengakses URL endpoint Journal Entries secara langsung (mis. `/accounting/journal`), **Then** server mengembalikan HTTP 403, pesan "Access denied", dan percobaan akses dicatat di audit log (`action: access_denied`, actor, timestamp). |

---

## F-02 · Pelanggan, Pemasok, dan Syarat Kredit
*SR: SR-11, SR-20 · Tests: T-03, T-04*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F02-01 | ✅ | **Given** form Customer C01 dibuka oleh `finance_user`, **When** halaman selesai dimuat, **Then** ditampilkan: credit_limit = Rp2.000.000, open_AR = Rp900.000, payment_terms = "14 hari", current exposure = Rp1.122.000, dan dokumen S00007 (gross Rp222.000, status Confirmed;unbilled) tampil di Related Documents. |
| AC-F02-02 | ⛔ | **Given** SO S00007 sudah berstatus Confirmed dengan payment_terms "14 hari" yang terkunci, **When** `finance_user` mengubah payment_terms master C01 menjadi "Tunai", **Then** payment_terms pada S00007 yang sudah ada tidak berubah; hanya dokumen baru yang menggunakan payment_terms baru. |

---

## F-03 · Katalog Produk dan SKU Varian
*SR: SR-10, SR-09 · Tests: T-05, T-06*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F03-01 | ✅ | **Given** katalog produk dibuka, **When** SKU P01 (CLN-DTR-800-LMN) dibaca, **Then** ditampilkan: sales_price = Rp25.000, cost = Rp17.000, unit = pouch, default_vendor = V01 (PT Bersih Abadi); dan total variant SKU di katalog = 16, tidak ada duplikat. |
| AC-F03-02 | ⛔ | **Given** katalog berisi P01 (Detergen Cair Lemon) dan P11 (Detergen Cair Floral) dengan nama keluarga produk sama, **When** keduanya disimpan, **Then** variant_sku P01 (CLN-DTR-800-LMN) ≠ variant_sku P11 (CLN-DTR-800-FLR); sistem menolak insert SKU duplikat dengan error "variant_sku must be unique". |

---

## F-04 · Lead dan Pipeline CRM
*SR: SR-19 · Tests: T-07, T-08*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F04-01 | ✅ | **Given** Lead L00003 berstatus New milik C03 dengan expected revenue Rp500.000, **When** `sales_user` mengkonversinya ke Opportunity, **Then** stage berubah menjadi Qualified, relasi C03 tetap terhubung, expected revenue Rp500.000 tetap, tidak ada jurnal atau stock move terbentuk, audit log mencatat `action: convert_to_opportunity`. |
| AC-F04-02 | ⛔ | **Given** Opportunity L00003 sudah berstatus Qualified, **When** `sales_user` mencoba menghapus field Customer (C03), **Then** sistem menolak dengan pesan "Customer relation is mandatory for Opportunities (SR-19)"; field Customer tetap berisi C03; audit log mencatat percobaan penghapusan. |

---

## F-05 · Quotation sampai Sales Order
*SR: SR-03, SR-12 · Tests: T-09, T-10*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F05-01 | ✅ | **Given** Quotation baru dibuat untuk C01 dengan 10 × P01 @ Rp25.000 tanpa diskon dari seed baseline, **When** `sales_user` mengkonfirmasi, **Then** nomor SO = S00008, subtotal = Rp250.000, PPN = Rp27.500, gross = Rp277.500, status = Confirmed, exposure C01 = Rp1.399.500. |
| AC-F05-02 | ⛔ | **Given** SO S00008 sudah berstatus Confirmed, **When** `sales_user` mencoba mengubah unit price P01 menjadi Rp30.000 tanpa melalui pembatalan resmi, **Then** perubahan ditolak dengan pesan "Cannot edit confirmed SO lines (SR-12)"; field tetap Rp25.000; audit log mencatat percobaan edit. |

---

## F-06 · Diskon dan Penguncian Harga
*SR: SR-07, SR-08 · Tests: T-09, T-10*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F06-01 | ✅ | **Given** Quotation dengan 10 × P01, **When** `sales_user` memberi diskon 10% dan mengkonfirmasi, **Then** harga netto per unit = Rp22.500, subtotal = Rp225.000, SO terkonfirmasi tanpa memerlukan approval Finance. |
| AC-F06-02 | ⛔ | **Given** Quotation dengan 10 × P01 dan diskon 15%, **When** `sales_user` mencoba mengkonfirmasi langsung, **Then** konfirmasi diblokir dengan pesan "Discount >10% requires Finance approval (SR-07/SR-08)"; status SO menjadi `To Approve`; notifikasi dikirim ke `finance_user`. |

---

## F-07 · Reservation dan Available Stock
*SR: SR-03, SR-04, SR-22 · Tests: T-11, T-12, T-13, T-14*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F07-01 | ✅ | **Given** baseline P01 WH-JKT: On Hand = 60, Reserved = 8 (dari S00007), **When** stock overview dibuka, **Then** Available = 52, On Hand = 60 (tidak berubah), stock valuation = Rp1.020.000 (60 × Rp17.000) (tidak berubah). |
| AC-F07-02 | ⛔ | **Given** Available P01 di WH-JKT = 52, **When** SO baru mencoba mereservasi 60 P01 dari WH-JKT (melebihi Available 52), **Then** sistem hanya mereservasi 52 (sebesar Available); sisa 8 ditandai untuk backorder atau notifikasi pengadaan; On Hand tetap 60; tidak ada stock negatif. |

---

## F-08 · Pengiriman Parsial dan Backorder
*SR: SR-02, SR-04 · Tests: T-11, T-12*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F08-01 | ✅ | **Given** SO untuk 12 × P02 dan WH-JKT P02 On Hand = 40, **When** DO divalidasi dengan qty Done = 7 oleh E01 (Aktif, D-WH), **Then** backorder untuk 5 P02 terbentuk otomatis, WH-JKT P02 On Hand menjadi 33, COGS Rp259.000 tercatat (7 × Rp37.000), Available DO backorder = 33. |
| AC-F08-02 | ⛔ | **Given** Available P02 di WH-JKT = 33 (setelah baseline), **When** DO untuk 45 P02 dicoba divalidasi (melebihi Available), **Then** sistem menolak dengan pesan "Insufficient available stock (SR-04)"; DO tidak dapat Done; On Hand tidak berubah. |

---

## F-09 · Customer Invoice Berdasarkan Delivery
*SR: SR-01, SR-02 · Tests: T-12, T-19, T-20*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F09-01 | ✅ | **Given** DO untuk 10 × P01 sudah Done (T-11 fixture), **When** `finance_user` membuat dan memposting Customer Invoice, **Then** invoiced qty = 10, subtotal = Rp250.000, PPN = Rp27.500, gross = Rp277.500, status = Posted, AR bertambah Rp277.500. |
| AC-F09-02 | ⛔ | **Given** DO untuk 7 dari 12 P02 sudah Done, **When** `finance_user` mencoba membuat invoice untuk 12 P02, **Then** sistem membatasi invoiced qty = 7 (Done − already_invoiced = 7 − 0 = 7); invoice untuk 5 sisanya tidak dapat dibuat sampai backorder selesai (sesuai DL-01 Opsi B / SR-02). |

---

## F-10 · Kontrol Credit Limit
*SR: SR-11 · Tests: T-03, T-04, T-09, T-33*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F10-01 | ✅ | **Given** C01 baseline exposure = Rp1.122.000, **When** SO S00008 untuk 10 × P01 gross Rp277.500 dikonfirmasi, **Then** exposure C01 menjadi Rp1.399.500 (< limit Rp2.000.000) dan SO dikonfirmasi; credit check pass. |
| AC-F10-02 | ⛔ | **Given** C01 baseline exposure = Rp1.122.000, **When** SO baru untuk 40 × P01 gross Rp1.110.000 dikonfirmasi, **Then** sistem menolak konfirmasi dengan pesan "Credit limit exceeded: projected exposure Rp2.232.000 > limit Rp2.000.000 (SR-11)"; SO tetap Draft. |

---

## F-11 · RFQ sampai Purchase Order
*SR: SR-21 · Tests: T-15, T-16*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F11-01 | ✅ | **Given** PO00007 dibuka oleh `purchase_user`, **When** data dibaca, **Then** ditampilkan: vendor = V04, SKU = P05, qty = 20, unit_price = Rp32.000, net = Rp640.000, PPN estimasi = Rp70.400, gross estimasi = Rp710.400; AP tidak berubah. |
| AC-F11-02 | ⛔ | **Given** PO00007 sudah berstatus Confirmed, **When** `purchase_user` mencoba mengubah unit_price P05 menjadi Rp35.000, **Then** sistem menolak dengan pesan "Cannot edit confirmed PO lines (SR-21)"; unit_price tetap Rp32.000; audit log mencatat percobaan. |

---

## F-12 · Receipt dan Quantity Accepted/Rejected
*SR: SR-06, SR-17 · Tests: T-10, T-17, T-18*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F12-01 | ✅ | **Given** PO00007 untuk 20 × P05 dengan validator = E01 (Aktif, D-WH), **When** receipt divalidasi dengan received = 20, accepted = 15, rejected = 5, **Then** WH-JKT P05 On Hand naik 15 (menjadi 45), stock value naik Rp480.000 (15 × Rp32.000), rejected qty = 5 tidak masuk stok, AP tetap Rp700.000. |
| AC-F12-02 | ⛔ | **Given** Receipt untuk PO00007 dengan validator = E03 (status: Nonaktif), **When** validasi dicoba, **Then** sistem menolak dengan pesan "Validator E03 is inactive and not authorized (SR-17/SR-18)"; receipt tidak dapat Done; audit log mencatat `action: sign_off_rejected, actor_employee: E03`. |

---

## F-13 · Vendor Bill Tiga-Arah
*SR: SR-05, SR-06 · Tests: T-15, T-16, T-21, T-22*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F13-01 | ✅ | **Given** Receipt PO00007 accepted = 15 × P05 (T-17 fixture), **When** `finance_user` membuat dan memposting Vendor Bill untuk 15 P05, **Then** net = Rp480.000, PPN = Rp52.800, gross = Rp532.800, AP V04 menjadi Rp1.232.800; match PO dan receipt tercatat. |
| AC-F13-02 | ⛔ | **Given** Receipt accepted = 15 × P05 dan sudah dibuat 1 bill untuk 15 P05 (already_billed = 15), **When** `finance_user` mencoba membuat bill kedua untuk 5 P05 tambahan tanpa receipt baru, **Then** sistem menolak karena available_to_bill = accepted − already_billed = 15 − 15 = 0 (SR-06). |

---

## F-14 · Ledger, Pajak, dan Biaya Pokok
*SR: SR-02, SR-06, SR-16 · Tests: T-11, T-17, T-19–T-26, T-31, T-32*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F14-01 | ✅ | **Given** DO untuk 10 × P01 (standard cost Rp17.000) diselesaikan Done, **When** jurnal otomatis terbentuk, **Then** Dr HPP (5100) Rp170.000 dan Cr Persediaan (1300) Rp170.000; jurnal seimbang; total stock value menjadi Rp22.925.000. |
| AC-F14-02 | ⛔ | **Given** Invoice sudah berstatus Posted, **When** `finance_user` mencoba memposting invoice yang sama kembali (double-click atau re-request), **Then** sistem menolak re-post dengan pesan "Invoice already posted (SR-16)"; tidak ada jurnal duplikat; audit log mencatat percobaan ke-2 sebagai "already posted — no action". |

---

## F-15 · Register Payment Parsial
*SR: SR-20, SR-16 · Tests: T-23, T-24*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F15-01 | ✅ | **Given** Customer Invoice Rp277.500 sudah Posted (T-19 fixture), **When** `finance_user` memregistrasi payment Rp100.000, **Then** outstanding invoice = Rp177.500, bank balance naik Rp100.000, AR turun Rp100.000, PPN tidak berubah, jurnal: Dr Bank Rp100.000 Cr AR Rp100.000. |
| AC-F15-02 | ⛔ | **Given** Customer Invoice outstanding = Rp277.500, **When** `finance_user` mengajukan payment Rp300.000 (overpayment), **Then** sistem menolak dengan pesan "Payment amount Rp300.000 exceeds outstanding Rp277.500 (SR-20)"; tidak ada jurnal terbentuk. |

---

## F-16 · Return dan Credit Note
*SR: SR-13 · Tests: T-31, T-32*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F16-01 | ✅ | **Given** Invoice 10 × P01 sudah Posted (T-19 fixture), **When** return 2 P01 layak jual diproses dan Credit Note diposting, **Then** Credit Note net = Rp50.000, PPN = Rp5.500, gross = Rp55.500; WH-JKT P01 On Hand naik 2; reverse COGS: Dr Persediaan Rp34.000 Cr HPP Rp34.000; AR total = Rp1.722.000. |
| AC-F16-02 | ⛔ | **Given** Invoice 10 × P01 sudah Posted (T-19 fixture), **When** Credit Note untuk 11 P01 diajukan, **Then** sistem menolak karena qty_creditable = invoiced − already_credited = 10 − 0 = 10; pesan "Credit note qty exceeds creditable qty (SR-13)"; Credit Note dibatasi maksimal 10. |

---

## F-17 · Internal Transfer Dua Lokasi
*SR: SR-14, SR-22 · Tests: T-13, T-14*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F17-01 | ✅ | **Given** WH-JKT P01 On Hand = 60, HUB-BDG P01 On Hand = 20, **When** transfer 5 × P01 dari WH-JKT ke HUB-BDG dilakukan dan Done, **Then** WH-JKT P01 = 55, HUB-BDG P01 = 25, total company P01 = 80 (tidak berubah); tidak ada jurnal AR/AP/PPN; stock value total tidak berubah. |
| AC-F17-02 | ⛔ | **Given** WH-JKT P01: On Hand = 60, Reserved = 8 (S00007), Available = 52, **When** transfer 53 × P01 dari WH-JKT dicoba, **Then** sistem menolak karena transfer akan melebihi available (52) atau melanggar reserved 8; pesan "Transfer qty exceeds available stock (SR-04/SR-22)"; transfer dibatasi maksimal 52. |

---

## F-18 · Direktori Employees dan Department
*SR: SR-15, SR-18 · Tests: T-27, T-28, T-29, T-30*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F18-01 | ✅ | **Given** `hr_user` membuka Employees directory, **When** filter Department = Warehouse (D-WH) diterapkan, **Then** 3 employee ditemukan (E01 Sari, E02 Arif, E03 Naya); total perusahaan = 8 employee; active = 7; tidak ada entri keuangan. |
| AC-F18-02 | ⛔ | **Given** `sales_user` mengakses API endpoint profil Employee E01, **When** request dikirim, **Then** response tidak mengandung field `personal_email` dan `private_phone`; hanya nama, department, jabatan, dan status yang dikembalikan; API mengembalikan HTTP 403 atau field kosong untuk data privat (SR-15). |

---

## F-19 · Penugasan Employee dan Sign-off
*SR: SR-17, SR-18 · Tests: T-11, T-17, T-18, T-29, T-30*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F19-01 | ✅ | **Given** `warehouse_mgr` (mapped ke E01, Aktif, D-WH) login dan menerima 15 × P05, **When** Receipt divalidasi dengan E01 sebagai validator, **Then** receipt status = Done, stock WH-JKT P05 naik 15, E01 tercatat sebagai validator di audit log (`action: sign_off_authorized, actor_employee: E01`). |
| AC-F19-02 | ⛔ | **Given** E03 (status: Nonaktif, tidak ada mapped_user aktif) dicoba dijadikan validator pada Receipt, **When** sign-off dicoba, **Then** operasi diblokir dengan pesan "Employee E03 is inactive or not authorized (SR-17/SR-18)"; receipt tidak dapat Done; audit log mencatat `action: sign_off_rejected, actor_employee: E03`. |

---

## F-20 · Dashboard Operasional Terintegrasi
*SR: SR-19, SR-22 · Tests: T-13, T-33, T-34*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F20-01 | ✅ | **Given** seed baseline dimuat dan dashboard dibuka oleh `owner_user`, **When** halaman direfresh, **Then** Stock Units = 880, Stock Value = Rp23.095.000, AR = Rp1.500.000, AP = Rp700.000, Bank = Rp20.000.000; setiap KPI dapat diklik untuk menelusuri ke dokumen sumber. |
| AC-F20-02 | ⛔ | **Given** draft Invoice Rp277.500 dibuat tetapi belum diposting (status: Draft), **When** dashboard direfresh, **Then** AR tidak bertambah (draft tidak dihitung); KPI AR tetap = Rp1.500.000 (SR-19: hanya dari dokumen tersimpan/Posted). |

---

## F-21 · Document Relations dan Audit Log
*SR: SR-16, SR-19 · Tests: T-01, T-02, T-07, T-08, T-19, T-21, T-23, T-29, T-34, T-35, T-36*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F21-01 | ✅ | **Given** PO00007 dibuka dari seed baseline, **When** Related Documents (smart buttons) dilihat, **Then** Receipt count = 0, Vendor Bill count = 0, AP V04 = Rp700.000; semua angka akurat dan dapat diklik untuk drill-down. |
| AC-F21-02 | ⛔ | **Given** `sales_user` mencoba mengakses Journal Entries (Finance-only area), **When** aksi unauthorized terjadi dan diaudit oleh `owner_user`, **Then** audit log menampilkan: actor = sales_user, action = access_denied, endpoint = /accounting/journal, timestamp; tidak ada perubahan saldo. |

---

## F-22 · Nomor Dokumen Unik dan Posting Idempotent
*SR: SR-16 · Tests: T-01, T-05, T-06, T-09, T-15, T-24–T-26, T-35, T-36*

| # | Tipe | Skenario BDD |
|---|------|-------------|
| AC-F22-01 | ✅ | **Given** seed baseline dengan dokumen SO terakhir S00007, **When** SO baru dikonfirmasi, **Then** nomor yang diberikan adalah S**00008** (satu kali, unik); nomor yang sama tidak dapat diberikan ke dokumen lain; sequence increment permanen. |
| AC-F22-02 | ⛔ | **Given** Invoice sudah diposting (status = Posted), **When** request POST `/invoices/{id}/post` dikirim ulang (simulasi double-click atau network retry), **Then** server mengembalikan respons idempotent (200 OK tanpa aksi baru); tidak ada jurnal duplikat terbentuk; audit log mencatat entri ke-2 sebagai "already posted — idempotency guard triggered (SR-16)". |

---

## Ringkasan Coverage

| Fitur | Skenario Positif | Skenario Negatif | SR Utama | Tests |
|-------|-----------------|-----------------|----------|-------|
| F-01 | AC-F01-01 | AC-F01-02 | SR-15, SR-18 | T-01, T-02 |
| F-02 | AC-F02-01 | AC-F02-02 | SR-11, SR-20 | T-03, T-04 |
| F-03 | AC-F03-01 | AC-F03-02 | SR-10, SR-09 | T-05, T-06 |
| F-04 | AC-F04-01 | AC-F04-02 | SR-19 | T-07, T-08 |
| F-05 | AC-F05-01 | AC-F05-02 | SR-03, SR-12 | T-09, T-10 |
| F-06 | AC-F06-01 | AC-F06-02 | SR-07, SR-08 | T-09, T-10 |
| F-07 | AC-F07-01 | AC-F07-02 | SR-03, SR-04, SR-22 | T-11–T-14 |
| F-08 | AC-F08-01 | AC-F08-02 | SR-02, SR-04 | T-11, T-12 |
| F-09 | AC-F09-01 | AC-F09-02 | SR-01, SR-02 | T-12, T-19, T-20 |
| F-10 | AC-F10-01 | AC-F10-02 | SR-11 | T-03, T-04, T-09 |
| F-11 | AC-F11-01 | AC-F11-02 | SR-21 | T-15, T-16 |
| F-12 | AC-F12-01 | AC-F12-02 | SR-06, SR-17 | T-17, T-18 |
| F-13 | AC-F13-01 | AC-F13-02 | SR-05, SR-06 | T-21, T-22 |
| F-14 | AC-F14-01 | AC-F14-02 | SR-02, SR-06, SR-16 | T-11, T-17, T-25, T-26 |
| F-15 | AC-F15-01 | AC-F15-02 | SR-20, SR-16 | T-23, T-24 |
| F-16 | AC-F16-01 | AC-F16-02 | SR-13 | T-31, T-32 |
| F-17 | AC-F17-01 | AC-F17-02 | SR-14, SR-22 | T-13, T-14 |
| F-18 | AC-F18-01 | AC-F18-02 | SR-15, SR-18 | T-27, T-28 |
| F-19 | AC-F19-01 | AC-F19-02 | SR-17, SR-18 | T-17, T-18, T-29, T-30 |
| F-20 | AC-F20-01 | AC-F20-02 | SR-19, SR-22 | T-33, T-34 |
| F-21 | AC-F21-01 | AC-F21-02 | SR-16, SR-19 | T-35, T-36 |
| F-22 | AC-F22-01 | AC-F22-02 | SR-16 | T-09, T-25, T-35, T-36 |

**Total:** 22 fitur × 2 skenario = **44 acceptance criteria**. Semua 22 SR tercakup.

---

*Penyusun: M1 · Referensi: Handout §6 (F-01–F-22) · Review: M2, M3, M4*
