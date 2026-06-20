<?php

namespace App\Models;

use Database\Factories\ListingHintSnapshotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingHintSnapshot extends Model
{
    /** @use HasFactory<ListingHintSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'property_id',
        'room_id',
        'city_id',
        'hint_key',
        'category',
        'type',
        'importance',
        'priority',
        'message_key',
        'message_params_json',
        'source',
        'show_on_card',
        'show_on_detail',
        'show_before_booking',
        'show_in_favorites',
        'show_in_saved_search',
        'valid_from',
        'valid_until',
        'calculated_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'message_params_json' => 'array',
            'priority' => 'integer',
            'show_on_card' => 'boolean',
            'show_on_detail' => 'boolean',
            'show_before_booking' => 'boolean',
            'show_in_favorites' => 'boolean',
            'show_in_saved_search' => 'boolean',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'calculated_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function scopeFresh(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $builder): void {
                $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function (Builder $builder): void {
                $builder->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function (Builder $builder): void {
                $builder->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            });
    }
}
