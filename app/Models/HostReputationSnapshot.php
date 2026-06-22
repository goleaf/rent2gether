<?php

namespace App\Models;

use Database\Factories\HostReputationSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostReputationSnapshot extends Model
{
    /** @use HasFactory<HostReputationSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'host_user_id',
        'overall_rating',
        'response_speed_rating',
        'description_accuracy_rating',
        'cleanliness_rating',
        'problem_resolution_rating',
        'honesty_rating',
        'hospitality_rating',
        'check_in_quality_rating',
        'checkout_quality_rating',
        'reviews_count',
        'completed_stays_count',
        'successful_check_ins_count',
        'host_cancellations_count',
        'confirmed_host_unresponsive_count',
        'confirmed_complaints_count',
        'resolved_complaints_count',
        'average_response_minutes',
        'verified_host',
        'trusted_host_future',
        'last_recalculated_at',
    ];

    /**
     * Defines how Laravel converts stored Host Reputation Snapshot attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'overall_rating' => 'decimal:2',
            'response_speed_rating' => 'decimal:2',
            'description_accuracy_rating' => 'decimal:2',
            'cleanliness_rating' => 'decimal:2',
            'problem_resolution_rating' => 'decimal:2',
            'honesty_rating' => 'decimal:2',
            'hospitality_rating' => 'decimal:2',
            'check_in_quality_rating' => 'decimal:2',
            'checkout_quality_rating' => 'decimal:2',
            'verified_host' => 'boolean',
            'trusted_host_future' => 'boolean',
            'last_recalculated_at' => 'datetime',
        ];
    }

    /**
     * Links this Host Reputation Snapshot to its host user.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }
}
