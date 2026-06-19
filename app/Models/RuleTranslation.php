<?php

namespace App\Models;

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

    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }
}
