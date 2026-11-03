# PT Arunika Distribusi Cepat — ERP FMCG
## Modul Sales & CRM — Kontribusi Penuh Anggota M01
**Peran:** Product Owner, Project Manager & Business Analyst  
**Referensi Desain Antarmuka:** Odoo Web Version 17.0 (Desktop Browser)  
**Teknologi:** Node.js (Standard Library Server & REST API), HTML5, CSS3 Odoo Design System, Vanilla JS SPA.

---

## 1. Ikhtisar Peran & Tanggung Jawab Anggota M01

Sesuai ketentuan skenario mahasiswa (Halaman 2–5):
- **Kepemilikan Modul:** Sales / CRM
- **Tanggung Jawab Dominan:** Analisis proses bisnis, elisitasi SR (Stakeholder Requirements), resolusi konflik kepentingan, perancangan Decision Log, acceptance tests penjualan, dan defense presentasi.
- **Fitur Wajib yang Dibangun:**
  - `F-01`: Beranda aplikasi dan kontrol peran (App Switcher, Sales permissions)
  - `F-02`: Pelanggan, pemasok, dan syarat kredit
  - `F-04`: Lead dan pipeline CRM (Kanban New -> Qualified -> Won)
  - `F-05`: Quotation sampai Sales Order (Penomoran unik sekuensial, PPN 11%)
  - `F-06`: Diskon dan penguncian harga (Workflow approval diskon > 10%)
  - `F-10`: Kontrol credit limit & exposure komprehensif
  - `F-21`: Document relations, smart buttons, dan audit log
  - `F-22`: Penomoran dokumen unik (`S00008`) dan posting idempoten
- **Pengujian yang Wajib Dites & Didukung (Acceptance Tests):**
  - `T-03`: Normal Flow — C01 Credit Exposure baseline (Rp1.122.000)
  - `T-04`: Rule / Edge Case — Penolakan SO 40 P01 karena melebihi kredit limit Rp2.000.000 (SR-11)
  - `T-07`: Normal Flow — Konversi Lead L00003 menjadi Opportunity Qualified (SR-19)
  - `T-08`: Rule / Edge Case — Larangan penghapusan relasi pelanggan pada Opportunity L00003 (SR-19)
  - `T-09`: Normal Flow — Quotation 10 P01 untuk C01 hingga Confirmed (`S00008`, Net Rp250.000, PPN Rp27.500, Gross Rp277.500, Exposure Rp1.399.500)
  - `T-10`: Rule / Edge Case — Diskon 15% tanpa Finance approval diblokir -> status `To Approve`; setelah approval Finance dikonfirmasi dan baris dikunci permanen (SR-07, SR-08, SR-12).
- **Rubrik Dominan:** Aturan & Keputusan Bisnis 25%, Defense & Presentasi 20%.

---

## 2. Cara Menjalankan Aplikasi

Aplikasi dibangun mandiri tanpa dependensi eksternal rumit, sehingga dapat dijalankan seketika menggunakan Node.js:

```bash
# 1. Pindah ke direktori proyek
cd C:\Users\user\.gemini\antigravity\scratch\arunika-erp

# 2. Jalankan acceptance test otomatis (T-03 s/d T-10)
npm test

# 3. Jalankan server web lokal
npm start
```
Buka browser dan akses: `http://localhost:3000`

---

## 3. Diagram Arsitektur & Alur Data (Order to Cash)

```mermaid
flowchart TD
    subgraph CRM_Module ["Modul CRM (F-04)"]
        Lead["Lead L00003<br>(Partner C03, Exp Rev 500k)"] -->|Convert to Opportunity| Opp["Opportunity Qualified<br>(SR-19: Relasi Wajib)"]
    end

    subgraph Sales_Module ["Modul Sales (F-02, F-05, F-06, F-10)"]
        Opp -->|Generate Quotation| Quot["Draft Quotation<br>(Pilih SKU & Qty)"]
        Quot --> DiscCheck{"Diskon > 10%?<br>(SR-07 vs SR-08)"}
        DiscCheck -- Ya (15%) --> ToApprove["Status: To Approve<br>(Finance Approval Required)"]
        ToApprove -->|Finance Approve| CreditCheck
        DiscCheck -- Tidak (<=10%) --> CreditCheck{"Credit Exposure Check<br>(SR-11)"}
        CreditCheck -- "Exposure + Gross > Limit" --> Reject["REJECT / BLOCKED<br>(Pesan Error & Audit Log)"]
        CreditCheck -- "Dalam Batas Limit" --> ConfirmSO["Confirm Sales Order<br>(S00008 Unique Sequence)"]
        ConfirmSO --> LockLine["Lock Prices & Lines<br>(SR-12 Immutability)"]
        ConfirmSO --> StockReserve["Reserve Stock WH-JKT<br>(SR-03, SR-04, SR-22)"]
    end

    subgraph Downstream_Integration ["Integrasi Modul Lain"]
        StockReserve --> DO["Delivery Order Done (M02)<br>(SR-04: No Negative Stock)"]
        DO --> Inv["Customer Invoice (M04)<br>(SR-02: Delivered Quantities)"]
        Inv --> Pay["Register Payment (M04)<br>(SR-20: Mengurangi AR)"]
    end
```

---

## 4. Tabel Kontribusi Anggota (Contribution Table)

| Anggota | Peran Fungsional | Kepemilikan Modul | Fitur Dibangun | Pengujian Divalidasi | Key Commits / Artefak |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **M01 (Anda)** | Product Owner, Project Manager & Business Analyst | Sales / CRM | `F-01`, `F-02`, `F-04`, `F-05`, `F-06`, `F-10`, `F-21`, `F-22` | `T-03`, `T-04`, `T-07`, `T-08`, `T-09`, `T-10`, `T-01`, `T-02`, `T-28` | `feat(sales): pricing engine & PPN 11%`<br>`feat(credit): exposure ceiling SR-11`<br>`feat(approval): discount workflow SR-07/08`<br>`docs: Decision Log & Defense Matrix` |
| **M02** | Lead AI Developer | Purchase / Inventory | `F-03`, `F-07`, `F-08`, `F-11`, `F-12`, `F-17` | `T-05`, `T-06`, `T-11`..`T-18` | Stock move perpetual, FEFO lot engine |
| **M03** | UI/UX Lead | Employees | `F-01`, `F-18`, `F-19` | `T-01`, `T-02`, `T-27`..`T-30` | Layout Odoo Web 17, Employee directory |
| **M04** | QA & Deployment Lead | Accounting / Invoicing | `F-09`, `F-13`..`F-16`, `F-20`..`F-22` | `T-19`..`T-26`, `T-31`..`T-36` | General Ledger, Invoice matching, Deploy |

---

## 5. Ringkasan Hasil Uji Otomatis M01 (Acceptance Tests)

Jalankan `npm run test01` untuk memverifikasi keluaran berikut:

```
[✅ PASS] T-03 - Customer C01 Credit Exposure Baseline (Normal Flow)
   Expected: Limit Rp2.000.000, Open AR Rp900.000, S00007 unbilled Rp222.000 -> Exposure Rp1.122.000
   Actual  : Limit Rp2.000.000, Open AR Rp900.000, Unbilled Rp222.000 -> Exposure Rp1.122.000

[✅ PASS] T-04 - Credit Limit Enforcement on Order Exceeding Ceiling (SR-11) (Rule / Edge Case)
   Expected: Order rejected because exposure Rp2.232.000 > credit limit Rp2.000.000.
   Actual  : Rejected: "Pesanan ditolak: Credit exposure melebihi batas kredit pelanggan (SR-11)!"

[✅ PASS] T-07 - Convert Lead L00003 to Opportunity (SR-19) (Normal Flow)
   Expected: Stage = Qualified; Partner = C03; Expected Revenue = Rp500.000; No AR/Stock moves
   Actual  : Stage = Qualified; Partner = C03; Expected Revenue = Rp500.000; Stock State = Tidak ada

[✅ PASS] T-08 - Enforce Customer Relation Traceability on Opportunity (SR-19) (Rule / Edge Case)
   Expected: Validation error: Customer relation cannot be deleted. Customer relation C03 preserved.
   Actual  : Blocked: "Rule violation (SR-19): Customer relation cannot be deleted on Opportunity L00003"

[✅ PASS] T-09 - Quotation to Confirmed Sales Order S00008 & Tax Calculation (Normal Flow)
   Expected: Document S00008; Net Rp250.000; PPN Rp27.500; Gross Rp277.500; Exposure C01 Rp1.399.500
   Actual  : Document S00008; Net Rp250.000; PPN Rp27.500; Gross Rp277.500; Exposure C01 Rp1.399.500

[✅ PASS] T-10 - Discount Approval Workflow > 10% (SR-07/SR-08) & Line Locking (SR-12) (Rule / Edge Case)
   Expected: Sales confirm without Finance approval blocked -> Status "To Approve". Post-approval: Confirmed & lines locked.
   Actual  : Sales confirm blocked: true. Post-approval: Confirmed. Line editing locked: true.

[✅ PASS] T-28-SUB - Employee Privacy & Role Boundary Check (SR-15) (Security / Rule)
   Expected: HR role has exclusive access to employee_private_records. Sales role excluded.
   Actual  : Sales role: Sales (Blocked from private phones/emails). HR role: HR (Allowed).

HASIL KESELURUHAN: 7 / 7 PENGUJIAN LULUS (100% SUCCESS RATE)
```

---

## 6. Catatan Penggunaan AI & Refleksi Akademik

### Refleksi 1: Resolusi Konflik Paling Kritis
Konflik paling kritis yang diselesaikan oleh M01 adalah **SR-01 (Keinginan Pemilik: Langsung Tagih Saat SO Konfirmasi)** versus **SR-02 (Prinsip Keuangan: Tagih Hanya Sesuai Barang Diterima)**.
- **Nilai Bisnis yang Dikorbankan:** Kecepatan arus kas penarikan dana di muka.
- **Pihak yang Dirugikan:** Hendra Widodo (Pemilik Perusahaan).
- **Mitigasi:** Diterapkannya kontrol eksposur kredit ketat (`SR-11`) dan percepatan *dispatch lead-time* gudang menjadi maksimal 3 hari kerja. Hal ini mematuhi standar PSAK 72 (pengakuan pendapatan hanya saat kewajiban pelaksanaan telah dipenuhi), mencegah risiko pembatalan sepihak, faktur pajak fiktif, serta retur berantai.

### Refleksi 2: Deteksi Halusinasi / Bias AI Coding Tool
- **Temuan Kasus:** Pada saat pembuatan fitur konfirmasi Quotation dengan diskon 15%, AI menghasilkan tombol *Confirm* yang langsung mengubah status order menjadi *Confirmed* tanpa memvalidasi role atau persetujuan pihak Finance. Antarmuka tampak cantik dan mulus, namun mencederai aturan tata kelola internal **SR-08**.
- **Langkah Perbaikan:** M01 menambahkan inspeksi *business rule* di `sales_crm_service.js`, mendeteksi jika ada baris diskon di atas 10%, membelokkan status pesanan ke `To Approve`, dan memblokir konfirmasi hingga user dengan role Finance melakukan *sign‑off* approval.
- **Bukti Regresi:** Diuji secara otomatis dan tervalidasi melalui test case `T-10`.

---
## 7. Hasil Uji Otomatis Modul M02 (Purchase & Inventory)

Jalankan `npm run test02` untuk memverifikasi keluaran berikut:

```

[✅ PASS] T-05 - Create Purchase Order PO00008 (F-07) (Normal Flow)
   Expected: Document PO00008; Net Rp640.000; PPN 11% Rp70.400; Gross Rp710.400
   Actual  : Document PO00008; Net Rp640.000; PPN Rp70.400; Gross Rp710.400
   Details : Sequential PO ID generated and effective 11% tax applied.
------------------------------------------------------------
[✅ PASS] T-06 - Goods Receipt & Stock Interim Journal Clearing (F-08) (Normal Flow)
   Expected: WH-JKT stock P05 +20 units; Stock Interim (1350) Credit = Rp640.000
   Actual  : WH-JKT stock P05: 50 units; Stock Interim Credit: Rp640.000
   Details : Goods received into physical inventory with transient interim journal recorded.
------------------------------------------------------------
[✅ PASS] T-11 - Enforce Anti-Negative Stock Ceiling (SR-04 / F-17) (Rule / Edge Case)
   Expected: Blocked per SR-04: Upaya pengeluaran melebihi stok ditolak.
   Actual  : Blocked: "Aturan Bisnis SR-04 Violasi: Stok fisik tidak mencukupi di WH-JKT! Diminta:..."
   Details : Prevents negative stock anomalies across warehouses.
------------------------------------------------------------
[✅ PASS] T-12 - FEFO Lot Picking Engine (F-12) (Normal Flow)
   Expected: Lot LOT-TEA-01 taken 15 units (earliest exp); Lot LOT-TEA-02 taken 10 units
   Actual  : Allocated 25 units via 2 lots: LOT-TEA-01(15), LOT-TEA-02(10)
   Details : Earliest expiry lot automatically prioritized.
------------------------------------------------------------
[✅ PASS] T-13 - Internal Stock Transfer Between Warehouses (F-11) (Normal Flow)
   Expected: WH-JKT reduced by 10 (to 50); HUB-BDG increased by 10 (to 30); Company Total remains 80
   Actual  : WH-JKT: 50, HUB-BDG: 30, Total Company: 80
   Details : Stock movement tracked accurately without changing total company valuation.
------------------------------------------------------------

```
Jalankan `npm run checksum` untuk memverifikasi keluaran berikut:

```
============================================================
ACCEPTANCE TEST M02 RESULTS: 5 / 5 PASSED (100%)
============================================================

(Seed Baseline Checksum Report)

Jalankan `npm checksum` untuk memverifikasi keluaran berikut:
============================================================
PT ARUNIKA DISTRIBUSI CEPAT - SEED BASELINE CHECKSUM REPORT
============================================================

Total SKU Products        : 16 (Expected: 16)
Total On Hand Company     : 880 unit (Expected: 880)
Total On Hand WH-JKT      : 665 unit (Expected: 665)
Total On Hand HUB-BDG     : 215 unit (Expected: 215)
Total Reserved (S00007)   : 8 unit (Expected: 8)
Total Stock Valuation     : Rp23.095.000 (Expected: Rp23.095.000)
Ledger Total Debit        : Rp44.595.000
Ledger Total Credit       : Rp44.595.000
Ledger Balance Status     : ✅ BALANCED (Rp44.595.000)

HASIL AKHIR CHECKSUM      : PASSED 100%
============================================================

```
## 08. Rencana Deployment & Kebijakan Operasional (M4-W1-05)

Sesuai dengan ketentuan Batasan Teknis, Jadwal Target Mingguan, dan Deployment pada Handout §8 & §9, berikut adalah arsitektur dan strategi rilis aplikasi PT Arunika Distribusi Cepat:

### 1. Arsitektur & Platform Deployment
- **Cloud Hosting Provider:** Render, Railway, atau Fly.io (Standard Node.js Runtime Environment).
- **URL Publik Aplikasi:** `[https://arunika-fmcg-erp.onrender.com](https://arunika-fmcg-erp.onrender.com)` *(Contoh tautan rilis publik untuk dosen dan penguji)*.
- **Persistensi Data:** Menggunakan penyimpanan file JSON lokal persisten (`data_store.json`) untuk memastikan seluruh mutasi dokumen (*Sales, Purchase, Inventory, Accounting*) tetap aman dan tersimpan utuh meskipun terjadi *restart* server atau *refresh* peramban.

### 2. Protokol Pemulihan Basis Data (Seed Reset & Fixture)
Untuk memenuhi aturan pengujian independen di mana setiap skenario `T-xx` harus dimulai dari *clean baseline*:
- Sistem dilengkapi mekanisme reset otomatis melalui fungsi `store.resetToSeed()`.
- Menyediakan endpoint pengujian `POST /api/fixture/reset` untuk mengembalikan kondisi basis data seketika ke baseline awal (16 SKU produk, 880 unit total stok, dan saldo pembukaan akuntansi yang seimbang).

### 3. Kredensial Akun Uji Lintas-Peran (Multi-Role Test Accounts)
Aplikasi menerapkan kontrol akses berbasis peran (*Role-Based Access Control*) yang memetakan identitas pegawai aktif (*Employee*) ke akun pengguna (*User Account*):

| Username | Peran (*Role*) | Pegawai Terhubung (*Employee*) | Password Uji | Hak Akses & Cakupan Modul |
| :--- | :--- | :--- | :--- | :--- |
| `sales_user` | Sales | E04 (Dini Pratiwi) | `Demo2026!` | CRM Leads, Quotation, Sales Order, dan Manajemen Kontak Pelanggan. |
| `sales_user2` | Sales | E05 (Farhan Saputra) | `Demo2026!` | CRM Leads, Quotation, Sales Order, dan Manajemen Kontak Pelanggan. |
| `warehouse_mgr` | Warehouse | E01 (Sari Prameswari) | `Demo2026!` | Stock Overview, Validasi Delivery Order / Goods Receipt, Transfer Antar Gudang. |
| `warehouse_user` | Warehouse | E02 (Arif Maulana) | `Demo2026!` | Operasional gudang harian dan entry data fisik barang masuk. |
| `purchase_user` | Purchase | E07 (Rita Kusuma) | `Demo2026!` | Permintaan Pembelian (RFQ), Purchase Order, dan Katalog Vendor Pricelist. |
| `finance_user` | Finance | E06 (Made Wirawan) | `Demo2026!` | Customer Invoice, Vendor Bill, Register Payment, General Ledger. |
| `hr_user` | HR | E08 (Melati Yusuf) | `Demo2026!` | Direktori Karyawan, Manajemen Departemen, dan Proteksi Data Privat (T-28). |
| `owner_user` | Owner | *Executive Access* | `Demo2026!` | Dashboard Operasional Eksekutif (KPI Total Stok, AR/AP, Bank) & Full Audit Log. |
---


## 9. Repository Overview (optional)

This repository contains the source code for the **Arunika FMCG ERP System** prototype, focusing on the Sales & CRM module (M01). Other modules (Purchase, Inventory, Employees, Accounting) will be developed by the remaining team members.

---

## 10. Technology Stack & Platform Decisions

> References: Handout §8 (Technical Constraints), §10 (Deployment & Grading Requirements)

### Backend

| Layer | Technology | Rationale |
| :--- | :--- | :--- |
| **Framework** | Laravel 10 (PHP 8.1+) | Full MVC, Eloquent ORM, Artisan CLI, Sanctum auth |
| **Application Server** | PHP built-in / Laravel Artisan `serve` | Zero-config local dev; swappable for Apache/Nginx on deploy |
| **Auth & RBAC** | `CheckRole` middleware + `User::role` column | Enforces per-route role gates (Sales, Warehouse, Finance, Purchase, HR, Owner) with automatic `access_denied` audit entry on rejection |
| **Business Logic** | `AccountingService` + domain Model methods | SR-02 (invoice only delivered qty), SR-06 (3-way match), SR-13 (credit note ceiling), SR-16 (idempotency key), SR-20 (overpayment guard) |

### Database

| Layer | Technology | Notes |
| :--- | :--- | :--- |
| **Local dev** | SQLite (`database/database.sqlite`) | Zero-install; all 18 application tables live here |
| **ORM** | Laravel Eloquent | Models for all 18 tables; `$primaryKey`, `$keyType`, `$incrementing` set correctly for string PKs (C01, P01, WH-JKT…) |
| **Schema + Seed** | `php artisan db:master-seed` (`RunFmcgMasterSeed.php`) | Single command drops, recreates, and repopulates the full Handout §4 baseline: 880 units, Rp23.095.000 stock value, Rp44.595.000 balanced opening journals |
| **Production** | MySQL / PostgreSQL compatible | Laravel driver swap via `.env`; no SQLite-specific queries used |

### Frontend & UI

| Layer | Technology | Notes |
| :--- | :--- | :--- |
| **Templating** | Laravel Blade | Server-side rendering; shared layouts, components, partials |
| **CSS Framework** | Tailwind CSS | Utility-first; configured to emulate Odoo 17 Desktop Browser aesthetic |
| **UI Pattern** | Odoo 17 Desktop Browser | App Switcher top-nav, List / Kanban / Form views, status-bar breadcrumb, smart buttons, log-notes chatter |
| **JS** | Vanilla JS (minimal) | Form interactions, inline confirmation dialogs; no build pipeline required |

### Deployment

| Target | Platform | Details |
| :--- | :--- | :--- |
| **Public URL** | Render / Railway / Fly.io | Standard PHP/Laravel runtime; accessible to graders without VPN |
| **Database reset** | `php artisan db:master-seed` | Restores clean §4 baseline for independent test-case execution |
| **Credentials** | See Section 8 credential table | All test accounts: password `Demo2026!` |

---

## 11. System Architecture Diagram

```mermaid
flowchart TD
    %% ── CLIENT LAYER ──────────────────────────────────────────────────────
    subgraph CLIENT ["Client Layer — Odoo 17 Browser UI (Blade + Tailwind)"]
        direction LR
        LIST["List View\n(paginated tables)"]
        KANBAN["Kanban View\n(CRM pipeline)"]
        FORM["Form View\n(document detail +\nsmart buttons +\nlog notes)"]
    end

    %% ── SECURITY & AUDIT LAYER ────────────────────────────────────────────
    subgraph SECURITY ["Security & Audit Control Layer"]
        RBAC["CheckRole Middleware\n(Sales · Warehouse · Finance\nPurchase · HR · Owner)"]
        IDEM["Idempotency Checker\nJournalEntry::alreadyPosted()\n(SR-16 double-post guard)"]
        AUDIT["Immutable Audit Log\nAuditLog::record()\nAll state changes captured"]
    end

    %% ── APPLICATION CORE ──────────────────────────────────────────────────
    subgraph APP ["Application Core (Laravel Controllers + Services + Models)"]
        direction TB
        SALES["Sales & CRM Module\nF-01 F-02 F-04 F-05 F-06 F-10\nLead → Quotation → SO\nCredit Limit · Discount Approval\nPrice Lock SR-12"]
        PURCH["Purchase & Inventory Module\nF-03 F-07 F-08 F-11 F-12 F-17\nRFQ → PO → Receipt\nFEFO Lot · Anti-Negative Stock SR-04\nStock Transfer"]
        ACCT["Accounting & Invoicing Module\nF-09 F-13 F-14 F-15 F-16\nAccountingService\nCustomer Invoice · Vendor Bill\nCredit Note · Payment · General Ledger\nPPN 11% SR-16 SR-20"]
        HR["Employees & HR Module\nF-18 F-19\nEmployee Directory\nPrivate Records SR-15\nSign-off Validation SR-17 SR-18"]
    end

    %% ── DATABASE LAYER ────────────────────────────────────────────────────
    subgraph DB ["Database Layer — SQLite (dev) / MySQL-PostgreSQL (prod)"]
        direction LR
        CORE_T["Core Tables\nusers · departments · employees\nemployee_private_records\naudit_logs"]
        CONTACTS["Contact Tables\ncontacts_customers · contacts_vendors\ntax_rates · payment_terms"]
        INVENTORY_T["Inventory Tables\nproducts · vendor_pricelist\nwarehouses_locations\nopening_stock · product_lots"]
        ACCT_T["Accounting Tables\nchart_of_accounts · invoices\ninvoice_items · journal_entries"]
        SEED["Baseline Seed\n(php artisan db:master-seed)\n880 units · Rp23.095.000\nRp44.595.000 balanced journals"]
    end

    %% ── CONNECTIONS ───────────────────────────────────────────────────────
    CLIENT -->|HTTP Request| RBAC
    RBAC -->|Authorized| APP
    RBAC -->|Denied → 403 + audit_log| AUDIT
    APP --> IDEM
    APP --> AUDIT
    APP --> DB
    AUDIT --> DB
```

---

## 12. Data Flow & End-to-End Business Lifecycles

### Flow 1 — Order to Cash (O2C)

```mermaid
flowchart TD
    L["Lead L00003\nPartner: C03\nExp. Revenue: Rp500.000"]
    O["Opportunity\nStage: Qualified\nSR-19: partner relation locked"]
    Q["Draft Quotation\nSKU · Qty · Unit Price\nPPN 11% calculated per line"]
    DC{"Discount > 10%?\nSR-07 / SR-08"}
    TA["Status: To Approve\nBlocked until Finance sign-off"]
    CC{"Credit Exposure Check\nSR-11\n(open_ar + S00007 + new gross)\nvs credit_limit"}
    BLK["BLOCKED\nError message + audit log"]
    SO["Confirmed Sales Order\nUnique sequence S000XX\nPrices & lines locked SR-12"]
    RES["Stock Reservation\nopening_stock.reserved += qty\nAvailable qty decremented SR-22"]
    DO["Delivery Order\nPick FEFO lots (P03) SR-07\nValidate sign-off: Active WH employee SR-17\nStatus → Done"]
    SM["Stock Move Journal\nDr COGS 5100 / Cr Inventory 1300\nPerpetual standard cost SR-03"]
    CI["Customer Invoice\nQty = Delivered qty only SR-02\nINV-XXXXX · Net · PPN · Gross"]
    AR["AR Journal Posted\nDr AR 1100 / Cr Sales 4000\nDr AR 1100 / Cr VAT Output 2100\nSR-16 idempotency key"]
    PAY["Register Payment\nDr Bank 1000 / Cr AR 1100\nOutstanding decremented SR-20\nStatus → Paid if fully settled"]

    L -->|Convert| O
    O -->|Generate Quotation| Q
    Q --> DC
    DC -- Yes --> TA
    TA -->|Finance Approves| CC
    DC -- No --> CC
    CC -- Exceeds Limit --> BLK
    CC -- Within Limit --> SO
    SO --> RES
    RES --> DO
    DO --> SM
    DO --> CI
    CI --> AR
    AR --> PAY
```

---

### Flow 2 — Procure to Pay (P2P)

```mermaid
flowchart TD
    RFQ["Request for Quotation RFQ\nVendor from contacts_vendors\nSKU from vendor_pricelist SR-21"]
    PO["Confirmed Purchase Order\nUnit price locked at confirm SR-21\nStatus: Confirmed\nRelated Receipt = 0, Bill = 0"]
    RCV["Goods Receipt\nPhysical count vs PO qty"]
    INSP{"Inspection\nAccepted qty / Rejected qty"}
    ACC["Accepted Qty Recorded\nopening_stock.on_hand += accepted\nLot created if lot-tracked SR-07"]
    REJ["Rejected Qty\nReturned to vendor\nNo stock move generated"]
    INT["Stock Interim Journal\nDr Stock Interim 1350\nCr AP 2000\n(provisional — before bill)"]
    VB["Vendor Bill\nQty = Accepted receipt qty only SR-06\n3-way match PO × Receipt × Bill\nBILL-XXXXX"]
    APJ["AP Journal Posted\nDr Inventory 1300 / Cr AP 2000 net\nDr VAT Input 1400 / Cr AP 2000 tax\nSR-16 idempotency key\nInterim 1350 cleared"]
    PAY["Register Payment\nDr AP 2000 / Cr Bank 1000\nOutstanding decremented SR-20\nStatus → Paid if fully settled"]

    RFQ -->|Vendor confirms price| PO
    PO -->|Goods arrive| RCV
    RCV --> INSP
    INSP -- Accepted --> ACC
    INSP -- Rejected --> REJ
    ACC --> INT
    INT -->|Match to PO| VB
    VB --> APJ
    APJ --> PAY
```

---

### Flow 3 — FMCG Special Controls

```mermaid
flowchart TD
    subgraph FEFO ["SR-07 — FEFO Lot Selection (Product P03 · BEV-TEA-C24-LCH)"]
        direction LR
        REQ["Delivery Order\nneeds 25 karton of P03"]
        L1["LOT-TEA-01\n15 qty · WH-JKT\nexp 2026-11-15 ← EARLIEST"]
        L2["LOT-TEA-02\n20 qty · WH-JKT\nexp 2027-02-28"]
        L3["LOT-TEA-03\n15 qty · HUB-BDG\nexp 2027-03-15"]
        PICK["FEFO Pick Result\n→ LOT-TEA-01: 15 units (exhausted)\n→ LOT-TEA-02: 10 units (partial)\nTotal allocated: 25 units"]
        REMAIN["Remaining after pick\nLOT-TEA-01: 0 (consumed)\nLOT-TEA-02: 10 remaining\nLOT-TEA-03: 15 untouched"]
        REQ --> L1 & L2 & L3
        L1 -->|"Sort by expiry_date ASC\nProductLot::fefoLotsFor()"| PICK
        L2 --> PICK
        PICK --> REMAIN
    end

    subgraph SIGNOFF ["SR-17 / SR-18 — Employee Active Status Gate"]
        direction LR
        ATTEMPT["Sign-off attempt\non Delivery / Receipt"]
        CHK{"Employee::canSignOff()\nstatus == Aktif AND\ndept == D-WH AND\nmapped_user_id NOT NULL"}
        OK["Sign-off Accepted\nDocument → Done\nAudit log: signed_off"]
        BLOCK["Sign-off REJECTED\nAudit log: sign_off_rejected\nExample: E03 Naya Putri (Nonaktif)\ncannot sign off RCV-ATTEMPT-001"]
        ATTEMPT --> CHK
        CHK -- Passes --> OK
        CHK -- Fails --> BLOCK
    end
```

---
