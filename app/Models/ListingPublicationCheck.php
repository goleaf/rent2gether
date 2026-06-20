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

    protected function casts(): array
    {
        return [
            'message_params_json' => 'array',
            'is_required' => 'boolean',
            'is_blocking' => 'boolean',
            'fixed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

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
