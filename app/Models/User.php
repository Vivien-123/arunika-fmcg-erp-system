<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'username'];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

    public function employee()
    {
        return $this->hasOne(Employee::class, 'mapped_user_id');
    }

    // ── Role helpers ─────────────────────────────────────────────────────

    public function isHR(): bool           { return $this->role === 'HR'; }
    public function isMasterAdmin(): bool  { return $this->role === 'MasterAdmin'; }
    public function isOwner(): bool        { return $this->role === 'Owner'; }

    /**
     * Owner = read-only everywhere.
     * MasterAdmin = full edit access to everything.
     */
    public function canEdit(): bool
    {
        return !$this->isOwner(); // Owner is view-only
    }

    public function hasRole(string $role): bool { return $this->role === $role; }

    public function canSignOffWarehouse(): bool
    {
        return in_array($this->role, ['Warehouse', 'MasterAdmin']);
    }

    /**
     * Menus visible per role.
     * Owner & MasterAdmin see all modules.
     * Others see only their domain.
     */
    public function allowedMenus(): array
    {
        if (in_array($this->role, ['Owner', 'MasterAdmin'])) {
            return ['Sales', 'CRM', 'Purchase', 'Inventory', 'Accounting', 'Invoicing', 'Employees', 'Reporting', 'Wireframes'];
        }

        $map = [
            'Sales'      => ['Sales', 'CRM'],
            'CRM'        => ['Sales', 'CRM'],
            'Purchase'   => ['Purchase', 'Inventory'],
            'Warehouse'  => ['Inventory'],
            'Finance'    => ['Accounting', 'Invoicing', 'Reporting'],
            'HR'         => ['Employees', 'Reporting'],
        ];

        return $map[$this->role] ?? [];
    }

    /**
     * Full module-to-submodule map, used by sidebar.
     * Returns the sub-menu items for a given top-level module.
     */
    public static function moduleSubMenus(): array
    {
        return [
            'Sales' => [
                ['label' => 'Sales Orders',    'route' => 'sales.orders.index',  'icon' => 'doc'],
                ['label' => 'Buat Quotation',  'route' => 'sales.orders.create', 'icon' => 'plus'],
            ],
            'CRM' => [
                ['label' => 'CRM Pipeline',    'route' => 'crm.leads.index',     'icon' => 'users'],
            ],
            'Purchase' => [
                ['label' => 'Purchase Orders', 'route' => 'purchase.orders.index',  'icon' => 'doc'],
                ['label' => 'Buat RFQ',        'route' => 'purchase.orders.create', 'icon' => 'plus'],
            ],
            'Inventory' => [
                ['label' => 'Stock Overview',    'route' => 'inventory.stock',              'icon' => 'box'],
                ['label' => 'Delivery Orders',   'route' => 'inventory.deliveries.index',   'icon' => 'truck'],
                ['label' => 'Goods Receipts',    'route' => 'inventory.receipts.index',     'icon' => 'inbox'],
                ['label' => 'Internal Transfer', 'route' => 'inventory.transfer',           'icon' => 'arrow'],
            ],
            'Accounting' => [
                ['label' => 'Customer Invoices', 'route' => 'accounting.invoices.index', 'icon' => 'doc'],
                ['label' => 'Vendor Bills',      'route' => 'accounting.bills.index',    'icon' => 'doc'],
                ['label' => 'General Ledger',    'route' => 'accounting.ledger.index',   'icon' => 'chart'],
            ],
            'Invoicing' => [
                ['label' => 'Customer Invoices', 'route' => 'accounting.invoices.index', 'icon' => 'doc'],
                ['label' => 'Credit Notes',      'route' => 'accounting.invoices.index', 'icon' => 'doc'],
            ],
            'Employees' => [
                ['label' => 'Employee Directory', 'route' => 'employees.index', 'icon' => 'users'],
                ['label' => 'Defense Prep',       'route' => 'employees.defense', 'icon' => 'shield'],
            ],
            'Reporting' => [
                ['label' => 'Dashboard',          'route' => 'reporting.dashboard',    'icon' => 'chart'],
                ['label' => 'Audit Trail',         'route' => 'reporting.audit',       'icon' => 'shield'],
                ['label' => 'Integration Testing', 'route' => 'reporting.testing',     'icon' => 'check'],
                ['label' => 'Test Results M3',     'route' => 'reporting.test-results','icon' => 'doc'],
            ],
            'Wireframes' => [
                ['label' => 'S-01..S-18 Wireframes', 'route' => 'wireframes', 'icon' => 'grid'],
            ],
        ];
    }
}
