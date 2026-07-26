<?php

namespace Tests\Feature;

use App\Livewire\SavedSearches\SavedSearchCard;
use Livewire\Livewire;
use Tests\TestCase;

class SavedSearchCardPayloadTest extends TestCase
{
    public function test_saved_search_card_keeps_display_card_out_of_public_state(): void
    {
        $component = Livewire::test(SavedSearchCard::class, [
            'card' => [
                'id' => 123,
                'href' => '/en/search?saved=123',
                'title' => 'Private hospital commute search',
                'location' => 'Vilnius medical district',
                'status' => 'active',
                'status_label' => 'Active',
                'dates' => 'Jul 10 - Jul 13',
                'budget' => 'EUR 20 - EUR 40',
                'new_matches_count' => 2,
                'price_drops_count' => 1,
                'available_again_count' => 0,
                'last_checked' => 'today',
                'frequency' => 'Daily',
            ],
        ])->assertSee('Private hospital commute search');

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('"searchId"', $encodedSnapshot);
        $this->assertStringNotContainsString('"card"', $encodedSnapshot);
        $this->assertStringNotContainsString('Private hospital commute search', $encodedSnapshot);
        $this->assertStringNotContainsString('Vilnius medical district', $encodedSnapshot);
    }
}
