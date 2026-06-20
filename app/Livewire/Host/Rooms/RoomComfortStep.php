<?php

namespace App\Livewire\Host\Rooms;

use App\Livewire\Host\Rooms\Concerns\HandlesRoomStep;
use App\Models\Room;
use App\Services\Rooms\RoomComfortService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RoomComfortStep extends Component
{
    use HandlesRoomStep;

    public bool $hasHeating = false;

    public bool $hasAirConditioning = false;

    public bool $hasFan = false;

    public string $winterTemperatureLevel = '';

    public string $summerTemperatureLevel = '';

    public string $ventilationLevel = '';

    public bool $canOpenWindow = true;

    public bool $canCloseWindow = true;

    public string $lightLevel = '';

    public bool $hasCurtains = false;

    public bool $hasBlackoutCurtains = false;

    public bool $canTurnLightAtNight = false;

    public bool $canUsePersonalLampAtNight = true;

    public string $noiseLevel = '';

    public string $soundproofingLevel = '';

    public bool $quietHoursEnabled = false;

    public string $quietHoursStart = '';

    public string $quietHoursEnd = '';

    public function mount(Room $room): void
    {
        $this->mountRoom($room);
        $room->loadMissing('comfortDetails');
        $details = $room->comfortDetails;

        $this->hasHeating = (bool) ($details?->has_heating ?? $room->has_heating);
        $this->hasAirConditioning = (bool) ($details?->has_air_conditioning ?? $room->has_air_conditioning);
        $this->hasFan = (bool) $details?->has_fan;
        $this->winterTemperatureLevel = (string) ($details?->winter_temperature_level ?? '');
        $this->summerTemperatureLevel = (string) ($details?->summer_temperature_level ?? '');
        $this->ventilationLevel = (string) ($details?->ventilation_level ?? $room->ventilation_level ?? '');
        $this->canOpenWindow = (bool) ($details?->can_open_window ?? true);
        $this->canCloseWindow = (bool) ($details?->can_close_window ?? true);
        $this->lightLevel = (string) ($details?->light_level ?? $room->light_level ?? '');
        $this->hasCurtains = (bool) ($details?->has_curtains ?? $room->has_curtains);
        $this->hasBlackoutCurtains = (bool) ($details?->has_blackout_curtains ?? $room->has_blackout_curtains);
        $this->canTurnLightAtNight = (bool) ($details?->can_turn_light_at_night ?? $room->can_turn_light_at_night);
        $this->canUsePersonalLampAtNight = (bool) ($details?->can_use_personal_lamp_at_night ?? true);
        $this->noiseLevel = (string) ($details?->noise_level ?? $room->noise_level ?? '');
        $this->soundproofingLevel = (string) ($details?->soundproofing_level ?? '');
        $this->quietHoursEnabled = (bool) $details?->quiet_hours_enabled;
        $this->quietHoursStart = (string) ($details?->quiet_hours_start ?? '');
        $this->quietHoursEnd = (string) ($details?->quiet_hours_end ?? '');
    }

    public function save(RoomComfortService $comfort): void
    {
        $validated = $this->validate([
            'hasHeating' => ['boolean'],
            'hasAirConditioning' => ['boolean'],
            'hasFan' => ['boolean'],
            'winterTemperatureLevel' => ['nullable', 'string', 'max:80'],
            'summerTemperatureLevel' => ['nullable', 'string', 'max:80'],
            'ventilationLevel' => ['nullable', 'string', 'max:80'],
            'canOpenWindow' => ['boolean'],
            'canCloseWindow' => ['boolean'],
            'lightLevel' => ['nullable', 'string', 'max:80'],
            'hasCurtains' => ['boolean'],
            'hasBlackoutCurtains' => ['boolean'],
            'canTurnLightAtNight' => ['boolean'],
            'canUsePersonalLampAtNight' => ['boolean'],
            'noiseLevel' => ['nullable', 'string', 'max:80'],
            'soundproofingLevel' => ['nullable', 'string', 'max:80'],
            'quietHoursEnabled' => ['boolean'],
            'quietHoursStart' => ['nullable', 'date_format:H:i'],
            'quietHoursEnd' => ['nullable', 'date_format:H:i'],
        ], attributes: __('room.validation_attributes'));

        $room = $this->room();
        $comfort->updateComfortDetails($room, [
            'has_heating' => $validated['hasHeating'],
            'has_air_conditioning' => $validated['hasAirConditioning'],
            'has_fan' => $validated['hasFan'],
            'winter_temperature_level' => $validated['winterTemperatureLevel'] ?: null,
            'summer_temperature_level' => $validated['summerTemperatureLevel'] ?: null,
            'ventilation_level' => $validated['ventilationLevel'] ?: null,
            'can_open_window' => $validated['canOpenWindow'],
            'can_close_window' => $validated['canCloseWindow'],
            'light_level' => $validated['lightLevel'] ?: null,
            'has_curtains' => $validated['hasCurtains'],
            'has_blackout_curtains' => $validated['hasBlackoutCurtains'],
            'can_turn_light_at_night' => $validated['canTurnLightAtNight'],
            'can_use_personal_lamp_at_night' => $validated['canUsePersonalLampAtNight'],
            'noise_level' => $validated['noiseLevel'] ?: null,
            'soundproofing_level' => $validated['soundproofingLevel'] ?: null,
            'quiet_hours_enabled' => $validated['quietHoursEnabled'],
            'quiet_hours_start' => $validated['quietHoursStart'] ?: null,
            'quiet_hours_end' => $validated['quietHoursEnd'] ?: null,
        ]);

        $room->update([
            'has_heating' => $validated['hasHeating'],
            'has_air_conditioning' => $validated['hasAirConditioning'],
            'has_ac' => $validated['hasAirConditioning'],
            'light_level' => $validated['lightLevel'] ?: null,
            'noise_level' => $validated['noiseLevel'] ?: null,
            'ventilation_level' => $validated['ventilationLevel'] ?: null,
            'has_curtains' => $validated['hasCurtains'],
            'has_blackout_curtains' => $validated['hasBlackoutCurtains'],
            'can_turn_light_at_night' => $validated['canTurnLightAtNight'],
        ]);

        $this->markSaved();
    }

    public function render(): View
    {
        return view('livewire.host.rooms.room-comfort-step');
    }
}
