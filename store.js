// store.js
// In-Memory & File-persisted state manager with Fixture Reset, Audit Trail, and Unique Sequencing
const fs = require('fs');
const path = require('path');
const { getInitialSeed } = require('./seed_data');

const DATA_FILE = path.join(__dirname, 'data_store.json');

class Store {
  constructor() {
    this.state = null;
    this.auditLogs = [];
    this.loadOrReset();
  }

  loadOrReset(forceReset = false) {
    if (!forceReset && fs.existsSync(DATA_FILE)) {
      try {
        const raw = fs.readFileSync(DATA_FILE, 'utf8');
        const parsed = JSON.parse(raw);
        this.state = parsed.state;
        this.auditLogs = parsed.auditLogs || [];
        // Ensure M4 accounting collections exist even on old data_store.json files
        if (!this.state.invoices)        this.state.invoices        = [];
        if (!this.state.journal_entries) this.state.journal_entries = [];
        if (!this.state.sequences)       this.state.sequences       = {};
        return;
      } catch (err) {
        console.warn('Failed to load data_store.json, resetting to seed baseline:', err.message);
      }
    }
    this.resetToSeed();
  }

  resetToSeed() {
    this.state = getInitialSeed();

    // ── M4: initialise accounting collections ─────────────────────────
    this.state.invoices        = [];
    this.state.journal_entries = [];
    this.state.sequences       = {};

    // Opening balance journal entries (Handout §4, M4-W1-02)
    // Dr Bank 20.000.000 + Dr AR 1.500.000 + Dr Persediaan 23.095.000
    // = Cr AP 700.000 + Cr Modal 43.895.000
    this.state.journal_entries.push(
      { id:1, invoice_doc_number:null, journal_type:'opening', reference:'OPENING-BALANCE',
        idempotency_key:'opening|OPENING-BALANCE|1000|3000|20000000',
        debit_account:'1000', credit_account:'3000', amount:20000000,
        description:'Opening Balance: Kas dan Bank Rp20.000.000',
        posted_by:'system', posted_at:'2026-10-20T00:00:00.000Z', is_reversed:false },
      { id:2, invoice_doc_number:null, journal_type:'opening', reference:'OPENING-BALANCE',
        idempotency_key:'opening|OPENING-BALANCE|1100|3000|1500000',
        debit_account:'1100', credit_account:'3000', amount:1500000,
        description:'Opening Balance: Piutang Usaha AR Rp1.500.000 (C01 Rp900.000 + C02 Rp600.000)',
        posted_by:'system', posted_at:'2026-10-20T00:00:00.000Z', is_reversed:false },
      { id:3, invoice_doc_number:null, journal_type:'opening', reference:'OPENING-BALANCE',
        idempotency_key:'opening|OPENING-BALANCE|1300|3000|23095000',
        debit_account:'1300', credit_account:'3000', amount:23095000,
        description:'Opening Balance: Persediaan Barang Rp23.095.000 (WH-JKT Rp17.315.000 + HUB-BDG Rp5.780.000)',
        posted_by:'system', posted_at:'2026-10-20T00:00:00.000Z', is_reversed:false },
      { id:4, invoice_doc_number:null, journal_type:'opening', reference:'OPENING-BALANCE',
        idempotency_key:'opening|OPENING-BALANCE|3000|2000|700000',
        debit_account:'3000', credit_account:'2000', amount:700000,
        description:'Opening Balance: Utang Usaha AP Rp700.000 (V04)',
        posted_by:'system', posted_at:'2026-10-20T00:00:00.000Z', is_reversed:false }
    );

    this.auditLogs = [
      {
        id: 1,
        document_id: "SYSTEM",
        action: "INIT_BASELINE",
        actor: "system",
        timestamp: "2026-10-20T08:00:00.000Z",
        details: "Database reset to official scenario baseline fixture.",
        result: "Baseline initialized with 16 SKUs, 6 Customers, 8 Employees, 880 stock units."
      },
      {
        id: 2,
        document_id: "S00007",
        action: "RESERVE",
        actor: "warehouse_mgr",
        timestamp: "2026-10-19T10:00:00.000Z",
        details: "Reserved 8 units P01 at WH-JKT for order S00007.",
        result: "8 units reserved"
      },
      {
        id: 3,
        document_id: "PO00007",
        action: "CONFIRM",
        actor: "purchase_user",
        timestamp: "2026-10-19T11:00:00.000Z",
        details: "Confirmed PO00007 for 20 units P05 from V04.",
        result: "Once"
      }
    ];
    this.persist();
  }

  persist() {
    try {
      fs.writeFileSync(DATA_FILE, JSON.stringify({
        state: this.state,
        auditLogs: this.auditLogs
      }, null, 2), 'utf8');
    } catch (err) {
      console.error('Error persisting state:', err.message);
    }
  }

  logAudit({ document_id, action, actor = "system", details = "", result = "Success" }) {
    const entry = {
      id: this.auditLogs.length + 1,
      document_id: document_id || "GENERAL",
      action,
      actor,
      timestamp: new Date().toISOString(),
      details,
      result
    };
    this.auditLogs.unshift(entry);
    this.persist();
    return entry;
  }

  getAuditLogs(filter = {}) {
    let logs = [...this.auditLogs];
    if (filter.document_id) {
      logs = logs.filter(l => l.document_id.toLowerCase().includes(filter.document_id.toLowerCase()));
    }
    if (filter.actor) {
      logs = logs.filter(l => l.actor === filter.actor);
    }
    return logs;
  }

  getNextSalesOrderSequence() {
    const orders = this.state.existing_documents.sales_orders || [];
    let maxNum = 7; // baseline S00007
    orders.forEach(so => {
      const match = so.document_id.match(/S(\d+)/i);
      if (match) {
        const num = parseInt(match[1], 10);
        if (num > maxNum) maxNum = num;
      }
    });
    const nextNum = maxNum + 1;
    return `S${String(nextNum).padStart(5, '0')}`;
  }

  getNextLeadSequence() {
    const leads = this.state.existing_documents.leads || [];
    let maxNum = 3; // baseline L00003
    leads.forEach(l => {
      const match = l.document_id.match(/L(\d+)/i);
      if (match) {
        const num = parseInt(match[1], 10);
        if (num > maxNum) maxNum = num;
      }
    });
    const nextNum = maxNum + 1;
    return `L${String(nextNum).padStart(5, '0')}`;
  }
}

const storeInstance = new Store();
module.exports = storeInstance;
