<?php

namespace App\Livewire\Bookings\Extensions\Concerns;

use App\Models\Booking;
use App\Models\BookingExtension;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

trait LoadsBookingExtension
{
    public ?int $bookingId = null;

    public ?int $extensionId = null;

    public string $newCheckOutDate = '';

    public string $guestMessage = '';

    public function mountBookingExtension(Booking|int|null $booking = null, BookingExtension|int|null $extension = null): void
    {
        if ($extension instanceof BookingExtension) {
            $this->extensionId = $extension->id;
            $this->bookingId = $extension->booking_id;
            $this->newCheckOutDate = $extension->new_check_out_date?->toDateString() ?: '';
            $this->guestMessage = $extension->guest_message ?: '';

            return;
        }

        if ($extension) {
            $this->extensionId = (int) $extension;
            $resolvedExtension = $this->extension();
            $this->bookingId = $resolvedExtension?->booking_id;
            $this->newCheckOutDate = $resolvedExtension?->new_check_out_date?->toDateString() ?: '';
            $this->guestMessage = $resolvedExtension?->guest_message ?: '';

            return;
        }

        $this->bookingId = $booking instanceof Booking ? $booking->id : ($booking ? (int) $booking : null);

        if ($this->newCheckOutDate === '' && $this->booking()) {
            $this->newCheckOutDate = CarbonImmutable::parse($this->booking()?->check_out_date ?? $this->booking()?->check_out)
                ->addDay()
                ->toDateString();
        }
    }

    protected function booking(): ?Booking
    {
        if (! $this->bookingId) {
            return null;
        }

        return Booking::query()
            ->select([
                'id',
                'booking_number',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'check_in_date',
                'check_out_date',
                'check_out',
                'check_out_time',
                'status',
                'currency',
                'total_amount',
                'total',
            ])
            ->with([
                'guest:id,name',
                'host:id,name',
                'room:id,title,room_number',
                'sleepingPlace:id,display_name,place_number',
            ])
            ->find($this->bookingId);
    }

    protected function extension(): ?BookingExtension
    {
        $query = BookingExtension::query()
            ->with([
                'booking:id,booking_number,guest_user_id,host_user_id,property_id,room_id,sleeping_place_id,check_out_date,check_out_time,total_amount,total,currency',
                'guest:id,name',
                'host:id,name',
                'room:id,title,room_number',
                'sleepingPlace:id,display_name,place_number',
                'lines',
                'validationResults',
                'hostResponses',
                'guestResponses',
                'events',
            ]);

        if ($this->extensionId) {
            return $query->find($this->extensionId);
        }

        if (! $this->bookingId) {
            return null;
        }

        return $query->where('booking_id', $this->bookingId)->latest('id')->first();
    }

    /**
     * @return array<string, mixed>
     */
    protected function extensionViewData(string $variant): array
    {
        $extension = $this->extension();
        $booking = $this->booking() ?? $extension?->booking;

        return [
            'variant' => $variant,
            'booking' => $booking,
            'extension' => $extension,
            'lines' => $extension?->lines ?? collect(),
            'warnings' => $extension?->validationResults ?? collect(),
            'events' => $extension?->events ?? collect(),
            'status' => $this->statusValue($extension),
            'newCheckOutDate' => $this->newCheckOutDate,
            'guestMessage' => $this->guestMessage,
            'canRequest' => $booking !== null,
        ];
    }

    protected function statusValue(?BookingExtension $extension): string
    {
        $status = $extension?->status;

        if ($status instanceof \BackedEnum) {
            return (string) $status->value;
        }

        return $status ? (string) $status : 'draft';
    }

    /**
     * @return Collection<int, BookingExtension>
     */
    protected function hostExtensions(): Collection
    {
        return BookingExtension::query()
            ->with(['guest:id,name', 'room:id,title,room_number', 'sleepingPlace:id,display_name,place_number'])
            ->where('host_user_id', auth()->id())
            ->latest('id')
            ->limit(15)
            ->get();
    }
}
