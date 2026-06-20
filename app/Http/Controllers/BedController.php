<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Services\CompatibilityService;
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

        return view('beds.show', compact('bed', 'blockedDates', 'compatibilityResult'));
    }
}
