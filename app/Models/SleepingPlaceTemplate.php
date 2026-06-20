<?php

namespace App\Models;

use Database\Factories\SleepingPlaceTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepingPlaceTemplate extends Model
{
    /** @use HasFactory<SleepingPlaceTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'place_type',
        'template_json',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'template_json' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
