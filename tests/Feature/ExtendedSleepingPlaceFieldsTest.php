<?php

namespace Tests\Feature;

use App\Enums\GenderType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceComfortStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceCompletionPanel;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceConditionStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceMainInfoStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlacePhysicalStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlacePositionStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlacePricingStep;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceStorageStep;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceComfortDetail;
use App\Models\SleepingPlaceConditionDetail;
use App\Models\SleepingPlacePhysicalDetail;
use App\Models\SleepingPlacePositionDetail;
use App\Models\SleepingPlaceStorageDetail;
use App\Models\User;
use App\Services\SleepingPlaces\SleepingPlaceCompletionService;
use App\Services\SleepingPlaces\SleepingPlaceGuestSummaryService;
use App\Services\SleepingPlaces\SleepingPlacePrivacyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class ExtendedSleepingPlaceFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sleeping_place_has_extended_detail_tables_relationships_indexes_and_cascade_delete(): void
    {
        $this->assertTrue(Schema::hasTable('sleeping_place_physical_details'));
        $this->assertTrue(Schema::hasTable('sleeping_place_comfort_details'));
        $this->assertTrue(Schema::hasTable('sleeping_place_storage_details'));
        $this->assertTrue(Schema::hasTable('sleeping_place_position_details'));
        $this->assertTrue(Schema::hasTable('sleeping_place_condition_details'));
        $this->assertTrue(Schema::hasColumn('sleeping_places', 'sleeping_place_type'));
        $this->assertTrue(Schema::hasColumn('sleeping_places', 'internal_name'));
        $this->assertTrue(Schema::hasColumn('sleeping_place_translations', 'what_guest_should_bring'));
        $this->assertTrue(Schema::hasIndex('sleeping_places', ['room_id', 'sort_order']));
        $this->assertTrue(Schema::hasIndex('sleeping_place_physical_details', ['sleeping_place_id'], 'unique'));
        $this->assertTrue(Schema::hasIndex('sleeping_place_comfort_details', ['has_bedding']));
        $this->assertTrue(Schema::hasIndex('sleeping_place_storage_details', ['has_personal_locker']));
        $this->assertTrue(Schema::hasIndex('sleeping_place_position_details', ['has_power_socket']));
        $this->assertTrue(Schema::hasIndex('sleeping_place_condition_details', ['needs_repair']));

        $place = SleepingPlace::factory()->create();

        SleepingPlacePhysicalDetail::factory()->for($place)->create([
            'length_cm' => 200,
            'width_cm' => 90,
            'safety_rail_available' => true,
            'suitable_for_tall_person' => true,
        ]);
        SleepingPlaceComfortDetail::factory()->for($place)->create([
            'mattress_firmness' => 'medium',
            'has_bedding' => true,
            'has_towel' => true,
        ]);
        SleepingPlaceStorageDetail::factory()->for($place)->create([
            'has_personal_locker' => true,
            'locker_has_lock' => true,
            'guest_should_bring_lock' => true,
        ]);
        SleepingPlacePositionDetail::factory()->for($place)->create([
            'privacy_level' => 'moderate',
            'has_curtain' => true,
            'has_power_socket' => true,
            'near_door' => true,
        ]);
        SleepingPlaceConditionDetail::factory()->for($place)->create([
            'condition_state' => 'good',
            'mattress_condition' => 'good',
            'squeaks' => true,
        ]);

        $place = $place->fresh([
            'physicalDetails',
            'comfortDetails',
            'storageDetails',
            'positionDetails',
            'conditionDetails',
        ]);

        $this->assertSame(200, $place->physicalDetails->length_cm);
        $this->assertSame('medium', $place->comfortDetails->mattress_firmness);
        $this->assertTrue($place->storageDetails->has_personal_locker);
        $this->assertTrue($place->positionDetails->has_power_socket);
        $this->assertTrue($place->conditionDetails->squeaks);

        $place->forceDelete();

        $this->assertDatabaseMissing('sleeping_place_physical_details', ['sleeping_place_id' => $place->id]);
        $this->assertDatabaseMissing('sleeping_place_comfort_details', ['sleeping_place_id' => $place->id]);
        $this->assertDatabaseMissing('sleeping_place_storage_details', ['sleeping_place_id' => $place->id]);
        $this->assertDatabaseMissing('sleeping_place_position_details', ['sleeping_place_id' => $place->id]);
        $this->assertDatabaseMissing('sleeping_place_condition_details', ['sleeping_place_id' => $place->id]);
    }

    public function test_sleeping_place_services_build_privacy_safe_guest_summary(): void
    {
        [$place, $guest] = $this->placeWithDetails();

        $privacy = app(SleepingPlacePrivacyService::class);

        $this->assertFalse($privacy->canShowInternalName($guest, $place, null));
        $this->assertFalse($privacy->canShowHostConditionNote($guest, $place, null));

        $summary = app(SleepingPlaceGuestSummaryService::class)->build($place->fresh([
            'property',
            'translations',
            'physicalDetails',
            'comfortDetails',
            'storageDetails',
            'positionDetails',
            'conditionDetails',
        ]), $guest);

        $encoded = json_encode($summary, JSON_THROW_ON_ERROR);

        $this->assertSame(__('sleeping_place.public.title'), $summary['title']);
        $this->assertStringContainsString(__('sleeping_place.sections.physical'), $encoded);
        $this->assertStringContainsString(__('sleeping_place.values.size_cm', ['length' => 200, 'width' => 90]), $encoded);
        $this->assertStringContainsString(__('sleeping_place.values.personal_locker_with_lock'), $encoded);
        $this->assertStringContainsString(__('sleeping_place.warnings.near_door'), $encoded);
        $this->assertStringNotContainsString('Host internal lower bunk', $encoded);
        $this->assertStringNotContainsString('Private host condition note', $encoded);
    }

    public function test_host_extended_sleeping_place_steps_update_data_and_block_other_hosts(): void
    {
        [$place] = $this->placeWithDetails();
        $host = $place->property->host;
        $otherHost = User::factory()->create(['is_host' => true]);

        Livewire::actingAs($host)
            ->test(SleepingPlaceMainInfoStep::class, ['sleepingPlace' => $place])
            ->set('translations.en.title', 'Lower bunk with curtain')
            ->set('translations.ru.title', 'Нижнее место с занавеской')
            ->set('placeNumber', '2')
            ->set('sleepingPlaceType', SleepingPlaceType::BunkBottom->value)
            ->set('bunkLevel', 'bottom')
            ->set('isBottomBunk', true)
            ->set('isForOnePerson', true)
            ->set('maxGuests', 1)
            ->set('status', SleepingPlaceStatus::Active->value)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee(__('sleeping_place.messages.saved'));

        Livewire::actingAs($host)
            ->test(SleepingPlacePhysicalStep::class, ['sleepingPlace' => $place])
            ->set('lengthCm', 205)
            ->set('widthCm', 95)
            ->set('maxWeightKg', 120)
            ->set('suitableForTallPerson', true)
            ->set('safetyRailAvailable', true)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(SleepingPlaceComfortStep::class, ['sleepingPlace' => $place])
            ->set('mattressType', 'foam')
            ->set('mattressFirmness', 'medium')
            ->set('mattressCondition', 'good')
            ->set('hasBedding', true)
            ->set('hasTowel', true)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(SleepingPlaceStorageStep::class, ['sleepingPlace' => $place])
            ->set('hasLuggageSpace', true)
            ->set('hasPersonalLocker', true)
            ->set('lockerHasLock', true)
            ->set('guestShouldBringLock', true)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(SleepingPlacePositionStep::class, ['sleepingPlace' => $place])
            ->set('privacyLevel', 'moderate')
            ->set('hasCurtain', true)
            ->set('hasPowerSocket', true)
            ->set('hasPersonalLamp', true)
            ->set('nearDoor', true)
            ->set('noiseLevelNearPlace', 'moderate')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(SleepingPlacePricingStep::class, ['sleepingPlace' => $place])
            ->set('basePricePerNight', 24.5)
            ->set('depositAmount', 30)
            ->set('cleaningFee', 5)
            ->set('minNights', 2)
            ->set('instantBookingEnabled', true)
            ->set('requiresHostApproval', false)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(SleepingPlaceConditionStep::class, ['sleepingPlace' => $place])
            ->set('conditionState', 'good')
            ->set('mattressCondition', 'good')
            ->set('squeaks', true)
            ->set('lastCheckedAt', '2026-06-20')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(SleepingPlaceCompletionPanel::class, ['sleepingPlace' => $place->fresh()])
            ->assertSee(__('sleeping_place.completion.title'));

        $this->assertGreaterThan(0, app(SleepingPlaceCompletionService::class)->percentage($place->fresh([
            'translations',
            'physicalDetails',
            'comfortDetails',
            'storageDetails',
            'positionDetails',
            'conditionDetails',
        ])));

        Livewire::actingAs($otherHost)
            ->test(SleepingPlacePhysicalStep::class, ['sleepingPlace' => $place])
            ->assertForbidden();

        $this->assertDatabaseHas('sleeping_place_physical_details', [
            'sleeping_place_id' => $place->id,
            'length_cm' => 205,
            'width_cm' => 95,
        ]);
        $this->assertDatabaseHas('sleeping_places', [
            'id' => $place->id,
            'base_price_per_night' => 24.5,
            'instant_booking_enabled' => true,
        ]);
    }

    public function test_listing_detail_renders_public_sleeping_place_profile_without_private_notes(): void
    {
        [$place] = $this->placeWithDetails();

        $this->get(route('places.show', ['locale' => 'en', 'sleepingPlace' => $place]))
            ->assertOk()
            ->assertSee(__('sleeping_place.public.title'))
            ->assertSee(__('sleeping_place.sections.comfort'))
            ->assertSee(__('sleeping_place.values.size_cm', ['length' => 200, 'width' => 90]))
            ->assertSee(__('sleeping_place.values.personal_locker_with_lock'))
            ->assertSee(__('sleeping_place.warnings.near_door'))
            ->assertDontSee('Host internal lower bunk')
            ->assertDontSee('Private host condition note');

        $this->get(route('places.show', ['locale' => 'ru', 'sleepingPlace' => $place]))
            ->assertOk()
            ->assertSee(__('sleeping_place.public.title'))
            ->assertSee('Нижнее место с занавеской');
    }

    /**
     * @return array{0: SleepingPlace, 1: User}
     */
    private function placeWithDetails(): array
    {
        $host = User::factory()->create(['is_host' => true]);
        $guest = User::factory()->create(['name' => 'Private Guest Name']);
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'user_id' => $host->id,
                'host_user_id' => $host->id,
                'status' => PropertyStatus::Active,
                'type' => PropertyType::Apartment,
                'city' => 'Vilnius',
                'district' => 'Old Town',
                'show_exact_address_before_booking' => false,
            ]);
        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'type' => RoomType::Shared,
            'gender_policy' => GenderType::Mixed,
            'sleeping_places_count' => 4,
            'free_sleeping_places_count' => 1,
            'occupied_sleeping_places_count' => 2,
            'max_guests' => 4,
        ]);

        $place = SleepingPlace::factory()->for($room)->for($property)->create([
            'status' => SleepingPlaceStatus::Active,
            'type' => SleepingPlaceType::BunkBottom,
            'sleeping_place_type' => SleepingPlaceType::BunkBottom->value,
            'place_number' => '2',
            'internal_name' => 'Host internal lower bunk',
            'display_name' => 'Lower bunk with curtain',
            'bunk_level' => 'bottom',
            'is_bottom_bunk' => true,
            'is_for_one_person' => true,
            'base_price_per_night' => 24,
            'currency' => 'EUR',
        ]);

        $place->translations()->create([
            'locale' => 'en',
            'title' => 'Lower bunk with curtain',
            'summary' => 'A lower bunk with curtain, lamp, socket, and locker.',
            'description' => 'A lower bunk with curtain, lamp, socket, and locker.',
            'what_guest_should_bring' => 'Bring your own small lock if you prefer.',
        ]);
        $place->translations()->create([
            'locale' => 'ru',
            'title' => 'Нижнее место с занавеской',
            'summary' => 'Нижнее место с занавеской, лампой, розеткой и шкафчиком.',
        ]);

        SleepingPlacePhysicalDetail::factory()->for($place)->create([
            'length_cm' => 200,
            'width_cm' => 90,
            'suitable_for_tall_person' => true,
            'safety_rail_available' => true,
        ]);
        SleepingPlaceComfortDetail::factory()->for($place)->create([
            'mattress_type' => 'foam',
            'mattress_firmness' => 'medium',
            'mattress_condition' => 'good',
            'has_bedding' => true,
            'has_towel' => true,
        ]);
        SleepingPlaceStorageDetail::factory()->for($place)->create([
            'has_luggage_space' => true,
            'has_personal_locker' => true,
            'locker_has_lock' => true,
            'guest_should_bring_lock' => true,
            'can_store_valuables' => true,
        ]);
        SleepingPlacePositionDetail::factory()->for($place)->create([
            'privacy_level' => 'moderate',
            'has_curtain' => true,
            'has_personal_lamp' => true,
            'has_power_socket' => true,
            'has_usb_charger' => true,
            'near_door' => true,
            'noise_level_near_place' => 'moderate',
        ]);
        SleepingPlaceConditionDetail::factory()->for($place)->create([
            'condition_state' => 'good',
            'mattress_condition' => 'good',
            'squeaks' => true,
            'host_condition_note' => 'Private host condition note',
            'last_checked_at' => '2026-06-20 10:00:00',
        ]);

        return [$place->fresh(), $guest];
    }
}
