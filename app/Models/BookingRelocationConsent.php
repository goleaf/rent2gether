<?php

namespace App\Models;

use Database\Factories\BookingRelocationConsentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRelocationConsent extends Model
{
    /** @use HasFactory<BookingRelocationConsentFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_relocation_id',
        'user_id',
        'consent_type',
        'status',
        'message',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    /**
     * Links this consent row to the relocation that needs it.
     */
    public function relocation(): BelongsTo
    {
        return $this->belongsTo(BookingRelocation::class, 'booking_relocation_id');
    }

    /**
     * Links this consent row to the guest or host who must respond.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
