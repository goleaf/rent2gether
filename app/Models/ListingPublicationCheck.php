<?php

namespace App\Models;

use Database\Factories\ListingPublicationCheckFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingPublicationCheck extends Model
{
    /** @use HasFactory<ListingPublicationCheckFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'check_key',
        'category',
        'severity',
        'status',
        'message_key',
        'message_params_json',
        'is_required',
        'is_blocking',
        'fixed_at',
    ];

    /**
     * Defines how Laravel converts stored Listing Publication Check attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'message_params_json' => 'array',
            'is_required' => 'boolean',
            'is_blocking' => 'boolean',
            'fixed_at' => 'datetime',
        ];
    }

    /**
     * Links this Listing Publication Check to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Listing Publication Check to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Listing Publication Check to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Listing Publication Check to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Adds the open query filter for reusable Listing Publication Check lookups.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /**
     * Adds the blocking query filter for reusable Listing Publication Check lookups.
     */
    public function scopeBlocking(Builder $query): Builder
    {
        return $query->where('is_blocking', true);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDisplayArray(?string $locale = null): array
    {
        return [
            'id' => $this->id,
            'check_key' => $this->check_key,
            'category' => $this->category,
            'severity' => $this->severity,
            'status' => $this->status,
            'message_key' => $this->message_key,
            'text' => __($this->message_key, $this->message_params_json ?? [], $locale),
            'is_required' => $this->is_required,
            'is_blocking' => $this->is_blocking,
        ];
    }
}
