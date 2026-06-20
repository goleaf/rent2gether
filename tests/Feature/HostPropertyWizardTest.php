<?php

namespace Tests\Feature;

use App\Enums\PropertyRentalUnitType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Livewire\Host\PropertyForm;
use App\Models\Amenity;
use App\Models\AmenityTranslation;
use App\Models\City;
use App\Models\Country;
use App\Models\Property;
use App\Models\Region;
use App\Models\Rule;
use App\Models\RuleTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HostPropertyWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_property_wizard_renders(): void
    {
        $host = User::factory()->create(['is_host' => true]);

        $this->actingAs($host)
            ->get(route('host.properties.create', ['locale' => 'en']))
            ->assertOk()
            ->assertSeeLivewire(PropertyForm::class)
            ->assertSee(__('host.property_wizard.steps.1.title'));
    }

    public function test_create_draft_property_after_first_step(): void
    {
        $host = User::factory()->create(['is_host' => true]);

        Livewire::actingAs($host)
            ->test(PropertyForm::class)
            ->set('rentalUnitType', PropertyRentalUnitType::SeveralSleepingPlaces->value)
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 2);

        $property = Property::query()->firstOrFail();

        $this->assertSame($host->id, $property->host_user_id);
        $this->assertSame(PropertyRentalUnitType::SeveralSleepingPlaces, $property->rental_unit_type);
        $this->assertSame(PropertyStatus::Draft, $property->status);
    }

    public function test_step_validation_is_scoped_to_current_step(): void
    {
        $host = User::factory()->create(['is_host' => true]);

        Livewire::actingAs($host)
            ->test(PropertyForm::class)
            ->call('nextStep')
            ->assertHasErrors(['rentalUnitType' => 'required'])
            ->assertSet('step', 1);

        $this->assertDatabaseCount('properties', 0);
    }

    public function test_translated_content_is_saved(): void
    {
        [$country, $region, $city] = $this->geo();
        $host = User::factory()->create(['is_host' => true]);

        Livewire::actingAs($host)
            ->test(PropertyForm::class)
            ->set('step', 5)
            ->set('rentalUnitType', PropertyRentalUnitType::SleepingPlace->value)
            ->set('propertyType', PropertyType::Apartment->value)
            ->set('countryQuery', 'Lithuania')
            ->set('countryId', $country->id)
            ->set('cityQuery', 'Vilnius')
            ->set('cityId', $city->id)
            ->set('regionName', $region->name)
            ->set('translations.en.title', 'Calm bed near the old town')
            ->set('translations.ru.title', 'Спокойное место рядом со старым городом')
            ->set('translations.en.summary', 'A simple shared stay with clear rules.')
            ->set('translations.ru.summary', 'Простое проживание с понятными правилами.')
            ->set('translations.en.description', 'Guests get one sleeping place inside a quiet apartment.')
            ->set('translations.ru.description', 'Гость получает одно спальное место в спокойной квартире.')
            ->set('translations.en.what_to_know', 'The address is shown after booking.')
            ->set('translations.ru.what_to_know', 'Адрес показывается после бронирования.')
            ->set('translations.en.suitable_for', 'Short work trips.')
            ->set('translations.ru.suitable_for', 'Короткие рабочие поездки.')
            ->set('translations.en.not_suitable_for', 'Late parties.')
            ->set('translations.ru.not_suitable_for', 'Поздние вечеринки.')
            ->call('saveStep')
            ->assertHasNoErrors();

        $property = Property::query()->with('translations')->firstOrFail();

        $this->assertSame(PropertyStatus::Draft, $property->status);
        $this->assertDatabaseHas('property_translations', [
            'property_id' => $property->id,
            'locale' => 'en',
            'title' => 'Calm bed near the old town',
            'what_to_know' => 'The address is shown after booking.',
        ]);
        $this->assertDatabaseHas('property_translations', [
            'property_id' => $property->id,
            'locale' => 'ru',
            'title' => 'Спокойное место рядом со старым городом',
            'suitable_for' => 'Короткие рабочие поездки.',
        ]);
    }

    public function test_amenities_are_saved(): void
    {
        $host = User::factory()->create(['is_host' => true]);
        $amenity = $this->amenity('wifi', 'Wi-Fi', 'Wi-Fi');
        $property = Property::factory()->for($host, 'host')->create(['user_id' => $host->id, 'status' => PropertyStatus::Draft]);

        Livewire::actingAs($host)
            ->test(PropertyForm::class, ['property' => $property])
            ->set('step', 6)
            ->set('amenityIds', [$amenity->id])
            ->call('saveStep')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('property_amenity', [
            'property_id' => $property->id,
            'amenity_id' => $amenity->id,
        ]);
    }

    public function test_rules_are_saved(): void
    {
        $host = User::factory()->create(['is_host' => true]);
        $rule = $this->rule('quiet_hours', 'Quiet hours', 'Тихие часы');
        $property = Property::factory()->for($host, 'host')->create(['user_id' => $host->id, 'status' => PropertyStatus::Draft]);

        Livewire::actingAs($host)
            ->test(PropertyForm::class, ['property' => $property])
            ->set('step', 7)
            ->set('ruleIds', [$rule->id])
            ->call('saveStep')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('property_rule', [
            'property_id' => $property->id,
            'rule_id' => $rule->id,
        ]);
    }

    public function test_property_status_moves_from_draft_to_active_on_review_save(): void
    {
        [$country, $region, $city] = $this->geo();
        $host = User::factory()->create(['is_host' => true]);
        $amenity = $this->amenity('kitchen', 'Kitchen', 'Кухня');
        $rule = $this->rule('no_smoking', 'No smoking', 'Не курить');

        Livewire::actingAs($host)
            ->test(PropertyForm::class)
            ->set('rentalUnitType', PropertyRentalUnitType::WholeProperty->value)
            ->call('nextStep')
            ->set('propertyType', PropertyType::House->value)
            ->call('nextStep')
            ->set('countryQuery', 'Lithuania')
            ->set('countryId', $country->id)
            ->set('cityQuery', 'Vilnius')
            ->set('cityId', $city->id)
            ->set('regionName', $region->name)
            ->set('district', 'Old Town')
            ->set('street', 'Pilies')
            ->set('houseNumber', '10')
            ->set('floor', 2)
            ->set('totalFloors', 4)
            ->set('hasElevator', false)
            ->call('nextStep')
            ->set('totalArea', 64.5)
            ->set('roomsCount', 3)
            ->set('bathroomsCount', 1)
            ->set('showersCount', 1)
            ->set('kitchensCount', 1)
            ->set('balconiesCount', 1)
            ->set('maxGuests', 4)
            ->set('repairState', 'good')
            ->set('noiseLevel', 'low')
            ->set('cleanlinessLevel', 'high')
            ->set('safetyLevel', 'good')
            ->call('nextStep')
            ->set('translations.en.title', 'Quiet house with shared beds')
            ->set('translations.ru.title', 'Тихий дом со спальными местами')
            ->set('translations.en.summary', 'A calm place for short stays.')
            ->set('translations.ru.summary', 'Спокойное место для коротких поездок.')
            ->call('nextStep')
            ->set('amenityIds', [$amenity->id])
            ->call('nextStep')
            ->set('ruleIds', [$rule->id])
            ->call('nextStep')
            ->call('nextStep')
            ->call('publish')
            ->assertHasNoErrors()
            ->assertRedirect();

        $property = Property::query()->firstOrFail();

        $this->assertSame(PropertyStatus::Active, $property->status);
        $this->assertSame(PropertyRentalUnitType::WholeProperty, $property->rental_unit_type);
        $this->assertSame(PropertyType::House, $property->type);
        $this->assertDatabaseHas('property_amenity', ['property_id' => $property->id, 'amenity_id' => $amenity->id]);
        $this->assertDatabaseHas('property_rule', ['property_id' => $property->id, 'rule_id' => $rule->id]);
    }

    /**
     * @return array{0: Country, 1: Region, 2: City}
     */
    private function geo(): array
    {
        $country = Country::factory()->create([
            'iso2' => 'LT',
            'code' => 'LT',
            'name_en' => 'Lithuania',
            'name_ru' => 'Литва',
        ]);
        $region = Region::factory()->for($country)->create([
            'code' => 'VL',
            'name' => 'Vilnius County',
        ]);
        $city = City::factory()->for($country)->for($region)->create([
            'name' => 'Vilnius',
            'ascii_name' => 'Vilnius',
            'population' => 542366,
            'latitude' => 54.68916,
            'longitude' => 25.2798,
        ]);

        return [$country, $region, $city];
    }

    private function amenity(string $slug, string $en, string $ru): Amenity
    {
        $amenity = Amenity::factory()->create([
            'slug' => $slug,
            'name_normalized' => str_replace('_', ' ', $slug),
        ]);

        AmenityTranslation::factory()->for($amenity)->create(['locale' => 'en', 'name' => $en]);
        AmenityTranslation::factory()->for($amenity)->create(['locale' => 'ru', 'name' => $ru]);

        return $amenity;
    }

    private function rule(string $slug, string $en, string $ru): Rule
    {
        $rule = Rule::factory()->create([
            'slug' => $slug,
            'name_normalized' => str_replace('_', ' ', $slug),
        ]);

        RuleTranslation::factory()->for($rule)->create(['locale' => 'en', 'name' => $en]);
        RuleTranslation::factory()->for($rule)->create(['locale' => 'ru', 'name' => $ru]);

        return $rule;
    }
}
