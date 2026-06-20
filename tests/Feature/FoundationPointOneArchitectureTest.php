<?php

namespace Tests\Feature;

use App\Enums\UserRoleMode;
use App\Livewire\Host\Properties\PropertyCard;
use App\Livewire\Host\Rooms\RoomCard;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceCard;
use App\Livewire\SleepingPlaces\SleepingPlacePublicPage;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Domain\DomainOwnershipService;
use App\Services\Properties\PropertyService;
use App\Services\Rooms\RoomService;
use App\Services\SleepingPlaces\SleepingPlaceHierarchyService;
use App\Services\SleepingPlaces\SleepingPlaceService;
use App\Services\Users\UserRoleModeService;
use Composer\InstalledVersions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class FoundationPointOneArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_foundation_tables_fields_and_indexes_exist(): void
    {
        foreach ([
            'users',
            'user_profiles',
            'properties',
            'rooms',
            'sleeping_places',
            'property_photos',
            'room_photos',
            'sleeping_place_photos',
            'property_rules',
            'room_rules',
            'sleeping_place_rules',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table [{$table}].");
        }

        $this->assertTrue(Schema::hasColumns('users', [
            'role_mode',
            'preferred_locale',
            'timezone',
            'is_guest',
            'is_host',
            'phone_verified_at',
            'email_verified_at',
            'identity_verified_at',
        ]));

        $this->assertTrue(Schema::hasColumns('rooms', [
            'property_id',
            'user_id',
            'room_type',
            'gender_policy',
            'sleeping_places_count',
            'occupied_places_count',
            'free_sleeping_places_count',
            'has_lockable_door',
            'has_room_key',
            'has_lockers',
            'rules_text',
        ]));

        $this->assertTrue(Schema::hasColumns('sleeping_places', [
            'property_id',
            'room_id',
            'user_id',
            'title',
            'place_type',
            'bed_type',
            'is_top_bunk',
            'is_bottom_bunk',
            'is_double_place',
            'max_guests_count',
            'base_price',
            'currency',
            'status',
            'publication_status',
            'has_mattress',
            'has_privacy_curtain',
            'has_personal_lamp',
            'has_socket',
            'has_lockable_locker',
            'suitable_for_tall_guest',
            'suitable_for_heavy_guest',
            'suitable_for_couple',
            'near_passage',
        ]));

        foreach ([
            'properties_user_id_foundation_index' => ['properties', 'user_id'],
            'properties_city_district_foundation_index' => ['properties', ['city_id', 'district_id']],
            'rooms_user_id_foundation_index' => ['rooms', 'user_id'],
            'sleeping_places_user_id_foundation_index' => ['sleeping_places', 'user_id'],
            'sleeping_places_publication_status_status_index' => ['sleeping_places', ['publication_status', 'status']],
        ] as $indexName => [$table, $columns]) {
            $this->assertTrue(
                Schema::hasIndex($table, $columns) || Schema::hasIndex($table, $indexName),
                "Missing index [{$indexName}].",
            );
        }
    }

    public function test_property_room_sleeping_place_hierarchy_is_host_owned(): void
    {
        [$host, $property, $room, $place] = $this->createFoundationGraph();

        $this->assertTrue($host->properties()->whereKey($property->id)->exists());
        $this->assertTrue($host->rooms()->whereKey($room->id)->exists());
        $this->assertTrue($host->sleepingPlaces()->whereKey($place->id)->exists());
        $this->assertTrue($property->rooms()->whereKey($room->id)->exists());
        $this->assertTrue($room->sleepingPlaces()->whereKey($place->id)->exists());
        $this->assertTrue($place->property()->is($property));
        $this->assertTrue($place->room()->is($room));

        $context = app(SleepingPlaceHierarchyService::class)->getFullContext($place);

        $this->assertTrue($context['sleeping_place']->is($place));
        $this->assertTrue($context['room']->is($room));
        $this->assertTrue($context['property']->is($property));
        $this->assertTrue($context['host']->is($host));
    }

    public function test_sleeping_place_is_the_main_rentable_unit(): void
    {
        $this->assertTrue(Schema::hasColumn('bookings', 'sleeping_place_id'));
        $this->assertTrue(Schema::hasColumn('booking_price_lines', 'booking_id'));
        $this->assertTrue(Schema::hasColumn('availability_days', 'sleeping_place_id'));
        $this->assertTrue(Schema::hasColumn('reviews', 'sleeping_place_id'));
        $this->assertTrue(Schema::hasColumn('complaints', 'sleeping_place_id'));
        $this->assertTrue(Schema::hasColumn('message_threads', 'sleeping_place_id'));
        $this->assertTrue(Schema::hasColumn('notifications', 'sleeping_place_id'));
    }

    public function test_user_role_modes_are_limited_to_guest_host_and_guest_host(): void
    {
        $roles = app(UserRoleModeService::class);

        $guest = User::factory()->create();
        $host = User::factory()->host()->create();
        $guestHost = User::factory()->guestHost()->create();

        $this->assertSame(['guest', 'host', 'guest_host'], $roles->allowedModes());
        $this->assertTrue($guest->isGuest());
        $this->assertFalse($guest->isHost());
        $this->assertTrue($host->isHost());
        $this->assertTrue($guestHost->isGuest());
        $this->assertTrue($guestHost->isHost());

        $this->assertEmpty(array_intersect($roles->allowedModes(), [
            'admin',
            'support',
            'moderator',
            'staff',
            'manager',
            'cleaner',
            'finance',
        ]));
    }

    public function test_host_can_create_property_room_and_sleeping_place(): void
    {
        [$host, $property, $room, $place] = $this->createFoundationGraph();

        $this->assertSame($host->id, $property->host_user_id);
        $this->assertSame($host->id, $room->user_id);
        $this->assertSame($host->id, $place->user_id);
        $this->assertSame('single_bed', $place->place_type);
        $this->assertSame('20.00', (string) $place->base_price);
    }

    public function test_guest_cannot_create_sleeping_place_unless_host_mode_is_enabled(): void
    {
        [$host, $property, $room] = $this->createFoundationGraph();
        $guest = User::factory()->create(['role_mode' => UserRoleMode::Guest->value, 'is_guest' => true, 'is_host' => false]);

        $this->expectException(AuthorizationException::class);

        app(SleepingPlaceService::class)->create($guest, $room, [
            'title' => 'Guest-only attempt',
            'place_type' => 'single_bed',
            'base_price' => 12,
            'currency' => 'EUR',
        ]);
    }

    public function test_host_cannot_edit_another_hosts_domain_objects(): void
    {
        [$host, $property, $room, $place] = $this->createFoundationGraph();
        $otherHost = User::factory()->host()->create();
        $ownership = app(DomainOwnershipService::class);

        $this->assertTrue($ownership->canEditSleepingPlace($host, $place));
        $this->assertFalse($ownership->canEditSleepingPlace($otherHost, $place));

        $this->expectException(AuthorizationException::class);
        $ownership->ensureHostOwnsProperty($otherHost, $property);
    }

    public function test_sleeping_place_cards_and_public_page_render(): void
    {
        [$host, $property, $room, $place] = $this->createFoundationGraph();

        Livewire::actingAs($host)
            ->test(PropertyCard::class, ['propertyId' => $property->id])
            ->assertSee($property->title);

        Livewire::actingAs($host)
            ->test(RoomCard::class, ['roomId' => $room->id])
            ->assertSee($room->title);

        Livewire::actingAs($host)
            ->test(SleepingPlaceCard::class, ['sleepingPlaceId' => $place->id])
            ->assertSee($place->title);

        Livewire::test(SleepingPlacePublicPage::class, ['sleepingPlace' => $place])
            ->assertSee($place->title)
            ->assertSee($room->title)
            ->assertSee($property->title);
    }

    public function test_translations_render_in_english_and_russian(): void
    {
        app()->setLocale('en');
        $this->assertSame('Sleeping place', __('domain.entities.sleeping_place'));

        app()->setLocale('ru');
        $this->assertSame('Спальное место', __('domain.entities.sleeping_place'));
    }

    public function test_forbidden_packages_and_route_surfaces_are_absent(): void
    {
        $this->assertFalse(InstalledVersions::isInstalled('filament/filament'));
        $this->assertFalse(InstalledVersions::isInstalled('livewire/volt'));
        $this->assertFalse(InstalledVersions::isInstalled('inertiajs/inertia-laravel'));

        $forbiddenRoutes = collect(Route::getRoutes())->filter(function ($route): bool {
            $name = (string) $route->getName();
            $uri = $route->uri();

            return Str::startsWith($uri, ['admin', 'admin/', 'support', 'support/', 'staff', 'staff/'])
                || Str::startsWith($name, ['admin.', 'support.', 'staff.']);
        });

        $this->assertCount(0, $forbiddenRoutes);
    }

    public function test_http_controller_surface_has_been_removed_for_livewire_pages(): void
    {
        $this->assertDirectoryDoesNotExist(app_path('Http/Controllers'));

        $controllerRoutes = collect(Route::getRoutes())->filter(
            fn ($route): bool => Str::contains((string) $route->getActionName(), 'App\\Http\\Controllers')
        );

        $this->assertCount(0, $controllerRoutes);
    }

    public function test_view_surface_is_limited_to_livewire_views_and_support_layouts(): void
    {
        $viewsPath = resource_path('views');

        $topLevelDirectories = collect(File::directories($viewsPath))
            ->map(fn (string $path): string => basename($path))
            ->sort()
            ->values()
            ->all();

        $this->assertSame(['components', 'layouts', 'livewire'], $topLevelDirectories);

        $topLevelBladeFiles = collect(File::files($viewsPath))
            ->map(fn ($file): string => $file->getFilename())
            ->filter(fn (string $filename): bool => Str::endsWith($filename, '.blade.php'))
            ->values()
            ->all();

        $this->assertSame([], $topLevelBladeFiles);

        foreach (['auth', 'beds', 'search'] as $deletedViewDirectory) {
            $this->assertDirectoryDoesNotExist(resource_path("views/{$deletedViewDirectory}"));
        }

        $nonLivewireRenderViews = collect(File::allFiles(app_path('Livewire')))
            ->filter(fn ($file): bool => $file->getExtension() === 'php')
            ->flatMap(function ($file): array {
                preg_match_all(
                    "/return\\s+view\\(\\s*['\\\"]([^'\\\"]+)['\\\"]/",
                    (string) file_get_contents($file->getPathname()),
                    $matches,
                );

                return $matches[1];
            })
            ->reject(fn (string $view): bool => Str::startsWith($view, 'livewire.'))
            ->values()
            ->all();

        $this->assertSame([], $nonLivewireRenderViews);
    }

    /**
     * @return array{0:User,1:Property,2:Room,3:SleepingPlace}
     */
    private function createFoundationGraph(): array
    {
        $host = User::factory()->host()->create();

        $property = app(PropertyService::class)->create($host, [
            'title' => 'Foundation property',
            'property_type' => 'apartment',
            'description' => 'Host-owned property container.',
            'country' => 'Lithuania',
            'city' => 'Vilnius',
            'rooms_count' => 1,
        ]);

        $room = app(RoomService::class)->create($host, $property, [
            'title' => 'Foundation room',
            'room_type' => 'shared',
            'gender_policy' => 'mixed',
            'sleeping_places_count' => 1,
            'max_guests' => 1,
        ]);

        $place = app(SleepingPlaceService::class)->create($host, $room, [
            'title' => 'Foundation sleeping place',
            'place_type' => 'single_bed',
            'bed_type' => 'single',
            'base_price' => 20,
            'currency' => 'EUR',
            'has_mattress' => true,
            'has_bedding' => true,
            'has_socket' => true,
        ]);

        return [$host, $property, $room, $place];
    }
}
