<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LocalizationFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_and_russian_public_routes_render_in_the_url_locale(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('Find a bed, not just a room');

        $this->get('/ru')
            ->assertOk()
            ->assertSee('Ищите не просто комнату, а конкретное спальное место');

        $this->get('/en/search')
            ->assertOk()
            ->assertSee('Search sleeping places');

        $this->get('/ru/search')
            ->assertOk()
            ->assertSee('Поиск спальных мест');
    }

    public function test_route_locale_is_stored_in_the_session(): void
    {
        $this->get('/ru')
            ->assertOk()
            ->assertSessionHas('locale', 'ru');
    }

    public function test_authenticated_users_locale_is_stored_in_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/ru')->assertOk();

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'locale' => 'ru',
        ]);
    }

    public function test_locale_switcher_preserves_the_current_route_and_query(): void
    {
        $russianSearchUrl = route('search.index', [
            'locale' => 'ru',
            'city' => 'Berlin',
        ]);

        $this->get('/en/search?city=Berlin')
            ->assertOk()
            ->assertSee($russianSearchUrl, escape: false);
    }

    public function test_validation_messages_are_localized(): void
    {
        app()->setLocale('ru');

        $validator = Validator::make([], [
            'email' => ['required'],
        ]);

        $this->assertSame('Поле «электронная почта» обязательно.', $validator->errors()->first('email'));
    }

    public function test_translation_catalogue_has_no_missing_keys(): void
    {
        $this->assertSame(0, Artisan::call('translations:missing'));
    }
}
