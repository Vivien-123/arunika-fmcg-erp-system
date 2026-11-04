<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Lead — CRM pipeline (F-04, S-04)
 *
 * SR-19: A Lead converted to Opportunity must keep its customer relation.
 *
 * Stages: new → qualified → won | lost
 */
class Lead extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'id',
        'partner_id',
        'partner_name',
        'stage',
        'salesperson_id',
        'salesperson_name',
        'expected_revenue',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'expected_revenue' => 'integer',
    ];

    /** Customer associated with this lead */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'partner_id', 'id');
    }

    /** Is this lead already an Opportunity? */
    public function isOpportunity(): bool
    {
        return in_array($this->stage, ['qualified', 'won']);
    }

    /**
     * SR-19: Validate that customer relation exists before conversion.
     */
    public function canConvertToOpportunity(): bool
    {
        return !empty($this->partner_id);
    }

    /** Next sequential Lead ID: L00004 */
    public static function nextId(): string
    {
        $last = self::orderByDesc('id')->value('id');
        if (!$last) return 'L00001';
        $num = (int) substr($last, 1) + 1;
        return 'L' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
