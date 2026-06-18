<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizedShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_in_english(): void
    {
        $response = $this->get('/en');

        $response->assertStatus(200);
        $response->assertSee('Find a bed, not just a room');
    }

    public function test_home_page_renders_in_russian(): void
    {
        $response = $this->get('/ru');

        $response->assertStatus(200);
        $response->assertSee('Ищите не просто комнату, а конкретное спальное место');
    }

    public function test_health_page_renders_in_russian(): void
    {
        $response = $this->get('/ru/health');

        $response->assertStatus(200);
        $response->assertSee('Состояние приложения');
    }
}
