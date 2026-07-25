<?php

namespace App\Models;

use Database\Factories\SleepingPlaceRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceRule extends Model
{
    /** @use HasFactory<SleepingPlaceRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'sleeping_place_id',
        'rule_id',
        'rule_key',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * Links this rule assignment to the Sleeping Place it constrains.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Links this rule assignment to a catalog Rule when a catalog record exists.
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }
}
