<?php

namespace App\Models;

use App\Enums\DiscountRuleType;
use Database\Factories\DiscountRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountRule extends Model
{
    /** @use HasFactory<DiscountRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'type',
        'min_nights',
        'percent',
        'amount',
        'starts_on',
        'ends_on',
        'status',
    ];

    /**
     * Defines how Laravel converts stored Discount Rule attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'type' => DiscountRuleType::class,
            'percent' => 'decimal:2',
            'amount' => 'decimal:2',
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    /**
     * Links this Discount Rule to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
