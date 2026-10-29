<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'document_type','document_id','action','actor_user',
        'actor_employee','description','old_values','new_values','acted_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'acted_at'   => 'datetime',
    ];

    public static function record(
        string $documentType, string $documentId, string $action,
        string $actorUser, string $description = '', ?string $actorEmployee = null,
        array $oldValues = [], array $newValues = []
    ): self {
        return self::create([
            'document_type'  => $documentType,
            'document_id'    => $documentId,
            'action'         => $action,
            'actor_user'     => $actorUser,
            'actor_employee' => $actorEmployee,
            'description'    => $description,
            'old_values'     => $oldValues ?: null,
            'new_values'     => $newValues ?: null,
            'acted_at'       => now(),
        ]);
    }

    public static function forDocument(string $type, string $id)
    {
        return self::where('document_type', $type)
                   ->where('document_id', $id)
                   ->orderByDesc('acted_at')
                   ->get();
    }

    /**
     * F-21 / S-18: query builder for the audit list page with optional filters.
     * Filters: document_ref (searches document_id), actor, action, date_from, date_to, document_type.
     */
    public static function filteredQuery(array $filters = [])
    {
        $q = self::query()->orderByDesc('acted_at');

        if (!empty($filters['document_ref'])) {
            $q->where('document_id', 'like', '%' . $filters['document_ref'] . '%');
        }
        if (!empty($filters['document_type'])) {
            $q->where('document_type', $filters['document_type']);
        }
        if (!empty($filters['actor'])) {
            $q->where(function ($q2) use ($filters) {
                $q2->where('actor_user', 'like', '%' . $filters['actor'] . '%')
                   ->orWhere('actor_employee', 'like', '%' . $filters['actor'] . '%');
            });
        }
        if (!empty($filters['action'])) {
            $q->where('action', $filters['action']);
        }
        if (!empty($filters['date_from'])) {
            $q->where('acted_at', '>=', $filters['date_from'] . ' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $q->where('acted_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        return $q;
    }

    /**
     * F-21: Return related document links for a given document_type + document_id.
     * Used by smart-buttons on any document form.
     */
    public static function relatedDocuments(string $docType, string $docId): array
    {
        // Map of parent type → child types to look up
        $childMap = [
            'purchase_order' => ['receipt', 'vendor_bill'],
            'sales_order'    => ['delivery', 'customer_invoice'],
            'delivery'       => ['customer_invoice'],
            'receipt'        => ['vendor_bill'],
        ];

        $related = [];

        if (isset($childMap[$docType])) {
            foreach ($childMap[$docType] as $childType) {
                $count = self::where('document_type', $childType)
                             ->where('description', 'like', '%' . $docId . '%')
                             ->count();
                $related[$childType] = $count;
            }
        }

        return $related;
    }

    /** Distinct actions for filter dropdown */
    public static function distinctActions(): array
    {
        return self::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->toArray();
    }

    /** Distinct document types for filter dropdown */
    public static function distinctTypes(): array
    {
        return self::query()
            ->select('document_type')
            ->distinct()
            ->orderBy('document_type')
            ->pluck('document_type')
            ->toArray();
    }
}
