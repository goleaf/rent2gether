<?php

namespace App\Models;

use Database\Factories\UserDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDocument extends Model
{
    /** @use HasFactory<UserDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'status',
        'file_path',
        'encrypted',
        'uploaded_at',
        'verified_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $hidden = [
        'file_path',
        'rejection_reason',
    ];

    /**
     * Defines how Laravel converts stored User Document attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'encrypted' => 'boolean',
            'uploaded_at' => 'datetime',
            'verified_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /**
     * Links this User Document to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
