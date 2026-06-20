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

    public function scopeFresh(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
