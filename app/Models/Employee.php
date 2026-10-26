<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = ['id', 'name', 'department_id', 'job_title', 'location', 'status', 'mapped_user_id'];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'dept_id');
    }

    public function mappedUser()
    {
        return $this->belongsTo(User::class, 'mapped_user_id');
    }

    public function privateRecord()
    {
        return $this->hasOne(EmployeePrivateRecord::class, 'employee_id', 'id');
    }

    public function isActive(): bool { return $this->status === 'Aktif'; }

    /** SR-17, SR-18: boleh sign-off hanya jika Aktif + Warehouse + punya mapped user */
    public function canSignOff(): bool
    {
        return $this->status === 'Aktif'
            && $this->department_id === 'D-WH'
            && $this->mapped_user_id !== null;
    }
}
