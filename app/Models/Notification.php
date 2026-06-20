<?php

namespace App\Models;

use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'user_id',
        'sleeping_place_id',
        'data',
        'title_key',
        'body_key',
        'action_url',
        'channel',
        'status',
        'read_at',
    ];

    /**
     * Defines how Laravel converts stored Notification attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    /**
     * Adds the for user query filter for reusable Notification lookups.
     */
    public function scopeForUser(Builder $query, User|int|null $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId ?: 0);
    }

    /**
     * Adds the unread query filter for reusable Notification lookups.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Adds the recent query filter for reusable Notification lookups.
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    /**
     * Checks whether this Notification has not been read yet.
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Marks this Notification as read and stores the read timestamp.
     */
    public function markRead(): void
    {
        if ($this->read_at !== null) {
            return;
        }

        $this->forceFill([
            'read_at' => now(),
            'status' => 'read',
        ])->save();
    }

    /**
     * Returns the title text for this Notification.
     */
    public function title(?string $locale = null): string
    {
        return __($this->title_key ?: 'notifications.'.$this->type.'.title', $this->translationParams(), $locale);
    }

    /**
     * Returns the body text for this Notification.
     */
    public function body(?string $locale = null): string
    {
        return __($this->body_key ?: 'notifications.'.$this->type.'.body', $this->translationParams(), $locale);
    }

    /**
     * @return array<string, scalar|null>
     */
    public function translationParams(): array
    {
        $data = is_array($this->data) ? $this->data : [];
        $params = isset($data['params']) && is_array($data['params']) ? $data['params'] : [];

        foreach (['reference', 'place', 'guest', 'host', 'date', 'deadline', 'amount', 'currency'] as $key) {
            if (! array_key_exists($key, $params) && array_key_exists($key, $data) && is_scalar($data[$key])) {
                $params[$key] = $data[$key];
            }
        }

        return $params;
    }

    /**
     * Links this Notification to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Notification to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }
}
