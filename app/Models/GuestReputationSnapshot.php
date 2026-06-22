<?php

namespace App\Models;

use Database\Factories\GuestReputationSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestReputationSnapshot extends Model
{
    /** @use HasFactory<GuestReputationSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'guest_user_id',
        'overall_rating',
        'rules_respect_rating',
        'cleanliness_rating',
        'communication_rating',
        'punctuality_rating',
        'respect_for_roommates_rating',
        'care_for_property_rating',
        'payment_reliability_rating',
        'reviews_count',
        'completed_stays_count',
        'confirmed_no_show_count',
        'guest_cancellations_count',
        'confirmed_deposit_deductions_count',
        'confirmed_complaints_count',
        'resolved_complaints_count',
        'recommended_by_hosts_count',
        'not_recommended_by_hosts_count',
        'last_recalculated_at',
    ];

    /**
     * Defines how Laravel converts stored Guest Reputation Snapshot attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'overall_rating' => 'decimal:2',
            'rules_respect_rating' => 'decimal:2',
            'cleanliness_rating' => 'decimal:2',
            'communication_rating' => 'decimal:2',
            'punctuality_rating' => 'decimal:2',
            'respect_for_roommates_rating' => 'decimal:2',
            'care_for_property_rating' => 'decimal:2',
            'payment_reliability_rating' => 'decimal:2',
            'last_recalculated_at' => 'datetime',
        ];
    }

    /**
     * Links this Guest Reputation Snapshot to its guest user.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }
}
