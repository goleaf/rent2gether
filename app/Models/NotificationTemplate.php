<?php

namespace App\Models;

use Database\Factories\NotificationTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationTemplate extends Model
{
    /** @use HasFactory<NotificationTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'template_key',
        'notification_category',
        'title_translation_key',
        'body_translation_key',
        'short_body_translation_key',
        'default_priority',
        'default_action_type',
        'supports_in_app',
        'supports_email',
        'supports_sms_future',
        'supports_push_future',
        'supports_conversation_event',
        'requires_booking',
        'requires_action',
        'is_critical',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'supports_in_app' => 'boolean',
            'supports_email' => 'boolean',
            'supports_sms_future' => 'boolean',
            'supports_push_future' => 'boolean',
            'supports_conversation_event' => 'boolean',
            'requires_booking' => 'boolean',
            'requires_action' => 'boolean',
            'is_critical' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /**
     * Lists notifications created from this template.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
