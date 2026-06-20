<?php

namespace Tests\Feature;

use App\Livewire\Geo\CityAutocomplete;
use App\Livewire\Pages\HomePage;
use App\Livewire\Search\SleepingPlaceSearch;
use App\Livewire\Shell\FavoritesPage;
use App\Livewire\Shell\HostCalendarPage;
use App\Livewire\Shell\HostHomePage;
use App\Livewire\Shell\HostListingsPage;
use App\Livewire\Shell\HostProfilePage;
use App\Livewire\Shell\HostRequestsPage;
use App\Livewire\Shell\MessagesPage;
use App\Livewire\Trips\TripList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Livewire\Livewire;
use Tests\TestCase;

class MobileApplicationShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_shell_pages_render_in_english_and_russian(): void
    {
        $user = User::factory()->create(['is_host' => true]);

        $pages = [
            ['path' => '/', 'title' => 'app.home_heading', 'helper' => 'app.home_subtitle', 'component' => HomePage::class],
            ['path' => '/search', 'title' => 'search.title', 'helper' => 'search.helper', 'component' => SleepingPlaceSearch::class],
            ['path' => '/trips', 'title' => 'booking.trips.scopes.upcoming.title', 'helper' => 'booking.trips.scopes.upcoming.helper', 'component' => TripList::class],
            ['path' => '/favorites', 'title' => 'shell.pages.guest.favorites.title', 'helper' => 'shell.pages.guest.favorites.helper', 'component' => FavoritesPage::class],
            ['path' => '/messages', 'title' => 'shell.pages.guest.messages.title', 'helper' => 'shell.pages.guest.messages.helper', 'component' => MessagesPage::class],
        ];

        foreach (['en', 'ru'] as $locale) {
            foreach ($pages as $page) {
                $response = $this->actingAs($user)->get('/'.$locale.$page['path']);

                $response->assertOk();
                $response->assertSee(Lang::get($page['title'], [], $locale));
                $response->assertSee(Lang::get($page['helper'], [], $locale));
                $response->assertSee(Lang::get('navigation.guest_mode', [], $locale));
                $response->assertSee(Lang::get('navigation.host_mode', [], $locale));
                $response->assertSee(Lang::get('navigation.offline_banner', [], $locale));

                if ($page['component']) {
                    $response->assertSeeLivewire($page['component']);
                }
            }
        }
    }

    public function test_host_shell_pages_render_in_english_and_russian(): void
    {
        $host = User::factory()->create(['is_host' => true]);

        $pages = [
            ['path' => '/host', 'key' => 'host.home', 'component' => HostHomePage::class],
            ['path' => '/host/listings', 'key' => 'host.listings', 'component' => HostListingsPage::class],
            ['path' => '/host/calendar', 'key' => 'host.calendar', 'component' => HostCalendarPage::class],
            ['path' => '/host/requests', 'key' => 'host.requests', 'component' => HostRequestsPage::class],
            ['path' => '/host/profile', 'key' => 'host.profile', 'component' => HostProfilePage::class],
        ];

        foreach (['en', 'ru'] as $locale) {
            foreach ($pages as $page) {
                $response = $this->actingAs($host)->get('/'.$locale.$page['path']);

                $response->assertOk();
                $response->assertSeeLivewire($page['component']);
                $response->assertSee(Lang::get('shell.pages.'.$page['key'].'.title', [], $locale));
                $response->assertSee(Lang::get('shell.pages.'.$page['key'].'.helper', [], $locale));
                $response->assertSee(Lang::get('shell.pages.'.$page['key'].'.empty_title', [], $locale));
                $response->assertSee(Lang::get('navigation.host_mobile', [], $locale));
            }
        }
    }

    public function test_city_autocomplete_remains_lightweight_inside_shell_work(): void
    {
        Livewire::test(CityAutocomplete::class)
            ->set('query', 'z')
            ->assertDontSee(Lang::get('search.city_autocomplete.no_results', [], 'en'));
    }
}
