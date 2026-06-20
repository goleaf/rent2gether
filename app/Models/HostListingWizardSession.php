<?php

namespace App\Models;

use Database\Factories\HostListingWizardSessionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostListingWizardSession extends Model
{
    /** @use HasFactory<HostListingWizardSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'current_step',
        'completed_steps_json',
        'skipped_steps_json',
        'last_saved_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'completed_steps_json' => 'array',
            'skipped_steps_json' => 'array',
            'last_saved_at' => 'datetime',
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

    public function scopeForHost(Builder $query, User|int $host): Builder
    {
        $id = $host instanceof User ? $host->id : $host;

        return $query->where('user_id', $id);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }
}
