<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $primaryKey = 'dept_id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = ['dept_id', 'name', 'manager_employee', 'company'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id', 'dept_id');
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_employee', 'id');
    }
}
