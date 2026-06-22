<?php

namespace App\Models;

use Database\Factories\ComplaintEvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintEvidence extends Model
{
    /** @use HasFactory<ComplaintEvidenceFactory> */
    use HasFactory;

    protected $table = 'complaint_evidence';

    protected $fillable = [
        'complaint_case_id',
        'booking_id',
        'uploaded_by_user_id',
        'evidence_type',
        'media_type',
        'evidence_role',
        'path',
        'thumbnail_path',
        'message_thread_id',
        'message_id',
        'source_type',
        'source_id',
        'caption',
        'visibility',
    ];

    protected $attributes = [
        'visibility' => 'guest_and_host',
    ];

    public function complaintCase(): BelongsTo
    {
        return $this->belongsTo(ComplaintCase::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
