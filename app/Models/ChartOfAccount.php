<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $primaryKey = 'code';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = ['code', 'name', 'type', 'normal_balance', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function debitEntries()
    {
        return $this->hasMany(JournalEntry::class, 'debit_account', 'code');
    }

    public function creditEntries()
    {
        return $this->hasMany(JournalEntry::class, 'credit_account', 'code');
    }

    public function isDebitNormal(): bool
    {
        return $this->normal_balance === 'Debit';
    }

    public function computeBalance(): int
    {
        $debits  = JournalEntry::where('debit_account',  $this->code)->where('is_reversed', false)->sum('amount');
        $credits = JournalEntry::where('credit_account', $this->code)->where('is_reversed', false)->sum('amount');
        return $this->isDebitNormal() ? ($debits - $credits) : ($credits - $debits);
    }
}
