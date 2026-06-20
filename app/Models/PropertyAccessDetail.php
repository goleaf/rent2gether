<?php

namespace App\Models;

use Database\Factories\PropertyAccessDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyAccessDetail extends Model
{
    /** @use HasFactory<PropertyAccessDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'entry_type',
        'entrance_type',
        'has_private_entrance',
        'has_shared_entrance',
        'entrance_through_yard',
        'entrance_through_reception',
        'has_intercom',
        'has_intercom_code',
        'has_door_code',
        'has_gate_code',
        'has_key',
        'has_keycard',
        'has_electronic_lock',
        'has_smart_lock',
        'has_key_safe',
        'key_safe_location_note',
        'code_visible_after_confirmation',
        'code_visible_after_payment',
        'code_visible_on_checkin_day',
        'code_changes_after_guest',
        'key_sets_count',
        'key_pickup_method',
        'key_pickup_contact_type',
        'meet_host_required',
        'host_meeting_required',
        'meet_host_representative_required',
        'representative_meeting_available',
        'self_check_in_available',
        'self_check_in_available_at_night',
        'check_in_instruction_available',
        'entrance_photo_available',
        'door_photo_available',
        'key_safe_photo_available',
        'emergency_contact_available',
        'what_if_code_fails',
        'what_if_key_does_not_work',
        'entry_24_7',
        'access_24_7',
        'can_return_at_night',
        'night_entry_restrictions',
        'has_night_entry_restrictions',
        'night_entry_restriction_text',
        'must_be_quiet_at_night_entry',
        'guest_visitors_allowed',
        'guest_visitors_need_approval',
        'courier_rules_enabled',
        'delivery_allowed',
        'delivery_dropoff_location',
        'courier_can_enter_building',
        'courier_can_come_to_door',
        'courier_must_leave_at_entrance',
        'parcels_allowed',
        'parcel_pickup_location',
        'delivery_responsibility_note',
        'key_pickup_instruction',
        'key_return_instruction',
        'check_in_instruction',
        'night_entry_instruction',
        'door_code_encrypted',
        'intercom_code_encrypted',
        'key_safe_code_encrypted',
        'show_access_details_after_booking',
    ];

    /**
     * Defines how Laravel converts stored Property Access Detail attributes into PHP values.
     */
    protected function casts(): array
    {
        return [
            'has_private_entrance' => 'boolean',
            'has_shared_entrance' => 'boolean',
            'entrance_through_yard' => 'boolean',
            'entrance_through_reception' => 'boolean',
            'has_intercom' => 'boolean',
            'has_intercom_code' => 'boolean',
            'has_door_code' => 'boolean',
            'has_gate_code' => 'boolean',
            'has_key' => 'boolean',
            'has_keycard' => 'boolean',
            'has_electronic_lock' => 'boolean',
            'has_smart_lock' => 'boolean',
            'has_key_safe' => 'boolean',
            'code_visible_after_confirmation' => 'boolean',
            'code_visible_after_payment' => 'boolean',
            'code_visible_on_checkin_day' => 'boolean',
            'code_changes_after_guest' => 'boolean',
            'key_sets_count' => 'integer',
            'meet_host_required' => 'boolean',
            'host_meeting_required' => 'boolean',
            'meet_host_representative_required' => 'boolean',
            'representative_meeting_available' => 'boolean',
            'self_check_in_available' => 'boolean',
            'self_check_in_available_at_night' => 'boolean',
            'check_in_instruction_available' => 'boolean',
            'entrance_photo_available' => 'boolean',
            'door_photo_available' => 'boolean',
            'key_safe_photo_available' => 'boolean',
            'emergency_contact_available' => 'boolean',
            'access_24_7' => 'boolean',
            'entry_24_7' => 'boolean',
            'can_return_at_night' => 'boolean',
            'has_night_entry_restrictions' => 'boolean',
            'night_entry_restrictions' => 'boolean',
            'must_be_quiet_at_night_entry' => 'boolean',
            'guest_visitors_allowed' => 'boolean',
            'guest_visitors_need_approval' => 'boolean',
            'courier_rules_enabled' => 'boolean',
            'delivery_allowed' => 'boolean',
            'courier_can_enter_building' => 'boolean',
            'courier_can_come_to_door' => 'boolean',
            'courier_must_leave_at_entrance' => 'boolean',
            'parcels_allowed' => 'boolean',
            'show_access_details_after_booking' => 'boolean',
        ];
    }

    /**
     * Links this Property Access Detail to the Property record used by its property relation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
