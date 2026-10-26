<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePrivateRecord extends Model
{
    protected $fillable = ['employee_id', 'personal_email', 'emergency_contact', 'private_phone'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
