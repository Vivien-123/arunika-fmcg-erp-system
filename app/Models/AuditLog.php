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
}
