<?php

namespace App\Models;

use Database\Factories\ReviewPolicyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewPolicy extends Model
{
    /** @use HasFactory<ReviewPolicyFactory> */
    use HasFactory;

    protected $fillable = [
        'scope_type',
        'scope_id',
        'review_window_days',
        'edit_window_hours',
        'double_blind_enabled',
        'publish_after_both_submitted',
        'publish_after_window_expired',
        'allow_review_photos',
        'allow_host_response',
        'allow_guest_response_future',
        'minimum_stay_nights_for_review',
        'active',
    ];

    protected $attributes = [
        'scope_type' => 'global',
        'review_window_days' => 14,
        'edit_window_hours' => 24,
        'double_blind_enabled' => true,
        'publish_after_both_submitted' => true,
        'publish_after_window_expired' => true,
        'allow_review_photos' => true,
        'allow_host_response' => true,
        'allow_guest_response_future' => false,
        'minimum_stay_nights_for_review' => 1,
        'active' => true,
    ];

    /**
     * Defines how Laravel converts stored Review Policy attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'review_window_days' => 'integer',
            'edit_window_hours' => 'integer',
            'double_blind_enabled' => 'boolean',
            'publish_after_both_submitted' => 'boolean',
            'publish_after_window_expired' => 'boolean',
            'allow_review_photos' => 'boolean',
            'allow_host_response' => 'boolean',
            'allow_guest_response_future' => 'boolean',
            'minimum_stay_nights_for_review' => 'integer',
            'active' => 'boolean',
        ];
    }
}
