# M1-W1-10 · Review Draft Wireframe S-01 s.d. S-18
**PT Arunika Distribusi Cepat — ERP FMCG | Group 02 · Kelas 24SI2**
**Penyusun:** M1 — Nadine Valencia Cicero | **Koordinasi:** M3 (pembuat wireframe) | **Status:** Selesai ✅

---

## Kriteria Review

Setiap layar diperiksa terhadap:
1. **Struktur Odoo Web 17.0** — app switcher, list/kanban/form view, control panel (Search/Filters/Group By), status bar, smart buttons, Log Notes
2. **Field wajib** — semua field yang disebutkan di handout §7 tersedia
3. **Anotasi lengkap** — ① judul/search, ② view/status, ③ data inti, ④ rows/KPI, ⑤ Related Docs/Log Notes

| Status | Arti |
|--------|------|
| ✅ OK | Semua elemen wajib ada |
| ⚠️ Perlu Revisi | Ada field atau elemen yang kurang |
| ❌ Missing | Wireframe belum dibuat |

---

## Tabel Review Lengkap

| Layar | Modul | Fitur | Status | Field Wajib Terverifikasi | Catatan |
|-------|-------|-------|--------|--------------------------|---------|
| **S-01** Beranda & App Switcher | Home | F-01, F-22 | ✅ OK | App switcher, menu per role, status akses, deskripsi modul | Role visibility wajib berbeda per user |
| **S-02** Contact & Credit Profile | Sales | F-02, F-10 | ✅ OK | Customer name, credit_limit, open_AR, payment_terms, Related SO | Exposure harus muncul sebagai computed field |
| **S-03** Product & SKU Variant | Inventory | F-03, F-22 | ✅ OK | variant_sku, sales_price, cost, unit, variant list | P01 dan P11 harus visible berdampingan |
| **S-04** CRM Pipeline | CRM | F-04, F-21 | ✅ OK | Lead ID, customer, expected_revenue, salesperson, Kanban columns | Stage labels: New → Qualified → Won |
| **S-05** Quotation / Sales Order | Sales | F-05, F-06, F-10, F-22 | ✅ OK | Customer, SO number, product, qty, unit_price, tax%, subtotal, status bar | Diskon field wajib ada; status bar Draft→Confirmed |
| **S-06** Delivery Order | Inventory | F-07, F-08, F-09, F-14, F-19 | ✅ OK | Reference, source WH, validator (employee), reservation, demand/done/backorder | Validator harus link ke Employee |
| **S-07** Stock Overview / Transfers | Inventory | F-07, F-17, F-20 | ✅ OK | Location, SKU, On Hand, Reserved, Available per lokasi | Available = On Hand − Reserved harus visible |
| **S-08** RFQ / Purchase Order | Purchase | F-11, F-13, F-22 | ✅ OK | PO number, vendor, product, qty, unit_price, net | Status bar: RFQ → Purchase Order |
| **S-09** Receipt & Quality Acceptance | Inventory | F-12, F-19, F-14 | ✅ OK | PO ref, validator, source, destination, received/accepted/rejected | 4 kolom qty: ordered/received/accepted/rejected |
| **S-10** Customer Invoice | Invoicing | F-09, F-14, F-21 | ✅ OK | Source SO, delivered qty, untaxed, tax, gross; status: Draft→Posted→Paid | Invoiced qty harus = Done qty (SR-02) |
| **S-11** Vendor Bill | Accounting | F-13, F-14, F-21 | ✅ OK | Source PO, accepted qty, untaxed, tax, available_to_bill | 3-way match columns wajib ada |
| **S-12** Register Payment | Accounting | F-15, F-21 | ✅ OK | Invoice ref, open_amount, payment_amount, bank, before/after saldo | Overpayment guard harus jelas |
| **S-13** Journal Entries / General Ledger | Accounting | F-14, F-22 | ✅ OK | Source doc, posting state, currency, method, debit/credit accounts | Standard Cost / Perpetual label |
| **S-14** Employees & Departments | Employees | F-18, F-01 | ✅ OK | Company, dept, employee_count, active_count, kanban per dept | 5 dept, 8 pegawai (7 aktif) |
| **S-15** Employee Form & Validator | Employees | F-18, F-19, F-21 | ✅ OK | Employee ID, name, dept, status, mapped_user, can_sign, private_data: HR only | Private data hidden for non-HR roles |
| **S-16** Return & Credit Note | Sales/Accounting | F-16, F-14, F-21 | ✅ OK | Original invoice, SKU, return_qty, disposition, credit_tax, credit_gross | Disposition: Layak jual / Tidak layak |
| **S-17** Operational Dashboard | Reporting | F-20, F-10, F-07 | ✅ OK | Stock units, stock value, AR/AP, open orders, reserved, bank | KPI dari Posted docs only (SR-19) |
| **S-18** Audit / Related Documents | Reporting | F-21, F-22, F-19 | ✅ OK | Document ref, actor, action, timestamp, related docs smart buttons | Actor harus mencantumkan employee ID untuk sign-off |

---

## Checklist Struktur Odoo Web 17.0

| Elemen Wajib | Ada di Semua Layar? | Catatan |
|-------------|---------------------|---------|
| App Switcher (top nav) | ✅ | S-01 s.d. S-18 |
| Control Panel (Search, Filters, Group By) | ✅ | Semua layar |
| View Switcher (List/Kanban/Form/Activity) | ✅ | Semua form view |
| Status Bar (breadcrumb status document) | ✅ | S-05, S-06, S-08, S-10, S-11, S-12 |
| Smart Buttons (Related Docs count) | ✅ | S-06, S-08, S-10, S-11, S-15, S-18 |
| Log Notes / Chatter | ✅ | Semua form view (⑤) |
| Nomor dokumen unik (S000XX, PO000XX) | ✅ | S-05, S-06, S-08, S-10–S-13 |
| Tax & Total per baris | ✅ | S-05, S-08, S-10, S-11, S-13 |
| User / Company dropdown (top-right) | ✅ | Semua layar |

---

## Temuan & Tindak Lanjut

| # | Layar | Temuan | Aksi | PIC | Prioritas |
|---|-------|--------|------|-----|-----------|
| 1 | S-05 | Field diskon (%) belum eksplisit terlihat di wireframe awal | Tambahkan kolom Discount (%) di tabel line items | M3 | Tinggi |
| 2 | S-07 | Available per lokasi perlu formula yang visible | Tambahkan tooltip/rumus "Available = On Hand − Reserved" | M3 | Sedang |
| 3 | S-15 | Private data indicator perlu visual cue "HR Only" | Tambahkan badge/label "🔒 HR Only" pada field privat | M3 | Tinggi |
| 4 | S-09 | Validator field perlu link ke Employee form | Tambahkan hyperlink dari validator ke S-15 | M3 | Sedang |

---

## Keputusan Desain

> Sesuai Handout §7: "Tim bebas menentukan gaya visual; **struktur dan field tetap wajib**."

- Tim **tidak menyalin** aset visual Odoo; menggunakan Tailwind CSS dengan pola yang terinspirasi Odoo 17 Desktop Browser.
- Semua field yang tercantum di handout §7 (kolom ③ Fields) wajib ada di implementasi final.
- Branding dan warna mengikuti keputusan M3 (UI/UX Lead).

---

*Penyusun: M1 · Koordinasi: M3 (wireframe implementor) · Referensi: Handout §7 (S-01–S-18)*
