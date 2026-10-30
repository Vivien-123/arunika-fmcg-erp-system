// server.js
// Standalone HTTP Server & REST API for PT Arunika Distribusi Cepat (Role M01: Sales & CRM)
// Built with Node.js standard library - zero external dependencies required!

const http = require('http');
const fs = require('fs');
const path = require('path');
const url = require('url');

const purchaseInventoryService = require('./purchase_inventory_service');

const store = require('./store');
const salesCrmService = require('./sales_crm_service');

const PORT = process.env.PORT || 3000;
const PUBLIC_DIR = path.join(__dirname, 'public');

const MIME_TYPES = {
  '.html': 'text/html; charset=UTF-8',
  '.css': 'text/css; charset=UTF-8',
  '.js': 'application/javascript; charset=UTF-8',
  '.json': 'application/json; charset=UTF-8',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.svg': 'image/svg+xml',
  '.ico': 'image/x-icon'
};

function sendJson(res, statusCode, data) {
  const payload = JSON.stringify(data, null, 2);
  res.writeHead(statusCode, {
    'Content-Type': 'application/json; charset=UTF-8',
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type, X-User-Role, X-User-Name'
  });
  res.end(payload);
}

function parseBody(req) {
  return new Promise((resolve, reject) => {
    let body = '';
    req.on('data', chunk => { body += chunk; });
    req.on('end', () => {
      if (!body) return resolve({});
      try {
        resolve(JSON.parse(body));
      } catch (err) {
        reject(err);
      }
    });
    req.on('error', reject);
  });
}

function serveStatic(res, filePath) {
  fs.readFile(filePath, (err, data) => {
    if (err) {
      res.writeHead(404, { 'Content-Type': 'text/plain; charset=UTF-8' });
      res.end('404 Not Found');
      return;
    }
    const ext = path.extname(filePath).toLowerCase();
    const contentType = MIME_TYPES[ext] || 'application/octet-stream';
    res.writeHead(200, { 'Content-Type': contentType });
    res.end(data);
  });
}

const server = http.createServer(async (req, res) => {
  const parsedUrl = url.parse(req.url, true);
  const pathname = parsedUrl.pathname;
  const method = req.method.toUpperCase();

  // Handle CORS preflight
  if (method === 'OPTIONS') {
    res.writeHead(204, {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
      'Access-Control-Allow-Headers': 'Content-Type, X-User-Role, X-User-Name'
    });
    res.end();
    return;
  }

  // Get acting user & role from headers or query
  const actor = req.headers['x-user-name'] || parsedUrl.query.user || 'sales_user';
  const role = req.headers['x-user-role'] || parsedUrl.query.role || 'Sales';

  // -------------------------------------------------------------
  // API ROUTES
  // -------------------------------------------------------------
  try {
    // 1. System & Fixture Reset
    if (pathname === '/api/status' && method === 'GET') {
      return sendJson(res, 200, {
        status: 'online',
        app: 'PT Arunika Distribusi Cepat ERP',
        version: '1.0.0 (Odoo Web 17.0 Experience)',
        module: 'M01 - Sales & CRM',
        active_user: actor,
        active_role: role,
        total_products: store.state.products.length,
        total_customers: store.state.contacts_customers.length,
        total_leads: store.state.existing_documents.leads.length,
        total_sales_orders: store.state.existing_documents.sales_orders.length
      });
    }

    if (pathname === '/api/fixture/reset' && method === 'POST') {
      store.resetToSeed();
      return sendJson(res, 200, {
        success: true,
        message: 'Fixture database successfully reset to clean baseline (880 stock units, Rp23.095.000, S00007, L00003, PO00007).'
      });
    }

    // 2. Customers & Credit Profile (F-02, F-10, S-02)
    if (pathname === '/api/customers' && method === 'GET') {
      const customersWithExposure = store.state.contacts_customers.map(c => {
        return salesCrmService.getCustomerCreditProfile(c.id);
      });
      return sendJson(res, 200, customersWithExposure);
    }

    const customerMatch = pathname.match(/^\/api\/customers\/([A-Za-z0-9_-]+)$/);
    if (customerMatch && method === 'GET') {
      const custId = customerMatch[1];
      const profile = salesCrmService.getCustomerCreditProfile(custId);
      return sendJson(res, 200, profile);
    }

    // 3. Products Catalogue (F-03, S-03)
    if (pathname === '/api/products' && method === 'GET') {
      const products = store.state.products.map(p => {
        const stock = store.state.opening_stock.find(s => s.variant_sku === p.variant_sku);
        return {
          ...p,
          wh_jkt_on_hand: stock ? stock.WH_JKT_on_hand : 0,
          hub_bdg_on_hand: stock ? stock.HUB_BDG_on_hand : 0,
          company_total: stock ? stock.company_total : 0,
          reserved: stock ? stock.reserved : 0,
          available: stock ? (stock.WH_JKT_on_hand - stock.reserved) : 0
        };
      });
      return sendJson(res, 200, products);
    }

    // 4. CRM Pipeline & Leads (F-04, S-04)
    if (pathname === '/api/leads' && method === 'GET') {
      const leads = salesCrmService.getLeads(parsedUrl.query);
      return sendJson(res, 200, leads);
    }

    if (pathname === '/api/leads' && method === 'POST') {
      const body = await parseBody(req);
      const created = salesCrmService.createLead(body, actor);
      return sendJson(res, 201, created);
    }

    const leadConvertMatch = pathname.match(/^\/api\/leads\/([A-Za-z0-9_-]+)\/convert$/);
    if (leadConvertMatch && method === 'POST') {
      const leadId = leadConvertMatch[1];
      const converted = salesCrmService.convertToOpportunity(leadId, actor);
      return sendJson(res, 200, converted);
    }

    const leadUpdateMatch = pathname.match(/^\/api\/leads\/([A-Za-z0-9_-]+)$/);
    if (leadUpdateMatch && method === 'PUT') {
      const leadId = leadUpdateMatch[1];
      const body = await parseBody(req);
      const updated = salesCrmService.updateOpportunity(leadId, body, actor);
      return sendJson(res, 200, updated);
    }

    // 5. Sales Orders & Quotations (F-05, F-06, F-10, S-05)
    if (pathname === '/api/sales-orders' && method === 'GET') {
      const orders = salesCrmService.getSalesOrders(parsedUrl.query);
      return sendJson(res, 200, orders);
    }

    if (pathname === '/api/quotations' && method === 'POST') {
      const body = await parseBody(req);
      const quotation = salesCrmService.createQuotation(body, actor);
      return sendJson(res, 201, quotation);
    }

    const soMatch = pathname.match(/^\/api\/sales-orders\/([A-Za-z0-9_-]+)$/);
    if (soMatch && method === 'GET') {
      const soId = soMatch[1];
      const so = salesCrmService.getSalesOrderById(soId);
      const creditProfile = salesCrmService.getCustomerCreditProfile(so.partner);
      return sendJson(res, 200, { ...so, customer_credit_profile: creditProfile });
    }

    const soLinesMatch = pathname.match(/^\/api\/quotations\/([A-Za-z0-9_-]+)\/lines$/);
    if (soLinesMatch && method === 'PUT') {
      const soId = soLinesMatch[1];
      const body = await parseBody(req);
      const updated = salesCrmService.updateOrderLines(soId, body.lines || [], actor);
      return sendJson(res, 200, updated);
    }

    const soApproveDiscountMatch = pathname.match(/^\/api\/sales-orders\/([A-Za-z0-9_-]+)\/approve-discount$/);
    if (soApproveDiscountMatch && method === 'POST') {
      const soId = soApproveDiscountMatch[1];
      if (role !== 'Finance' && role !== 'Owner') {
        return sendJson(res, 403, { error: 'Approval required from Finance role (SR-08)' });
      }
      const approved = salesCrmService.approveDiscount(soId, actor);
      return sendJson(res, 200, approved);
    }

    const soConfirmMatch = pathname.match(/^\/api\/sales-orders\/([A-Za-z0-9_-]+)\/confirm$/);
    if (soConfirmMatch && method === 'POST') {
      // Role enforcement: Sales, Finance, or Owner can confirm (SR-03, SR-07/SR-08)
      // Note: Finance approval for discount > 10% is enforced inside confirmSalesOrder(),
      // not at the route level — Sales CAN call confirm, but the service will reject if
      // the discount approval is pending (SR-08).
      if (!['Sales', 'Finance', 'Owner'].includes(role)) {
        return sendJson(res, 403, { error: 'Confirmation requires Sales, Finance or Owner role.' });
      }

      const soId = soConfirmMatch[1];
      const result = salesCrmService.confirmSalesOrder(soId, actor);
      return sendJson(res, 200, result);
    }

    // 6. Audit & History (F-21, S-18)
    if (pathname === '/api/audit-logs' && method === 'GET') {
      const logs = store.getAuditLogs(parsedUrl.query);
      return sendJson(res, 200, logs);
    }

    // 7. Role Enforcement Test Endpoint: Accounting Ledger (T-02, SR-15)
    if (pathname === '/api/accounting/ledger' && method === 'GET') {
      if (role !== 'Finance' && role !== 'Owner') {
        store.logAudit({
          document_id: 'JOURNAL',
          action: 'SECURITY_BLOCKED_LEDGER_ACCESS',
          actor,
          details: `User with role '${role}' attempted unauthorized access to Accounting Ledger. Blocked per SR-15.`,
          result: '403 Forbidden'
        });
        return sendJson(res, 403, {
          error: 'Access Denied (SR-15): Finance ledger is restricted to Finance and Owner roles.',
          attempted_role: role
        });
      }
      return sendJson(res, 200, {
        general_ledger: store.state.general_ledger_opening,
        chart_of_accounts: store.state.chart_of_accounts
      });
    }

    // 8. Employee Directory & Privacy (SR-15, SR-18, F-18, T-28)
    if (pathname === '/api/employees' && method === 'GET') {
      const isHr = (role === 'HR' || role === 'Owner');
      const publicEmployees = store.state.employees.map(emp => {
        const item = { ...emp };
        if (isHr) {
          const priv = store.state.employee_private_records.find(p => p.employee_id === emp.id);
          if (priv) {
            item.private_records = priv;
          }
        }
        return item;
      });
      return sendJson(res, 200, publicEmployees);
    }

    // 9. Run Test Suite (T-03, T-04, T-07, T-08, T-09, T-10)
    if (pathname === '/api/run-tests' && method === 'GET') {
      // Execute test suite dynamically
      delete require.cache[require.resolve('./test_m01')];
      const { results } = require('./test_m01');
      return sendJson(res, 200, {
        timestamp: new Date().toISOString(),
        total: results.length,
        passed: results.filter(r => r.pass).length,
        failed: results.filter(r => !r.pass).length,
        tests: results
      });
    }

    // -------------------------------------------------------------
    // MODUL M02: PURCHASE & INVENTORY API ROUTES
    // -------------------------------------------------------------

    // API Purchase Orders
    if (pathname === '/api/purchase-orders' && method === 'GET') {
      return sendJson(res, 200, store.state.existing_documents.purchase_orders || []);
    }

    if (pathname === '/api/purchase-orders' && method === 'POST') {
      const body = await parseBody(req);
      const po = purchaseInventoryService.createPurchaseOrder(body, actor);
      return sendJson(res, 201, po);
    }

    const poReceiveMatch = pathname.match(/^\/api\/purchase-orders\/([A-Za-z0-9_-]+)\/receive$/);
    if (poReceiveMatch && method === 'POST') {
      const poId = poReceiveMatch[1];
      const body = await parseBody(req);
      const result = purchaseInventoryService.receiveGoods(poId, body.lines || [], body.warehouse || 'WH-JKT', actor);
      return sendJson(res, 200, result);
    }

    // API Stock Transfer & FEFO
    if (pathname === '/api/inventory/transfer' && method === 'POST') {
      const body = await parseBody(req);
      const transfer = purchaseInventoryService.transferStockBetweenWarehouses(body, actor);
      return sendJson(res, 200, transfer);
    }

    if (pathname === '/api/inventory/fefo-allocation' && method === 'GET') {
      const sku = parsedUrl.query.sku;
      const wh = parsedUrl.query.warehouse || 'WH-JKT';
      const qty = parseInt(parsedUrl.query.qty || '1', 10);
      const fefo = purchaseInventoryService.allocateLotFEFO(sku, wh, qty);
      return sendJson(res, 200, fefo);
    }

    // -------------------------------------------------------------
    // STATIC FILE SERVING
    // -------------------------------------------------------------
    let relativePath = pathname === '/' ? 'index.html' : pathname;
    if (relativePath.startsWith('/')) relativePath = relativePath.slice(1);
    const filePath = path.join(PUBLIC_DIR, relativePath);

    // Prevent directory traversal
    if (!filePath.startsWith(PUBLIC_DIR)) {
      res.writeHead(403, { 'Content-Type': 'text/plain' });
      res.end('Forbidden');
      return;
    }

    if (fs.existsSync(filePath) && fs.statSync(filePath).isFile()) {
      return serveStatic(res, filePath);
    } else {
      // Fallback to index.html for SPA routing
      return serveStatic(res, path.join(PUBLIC_DIR, 'index.html'));
    }

  } catch (error) {
    console.error('API Error:', error.message);
    return sendJson(res, 400, {
      error: error.message,
      rule_referenced: error.message.includes('SR-') ? error.message.match(/SR-\d+/)[0] : null
    });
  }
});

server.listen(PORT, '127.0.0.1', () => {
  console.log(`====================================================`);
  console.log(`Arunika FMCG ERP (Role M01: Sales & CRM) Running!`);
  console.log(`Listening on http://127.0.0.1:${PORT}`);
  console.log(`====================================================`);
});
