# M1-W1-03 · Repository & Aturan Commit
**PT Arunika Distribusi Cepat — ERP FMCG | Group 02 · Kelas 24SI2**
**Penyusun:** M1 — Nadine Valencia Cicero | **Koordinasi teknis:** M2 | **Status:** Selesai ✅

---

## Ringkasan

Dokumen ini merangkum kepemilikan modul, aturan commit, dan status repository yang dibutuhkan sebagai bukti checkpoint Minggu 1 (Handout §8, §10).

---

## Kepemilikan Modul (Module Ownership Map)

| Anggota | Modul | Controllers | Service | Views | Route Prefix |
|---------|-------|------------|---------|-------|-------------|
| **M1** | Sales & CRM | `Sales/LeadController`<br>`Sales/QuotationController`<br>`Sales/SalesOrderController` | `SalesCrmService` | `resources/views/sales/` | `/crm`, `/sales` |
| **M2** | Purchase & Inventory | `Purchase/RfqController`<br>`Purchase/PurchaseOrderController`<br>`Inventory/ReceiptController`<br>`Inventory/DeliveryController` | `PurchaseService`<br>`InventoryService` | `resources/views/purchase/`<br>`resources/views/inventory/` | `/purchase`, `/inventory` |
| **M3** | Employees & HR | `EmployeeController` ✅ (exists) | — | `resources/views/employees/` ✅ | `/employees` |
| **M4** | Accounting & Invoicing | `AccountingController` ✅ (exists) | `AccountingService` ✅ (exists) | `resources/views/accounting/` | `/accounting` |

> Perubahan kepemilikan wajib ditulis di README dan Role Assignment Sheet diperbarui.

---

## Format Pesan Commit

```
[Mx][F-xx] tipe: deskripsi singkat (≤72 karakter)
```

**Tipe yang valid:** `feat` · `fix` · `refactor` · `test` · `docs` · `style` · `chore`

**Contoh:**
```
[M1][F-05] feat: pricing engine dengan PPN 11% dan nomor sekuens S000XX
[M1][F-10] feat: credit exposure ceiling SR-11 hard block
[M2][F-07] feat: FEFO lot allocation untuk P03 BEV-TEA-C24-LCH
[M4][F-14] feat: journal entry idempotent DR HPP / CR Persediaan
[M1][docs] docs: Decision Log konflik SR-01/02 dan SR-07/08
```

---

## Aturan Branch

```
main          → production-ready; hanya via PR yang sudah ditinjau
develop       → integrasi semua anggota
feature/F-xx-nama  → kerja fitur individual

Contoh:
  feature/F-05-quotation-to-so
  feature/F-07-reservation-stock
  feature/F-14-ledger-cogs
  fix/F-09-invoice-qty-guard
```

**Dilarang force-push ke `main`. Konflik merge diselesaikan oleh penulis perubahan.**

---

## Checklist Repository Minggu 1

| Item | Penanggung Jawab | Status |
|------|-----------------|--------|
| Repository dibuat dan dapat diakses semua anggota | M2 (teknis), M1 (koordinasi) | ✅ Selesai |
| README berisi cara menjalankan | M4 | ✅ Selesai |
| README berisi diagram arsitektur | M2 | ✅ Selesai |
| README berisi kepemilikan modul | M1 | ✅ Selesai |
| Setiap anggota punya minimal 1 commit | Semua | Cek commit history |
| `.gitignore` mengecualikan `.env`, `database.sqlite` produksi | M2 | ✅ Selesai |
| Branch convention disepakati dan didokumentasikan | M1, M2 | ✅ Dokumen ini |

---

## Catatan Penamaan Berkas Penyerahan

Sesuai Handout §10, semua berkas penyerahan menggunakan format:

```
Group02_24SI2_FMCG_ItemName
```

Contoh:
- `Group02_24SI2_FMCG_TestResults.xlsx`
- `Group02_24SI2_FMCG_DecisionLog.pdf`
- `Group02_24SI2_FMCG_TeamAgreement.xlsx`
- `Group02_24SI2_FMCG_RoleAssignmentSheet.xlsx`

---

*Koordinasi teknis: M2 (arsitektur); kepemilikan modul: M1 (PO/PM)*
