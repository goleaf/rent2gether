<?php

namespace App\Models;

use App\Enums\GenderType;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'property_id', 'title', 'gender_type', 'description', 'capacity', 'area_sqm',
    'has_lock', 'has_window', 'has_wardrobe', 'has_desk', 'has_ac', 'has_heating',
    'has_balcony', 'rules', 'status',
])]
class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    protected $casts = [
        'gender_type' => GenderType::class,
        'has_lock' => 'boolean',
        'has_window' => 'boolean',
        'has_wardrobe' => 'boolean',
        'has_desk' => 'boolean',
        'has_ac' => 'boolean',
        'has_heating' => 'boolean',
        'has_balcony' => 'boolean',
        'rules' => 'array',
        'area_sqm' => 'decimal:1',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function scopeGender(Builder $query, GenderType $gender): void
    {
        $query->where('gender_type', $gender)->orWhere('gender_type', GenderType::Mixed);
    }
}
