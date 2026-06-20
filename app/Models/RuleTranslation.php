<?php

namespace App\Models;

use App\Services\Catalog\AmenityRuleLookupService;
use Database\Factories\RuleTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuleTranslation extends Model
{
    /** @use HasFactory<RuleTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'rule_id',
        'locale',
        'name',
        'name_normalized',
        'description',
    ];

    /**
     * Registers lifecycle hooks that keep Rule Translation records consistent.
     */
    protected static function booted(): void
    {
        static::saved(function (): void {
            AmenityRuleLookupService::clearRuleCache();
        });
        static::deleted(function (): void {
            AmenityRuleLookupService::clearRuleCache();
        });
    }

    /**
     * Links this Rule Translation to the Rule record used by its rule relation.
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }
}
