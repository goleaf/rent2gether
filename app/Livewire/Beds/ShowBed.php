<?php

namespace App\Livewire\Beds;

use App\Models\Bed;
use App\Models\User;
use App\Services\Compatibility\CompatibilityService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ShowBed extends Component
{
    #[Locked]
    public int $bedId;

    public function mount(Bed $bed): void
    {
        $this->bedId = $bed->id;
    }

    public function render(): View
    {
        $bed = $this->bed();
        $compatibilityResult = $this->compatibilityResult($bed);
        $media = $bed->room->property->cardMedia;

        return view('livewire.beds.show-bed', [
            'bed' => $bed,
            'blockedDates' => $this->blockedDates($bed),
            'compatibilityResult' => $compatibilityResult,
            'media' => $media,
            'propertyAmenityLabels' => $this->propertyAmenityLabels($bed),
        ])->layout('layouts.app', ['title' => $bed->title]);
    }

    private function bed(): Bed
    {
        return Bed::query()
            ->with([
                'room.property.cardMedia',
                'room.property.host.hostProfile',
                'room.beds' => fn ($query) => $query
                    ->active()
                    ->where('id', '!=', $this->bedId)
                    ->limit(5),
            ])
            ->findOrFail($this->bedId);
    }

    /**
     * @return list<string>
     */
    private function blockedDates(Bed $bed): array
    {
        return $bed->bookings()
            ->upcoming()
            ->select(['check_in', 'check_out'])
            ->get()
            ->flatMap(function ($booking): array {
                $dates = [];
                $current = $booking->check_in->copy();

                while ($current->lt($booking->check_out)) {
                    $dates[] = $current->toDateString();
                    $current->addDay();
                }

                return $dates;
            })
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function compatibilityResult(Bed $bed): ?array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        return app(CompatibilityService::class)->check($user, $bed);
    }

    /**
     * @return list<string>
     */
    private function propertyAmenityLabels(Bed $bed): array
    {
        return collect($bed->room->property->amenities ?: [])
            ->map(function (string $amenity): string {
                $key = 'listing.legacy_amenities.'.Str::of($amenity)->snake()->toString();

                return Lang::has($key) ? __($key) : __('listing.legacy_amenities.other');
            })
            ->all();
    }
}
