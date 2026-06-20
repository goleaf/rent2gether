<?php

namespace Tests\Feature;

use App\Livewire\Pages\HealthPage;
use App\Livewire\Pages\HomePage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizedShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_in_english(): void
    {
        $this->expectsDatabaseQueryCount(2);

        $response = $this->get('/en');

        $response->assertOk();
        $response->assertSeeLivewire(HomePage::class);
        $response->assertSee('Find a bed, not just a room');
        $response->assertSee('Login');
    }

    public function test_home_page_renders_in_russian(): void
    {
        $response = $this->get('/ru');

        $response->assertOk();
        $response->assertSeeLivewire(HomePage::class);
        $response->assertSee('Ищите не просто комнату, а конкретное спальное место');
        $response->assertSee('Войти');
        $response->assertSee('Основная мобильная навигация');
        $response->assertSee('Предпочтительная цветовая схема');
        $response->assertDontSee('Search workspace...');
        $response->assertDontSee('Login');
    }

    public function test_health_page_renders_in_english(): void
    {
        $response = $this->get('/en/health');

        $response->assertOk();
        $response->assertSeeLivewire(HealthPage::class);
        $response->assertSee('Application health');
        $response->assertSee('Back to home');
    }

    public function test_health_page_renders_in_russian(): void
    {
        $response = $this->get('/ru/health');

        $response->assertOk();
        $response->assertSeeLivewire(HealthPage::class);
        $response->assertSee('Состояние приложения');
        $response->assertSee('На главную');
    }
}
