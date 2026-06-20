<?php

namespace App\Models;

use Database\Factories\UserLanguageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLanguage extends Model
{
    /** @use HasFactory<UserLanguageFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'language_code',
        'level',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
