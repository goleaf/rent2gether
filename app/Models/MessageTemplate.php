<?php

namespace App\Models;

use Database\Factories\MessageTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageTemplate extends Model
{
    /** @use HasFactory<MessageTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'template_key',
        'template_category',
        'sender_type',
        'conversation_type',
        'title_translation_key',
        'body_translation_key',
        'visible_to_guest',
        'visible_to_host',
        'requires_booking',
        'requires_check_in',
        'requires_check_out',
        'requires_active_stay',
        'creates_action',
        'action_type',
        'sort_order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'visible_to_guest' => 'boolean',
            'visible_to_host' => 'boolean',
            'requires_booking' => 'boolean',
            'requires_check_in' => 'boolean',
            'requires_check_out' => 'boolean',
            'requires_active_stay' => 'boolean',
            'creates_action' => 'boolean',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Lists usage records created when users send this template.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(MessageTemplateUsage::class);
    }
}
