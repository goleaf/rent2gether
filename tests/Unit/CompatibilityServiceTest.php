<?php

namespace Tests\Unit;

use App\Enums\GenderType;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Models\GuestPreference;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Services\Compatibility\CompatibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompatibilityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_smoking_conflict_is_warned(): void
    {
        $result = $this->compatibility(
            preferences: ['avoids_smoking' => true],
            property: ['rules' => []],
        );

        $this->assertContains('smoking_conflict', $result['warning_reason_keys']);
        $this->assertLessThan(100, $result['score']);
    }

    public function test_pet_allergy_conflict_is_warned(): void
    {
        $result = $this->compatibility(
            preferences: ['allergies' => 'Cats and dogs'],
            property: ['rules' => []],
        );

        $this->assertContains('pets_allergy_conflict', $result['warning_reason_keys']);
        $this->assertSame('attention', $result['fit_level']);
    }

    public function test_upper_bunk_conflict_is_warned(): void
    {
        $result = $this->compatibility(
            preferences: ['wants_lower_bunk' => true],
            sleepingPlace: ['type' => SleepingPlaceType::BunkTop->value, 'bunk_level' => 'upper'],
        );

        $this->assertContains('upper_bunk_conflict', $result['warning_reason_keys']);
    }

    public function test_quiet_preference_match_is_positive(): void
    {
        $result = $this->compatibility(
            preferences: ['needs_quiet_hours' => true],
            property: ['rules' => ['quiet_hours']],
        );

        $this->assertContains('quiet_hours', $result['positive_reason_keys']);
        $this->assertNotContains('missing_quiet_hours', $result['warning_reason_keys']);
    }

    public function test_workspace_match_is_positive(): void
    {
        $result = $this->compatibility(
            preferences: ['needs_workspace' => true],
            room: ['has_desk' => true],
        );

        $this->assertContains('workspace', $result['positive_reason_keys']);
    }

    public function test_price_outside_budget_is_warned(): void
    {
        $result = $this->compatibility(
            preferences: ['preferred_budget_max' => 20],
            sleepingPlace: ['base_price_per_night' => 35],
        );

        $this->assertContains('price_above_budget', $result['warning_reason_keys']);
    }

    public function test_mixed_room_warning_is_added(): void
    {
        $result = $this->compatibility(
            preferences: ['avoids_mixed_room' => true],
            room: ['gender_policy' => GenderType::Mixed->value],
        );

        $this->assertContains('mixed_room_warning', $result['warning_reason_keys']);
    }

    /**
     * @param  array<string, mixed>  $preferences
     * @param  array<string, mixed>  $property
     * @param  array<string, mixed>  $room
     * @param  array<string, mixed>  $sleepingPlace
     * @return array<string, mixed>
     */
    private function compatibility(array $preferences = [], array $property = [], array $room = [], array $sleepingPlace = []): array
    {
        $guestPreference = GuestPreference::factory()->create([
            'preferred_budget_min' => null,
            'preferred_budget_max' => null,
            'wants_wifi' => false,
            'wants_kitchen' => false,
            'wants_washing_machine' => false,
            'wants_locker' => false,
            'wants_lower_bunk' => false,
            'avoids_mixed_room' => false,
            'avoids_smoking' => false,
            'avoids_pets' => false,
            'needs_workspace' => false,
            'needs_quiet_hours' => false,
            ...$preferences,
        ]);

        $propertyModel = Property::factory()->create([
            'rules' => ['no_smoking', 'quiet_hours'],
            'amenities' => ['wifi', 'kitchen', 'washer'],
            ...$property,
        ]);

        $roomModel = Room::factory()->for($propertyModel)->create([
            'status' => RoomStatus::Active->value,
            'gender_policy' => GenderType::Female->value,
            'has_desk' => false,
            ...$room,
        ]);

        $sleepingPlaceModel = SleepingPlace::factory()
            ->for($propertyModel)
            ->for($roomModel)
            ->create([
                'status' => SleepingPlaceStatus::Active->value,
                'type' => SleepingPlaceType::Single->value,
                'base_price_per_night' => 25,
                ...$sleepingPlace,
            ]);

        return app(CompatibilityService::class)->evaluate($guestPreference, $propertyModel, $roomModel, $sleepingPlaceModel);
    }
}
