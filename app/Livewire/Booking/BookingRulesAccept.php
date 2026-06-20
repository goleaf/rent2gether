<?php

namespace App\Livewire\Booking;

use App\Models\Rule;
use App\Models\SleepingPlace;
use App\Services\Localization\LocalizedModelContentResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingRulesAccept extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public bool $accepted = false;

    public function mount(int $sleepingPlaceId): void
    {
        $this->sleepingPlaceId = $sleepingPlaceId;
    }

    public function updatedAccepted(bool $accepted): void
    {
        $this->dispatch('booking-rules-accepted', accepted: $accepted);
    }

    public function render(): View
    {
        return view('livewire.booking.booking-rules-accept', [
            'rulesByGroup' => $this->rulesByGroup(),
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    private function rulesByGroup(): array
    {
        $place = SleepingPlace::query()
            ->select(['id', 'room_id', 'property_id'])
            ->with([
                'rules:id,slug,category,status',
                'rules.translations:id,rule_id,locale,name',
                'room:id,property_id',
                'room.rules:id,slug,category,status',
                'room.rules.translations:id,rule_id,locale,name',
                'property:id',
                'property.rules:id,slug,category,status',
                'property.rules.translations:id,rule_id,locale,name',
            ])
            ->findOrFail($this->sleepingPlaceId);

        return collect()
            ->merge($place->property?->rules ?? collect())
            ->merge($place->room?->rules ?? collect())
            ->merge($place->rules)
            ->unique('slug')
            ->groupBy(fn (Rule $rule): string => $rule->category ?: 'shared_room_behavior')
            ->map(fn (Collection $rules): array => $rules
                ->map(fn (Rule $rule): string => $this->ruleLabel($rule))
                ->values()
                ->all())
            ->all();
    }

    private function ruleLabel(Rule $rule): string
    {
        $translation = app(LocalizedModelContentResolver::class)->resolve(
            $rule->translations,
            app()->getLocale(),
            config('localization.fallback_locale'),
        );

        return (string) ($translation?->name ?: str($rule->slug)->replace('-', ' ')->title());
    }
}
