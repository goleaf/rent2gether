<?php

namespace App\Models;

use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'notification_number',
        'notification_event_id',
        'notification_template_id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'user_id',
        'recipient_user_id',
        'recipient_type',
        'notification_category',
        'notification_type',
        'priority',
        'sleeping_place_id',
        'data',
        'title_key',
        'body_key',
        'title_translation_key',
        'body_translation_key',
        'short_body_translation_key',
        'translation_params_json',
        'locale',
        'source_type',
        'source_id',
        'booking_id',
        'property_id',
        'room_id',
        'action_url',
        'action_type',
        'action_label_translation_key',
        'deduplication_key',
        'throttle_key',
        'channel',
        'status',
        'scheduled_at',
        'ready_at',
        'sent_at',
        'read_at',
        'dismissed_at',
        'action_taken_at',
        'expired_at',
        'expires_at',
        'cancelled_at',
        'archived_at',
        'is_read',
        'is_dismissed',
        'is_action_required',
        'is_urgent',
        'is_critical',
    ];

    /**
     * Defines how Laravel converts stored Notification attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'translation_params_json' => 'array',
            'scheduled_at' => 'datetime',
            'ready_at' => 'datetime',
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
            'dismissed_at' => 'datetime',
            'action_taken_at' => 'datetime',
            'expired_at' => 'datetime',
            'expires_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'archived_at' => 'datetime',
            'is_read' => 'boolean',
            'is_dismissed' => 'boolean',
            'is_action_required' => 'boolean',
            'is_urgent' => 'boolean',
            'is_critical' => 'boolean',
        ];
    }

    /**
     * Adds the for user query filter for reusable Notification lookups.
     */
    public function scopeForUser(Builder $query, User|int|null $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where(function (Builder $query) use ($userId): void {
            $query->where('user_id', $userId ?: 0)
                ->orWhere('recipient_user_id', $userId ?: 0);
        });
    }

    /**
     * Adds the point-25 recipient query filter.
     */
    public function scopeForRecipient(Builder $query, User|int|null $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('recipient_user_id', $userId ?: 0);
    }

    /**
     * Adds the unread query filter for reusable Notification lookups.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('read_at')
                ->orWhere('is_read', false);
        });
    }

    /**
     * Adds the urgent unread query filter for dashboard badges.
     */
    public function scopeUrgentUnread(Builder $query): Builder
    {
        return $query->unread()
            ->where(function (Builder $query): void {
                $query->where('is_urgent', true)
                    ->orWhere('is_critical', true)
                    ->orWhereIn('priority', ['urgent', 'critical']);
            });
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
            'is_read' => true,
        ])->save();
    }

    /**
     * Returns the title text for this Notification.
     */
    public function title(?string $locale = null): string
    {
        return __($this->title_translation_key ?: $this->title_key ?: 'notifications.'.$this->type.'.title', $this->translationParams(), $locale);
    }

    /**
     * Returns the body text for this Notification.
     */
    public function body(?string $locale = null): string
    {
        return __($this->body_translation_key ?: $this->body_key ?: 'notifications.'.$this->type.'.body', $this->translationParams(), $locale);
    }

    /**
     * @return array<string, scalar|null>
     */
    public function translationParams(): array
    {
        $data = is_array($this->data) ? $this->data : [];
        $params = isset($data['params']) && is_array($data['params']) ? $data['params'] : [];
        $newParams = is_array($this->translation_params_json) ? $this->translation_params_json : [];

        $params = array_merge($params, $newParams);

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
     * Links this Notification to the point-25 recipient user.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    /**
     * Links this Notification to the event that produced it.
     */
    public function notificationEvent(): BelongsTo
    {
        return $this->belongsTo(NotificationEvent::class);
    }

    /**
     * Links this Notification to its reusable template.
     */
    public function notificationTemplate(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class);
    }

    /**
     * Links this Notification to the booking context.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Links this Notification to the property context.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Links this Notification to the room context.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Links this Notification to the Sleeping Place record used by its sleeping place relation.
     */
    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    /**
     * Lists delivery records for each notification channel.
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    /**
     * Lists user actions attached to this notification.
     */
    public function actions(): HasMany
    {
        return $this->hasMany(NotificationAction::class);
    }

    /**
     * Lists status transitions recorded for auditability.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(NotificationStatusLog::class);
    }
}
