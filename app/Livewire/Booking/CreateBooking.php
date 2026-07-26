<?php

namespace App\Livewire\Booking;

use App\Models\Bed;
use App\Services\Bookings\BookingPriceCalculator;
use App\Services\Bookings\BookingService;
use App\Services\Compatibility\CompatibilityService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CreateBooking extends Component
{
    #[Locked]
    public int $bedId;

    public string $checkIn = '';

    public string $checkOut = '';

    public int $guestsCount = 1;

    public string $guestMessage = '';

    public function mount(Bed $bed): void
    {
        $this->bedId = $bed->id;
    }

    #[Computed]
    public function bed(): Bed
    {
        return Bed::query()
            ->select([
                'id',
                'room_id',
                'title',
                'type',
                'max_guests',
                'price_per_night',
                'price_weekend',
                'price_weekly',
                'price_monthly',
                'cleaning_fee',
                'deposit',
                'min_nights',
                'max_nights',
                'instant_book',
                'cancellation_policy',
                'has_locker',
                'has_lamp',
                'has_luggage_space',
                'status',
            ])
            ->with([
                'room' => fn ($query) => $query->select([
                    'id',
                    'property_id',
                    'title',
                    'type',
                    'gender_policy',
                    'gender_type',
                    'max_guests',
                    'capacity',
                    'has_desk',
                    'has_chair',
                    'can_work_at_night',
                    'noise_level',
                ]),
                'room.property' => fn ($query) => $query->select([
                    'id',
                    'user_id',
                    'title',
                    'city',
                    'city_id',
                    'rules',
                    'amenities',
                    'distance_to_transport_meters',
                ]),
            ])
            ->findOrFail($this->bedId);
    }

    #[Computed]
    public function nights(): int
    {
        if ($this->checkIn && $this->checkOut) {
            try {
                $n = (int) now()->parse($this->checkIn)->diffInDays($this->checkOut);

                return max(0, $n);
            } catch (\Throwable) {
                return 0;
            }
        }

        return 0;
    }

    #[Computed]
    public function priceBreakdown(): ?array
    {
        if ($this->nights <= 0) {
            return null;
        }

        return app(BookingPriceCalculator::class)->calculate($this->bed, $this->checkIn, $this->checkOut);
    }

    /**
     * @return null|array{
     *     rows:list<array{label:string,amount:string,class:string}>,
     *     total_label:string,
     *     total_amount:string
     * }
     */
    #[Computed]
    public function priceSummary(): ?array
    {
        $breakdown = $this->priceBreakdown;

        if (! $breakdown) {
            return null;
        }

        $currency = 'EUR';
        $rows = [
            [
                'label' => trans_choice('booking.nights_count', (int) $breakdown['nights'], ['count' => (int) $breakdown['nights']]),
                'amount' => $this->money($breakdown['subtotal'], $currency),
                'class' => 'flex justify-between',
            ],
        ];

        if ((float) $breakdown['discount'] > 0) {
            $rows[] = [
                'label' => __('booking.discount'),
                'amount' => $this->money(-abs((float) $breakdown['discount']), $currency),
                'class' => 'flex justify-between text-green-600',
            ];
        }

        if ((float) $breakdown['cleaning_fee'] > 0) {
            $rows[] = [
                'label' => __('booking.cleaning_fee'),
                'amount' => $this->money($breakdown['cleaning_fee'], $currency),
                'class' => 'flex justify-between',
            ];
        }

        if ((float) $breakdown['deposit'] > 0) {
            $rows[] = [
                'label' => __('booking.deposit'),
                'amount' => $this->money($breakdown['deposit'], $currency),
                'class' => 'flex justify-between',
            ];
        }

        $rows[] = [
            'label' => __('booking.service_fee'),
            'amount' => $this->money($breakdown['service_fee'], $currency),
            'class' => 'flex justify-between',
        ];

        return [
            'rows' => $rows,
            'total_label' => __('booking.total'),
            'total_amount' => $this->money($breakdown['total'], $currency),
        ];
    }

    #[Computed]
    public function compatibility(): ?array
    {
        if (! auth()->check()) {
            return null;
        }

        return app(CompatibilityService::class)->check(auth()->user(), $this->bed);
    }

    /**
     * @return null|array{score_label:string,score_class:string,warnings:list<string>,matches:list<string>}
     */
    #[Computed]
    public function compatibilitySummary(): ?array
    {
        $compatibility = $this->compatibility;

        if (! $compatibility) {
            return null;
        }

        $score = (int) $compatibility['score'];

        return [
            'score_label' => $score.'%',
            'score_class' => match (true) {
                $score >= 70 => 'text-green-600',
                $score >= 40 => 'text-yellow-600',
                default => 'text-red-600',
            },
            'warnings' => $compatibility['warnings'],
            'matches' => $compatibility['matches'],
        ];
    }

    #[Computed]
    public function roomOccupancy(): array
    {
        $room = $this->bed->room;
        $totalBeds = $room->beds()->active()->count();
        $bookedBeds = 0;

        if ($this->checkIn && $this->checkOut) {
            $bookedBeds = $room->beds()
                ->active()
                ->where('id', '!=', $this->bed->id)
                ->whereHas('bookings', function ($q) {
                    $q->whereNotIn('status', ['cancelled_guest', 'cancelled_host', 'cancelled_system', 'no_show'])
                        ->whereDate('check_in', '<', $this->checkOut)
                        ->whereDate('check_out', '>', $this->checkIn);
                })
                ->count();
        }

        return [
            'total' => $totalBeds,
            'occupied' => $bookedBeds,
            'free' => $totalBeds - $bookedBeds,
            'with_you' => $bookedBeds + 1,
        ];
    }

    public function book(): void
    {
        $bed = $this->bed;

        $this->validate([
            'checkIn' => ['required', 'date', 'after_or_equal:today'],
            'checkOut' => ['required', 'date', 'after:checkIn'],
            'guestsCount' => ['required', 'integer', 'min:1', 'max:'.$bed->max_guests],
        ]);

        $result = app(BookingService::class)->create(
            auth()->user(),
            $bed,
            $this->checkIn,
            $this->checkOut,
            $this->guestsCount,
            $this->guestMessage ?: null,
        );

        if (! $result['success']) {
            $this->addError('booking', $result['error']);

            return;
        }

        session()->flash('success', __('notifications.flash.booking_created'));
        $this->redirect(route('guest.bookings.show', [
            'locale' => app()->getLocale(),
            'booking' => $result['booking'],
        ]), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.booking.create-booking', [
            'bed' => $this->bed,
            'priceSummary' => $this->priceSummary,
            'compatibilitySummary' => $this->compatibilitySummary,
        ]);
    }

    private function money(mixed $amount, string $currency): string
    {
        return Number::currency((float) $amount, $currency, app()->getLocale());
    }
}
