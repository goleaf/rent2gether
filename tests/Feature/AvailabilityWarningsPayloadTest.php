<?php

namespace Tests\Feature;

use App\Livewire\Bookings\Availability\AvailabilityWarnings;
use Livewire\Livewire;
use Tests\TestCase;

class AvailabilityWarningsPayloadTest extends TestCase
{
    public function test_availability_warnings_keep_reason_keys_out_of_public_state(): void
    {
        $component = Livewire::test(AvailabilityWarnings::class, [
            'reasons' => ['range_overlaps_existing_booking'],
        ])->assertSee('The selected dates overlap another booking or active hold.');

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString('reasons', $encodedSnapshot);
        $this->assertStringNotContainsString('range_overlaps_existing_booking', $encodedSnapshot);
    }
}
