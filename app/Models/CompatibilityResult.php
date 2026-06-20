<?php

namespace App\Models;

use Database\Factories\CompatibilityResultFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompatibilityResult extends Model
{
    /** @use HasFactory<CompatibilityResultFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'check_in_date',
        'check_out_date',
        'nights_count',
        'compatibility_score',
        'fit_status',
        'positive_reasons_json',
        'warning_reasons_json',
        'blocking_reasons_json',
        'calculated_at',
        'expires_at',
    ];

    /**
     * Defines how Laravel converts stored Compatibility Result attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'nights_count' => 'integer',
            'compatibility_score' => 'integer',
            'positive_reasons_json' => 'array',
            'warning_reasons_json' => 'array',
            'blocking_reasons_json' => 'array',
            'calculated_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Links this Compatibility Result to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Compatibility Result to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Compatibility Result to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Compatibility Result to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Adds the fresh query filter for reusable Compatibility Result lookups.
     */
    public function scopeFresh(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
