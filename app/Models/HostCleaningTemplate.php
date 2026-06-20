<?php

namespace App\Models;

use Database\Factories\HostCleaningTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostCleaningTemplate extends Model
{
    /** @use HasFactory<HostCleaningTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'cleaning_type',
        'target_type',
        'items_json',
        'is_default',
    ];

    protected $attributes = [
        'is_default' => false,
    ];

    /**
     * Defines how Laravel converts stored Host Cleaning Template attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'items_json' => 'array',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Links this Host Cleaning Template to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
