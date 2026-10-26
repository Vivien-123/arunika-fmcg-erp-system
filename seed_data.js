// seed_data.js
// Dataset Realistis PT Arunika Distribusi Cepat
// Berdasarkan Student Scenario Handout Halaman 6-10

function getInitialSeed() {
  return {
    contacts_customers: [
      { id: "C01", name: "PT Toko Makmur Sejahtera", type: "B2B", city: "Jakarta", credit_limit: 2000000, payment_terms: "14 hari", open_ar: 900000 },
      { id: "C02", name: "CV Sinar Harapan", type: "B2B", city: "Bekasi", credit_limit: 1000000, payment_terms: "14 hari", open_ar: 600000 },
      { id: "C03", name: "UD Berkah Niaga", type: "B2B", city: "Depok", credit_limit: 3000000, payment_terms: "30 hari", open_ar: 0 },
      { id: "C04", name: "Toko Aneka Jaya", type: "B2B", city: "Bogor", credit_limit: 1250000, payment_terms: "Tunai", open_ar: 0 },
      { id: "C05", name: "PT Mini Mart Bersama", type: "B2B", city: "Tangerang", credit_limit: 4000000, payment_terms: "30 hari", open_ar: 0 },
      { id: "C06", name: "Toko Karunia", type: "B2B", city: "Jakarta", credit_limit: 750000, payment_terms: "Tunai", open_ar: 0 }
    ],

    contacts_vendors: [
      { id: "V01", name: "PT Bersih Abadi", city: "Jakarta", payment_terms: "14 hari", category: "Produk kebersihan" },
      { id: "V02", name: "CV Pangan Rasa", city: "Bekasi", payment_terms: "14 hari", category: "Makanan ringan" },
      { id: "V03", name: "PT Segar Nusantara", city: "Tangerang", payment_terms: "30 hari", category: "Minuman" },
      { id: "V04", name: "PT Personal Sentosa", city: "Bogor", payment_terms: "14 hari", category: "Perawatan pribadi" }
    ],

    products: [
      { id: "P01", variant_sku: "CLN-DTR-800-LMN", name: "Detergen Cair Lemon", size: "800 ml", color: "Lemon", unit: "pouch", sales_price: 25000, cost: 17000, default_vendor: "V01" },
      { id: "P02", variant_sku: "FOD-MIE-B12-ORI", name: "Mie Instan Dus Original", size: "12 pcs", color: "Original", unit: "dus", sales_price: 54000, cost: 37000, default_vendor: "V02" },
      { id: "P03", variant_sku: "BEV-TEA-C24-LCH", name: "Teh Botol Karton Leci", size: "24 botol", color: "Leci", unit: "karton", sales_price: 72000, cost: 51000, default_vendor: "V03" },
      { id: "P04", variant_sku: "CLN-DIS-750-JER", name: "Sabun Cuci Piring Jeruk", size: "750 ml", color: "Jeruk", unit: "botol", sales_price: 20000, cost: 13000, default_vendor: "V01" },
      { id: "P05", variant_sku: "PRS-SHM-B24-MNT", name: "Sampo Sachet Dus Mentol", size: "24 sachet", color: "Mentol", unit: "dus", sales_price: 48000, cost: 32000, default_vendor: "V04" },
      { id: "P06", variant_sku: "PRS-SPR-100-ROS", name: "Sabun Mandi Mawar", size: "100 ml", color: "Mawar", unit: "botol", sales_price: 14000, cost: 9000, default_vendor: "V04" },
      { id: "P07", variant_sku: "SNK-BIS-B10-CHO", name: "Biskuit Cokelat Dus", size: "10 pak", color: "Cokelat", unit: "dus", sales_price: 62000, cost: 44000, default_vendor: "V02" },
      { id: "P08", variant_sku: "SNK-WFR-B12-VNL", name: "Wafer Vanila Dus", size: "12 pak", color: "Vanila", unit: "dus", sales_price: 52000, cost: 36000, default_vendor: "V02" },
      { id: "P09", variant_sku: "BEV-COF-B20-ORI", name: "Kopi Sachet Dus", size: "20 sachet", color: "Original", unit: "dus", sales_price: 68000, cost: 48000, default_vendor: "V03" },
      { id: "P10", variant_sku: "CLN-FLR-900-PIN", name: "Pembersih Lantai Pine", size: "900 ml", color: "Pine", unit: "botol", sales_price: 28000, cost: 18000, default_vendor: "V01" },
      { id: "P11", variant_sku: "CLN-DTR-800-FLR", name: "Detergen Cair Floral", size: "800 ml", color: "Floral", unit: "pouch", sales_price: 25000, cost: 17000, default_vendor: "V01" },
      { id: "P12", variant_sku: "BEV-TEA-C24-JSM", name: "Teh Botol Karton Melati", size: "24 botol", color: "Melati", unit: "karton", sales_price: 72000, cost: 51000, default_vendor: "V03" },
      { id: "P13", variant_sku: "FOD-MIE-B12-SPC", name: "Mie Instan Dus Pedas", size: "12 pcs", color: "Pedas", unit: "dus", sales_price: 54000, cost: 37000, default_vendor: "V02" },
      { id: "P14", variant_sku: "PRS-TOO-120-HRB", name: "Pasta Gigi Herbal", size: "120 g", color: "Herbal", unit: "tube", sales_price: 19000, cost: 12000, default_vendor: "V04" },
      { id: "P15", variant_sku: "SNK-CRK-B08-BBQ", name: "Kerupuk BBQ Dus", size: "8 pak", color: "BBQ", unit: "dus", sales_price: 47000, cost: 30000, default_vendor: "V02" },
      { id: "P16", variant_sku: "PRS-SHM-B24-ALV", name: "Sampo Sachet Dus Aloe", size: "24 sachet", color: "Aloe", unit: "dus", sales_price: 48000, cost: 32000, default_vendor: "V04" }
    ],

    vendor_pricelist: [
      { vendor_id: "V01", variant_sku: "CLN-DTR-800-LMN", unit: "pouch", unit_price: 17000, min_qty: 1 },
      { vendor_id: "V02", variant_sku: "FOD-MIE-B12-ORI", unit: "dus", unit_price: 37000, min_qty: 1 },
      { vendor_id: "V03", variant_sku: "BEV-TEA-C24-LCH", unit: "karton", unit_price: 51000, min_qty: 1 },
      { vendor_id: "V01", variant_sku: "CLN-DIS-750-JER", unit: "botol", unit_price: 13000, min_qty: 1 },
      { vendor_id: "V04", variant_sku: "PRS-SHM-B24-MNT", unit: "dus", unit_price: 32000, min_qty: 1 },
      { vendor_id: "V04", variant_sku: "PRS-SPR-100-ROS", unit: "botol", unit_price: 9000, min_qty: 1 },
      { vendor_id: "V02", variant_sku: "SNK-BIS-B10-CHO", unit: "dus", unit_price: 44000, min_qty: 1 },
      { vendor_id: "V02", variant_sku: "SNK-WFR-B12-VNL", unit: "dus", unit_price: 36000, min_qty: 1 },
      { vendor_id: "V03", variant_sku: "BEV-COF-B20-ORI", unit: "dus", unit_price: 48000, min_qty: 1 },
      { vendor_id: "V01", variant_sku: "CLN-FLR-900-PIN", unit: "botol", unit_price: 18000, min_qty: 1 },
      { vendor_id: "V01", variant_sku: "CLN-DTR-800-FLR", unit: "pouch", unit_price: 17000, min_qty: 1 },
      { vendor_id: "V03", variant_sku: "BEV-TEA-C24-JSM", unit: "karton", unit_price: 51000, min_qty: 1 },
      { vendor_id: "V02", variant_sku: "FOD-MIE-B12-SPC", unit: "dus", unit_price: 37000, min_qty: 1 },
      { vendor_id: "V04", variant_sku: "PRS-TOO-120-HRB", unit: "tube", unit_price: 12000, min_qty: 1 },
      { vendor_id: "V02", variant_sku: "SNK-CRK-B08-BBQ", unit: "dus", unit_price: 30000, min_qty: 1 },
      { vendor_id: "V04", variant_sku: "PRS-SHM-B24-ALV", unit: "dus", unit_price: 32000, min_qty: 1 }
    ],

    warehouses_locations: [
      { code: "WH-JKT", name: "Gudang Utama Arunika", city: "Jakarta", usage: "Internal" },
      { code: "HUB-BDG", name: "Hub Distribusi Bandung", city: "Bandung", usage: "Internal" }
    ],

    opening_stock: [
      { variant_sku: "CLN-DTR-800-LMN", WH_JKT_on_hand: 60, HUB_BDG_on_hand: 20, company_total: 80, standard_cost: 17000, standard_value: 1360000, reserved: 8 },
      { variant_sku: "FOD-MIE-B12-ORI", WH_JKT_on_hand: 40, HUB_BDG_on_hand: 10, company_total: 50, standard_cost: 37000, standard_value: 1850000, reserved: 0 },
      { variant_sku: "BEV-TEA-C24-LCH", WH_JKT_on_hand: 35, HUB_BDG_on_hand: 15, company_total: 50, standard_cost: 51000, standard_value: 2550000, reserved: 0 },
      { variant_sku: "CLN-DIS-750-JER", WH_JKT_on_hand: 50, HUB_BDG_on_hand: 20, company_total: 70, standard_cost: 13000, standard_value: 910000, reserved: 0 },
      { variant_sku: "PRS-SHM-B24-MNT", WH_JKT_on_hand: 30, HUB_BDG_on_hand: 10, company_total: 40, standard_cost: 32000, standard_value: 1280000, reserved: 0 },
      { variant_sku: "PRS-SPR-100-ROS", WH_JKT_on_hand: 80, HUB_BDG_on_hand: 20, company_total: 100, standard_cost: 9000, standard_value: 900000, reserved: 0 },
      { variant_sku: "SNK-BIS-B10-CHO", WH_JKT_on_hand: 25, HUB_BDG_on_hand: 10, company_total: 35, standard_cost: 44000, standard_value: 1540000, reserved: 0 },
      { variant_sku: "SNK-WFR-B12-VNL", WH_JKT_on_hand: 30, HUB_BDG_on_hand: 10, company_total: 40, standard_cost: 36000, standard_value: 1440000, reserved: 0 },
      { variant_sku: "BEV-COF-B20-ORI", WH_JKT_on_hand: 20, HUB_BDG_on_hand: 10, company_total: 30, standard_cost: 48000, standard_value: 1440000, reserved: 0 },
      { variant_sku: "CLN-FLR-900-PIN", WH_JKT_on_hand: 45, HUB_BDG_on_hand: 15, company_total: 60, standard_cost: 18000, standard_value: 1080000, reserved: 0 },
      { variant_sku: "CLN-DTR-800-FLR", WH_JKT_on_hand: 55, HUB_BDG_on_hand: 15, company_total: 70, standard_cost: 17000, standard_value: 1190000, reserved: 0 },
      { variant_sku: "BEV-TEA-C24-JSM", WH_JKT_on_hand: 40, HUB_BDG_on_hand: 10, company_total: 50, standard_cost: 51000, standard_value: 2550000, reserved: 0 },
      { variant_sku: "FOD-MIE-B12-SPC", WH_JKT_on_hand: 35, HUB_BDG_on_hand: 10, company_total: 45, standard_cost: 37000, standard_value: 1665000, reserved: 0 },
      { variant_sku: "PRS-TOO-120-HRB", WH_JKT_on_hand: 65, HUB_BDG_on_hand: 20, company_total: 85, standard_cost: 12000, standard_value: 1020000, reserved: 0 },
      { variant_sku: "SNK-CRK-B08-BBQ", WH_JKT_on_hand: 30, HUB_BDG_on_hand: 10, company_total: 40, standard_cost: 30000, standard_value: 1200000, reserved: 0 },
      { variant_sku: "PRS-SHM-B24-ALV", WH_JKT_on_hand: 25, HUB_BDG_on_hand: 10, company_total: 35, standard_cost: 32000, standard_value: 1120000, reserved: 0 }
    ],

    product_lots: [
      { lot_id: "LOT-TEA-01", variant_sku: "BEV-TEA-C24-LCH", location: "WH-JKT", qty: 15, expiry_date: "2026-11-15", selection_policy: "FEFO" },
      { lot_id: "LOT-TEA-02", variant_sku: "BEV-TEA-C24-LCH", location: "WH-JKT", qty: 20, expiry_date: "2027-02-28", selection_policy: "FEFO" },
      { lot_id: "LOT-TEA-03", variant_sku: "BEV-TEA-C24-LCH", location: "HUB-BDG", qty: 15, expiry_date: "2027-03-15", selection_policy: "FEFO" }
    ],

    chart_of_accounts: [
      { code: "1000", name: "Kas dan Bank", type: "Asset", normal_balance: "Debit" },
      { code: "1100", name: "Piutang Usaha", type: "Asset", normal_balance: "Debit" },
      { code: "1300", name: "Persediaan Barang", type: "Asset", normal_balance: "Debit" },
      { code: "1350", name: "Stock Interim / Receipt Clearing", type: "Asset", normal_balance: "Debit" },
      { code: "1400", name: "PPN Masukan", type: "Asset", normal_balance: "Debit" },
      { code: "1500", name: "Aset Tetap", type: "Asset", normal_balance: "Debit" },
      { code: "2000", name: "Utang Usaha", type: "Liability", normal_balance: "Credit" },
      { code: "2100", name: "PPN Keluaran", type: "Liability", normal_balance: "Credit" },
      { code: "2200", name: "Utang Biaya", type: "Liability", normal_balance: "Credit" },
      { code: "3000", name: "Modal / Saldo Awal", type: "Equity", normal_balance: "Credit" },
      { code: "4000", name: "Penjualan", type: "Income", normal_balance: "Credit" },
      { code: "4100", name: "Retur Penjualan", type: "Contra Income", normal_balance: "Debit" },
      { code: "4200", name: "Diskon Penjualan", type: "Contra Income", normal_balance: "Debit" },
      { code: "5100", name: "HPP", type: "Expense", normal_balance: "Debit" },
      { code: "6100", name: "Beban Operasional", type: "Expense", normal_balance: "Debit" }
    ],

    tax_rates: [
      { code: "PPN-NONLUX", formal_rate: "12%", tax_base_factor: "11/12", effective_rate: 0.11, rounding: "Per baris, rupiah terdekat", scope: "16 SKU simulasi" }
    ],

    payment_terms: [
      { code: "CASH", description: "Tunai", due_days: 0 },
      { code: "NET14", description: "14 hari kalender", due_days: 14 },
      { code: "NET30", description: "30 hari kalender", due_days: 30 }
    ],

    departments: [
      { dept_id: "D-WH", name: "Warehouse", manager_employee: "E01", company: "Arunika" },
      { dept_id: "D-SAL", name: "Sales", manager_employee: "E04", company: "Arunika" },
      { dept_id: "D-FIN", name: "Finance", manager_employee: "E06", company: "Arunika" },
      { dept_id: "D-PUR", name: "Procurement", manager_employee: "E07", company: "Arunika" },
      { dept_id: "D-HR", name: "Human Resources", manager_employee: "E08", company: "Arunika" }
    ],

    employees: [
      { id: "E01", name: "Sari Prameswari", department: "D-WH", job_title: "Supervisor Gudang", location: "WH-JKT", status: "Aktif", mapped_user: "warehouse_mgr" },
      { id: "E02", name: "Arif Maulana", department: "D-WH", job_title: "Petugas Gudang", location: "WH-JKT", status: "Aktif", mapped_user: "warehouse_user" },
      { id: "E03", name: "Naya Putri", department: "D-WH", job_title: "Petugas Gudang", location: "WH-JKT", status: "Nonaktif", mapped_user: null },
      { id: "E04", name: "Dini Pratiwi", department: "D-SAL", job_title: "Sales Lead", location: "JKT", status: "Aktif", mapped_user: "sales_user" },
      { id: "E05", name: "Farhan Saputra", department: "D-SAL", job_title: "Sales Executive", location: "JKT", status: "Aktif", mapped_user: "sales_user2" },
      { id: "E06", name: "Made Wirawan", department: "D-FIN", job_title: "Finance Officer", location: "JKT", status: "Aktif", mapped_user: "finance_user" },
      { id: "E07", name: "Rita Kusuma", department: "D-PUR", job_title: "Procurement Officer", location: "JKT", status: "Aktif", mapped_user: "purchase_user" },
      { id: "E08", name: "Melati Yusuf", department: "D-HR", job_title: "HR Administrator", location: "JKT", status: "Aktif", mapped_user: "hr_user" }
    ],

    employee_private_records: [
      { employee_id: "E01", personal_email: "sari.private@example.test", emergency_contact: "Nila Prameswari", private_phone: "0812-0000-0101", access_role: "HR only" },
      { employee_id: "E03", personal_email: "naya.private@example.test", emergency_contact: "Laras Putri", private_phone: "0812-0000-0303", access_role: "HR only" }
    ],

    user_accounts: [
      { username: "sales_user", role: "Sales", linked_employee: "E04", status: "Active", test_password: "Demo2026!" },
      { username: "warehouse_mgr", role: "Warehouse", linked_employee: "E01", status: "Active", test_password: "Demo2026!" },
      { username: "warehouse_user", role: "Warehouse", linked_employee: "E02", status: "Active", test_password: "Demo2026!" },
      { username: "finance_user", role: "Finance", linked_employee: "E06", status: "Active", test_password: "Demo2026!" },
      { username: "purchase_user", role: "Purchase", linked_employee: "E07", status: "Active", test_password: "Demo2026!" },
      { username: "hr_user", role: "HR", linked_employee: "E08", status: "Active", test_password: "Demo2026!" },
      { username: "owner_user", role: "Owner", linked_employee: null, status: "Active", test_password: "Demo2026!" }
    ],

    existing_documents: {
      leads: [
        {
          document_id: "L00003",
          type: "Lead",
          partner: "C03",
          partner_name: "UD Berkah Niaga",
          salesperson: "E04",
          status: "New",
          stage: "New", // New, Qualified, Won, Lost
          expected_revenue: 500000,
          details: "Expected Revenue 500.000",
          stock_state: "Tidak ada",
          financial_state: "Tidak ada",
          created_at: "2026-10-18T08:00:00Z"
        }
      ],
      sales_orders: [
        {
          document_id: "S00007",
          type: "Sales Order",
          partner: "C01",
          partner_name: "PT Toko Makmur Sejahtera",
          salesperson: "E04",
          status: "Confirmed", // Draft, To Approve, Confirmed, Cancelled
          approval_status: "Approved",
          payment_terms: "14 hari",
          warehouse: "WH-JKT",
          lines: [
            {
              id: 1,
              product_id: "P01",
              variant_sku: "CLN-DTR-800-LMN",
              description: "Detergen Cair Lemon 800 ml",
              qty: 8,
              unit: "pouch",
              unit_price: 25000,
              discount_percent: 0,
              effective_unit_price: 25000,
              net_amount: 200000,
              tax_rate: 0.11,
              tax_amount: 22000,
              gross_amount: 222000,
              reserved_qty: 8,
              delivered_qty: 0,
              invoiced_qty: 0
            }
          ],
          net_total: 200000,
          tax_total: 22000,
          gross_total: 222000,
          stock_state: "Reserved 8 P01 di WH-JKT; belum delivered",
          financial_state: "Belum invoiced; exposure 222.000",
          created_at: "2026-10-19T09:30:00Z",
          confirmed_at: "2026-10-19T10:00:00Z",
          confirmed_by: "sales_user"
        }
      ],
      purchase_orders: [
        {
          document_id: "PO00007",
          type: "Purchase Order",
          partner: "V04",
          partner_name: "PT Personal Sentosa",
          status: "Confirmed",
          warehouse: "WH-JKT",
          lines: [
            {
              id: 1,
              product_id: "P05",
              variant_sku: "PRS-SHM-B24-MNT",
              description: "Sampo Sachet Dus Mentol 24 sachet",
              qty: 20,
              unit: "dus",
              unit_price: 32000,
              net_amount: 640000,
              tax_rate: 0.11,
              tax_amount: 70400,
              gross_amount: 710400,
              received_qty: 0,
              accepted_qty: 0,
              rejected_qty: 0,
              billed_qty: 0
            }
          ],
          net_total: 640000,
          tax_total: 70400,
          gross_total: 710400,
          details: "20 x P05; net 640.000; gross est. 710.400",
          stock_state: "0 received / 0 accepted",
          financial_state: "0 billed / 0 paid",
          created_at: "2026-10-19T11:00:00Z"
        }
      ]
    },

    general_ledger_opening: {
      "1000": { debit: 20000000, credit: 0 }, // Bank
      "1100": { debit: 1500000, credit: 0 },  // AR (C01: 900k, C02: 600k)
      "1300": { debit: 23095000, credit: 0 }, // Persediaan
      "2000": { debit: 0, credit: 700000 },   // AP (V04: 700k)
      "3000": { debit: 0, credit: 43895000 }  // Modal
    }
  };
}

module.exports = { getInitialSeed };
