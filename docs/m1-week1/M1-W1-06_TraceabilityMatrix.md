# M1-W1-06 · SR Priority & Traceability Matrix
**PT Arunika Distribusi Cepat — ERP FMCG | Group 02 · Kelas 24SI2**
**Penyusun:** M1 — Nadine Valencia Cicero | **Status:** Selesai ✅

---

## Keterangan

Matriks ini memetakan hierarki dari **System Requirement (SR)** → **Fitur (F)** → **Layar UI (S)** → **Test Case (T)** lengkap dengan prioritas.

| Prioritas | Arti |
|-----------|------|
| **Must** | Wajib selesai (Minggu 2–3) — kegagalan = nilai merah |
| **Should** | Dikerjakan setelah seluruh Must selesai (Minggu 3) |
| **Optional** | Tidak menggantikan Must; boleh ditunda dengan pembelaan |

---

## Matriks Lengkap SR → F → S → T

| SR | Stakeholder | Prioritas SR | Fitur (F) | Prioritas F | Layar (S) | Test Cases (T) | Modul Owner |
|----|------------|-------------|-----------|-------------|-----------|----------------|-------------|
| SR-01 | Hendra Widodo | Must | F-09 | Must | S-10 | T-19, T-20 | M4 |
| SR-02 | Made Wirawan | Must | F-09 | Must | S-10 | T-12, T-19, T-20 | M4 |
| SR-03 | Dini Pratiwi | Must | F-05, F-07 | Must | S-05, S-06 | T-09, T-11, T-12 | M1, M2 |
| SR-04 | Sari Prameswari | Must | F-07, F-08 | Must | S-06, S-07 | T-11, T-12, T-13, T-14 | M2 |
| SR-05 | Rita Kusuma | Should | F-13 | Must | S-11 | T-21, T-22 | M4 |
| SR-06 | Made Wirawan | Must | F-12, F-13, F-14 | Must | S-09, S-11, S-13 | T-17, T-21, T-22 | M2, M4 |
| SR-07 | Dini Pratiwi | Must | F-06 | Must | S-05 | T-09, T-10 | M1 |
| SR-08 | Made Wirawan | Must | F-06 | Must | S-05 | T-09, T-10 | M1 |
| SR-09 | Bagus Hartanto | Must | F-03 | Must | S-03 | T-06 | M2 |
| SR-10 | Rita Kusuma | Must | F-03 | Must | S-03 | T-05, T-06 | M2 |
| SR-11 | Made Wirawan | Must | F-02, F-10 | Must | S-02 | T-03, T-04, T-09, T-33 | M1 |
| SR-12 | Dini Pratiwi | Must | F-05, F-06 | Must | S-05 | T-09, T-10 | M1 |
| SR-13 | Hendra Widodo | Must | F-16 | Must | S-16 | T-31, T-32 | M4 |
| SR-14 | Sari Prameswari | Must | F-17 | Must | S-07 | T-13, T-14 | M2 |
| SR-15 | Melati Yusuf | Must | F-01, F-18 | Must | S-01, S-14, S-15 | T-01, T-02, T-27, T-28 | M3 |
| SR-16 | Made Wirawan | Must | F-14, F-21, F-22 | Must | S-13, S-18 | T-19, T-24, T-25, T-26, T-35, T-36 | M4 |
| SR-17 | Sari Prameswari | Must | F-08, F-12, F-19 | Must | S-06, S-09, S-15 | T-11, T-17, T-18, T-29, T-30 | M2, M3 |
| SR-18 | Melati Yusuf | Must | F-18, F-19 | Must | S-14, S-15 | T-18, T-27, T-29, T-30 | M3 |
| SR-19 | Bagus Hartanto | Should | F-04, F-20, F-21 | Should/Must | S-04, S-17, S-18 | T-07, T-33, T-34, T-35 | M1, M4 |
| SR-20 | Made Wirawan | Must | F-15 | Must | S-12 | T-23, T-24 | M4 |
| SR-21 | Rita Kusuma | Must | F-11 | Must | S-08 | T-15, T-16 | M2 |
| SR-22 | Sari Prameswari | Must | F-07, F-17 | Must | S-07 | T-11, T-12, T-13, T-14 | M2 |
| SR-09, SR-19 *(optional)* | — | Optional | F-23 | Optional | S-17 | — | M4 |

---

## Ringkasan per Modul Owner

### M1 — Sales / CRM
| SR | F | S | T |
|----|---|---|---|
| SR-03, SR-07, SR-08, SR-11, SR-12, SR-19 | F-02, F-04, F-05, F-06, F-10 | S-02, S-04, S-05 | T-03, T-04, T-07, T-08, T-09, T-10 |

### M2 — Purchase / Inventory
| SR | F | S | T |
|----|---|---|---|
| SR-03, SR-04, SR-06, SR-09, SR-10, SR-14, SR-17, SR-21, SR-22 | F-03, F-07, F-08, F-11, F-12, F-17 | S-03, S-06, S-07, S-08, S-09 | T-05, T-06, T-11–T-18 |

### M3 — Employees
| SR | F | S | T |
|----|---|---|---|
| SR-15, SR-17, SR-18 | F-01, F-18, F-19 | S-01, S-14, S-15 | T-01, T-02, T-27–T-30 |

### M4 — Accounting / Invoicing
| SR | F | S | T |
|----|---|---|---|
| SR-01, SR-02, SR-05, SR-06, SR-13, SR-16, SR-19, SR-20 | F-09, F-13, F-14, F-15, F-16, F-20, F-21, F-22 | S-10, S-11, S-12, S-13, S-16, S-17, S-18 | T-19–T-26, T-31–T-36 |

---

## Urutan Implementasi yang Direkomendasikan

Berdasarkan dependency chain (upstream harus selesai sebelum downstream):

```
Minggu 2 (Must — inti):
  F-03 (Katalog SKU)
    └─► F-02 (Customer/Vendor + Credit)
         └─► F-05, F-06 (SO/Diskon)
              └─► F-07, F-08 (Reservation/DO)
  F-11 (PO)
    └─► F-12 (Receipt)

Minggu 2 (paralel):
  F-01 (Home/RBAC) — M3
  F-18, F-19 (Employees/Sign-off) — M3
  F-04 (CRM Lead/Pipeline) — M1

Minggu 3 (Must — integrasi):
  F-09 (Customer Invoice)  ← requires F-07/F-08 Done
  F-13 (Vendor Bill)       ← requires F-12 Done
  F-14 (Ledger/COGS)       ← requires F-09/F-13
  F-15 (Payment)           ← requires F-09/F-13
  F-16 (Return/CN)         ← requires F-09
  F-17 (Transfer)          ← requires F-07
  F-10 (Credit Limit)      ← requires F-02/F-05

Minggu 3 (Should):
  F-20 (Dashboard)         ← requires F-14 Posted docs
  F-21 (Audit/Relations)   ← requires all modules
  F-22 (Doc Sequence)      ← throughout all modules

Minggu 4 (Optional):
  F-23 (FEFO Notif + Audit Export)
```

---

## Coverage Check: 22 SR → 22 Fitur (F-01 s.d. F-22) → 18 Layar (S-01 s.d. S-18) → 36 Test (T-01 s.d. T-36)

| Item | Jumlah di Handout | Terpetakan di Matriks | Status |
|------|-------------------|----------------------|--------|
| SR | 22 | 22 | ✅ 100% |
| Fitur (F) | 22 (F-01–F-22) + 1 Optional (F-23) | 23 | ✅ 100% |
| Layar (S) | 18 (S-01–S-18) | 18 | ✅ 100% |
| Test Case (T) | 36 (T-01–T-36) | 36 | ✅ 100% |

---

*Penyusun: M1 · Referensi: Handout §2, §6, §7, §9 · Review: M2, M3, M4*
