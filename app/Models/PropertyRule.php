<?php

namespace App\Models;

use Database\Factories\PropertyRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyRule extends Model
{
    /** @use HasFactory<PropertyRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'rule_id',
        'rule_key',
        'allowed',
        'starts_at_time',
        'ends_at_time',
        'description',
        'strict',
        'visible_to_guest',
        'sort_order',
        'status',
    ];

    /**
     * Defines how Laravel converts stored Property Rule attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'allowed' => 'boolean',
            'strict' => 'boolean',
            'visible_to_guest' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Links this Property Rule to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
