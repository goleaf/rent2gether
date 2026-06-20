<?php

namespace App\Models;

use Database\Factories\HostHintSnapshotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostHintSnapshot extends Model
{
    /** @use HasFactory<HostHintSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'hint_key',
        'category',
        'type',
        'importance',
        'priority',
        'message_key',
        'message_params_json',
        'action_key',
        'action_url',
        'status',
        'source',
        'show_in_wizard',
        'show_in_dashboard',
        'show_before_publish',
        'show_on_listing_card',
        'calculated_at',
        'expires_at',
    ];

    /**
     * Defines how Laravel converts stored Host Hint Snapshot attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'message_params_json' => 'array',
            'priority' => 'integer',
            'show_in_wizard' => 'boolean',
            'show_in_dashboard' => 'boolean',
            'show_before_publish' => 'boolean',
            'show_on_listing_card' => 'boolean',
            'calculated_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Links this Host Hint Snapshot to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Host Hint Snapshot to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Host Hint Snapshot to the Room record used by its room relation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Host Hint Snapshot to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Lists related Host Hint Action records for this Host Hint Snapshot.
     */
    public function actions(): HasMany
    {
        return $this->hasMany(HostHintAction::class);
    }

    /**
     * Adds the active query filter for reusable Host Hint Snapshot lookups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Adds the fresh query filter for reusable Host Hint Snapshot lookups.
     */
    public function scopeFresh(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Checks whether this Host Hint Snapshot should block publishing until it is resolved.
     */
    public function isCriticalBeforePublish(): bool
    {
        return $this->show_before_publish && $this->importance === 'critical';
    }

    /**
     * @return array<string, mixed>
     */
    public function toDisplayArray(?string $locale = null): array
    {
        return [
            'id' => $this->id,
            'key' => $this->hint_key,
            'category' => $this->category,
            'category_label' => __('host_hints.categories.'.$this->category, [], $locale),
            'type' => $this->type,
            'importance' => $this->importance,
            'priority' => $this->priority,
            'text' => __($this->message_key, $this->message_params_json ?? [], $locale),
            'message_key' => $this->message_key,
            'message_params' => $this->message_params_json ?? [],
            'action_key' => $this->action_key,
            'action_url' => $this->action_url,
            'status' => $this->status,
            'source' => $this->source,
            'show_in_wizard' => $this->show_in_wizard,
            'show_in_dashboard' => $this->show_in_dashboard,
            'show_before_publish' => $this->show_before_publish,
            'show_on_listing_card' => $this->show_on_listing_card,
            'critical_before_publish' => $this->isCriticalBeforePublish(),
        ];
    }
}
