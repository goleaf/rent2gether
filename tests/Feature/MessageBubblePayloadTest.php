<?php

namespace Tests\Feature;

use App\Livewire\Messages\MessageBubble;
use Livewire\Livewire;
use Tests\TestCase;

class MessageBubblePayloadTest extends TestCase
{
    public function test_message_bubble_keeps_message_body_out_of_public_state(): void
    {
        $component = Livewire::test(MessageBubble::class, [
            'message' => [
                'id' => 123,
                'mine' => true,
                'is_system' => false,
                'is_important' => true,
                'body' => 'Private arrival details stay render-only.',
            ],
        ])->assertSee('Private arrival details stay render-only.');

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString('Private arrival details stay render-only.', $encodedSnapshot);
        $this->assertStringNotContainsString('body', $encodedSnapshot);
    }
}
