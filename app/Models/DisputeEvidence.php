<?php

namespace App\Models;

use Database\Factories\DisputeEvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisputeEvidence extends Model
{
    /** @use HasFactory<DisputeEvidenceFactory> */
    use HasFactory;

    protected $table = 'dispute_evidence';

    protected $fillable = [
        'dispute_case_id',
        'uploaded_by_user_id',
        'evidence_type',
        'media_type',
        'evidence_role',
        'path',
        'thumbnail_path',
        'source_type',
        'source_id',
        'caption',
        'visibility',
    ];

    protected $attributes = [
        'visibility' => 'guest_and_host',
    ];

    public function disputeCase(): BelongsTo
    {
        return $this->belongsTo(DisputeCase::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
