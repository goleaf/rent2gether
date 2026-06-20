<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PortalPageCacheWarmingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'portal_cache.enabled' => true,
            'portal_cache.store' => 'database',
            'portal_cache.ttl_seconds' => 300,
            'portal_cache.private_ttl_seconds' => 120,
            'portal_cache.cache_guest_pages' => true,
            'portal_cache.cache_authenticated_pages' => true,
        ]);

        Cache::store('database')->flush();
    }

    public function test_guest_html_pages_are_warmed_into_database_cache_after_first_load(): void
    {
        $response = $this->get('/en/health')
            ->assertOk()
            ->assertHeader('X-Portal-Cache', 'MISS');

        $this->assertDatabaseCount('cache', 1);
        $this->continueWithResponseSession($response);

        $this->get('/en/health')
            ->assertOk()
            ->assertHeader('X-Portal-Cache', 'HIT');
    }

    public function test_authenticated_page_cache_is_scoped_per_user_session(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $response = $this->actingAs($firstUser)
            ->get('/en/profile/edit')
            ->assertOk()
            ->assertHeader('X-Portal-Cache', 'MISS');

        $this->continueWithResponseSession($response);

        $this->actingAs($firstUser)
            ->get('/en/profile/edit')
            ->assertOk()
            ->assertHeader('X-Portal-Cache', 'HIT');

        $this->actingAs($secondUser)
            ->get('/en/profile/edit')
            ->assertOk()
            ->assertHeader('X-Portal-Cache', 'MISS');
    }

    public function test_json_and_livewire_requests_do_not_use_page_cache(): void
    {
        $this->getJson('/en/health')
            ->assertOk()
            ->assertHeaderMissing('X-Portal-Cache');

        $this->withHeader('X-Livewire', 'true')
            ->get('/en/health')
            ->assertOk()
            ->assertHeaderMissing('X-Portal-Cache');

        $this->assertDatabaseCount('cache', 0);
    }

    public function test_tracking_query_parameters_are_ignored_for_cache_key(): void
    {
        $response = $this->get('/ru/health?utm_source=first')
            ->assertOk()
            ->assertHeader('X-Portal-Cache', 'MISS');

        $this->continueWithResponseSession($response);

        $this->get('/ru/health?utm_source=second')
            ->assertOk()
            ->assertHeader('X-Portal-Cache', 'HIT');
    }

    private function continueWithResponseSession(TestResponse $response): void
    {
        $sessionCookie = collect($response->headers->getCookies())
            ->first(fn ($cookie): bool => $cookie->getName() === config('session.cookie'));

        $this->assertNotNull($sessionCookie, 'Expected the response to set the session cookie.');

        $this->withUnencryptedCookie($sessionCookie->getName(), $sessionCookie->getValue());
    }
}
