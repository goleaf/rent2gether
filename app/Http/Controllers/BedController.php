<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Services\CompatibilityService;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BedController extends Controller
{
    public function show(string $locale, Bed $bed, CompatibilityService $compatibility): View
    {
        $bed->load([
            'room.property.cardMedia',
            'room.property.host.hostProfile',
            'room.beds' => fn ($q) => $q->active()->where('id', '!=', $bed->id)->limit(5),
        ]);

        $currentBookings = $bed->bookings()
            ->upcoming()
            ->select(['check_in', 'check_out'])
            ->get();

        $blockedDates = $currentBookings->flatMap(function ($booking) {
            $dates = [];
            $current = $booking->check_in->copy();
            while ($current->lt($booking->check_out)) {
                $dates[] = $current->toDateString();
                $current->addDay();
            }

            return $dates;
        })->unique()->values()->all();

        $compatibilityResult = auth()->check()
            ? $compatibility->check(auth()->user(), $bed)
            : null;

        $media = $bed->room->property->cardMedia;
        $propertyAmenityLabels = collect($bed->room->property->amenities ?: [])
            ->map(function (string $amenity): string {
                $key = 'listing.legacy_amenities.'.Str::of($amenity)->snake()->toString();

                return Lang::has($key) ? __($key) : __('listing.legacy_amenities.other');
            })
            ->all();

        return view('beds.show', compact('bed', 'blockedDates', 'compatibilityResult', 'media', 'propertyAmenityLabels'));
    }
}
