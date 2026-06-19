<?php

namespace App\Models;

use App\Enums\PriceRuleType;
use Database\Factories\PriceRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceRule extends Model
{
    /** @use HasFactory<PriceRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'type',
        'starts_on',
        'ends_on',
        'price',
        'currency',
        'min_nights',
        'days_of_week_json',
        'priority',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => PriceRuleType::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
            'price' => 'decimal:2',
            'days_of_week_json' => 'array',
        ];
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
