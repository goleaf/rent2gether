<?php

namespace App\Models;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id', 'title', 'type', 'description', 'country', 'city', 'district',
    'street', 'building', 'apartment', 'floor', 'has_elevator', 'lat', 'lng',
    'show_exact_address', 'nearest_transport', 'access_instructions',
    'amenities', 'rules', 'status',
])]
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory, SoftDeletes;

    protected $casts = [
        'type' => PropertyType::class,
        'status' => PropertyStatus::class,
        'has_elevator' => 'boolean',
        'show_exact_address' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'amenities' => 'array',
        'rules' => 'array',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function beds(): HasManyThrough
    {
        return $this->hasManyThrough(Bed::class, Room::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', PropertyStatus::Active);
    }

    public function scopeInCity(Builder $query, string $city): void
    {
        $query->where('city', $city);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }
}
