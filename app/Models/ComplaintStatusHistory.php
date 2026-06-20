<?php

namespace App\Models;

use App\Enums\ComplaintStatus;
use Database\Factories\ComplaintStatusHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintStatusHistory extends Model
{
    /** @use HasFactory<ComplaintStatusHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'complaint_id',
        'actor_user_id',
        'status',
        'note_key',
        'note',
        'metadata_json',
    ];

    /**
     * Defines how Laravel converts stored Complaint Status History attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'status' => ComplaintStatus::class,
            'metadata_json' => 'array',
        ];
    }

    /**
     * Links this Complaint Status History to the Complaint record used by its complaint relation.
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    /**
     * Links this Complaint Status History to the User record used by its actor relation.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
