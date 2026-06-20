<?php

namespace App\Models;

use Database\Factories\BedAvailabilityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['bed_id', 'date', 'status', 'price_override', 'note'])]
class BedAvailability extends Model
{
    /** @use HasFactory<BedAvailabilityFactory> */
    use HasFactory;

    protected $casts = [
        'date' => 'date',
        'price_override' => 'decimal:2',
    ];

    /**
     * Links this Bed Availability to the Bed record used by its bed relation.
     */
    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }
}
