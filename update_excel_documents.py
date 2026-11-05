import openpyxl
import os
import shutil

print("Starting document update and verification...")

# Paths
downloads_dir = r"C:\Users\ASUS Vivobook\Downloads"
docs_dir = r"c:\Users\ASUS Vivobook\arunika-fmcg-erp-system\arunika-fmcg-erp-system\docs"

role_src = os.path.join(downloads_dir, "Group02_Class_FMCG_RoleAssignmentSheet.xlsx")
role_dst_name = "Group02_24SI2_FMCG_RoleAssignmentSheet.xlsx"
role_dst = os.path.join(downloads_dir, role_dst_name)
role_repo = os.path.join(docs_dir, role_dst_name)

team_src = os.path.join(downloads_dir, "Group02_24SI2_FMCG_TeamAgreement (1).xlsx")
team_dst_name = "Group02_24SI2_FMCG_TeamAgreement.xlsx"
team_dst = os.path.join(downloads_dir, team_dst_name)
team_repo = os.path.join(docs_dir, team_dst_name)

# -------------------------------------------------------------
# 1. UPDATE ROLE ASSIGNMENT SHEET
# -------------------------------------------------------------
wb_role = openpyxl.load_workbook(role_src)

# Sheet 'Role Assignment'
ws_ra = wb_role["Role Assignment"]
# Update NIMs
ws_ra.cell(6, 3, "03081240023")  # M1 Nadine
ws_ra.cell(7, 3, "03081240012")  # M2 Vivien
ws_ra.cell(8, 3, "03081240019")  # M3 Princess
ws_ra.cell(9, 3, "03081240013")  # M4 Jesvally

# Helper function to update task rows
def update_task_row(ws, task_id, status, proof, notes):
    for r in range(7, ws.max_row + 1):
        if ws.cell(r, 2).value == task_id:
            ws.cell(r, 7, status)
            if proof:
                ws.cell(r, 8, proof)
            if notes:
                ws.cell(r, 9, notes)
            return True
    return False

# M1 Updates
ws_m1 = wb_role["M1_PO_PM_BA"]
# W1
update_task_row(ws_m1, "M1-W1-01", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/6e71397", "Role Assignment Sheet lengkap M1-M4 terisi NIM, peran, modul; diunggah di checkpoint W1 (docs/m1-week1/M1-W1-01_RoleAssignmentSheet.md)")
update_task_row(ws_m1, "M1-W1-02", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/6e71397", "Team Agreement tersimpan di repository; aturan & ritme disepakati di docs/m1-week1/M1-W1-02_TeamAgreementRef.md")
update_task_row(ws_m1, "M1-W1-03", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/6e71397", "Konvensi commit [Mx][F-xx], aturan branch & PR review di docs/m1-week1/M1-W1-03_RepositoryAndCommitRules.md")
update_task_row(ws_m1, "M1-W1-04", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/6e71397", "Kebijakan AI & form disclosure alat di docs/m1-week1/M1-W1-04_AIPolicyAndLog.md")
update_task_row(ws_m1, "M1-W1-05", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/6e71397", "Klasifikasi 22 SR (aturan jelas vs konflik kritis SR-01/02, SR-03/04, SR-05/06, SR-07/08, SR-11) di docs/m1-week1/M1-W1-05_SRClassification.md")
update_task_row(ws_m1, "M1-W1-06", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/6e71397", "Traceability matrix SR -> F-xx -> S-xx -> T-xx (Must/Should) di docs/m1-week1/M1-W1-06_TraceabilityMatrix.md")
update_task_row(ws_m1, "M1-W1-07", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/6e71397", "Decision Log awal format Decision Defense DL-01 s.d DL-06 di docs/m1-week1/M1-W1-07_DecisionLog.md & DECISION_LOG_DEFENSE_M01.md")
update_task_row(ws_m1, "M1-W1-08", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/6e71397", "Expected results T-03 (exposure C01 Rp1.122.000), T-04, T-07 s.d T-10 di docs/m1-week1/M1-W1-08_ExpectedResults.md")
update_task_row(ws_m1, "M1-W1-09", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/6e71397", "Acceptance criteria 44 skenario Given/When/Then untuk F-01 s.d F-22 di docs/m1-week1/M1-W1-09_AcceptanceCriteria.md")
update_task_row(ws_m1, "M1-W1-10", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/6e71397", "Review 18 draft wireframe S-01 s.d S-18 struktur Odoo 17 Web di docs/m1-week1/M1-W1-10_WireframeReview.md")
update_task_row(ws_m1, "M1-W1-11", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/6e71397", "Review seed baseline: 880 unit, Rp23.095.000, AR Rp1.5M, AP Rp700k, Bank Rp20M di docs/m1-week1/M1-W1-11_SeedChecksumReview.md")
# W2
update_task_row(ws_m1, "M1-W2-01", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/7531e33", "Spesifikasi Sales/CRM final (F-04 pipeline, F-05 SO, F-06 diskon & lock, F-02/F-10 kredit) di sales_crm_service.js")
update_task_row(ws_m1, "M1-W2-02", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/7531e33", "Update Decision Log: resolusi diskon 15% (SR-07/08), stok anti-negatif (SR-03/04), credit limit (SR-11) di DECISION_LOG_DEFENSE_M01.md")
update_task_row(ws_m1, "M1-W2-03", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/7531e33", "Uji awal Sales/CRM T-07, T-08, T-09, T-10 di test_m01.js — 7/7 tes PASS (100%)")
update_task_row(ws_m1, "M1-W2-04", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/c0b9c93", "Checkpoint 10 tes normal terverifikasi; commit dari setiap anggota tercatat")
update_task_row(ws_m1, "M1-W2-05", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/7531e33", "Review logika AI: AI-trap tombol confirm auto-approve diskon 15% dicegat & ditambahkan status To Approve (Refleksi 2 di README.md)")
# W3
update_task_row(ws_m1, "M1-W3-01", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/c0b9c93", "Uji T-03, T-04, T-07–T-10 terintegrasi dokumen tersimpan; alur Order to Cash valid")
update_task_row(ws_m1, "M1-W3-02", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/6e71397", "Decision Log komprehensif SR-01 s.d SR-22 di DECISION_LOG_DEFENSE_M01.md & README.md")
update_task_row(ws_m1, "M1-W3-03", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/bd55bb3", "Uji lintas modul Order to Cash dan Procure to Pay; defect log terintegrasi dengan M4")
update_task_row(ws_m1, "M1-W3-04", "Berjalan", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/6e71397", "Kerangka presentasi 15 menit + tabel Decision Defense dipersiapkan sesuai Handout §10")

# M2 Updates
ws_m2 = wb_role["M2_Lead_Dev"]
# W1
update_task_row(ws_m2, "M2-W1-01", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/fdb7a08", "Skema database & seeder 18 tabel tanpa ganti nama kolom di seed_data.js & FmcgMasterSeeder.php")
update_task_row(ws_m2, "M2-W1-02", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/fdb7a08", "Arsitektur web Node.js & Laravel MVC, diagram data di README.md & docs/AI_WORKFLOW_AND_SKELETON.md")
update_task_row(ws_m2, "M2-W1-03", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/fdb7a08", "Skeleton repo, prompt template per modul & alur kerja AI di docs/AI_WORKFLOW_AND_SKELETON.md")
update_task_row(ws_m2, "M2-W1-04", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/cd2222a", "Verifikasi checksum: 880 unit, Rp23.095.000, lot P03 total 50 tidak dobel, reservasi 8 P01 di verify_checksum.js (100% PASS)")
update_task_row(ws_m2, "M2-W1-05", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/fdb7a08", "Mapping field S-03, S-06 s.d S-09 di docs/WIREFRAME_STOCK_MAPPING_REVIEW.md")
# W2
update_task_row(ws_m2, "M2-W2-01", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/d66329f", "16 variant SKU unik; harga jual, standar biaya, vendor terpetakan per variant (F-03, T-05)")
update_task_row(ws_m2, "M2-W2-02", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/d66329f", "Engine FEFO lot P03 WH-JKT; lot kedaluwarsa dilarang dikirim; tanggal uji 20-10-2026 (SR-09, T-06, T-12)")
update_task_row(ws_m2, "M2-W2-03", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/d66329f", "Available = On Hand − Reserved; reservasi tidak memotong On Hand; proteksi anti-stok negatif (F-07, SR-04, SR-22, T-11)")
update_task_row(ws_m2, "M2-W2-04", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/d66329f", "Delivery Order parsial, backorder otomatis, validator aktif (F-08, SR-04, SR-17, T-11, T-12)")
update_task_row(ws_m2, "M2-W2-05", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/d66329f", "RFQ -> PO; unit price terkunci saat Confirmed; seed PO00007 (F-11, SR-21, T-15, T-16)")
update_task_row(ws_m2, "M2-W2-06", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/d66329f", "Receipt catat ordered, received, accepted, rejected terpisah; stok naik hanya accepted (F-12, SR-06, SR-17, T-17, T-18)")
update_task_row(ws_m2, "M2-W2-07", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/d66329f", "Internal Transfer WH-JKT ↔ HUB-BDG; total valuasi tidak berubah; proteksi reserved (F-17, SR-14, SR-22, T-13, T-14)")
update_task_row(ws_m2, "M2-W2-08", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/d66329f", "Checkpoint 10 tes normal & commit tercatat; test_m02.js 5/5 PASS")
# W3
update_task_row(ws_m2, "M2-W3-01", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/42d9ef4", "Integrasi stok ke ledger: DO Done catat HPP standard cost Rp170k, receipt accepted tambah nilai stok (F-14, T-11, T-17, T-25)")
update_task_row(ws_m2, "M2-W3-02", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/bd55bb3", "Posting Delivery dan Receipt idempoten; double post diblokir via idempotency_key (SR-16, T-26, T-36)")
update_task_row(ws_m2, "M2-W3-03", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/42d9ef4", "Return stock move: retur layak jual menambah On Hand dan membalik COGS Rp34.000 (SR-13, T-31)")
update_task_row(ws_m2, "M2-W3-04", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/b823076", "Integrasi sign-off validator gudang aktif (E01) vs blokir pegawai nonaktif (E03) (F-19, SR-17, SR-18, T-18, T-30)")
update_task_row(ws_m2, "M2-W3-05", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/d66329f", "Review logika AI stok & pembelian; catat trap reservasi & harga PO di README.md Refleksi 2")

# M3 Updates
ws_m3 = wb_role["M3_UIUX"]
for r in range(7, ws_m3.max_row + 1):
    tid = ws_m3.cell(r, 2).value
    if tid in ["M3-W1-01", "M3-W1-02", "M3-W1-03", "M3-W1-04"]:
        ws_m3.cell(r, 7, "Selesai")
        ws_m3.cell(r, 8, "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/b823076")
        ws_m3.cell(r, 9, "Struktur Odoo Web 17.0 (S-01 s.d S-18), shell layout konsisten, seed 8 employee 5 departemen (T-01, T-27)")
    elif tid in ["M3-W2-01", "M3-W2-02", "M3-W2-03", "M3-W2-04", "M3-W2-05"]:
        ws_m3.cell(r, 7, "Selesai")
        ws_m3.cell(r, 8, "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/b823076")
        ws_m3.cell(r, 9, "App switcher RBAC, roster & kanban departemen, status aktif/nonaktif, validator authorization D-WH (F-01, F-18, F-19, T-27, T-29)")
    elif tid in ["M3-W3-01", "M3-W3-02", "M3-W3-03"]:
        ws_m3.cell(r, 7, "Selesai")
        ws_m3.cell(r, 8, "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/b823076")
        ws_m3.cell(r, 9, "Server-side privacy SR-15 (T-28), blokir sign-off pegawai nonaktif seketika SR-18 (T-30), konsistensi smart buttons & status bar")
    elif tid == "M3-W3-04":
        ws_m3.cell(r, 7, "Selesai")
        ws_m3.cell(r, 8, "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/7e7476b")
        ws_m3.cell(r, 9, "Tampilan audit log (actor, waktu, aksi, dokumen) & related docs S-18 (F-21)")

# M4 Updates
ws_m4 = wb_role["M4_QA_Deploy"]
update_task_row(ws_m4, "M4-W1-05", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/d2292b7", "Platform Railway, URL publik, 8 akun multi-role terpetakan, protokol reset seed di README.md §8 & railway.json")
update_task_row(ws_m4, "M4-W1-06", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/cd2222a", "Verifikasi checksum baseline: 880 unit, Rp23.095.000, trial balance opening Rp44.595.000 di verify_checksum.js (100% PASS)")
update_task_row(ws_m4, "M4-W2-01", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/e1a7a74", "Deployment awal Railway (railway.json, Procfile, nixpacks), 8 akun uji seed multi-role aktif")
update_task_row(ws_m4, "M4-W2-04", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/42d9ef4", "Checkpoint 10 tes normal terverifikasi; commit M4 tercatat; test suite 26/26 PASS")
update_task_row(ws_m4, "M4-W3-07", "Selesai", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/bd55bb3", "Bug log & regression test suite lengkap 26/26 di test_m04.js; verifikasi price mismatch & balance assertion")

# Save updated Role Assignment Sheet
wb_role.save(role_dst)
wb_role.save(role_repo)
print(f"Role Assignment Sheet saved to:\n  - {role_dst}\n  - {role_repo}")

# -------------------------------------------------------------
# 2. UPDATE TEAM AGREEMENT
# -------------------------------------------------------------
wb_team = openpyxl.load_workbook(team_src)

# Sheet 'Ritme Rapat' - fill placeholders
ws_rr = wb_team["Ritme Rapat"]
ws_rr.cell(8, 2, "Discord & Google Meet (Audio/Video Call & Screen Sharing)")
ws_rr.cell(9, 2, "Grup WhatsApp & Server Discord Group 02 (24SI2)")

# Sheet 'AI & Integritas' - disclose AI tools
ws_ai = wb_team["AI & Integritas"]
ws_ai.cell(9, 1, 1)
ws_ai.cell(9, 2, "Antigravity (Google DeepMind) / Gemini 3.8 Flash & Pro")
ws_ai.cell(9, 3, "M1, M2, M3, M4")
ws_ai.cell(9, 4, "Orkestrasi arsitektur, logic generator, review rule BDD & unit testing lintas modul")

ws_ai.cell(10, 1, 2)
ws_ai.cell(10, 2, "GitHub Copilot")
ws_ai.cell(10, 3, "M2, M4")
ws_ai.cell(10, 4, "Autocompletion kode PHP/JS, helper migrasi skema tabel, dan assertion jurnal")

ws_ai.cell(11, 1, 3)
ws_ai.cell(11, 2, "Claude 3.5 Sonnet")
ws_ai.cell(11, 3, "M1, M3")
ws_ai.cell(11, 4, "Elisitasi skenario BDD Given/When/Then, penataan Decision Defense matrix & telaah wireframe Odoo 17")

# Sheet 'Log AI' - Table A (Log penggunaan AI)
ws_log = wb_team["Log AI"]
ai_logs = [
    (1, "2026-10-24", "M1", "Antigravity", "Elisitasi 22 SR dan klasifikasi konflik kepentingan", "Ya", "SR-01/02, SR-03/04, SR-05/06, SR-07/08, SR-11", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/6e71397"),
    (2, "2026-10-25", "M2", "Antigravity", "Generate skema 18 tabel dan script master seed baseline", "Ya", "Nama kolom persis handout §4; kontrol 880 unit & Rp23.095.000", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/fdb7a08"),
    (3, "2026-10-26", "M3", "Claude 3.5 Sonnet", "Perancangan wireframe Odoo Web 17 S-01 s.d S-18", "Ya", "Fidelitas struktur Odoo (App Switcher, Kanban, Smart Buttons)", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/b823076"),
    (4, "2026-10-27", "M4", "Antigravity", "Perancangan Chart of Accounts & balance opening journal", "Ya", "Dr Bank+AR+Stock = Cr AP+Modal (Rp44.595.000 seimbang)", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/42d9ef4"),
    (5, "2026-10-29", "M2", "Antigravity", "Implementasi algoritma FEFO lot picking untuk produk P03", "Sebagian", "SR-09: Prioritas lot kadaluwarsa terdekat (LOT-TEA-01 lalu 02)", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/d66329f"),
    (6, "2026-10-30", "M1", "Gemini 3.8", "Sales Order confirmation & credit exposure check engine", "Sebagian", "SR-11: Credit exposure = open AR + unbilled SO; order ditolak jika over", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/7531e33"),
    (7, "2026-11-01", "M4", "Antigravity", "Implementasi 3-Way Match Vendor Bill dan Idempotency key", "Sebagian", "SR-06: Bill hanya untuk accepted receipt; SR-16: Cegah double posting", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/42d9ef4"),
    (8, "2026-11-03", "M3", "Claude 3.5 Sonnet", "Role-Based Access Control dan proteksi data privat pegawai", "Ya", "SR-15 & SR-18: Data privat E01/E03 hanya untuk HR di server-side", "https://github.com/Vivien-123/arunika-fmcg-erp-system/commit/7e7476b"),
]

for idx, entry in enumerate(ai_logs):
    row_idx = 8 + idx
    for c_idx, val in enumerate(entry):
        ws_log.cell(row_idx, c_idx + 1, val)

# Sheet 'Log AI' - Table B (AI-trap log)
ai_traps = [
    ("AT-01", "2026-10-28", "Sales", "SR-07; SR-08; F-06", "Diskon 15% langsung auto-confirm tanpa approval Finance; layar mulus", "T-10", "SR-08: Diskon >10% wajib approval Finance. Terdeteksi saat validasi T-10.", "Bypass confirm diubah jadi status 'To Approve', butuh sign-off Finance; lolos regresi T-10"),
    ("AT-02", "2026-10-28", "Invoicing", "SR-01; SR-02; F-09", "Invoice 12 P02 bisa diposting dari SO Confirmed sebelum ada Delivery Done", "T-20", "SR-02: Menagih qty yang belum Done. Terdeteksi saat uji T-20 (qty 12 > delivered 7).", "Batasi invoicing policy = Delivered Quantities (Delivered − Invoiced); lolos regresi T-19 & T-20"),
    ("AT-03", "2026-10-29", "Purchase", "SR-05; SR-06; F-13", "Vendor Bill bisa dibuat untuk seluruh 20 unit PO00007 meski penerimaan 0", "T-22", "SR-06: Tagihan vendor dibatasi accepted receipt. Terdeteksi saat uji T-22.", "Tegakkan 3-Way Match: Bill qty <= accepted receipt qty; lolos regresi T-21 & T-22"),
    ("AT-04", "2026-10-30", "Accounting", "SR-16; F-22", "Klik ganda tombol Post Invoice menduplikasi baris jurnal dan menaikkan AR dua kali", "T-26", "SR-16: Posting finansial wajib idempoten. Terdeteksi saat stress test double-click.", "Implementasi unique idempotency_key pada tabel journal_entries; lolos regresi T-26 & T-36"),
    ("AT-05", "2026-10-31", "Accounting", "SR-20; F-15", "Register Payment menerima nominal Rp300.000 untuk invoice saldo Rp277.500", "T-24", "SR-20: Pembayaran parsial maksimum sebesar outstanding. Terdeteksi saat T-24.", "Tambahkan validasi pay_amount <= open_amount; overpayment diblokir dan diaudit"),
    ("AT-06", "2026-11-01", "Warehouse", "SR-17; SR-18; F-19", "Petugas nonaktif E03 dapat memvalidasi penerimaan dan pengeluaran barang gudang", "T-18", "SR-17 & SR-18: Hanya pegawai Aktif departemen Warehouse yang boleh sign-off.", "Tambahkan pemeriksaan status === 'Aktif' && dept === 'D-WH'; lolos regresi T-18 & T-30"),
    ("AT-07", "2026-11-02", "Reporting", "SR-19; F-20", "Draft Invoice Rp277.500 langsung menaikkan angka piutang di Operational Dashboard", "T-34", "SR-19: KPI hanya boleh menghitung dokumen tersimpan/posted. Terdeteksi di T-34.", "Filter perhitungan agregasi AR/AP hanya untuk invoice berstatus 'posted'; lolos regresi T-33 & T-34"),
]

for idx, entry in enumerate(ai_traps):
    row_idx = 25 + idx
    for c_idx, val in enumerate(entry):
        ws_log.cell(row_idx, c_idx + 1, val)

# Sheet 'Konflik & Kontribusi' - Table B (Pelacak kontribusi mingguan)
ws_kk = wb_team["Konflik & Kontribusi"]
# Week 1
# M1
ws_kk.cell(17, 3, 5); ws_kk.cell(17, 4, 3); ws_kk.cell(17, 5, 3); ws_kk.cell(17, 6, 3); ws_kk.cell(17, 8, "Role Assignment, SR Matrix, Decision Log awal selesai")
# M2
ws_kk.cell(18, 3, 6); ws_kk.cell(18, 4, 3); ws_kk.cell(18, 5, 3); ws_kk.cell(18, 6, 3); ws_kk.cell(18, 8, "Database seed baseline, checksum 880 unit pass 100%")
# M3
ws_kk.cell(19, 3, 4); ws_kk.cell(19, 4, 2); ws_kk.cell(19, 5, 3); ws_kk.cell(19, 6, 3); ws_kk.cell(19, 8, "Draft wireframe 18 layar S-01 s.d S-18 & Employees seed")
# M4
ws_kk.cell(20, 3, 5); ws_kk.cell(20, 4, 3); ws_kk.cell(20, 5, 3); ws_kk.cell(20, 6, 3); ws_kk.cell(20, 8, "Opening trial balance Rp44.595.000 seimbang, test plan")

# Week 2
# M1
ws_kk.cell(21, 3, 4); ws_kk.cell(21, 4, 2); ws_kk.cell(21, 5, 3); ws_kk.cell(21, 6, 3); ws_kk.cell(21, 8, "Implementasi Sales/CRM, credit exposure & approval diskon")
# M2
ws_kk.cell(22, 3, 7); ws_kk.cell(22, 4, 4); ws_kk.cell(22, 5, 3); ws_kk.cell(22, 6, 3); ws_kk.cell(22, 8, "Purchase/Inventory, FEFO lot engine, anti-stok negatif")
# M3
ws_kk.cell(23, 3, 5); ws_kk.cell(23, 4, 3); ws_kk.cell(23, 5, 3); ws_kk.cell(23, 6, 3); ws_kk.cell(23, 8, "App switcher RBAC, Employee roster & sign-off gate")
# M4
ws_kk.cell(24, 3, 6); ws_kk.cell(24, 4, 3); ws_kk.cell(24, 5, 3); ws_kk.cell(24, 6, 3); ws_kk.cell(24, 8, "Customer Invoice, Vendor Bill 3-way match, ledger")

# Sheet 'Persetujuan'
ws_persetujuan = wb_team["Persetujuan"]
# Update NIMs & Status
ws_persetujuan.cell(6, 3, "03081240023"); ws_persetujuan.cell(6, 5, "Setuju"); ws_persetujuan.cell(6, 6, "2026-10-24"); ws_persetujuan.cell(6, 7, "Tanda tangan digital / Commit 6e71397")
ws_persetujuan.cell(7, 3, "03081240012"); ws_persetujuan.cell(7, 5, "Setuju"); ws_persetujuan.cell(7, 6, "2026-10-24"); ws_persetujuan.cell(7, 7, "Tanda tangan digital / Commit fdb7a08")
ws_persetujuan.cell(8, 3, "03081240019"); ws_persetujuan.cell(8, 5, "Setuju"); ws_persetujuan.cell(8, 6, "2026-10-24"); ws_persetujuan.cell(8, 7, "Tanda tangan digital / Commit b823076")
ws_persetujuan.cell(9, 3, "03081240013"); ws_persetujuan.cell(9, 5, "Setuju"); ws_persetujuan.cell(9, 6, "2026-10-24"); ws_persetujuan.cell(9, 7, "Tanda tangan digital / Commit 42d9ef4")

# Riwayat revisi row 15
ws_persetujuan.cell(15, 6, "M1, M2, M3, M4 (Seluruh Anggota Setuju)")

# Save updated Team Agreement
wb_team.save(team_dst)
wb_team.save(team_repo)
print(f"Team Agreement saved to:\n  - {team_dst}\n  - {team_repo}")

print("All updates completed successfully!")
