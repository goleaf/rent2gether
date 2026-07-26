<?php

namespace App\Livewire\Listings\Detail\Concerns;

use App\Models\SleepingPlace;
use App\Services\SleepingPlaces\SleepingPlaceGuestSummaryService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

trait LoadsSleepingPlaceProfileSection
{
    #[Locked]
    public int $sleepingPlaceId;

    protected function mountSleepingPlaceSection(int|SleepingPlace $sleepingPlace): void
    {
        $this->sleepingPlaceId = $sleepingPlace instanceof SleepingPlace ? $sleepingPlace->id : $sleepingPlace;
    }

    /**
     * @return array{title:string,badges:list<string>,sections:list<array{key:string,title:string,open:bool,items:list<array{label:string,value:string}>,warnings:list<string>}>}
     */
    #[Computed]
    public function profile(): array
    {
        return app(SleepingPlaceGuestSummaryService::class)->build($this->place(), auth()->user());
    }

    protected function place(): SleepingPlace
    {
        return SleepingPlace::query()
            ->select(['id', 'property_id', 'room_id', 'type', 'sleeping_place_type', 'status', 'place_number', 'display_name', 'bunk_level', 'max_guests', 'instant_booking_enabled'])
            ->with([
                'property:id,host_user_id,user_id',
                'translations:id,sleeping_place_id,locale,title,short_description,summary,main_pros,important_cons,special_notes,what_is_included,what_guest_should_bring,storage_instructions,safety_notes',
                'physicalDetails:id,sleeping_place_id,length_cm,width_cm,height_cm,clearance_above_cm,max_weight_kg,suitable_for_tall_person,suitable_for_heavy_person,suitable_for_elderly,suitable_for_limited_mobility,not_suitable_for_limited_mobility,frame_stability_level,squeak_level',
                'comfortDetails:id,sleeping_place_id,mattress_type,mattress_firmness,mattress_thickness_cm,mattress_condition,has_mattress_protector,mattress_has_stains,mattress_has_smell,mattress_sags,has_pillow,has_blanket,has_bedding,bedding_changed_before_guest,has_towel',
                'storageDetails:id,sleeping_place_id,has_shoe_space,has_luggage_space,has_personal_locker,locker_has_lock,guest_should_bring_lock,can_store_valuables,can_store_documents,can_store_laptop,locker_size,storage_responsibility_note',
                'positionDetails:id,sleeping_place_id,privacy_level,has_curtain,has_personal_lamp,has_power_socket,power_sockets_count,has_usb_charger,has_shelf,has_hook,near_door,near_window,near_radiator,near_air_conditioner,near_passage,noise_level_near_place,light_level_near_place,morning_light,draft_nearby',
                'conditionDetails:id,sleeping_place_id,condition_state,frame_condition,mattress_condition,bedding_condition,curtain_condition,lamp_condition,socket_condition,locker_condition,has_damage,has_stains,has_smell,squeaks,needs_repair,needs_mattress_replacement,needs_bedding_replacement,last_cleaned_at,last_bedding_changed_at,last_checked_at',
            ])
            ->findOrFail($this->sleepingPlaceId);
    }

    /**
     * @return array{key:string,title:string,open:bool,items:list<array{label:string,value:string}>,warnings:list<string>}|null
     */
    protected function section(string $key): ?array
    {
        foreach ($this->profile['sections'] ?? [] as $section) {
            if (($section['key'] ?? null) === $key) {
                return $section;
            }
        }

        return null;
    }
}
