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
        return;
      } catch (err) {
        console.warn('Failed to load data_store.json, resetting to seed baseline:', err.message);
      }
    }
    this.resetToSeed();
  }

  resetToSeed() {
    this.state = getInitialSeed();
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
