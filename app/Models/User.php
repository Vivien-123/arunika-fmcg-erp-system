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

    public function isHR(): bool { return $this->role === 'HR'; }

    public function hasRole(string $role): bool { return $this->role === $role; }

    public function canSignOffWarehouse(): bool
    {
        return in_array($this->role, ['Warehouse', 'Owner']);
    }

    public function allowedMenus(): array
    {
        $menus = [
            'Sales'      => ['Sales', 'Owner'],
            'CRM'        => ['Sales', 'Owner'],
            'Purchase'   => ['Purchase', 'Owner'],
            'Inventory'  => ['Warehouse', 'Purchase', 'Owner'],
            'Accounting' => ['Finance', 'Owner'],
            'Invoicing'  => ['Finance', 'Owner'],
            'Employees'  => ['HR', 'Owner'],
            'Reporting'  => ['Finance', 'Owner', 'Warehouse'],
            'Wireframes' => ['Owner', 'HR'],
        ];

        $allowed = [];
        foreach ($menus as $menu => $roles) {
            if (in_array($this->role, $roles)) {
                $allowed[] = $menu;
            }
        }
        return $allowed;
    }
}
