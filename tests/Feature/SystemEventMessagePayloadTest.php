<?php

namespace Tests\Feature;

use App\Livewire\Messages\SystemEventMessage;
use Livewire\Livewire;
use Tests\TestCase;

class SystemEventMessagePayloadTest extends TestCase
{
    public function test_system_event_message_keeps_translation_params_out_of_public_state(): void
    {
        $component = Livewire::test(SystemEventMessage::class, [
            'translationKey' => 'messages.system_events.booking_created',
            'params' => [
                'guest_name' => 'Private system event guest name',
            ],
        ])->assertSee('Booking created.');

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString('params', $encodedSnapshot);
        $this->assertStringNotContainsString('Private system event guest name', $encodedSnapshot);
        $this->assertStringNotContainsString('messages.system_events.booking_created', $encodedSnapshot);
    }
}
