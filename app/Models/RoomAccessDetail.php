<?php

namespace App\Models;

use Database\Factories\RoomAccessDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomAccessDetail extends Model
{
    /** @use HasFactory<RoomAccessDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'has_door',
        'has_lock',
        'has_key',
        'key_given_to_guest',
        'shared_key',
        'key_only_with_host',
        'can_lock_from_inside',
        'can_lock_from_outside',
        'has_latch',
        'glass_door',
        'privacy_level',
        'host_entry_rules',
        'other_guests_entry_rules',
        'has_wardrobe',
        'has_shared_wardrobe',
        'has_personal_lockers',
        'personal_lockers_count',
        'lockers_have_locks',
        'has_shelves',
        'has_hangers',
        'has_luggage_space',
        'has_shoe_space',
        'has_coat_space',
        'has_bedside_table',
        'has_drawer_unit',
        'has_desk',
        'desks_count',
        'has_chairs',
        'chairs_count',
        'has_mirror',
        'has_hooks',
        'has_drying_rack',
        'can_store_food',
        'food_storage_allowed_type',
    ];

    protected function casts(): array
    {
        return [
            'has_door' => 'boolean',
            'has_lock' => 'boolean',
            'has_key' => 'boolean',
            'key_given_to_guest' => 'boolean',
            'shared_key' => 'boolean',
            'key_only_with_host' => 'boolean',
            'can_lock_from_inside' => 'boolean',
            'can_lock_from_outside' => 'boolean',
            'has_latch' => 'boolean',
            'glass_door' => 'boolean',
            'has_wardrobe' => 'boolean',
            'has_shared_wardrobe' => 'boolean',
            'has_personal_lockers' => 'boolean',
            'personal_lockers_count' => 'integer',
            'lockers_have_locks' => 'boolean',
            'has_shelves' => 'boolean',
            'has_hangers' => 'boolean',
            'has_luggage_space' => 'boolean',
            'has_shoe_space' => 'boolean',
            'has_coat_space' => 'boolean',
            'has_bedside_table' => 'boolean',
            'has_drawer_unit' => 'boolean',
            'has_desk' => 'boolean',
            'desks_count' => 'integer',
            'has_chairs' => 'boolean',
            'chairs_count' => 'integer',
            'has_mirror' => 'boolean',
            'has_hooks' => 'boolean',
            'has_drying_rack' => 'boolean',
            'can_store_food' => 'boolean',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
