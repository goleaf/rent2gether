<?php

namespace App\Livewire\Bookings\CheckIn\Concerns;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Services\CheckIn\BookingCheckInInstructionService;
use App\Services\CheckIn\BookingCheckInService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;

trait LoadsBookingCheckIn
{
    #[Locked]
    public ?int $bookingId = null;

    #[Locked]
    public ?int $checkInId = null;

    public string $status = 'not_started';

    public function mount(Booking|int|null $booking = null, ?int $checkInId = null): void
    {
        if ($booking instanceof Booking) {
            $this->bookingId = $booking->id;
        } elseif ($booking !== null) {
            $this->bookingId = (int) $booking;
        }

        $this->checkInId = $checkInId;
        $this->refreshCheckInState();
    }

    protected function booking(): ?Booking
    {
        if (! $this->bookingId) {
            return null;
        }

        return Booking::query()
            ->select([
                'id',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'payment_status',
                'check_in_date',
                'check_out_date',
                'check_in_time',
                'check_out_time',
                'arrival_time',
                'check_in_instructions',
                'guest_checked_in_at',
                'host_confirmed_checkin_at',
                'checked_in_at',
            ])
            ->with([
                'guest:id,name',
                'host:id,name,phone,phone_verified,email',
                'property:id,title,city,district,address_line_1,house_number,apartment_number,show_exact_address_after_confirmation,show_exact_address_after_payment',
                'room:id,title,room_number',
                'sleepingPlace:id,display_name,place_number',
            ])
            ->find($this->bookingId);
    }

    protected function checkIn(): ?BookingCheckIn
    {
        if ($this->checkInId) {
            return BookingCheckIn::query()
                ->with(['booking', 'guest:id,name', 'host:id,name', 'room:id,title,room_number', 'sleepingPlace:id,display_name,place_number', 'checklistItems', 'problemReports', 'alerts', 'instruction', 'steps', 'media', 'problems'])
                ->find($this->checkInId);
        }

        $booking = $this->booking();

        if (! $booking) {
            return null;
        }

        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);
        $this->checkInId = $checkIn->id;

        return $checkIn->load(['booking', 'guest:id,name', 'host:id,name', 'room:id,title,room_number', 'sleepingPlace:id,display_name,place_number', 'checklistItems', 'problemReports', 'alerts', 'instruction', 'steps', 'media', 'problems']);
    }

    protected function refreshCheckInState(): void
    {
        $checkIn = $this->checkIn();

        if ($checkIn) {
            $this->checkInId = $checkIn->id;
            $this->status = $checkIn->status;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function checkInViewData(string $variant): array
    {
        $checkIn = $this->checkIn();
        $booking = $this->booking();

        return [
            'variant' => $variant,
            'booking' => $booking,
            'checkIn' => $checkIn,
            'status' => $checkIn?->status ?? $this->status,
            'instructions' => $this->visibleInstructions($checkIn),
            'hostContact' => $this->hostContact($checkIn, $booking),
            'problemOptions' => $this->translationOptions('check_in.problems'),
            'severityOptions' => $this->translationOptions('check_in.severities'),
            'mediaRoleOptions' => $this->translationOptions('check_in.media_roles'),
            'items' => $checkIn?->checklistItems ?? collect(),
            'steps' => $checkIn?->steps ?? collect(),
            'media' => $checkIn?->media ?? collect(),
            'reports' => $checkIn?->problemReports ?? collect(),
            'problems' => $checkIn?->problems ?? collect(),
            'alerts' => $checkIn?->alerts ?? collect(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function visibleInstructions(?BookingCheckIn $checkIn): ?array
    {
        $user = Auth::user();

        if (! $checkIn || ! $user || (int) $checkIn->guest_user_id !== (int) $user->id) {
            return null;
        }

        return app(BookingCheckInInstructionService::class)->getVisibleInstructions($user, $checkIn);
    }

    /**
     * @return array{name:?string, phone:?string, tel_href:?string, chat:bool}|null
     */
    private function hostContact(?BookingCheckIn $checkIn, ?Booking $booking): ?array
    {
        $user = Auth::user();

        if (! $checkIn || ! $user || (int) $checkIn->guest_user_id !== (int) $user->id) {
            return null;
        }

        if (! $booking) {
            return null;
        }

        $contact = app(BookingCheckInInstructionService::class)->getHostContact($user, $booking);
        $phone = is_string($contact['phone'] ?? null) ? $contact['phone'] : null;
        $tel = $phone ? preg_replace('/[^0-9+]/', '', $phone) : null;

        return [
            'name' => is_string($contact['name'] ?? null) ? $contact['name'] : null,
            'phone' => $phone,
            'tel_href' => $tel ? 'tel:'.$tel : null,
            'chat' => (bool) ($contact['chat'] ?? false),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function translationOptions(string $key): array
    {
        $options = __($key);

        if (! is_array($options)) {
            return [];
        }

        return collect($options)
            ->mapWithKeys(fn (string $label, string $value): array => [$value => $label])
            ->all();
    }
}
