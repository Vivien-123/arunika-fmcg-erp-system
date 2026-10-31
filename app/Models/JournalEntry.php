<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * JournalEntry — balanced debit/credit pair (M4)
 * SR-16: idempotency_key unique constraint prevents double posting.
 * F-14 : Perpetual standard cost, AR/AP, tax, COGS entries.
 */
class JournalEntry extends Model
{
    protected $fillable = [
        'invoice_id', 'journal_type', 'reference', 'idempotency_key',
        'debit_account', 'credit_account', 'amount',
        'description', 'posted_by', 'posted_at', 'is_reversed', 'reversed_by_entry_id',
    ];

    protected $casts = [
        'posted_at'   => 'datetime',
        'is_reversed' => 'boolean',
    ];

    public function invoice()       { return $this->belongsTo(Invoice::class, 'invoice_id'); }
    public function debitAccount()  { return $this->belongsTo(ChartOfAccount::class, 'debit_account',  'code'); }
    public function creditAccount() { return $this->belongsTo(ChartOfAccount::class, 'credit_account', 'code'); }

    /** Build deterministic idempotency key (SR-16) */
    public static function buildIdempotencyKey(string $journalType, string $reference, string $debitAcc, string $creditAcc, int $amount): string
    {
        return md5("{$journalType}|{$reference}|{$debitAcc}|{$creditAcc}|{$amount}");
    }

    /** SR-16: Returns true if this key already exists */
    public static function alreadyPosted(string $idempotencyKey): bool
    {
        return self::where('idempotency_key', $idempotencyKey)->exists();
    }
}
