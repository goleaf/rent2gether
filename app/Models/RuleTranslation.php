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

    protected static function booted(): void
    {
        static::saved(function (): void {
            AmenityRuleLookupService::clearRuleCache();
        });
        static::deleted(function (): void {
            AmenityRuleLookupService::clearRuleCache();
        });
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }
}
