<?php

namespace App\Models;

use Database\Factories\HostCalendarViewSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostCalendarViewSetting extends Model
{
    /** @use HasFactory<HostCalendarViewSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'default_view',
        'default_property_id',
        'default_room_id',
        'show_prices',
        'show_guest_names',
        'show_cleaning',
        'show_repairs',
        'show_payouts',
        'show_occupancy',
        'compact_mode',
    ];

    protected $attributes = [
        'default_view' => 'property',
        'show_prices' => true,
        'show_guest_names' => true,
        'show_cleaning' => true,
        'show_repairs' => true,
        'show_payouts' => true,
        'show_occupancy' => true,
        'compact_mode' => true,
    ];

    /**
     * Defines how Laravel converts stored Host Calendar View Setting attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'show_prices' => 'boolean',
            'show_guest_names' => 'boolean',
            'show_cleaning' => 'boolean',
            'show_repairs' => 'boolean',
            'show_payouts' => 'boolean',
            'show_occupancy' => 'boolean',
            'compact_mode' => 'boolean',
        ];
    }

    /**
     * Links this Host Calendar View Setting to the User record used by its user relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Links this Host Calendar View Setting to the Property record used by its default property relation.
     */
    public function defaultProperty(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'default_property_id');
    }

    /**
     * Links this Host Calendar View Setting to the Room record used by its default room relation.
     */
    public function defaultRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'default_room_id');
    }
}
