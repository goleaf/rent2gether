<?php

namespace Tests\Feature;

use App\Livewire\Hints\GuestHintsList;
use App\Livewire\Hints\HintDetailsSheet;
use Livewire\Livewire;
use Tests\TestCase;

class GuestHintPayloadComponentsTest extends TestCase
{
    public function test_guest_hints_list_keeps_display_payload_out_of_public_state(): void
    {
        $component = Livewire::test(GuestHintsList::class, [
            'hints' => [$this->displayHint()],
            'context' => 'detail',
        ])->assertSee(__('guest_hints.messages.high_cleanliness_rating'));

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('context', $encodedSnapshot);
        $this->assertStringNotContainsString('hintsPayload', $encodedSnapshot);
        $this->assertStringNotContainsString('guest_hints.messages.high_cleanliness_rating', $encodedSnapshot);
        $this->assertStringNotContainsString(__('guest_hints.messages.high_cleanliness_rating'), $encodedSnapshot);
    }

    public function test_hint_details_sheet_keeps_display_payload_out_of_public_state(): void
    {
        $component = Livewire::test(HintDetailsSheet::class, [
            'hint' => $this->displayHint(),
        ])
            ->call('open')
            ->assertSee(__('guest_hints.messages.high_cleanliness_rating'));

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('hintPayload', $encodedSnapshot);
        $this->assertStringContainsString('open', $encodedSnapshot);
        $this->assertStringNotContainsString('guest_hints.messages.high_cleanliness_rating', $encodedSnapshot);
        $this->assertStringNotContainsString(__('guest_hints.messages.high_cleanliness_rating'), $encodedSnapshot);
    }

    /**
     * @return array<string, mixed>
     */
    private function displayHint(): array
    {
        return [
            'key' => 'high_cleanliness_rating',
            'category' => 'trust',
            'message_key' => 'guest_hints.messages.high_cleanliness_rating',
            'message_params' => [],
            'text' => __('guest_hints.messages.high_cleanliness_rating'),
            'source' => 'trust',
        ];
    }
}
