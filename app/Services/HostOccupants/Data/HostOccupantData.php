<?php

namespace App\Services\HostOccupants\Data;

use App\Models\HostCurrentStaySnapshot;
use Illuminate\Support\Collection;

final readonly class HostOccupantData
{
    /**
     * @param  Collection<int, mixed>  $flags
     * @param  Collection<int, mixed>  $notes
     */
    public function __construct(
        public int $bookingId,
        public int $guestUserId,
        public string $guestDisplayName,
        public ?string $guestAvatarUrl,
        public string $guestAvatarAlt,
        public string $guestAvatarInitial,
        public string $roomLabel,
        public string $sleepingPlaceLabel,
        public string $checkInDate,
        public string $checkInDateLabel,
        public string $checkOutDate,
        public string $checkOutDateLabel,
        public ?int $nightsCount,
        public string $nightsCountLabel,
        public ?int $nightsLeft,
        public string $nightsLeftLabel,
        public ?string $paymentStatus,
        public string $paymentStatusLabel,
        public ?string $stayStatus,
        public string $stayStatusLabel,
        public ?string $checkInStatus,
        public string $guestContactLabel,
        public ?string $specialRequestsSummary,
        public string $specialRequestsLabel,
        public ?float $guestRating,
        public string $guestRatingLabel,
        public bool $hasComplaints,
        public int $openComplaintsCount,
        public string $complaintsLabel,
        public bool $needsCheckout,
        public string $needsCheckoutLabel,
        public bool $needsExtension,
        public string $needsExtensionLabel,
        public string $hostComment,
        public Collection $flags,
        public Collection $notes,
    ) {}

    /**
     * @param  Collection<int, mixed>  $flags
     * @param  Collection<int, mixed>  $notes
     * @param  array{chat?:bool, phone?:?string, email?:?string}  $contact
     */
    public static function fromSnapshot(HostCurrentStaySnapshot $snapshot, Collection $flags, Collection $notes, array $contact = []): self
    {
        $guestName = $snapshot->guest_display_name ?? __('current_occupants.empty.guest');

        return new self(
            bookingId: $snapshot->booking_id,
            guestUserId: $snapshot->guest_user_id,
            guestDisplayName: $guestName,
            guestAvatarUrl: self::avatarUrl($snapshot->guest_avatar_url),
            guestAvatarAlt: __('current_occupants.values.avatar_alt', ['name' => $guestName]),
            guestAvatarInitial: self::avatarInitial($guestName),
            roomLabel: $snapshot->room_label ?? __('current_occupants.empty.room'),
            sleepingPlaceLabel: $snapshot->sleeping_place_label ?? __('current_occupants.empty.sleeping_place'),
            checkInDate: $snapshot->check_in_date->toDateString(),
            checkInDateLabel: self::dateLabel($snapshot->check_in_date),
            checkOutDate: $snapshot->check_out_date->toDateString(),
            checkOutDateLabel: self::dateLabel($snapshot->check_out_date),
            nightsCount: $snapshot->nights_count,
            nightsCountLabel: self::numberLabel($snapshot->nights_count),
            nightsLeft: $snapshot->nights_left,
            nightsLeftLabel: self::numberLabel($snapshot->nights_left),
            paymentStatus: $snapshot->payment_status,
            paymentStatusLabel: self::statusLabel('payment_statuses', $snapshot->payment_status),
            stayStatus: $snapshot->stay_status,
            stayStatusLabel: self::statusLabel('stay_statuses', $snapshot->stay_status),
            checkInStatus: $snapshot->check_in_status,
            guestContactLabel: self::contactLabel($contact),
            specialRequestsSummary: $snapshot->special_requests_summary,
            specialRequestsLabel: $snapshot->special_requests_summary ?: __('current_occupants.values.no_special_requests'),
            guestRating: $snapshot->guest_rating_average === null ? null : (float) $snapshot->guest_rating_average,
            guestRatingLabel: self::ratingLabel($snapshot->guest_rating_average),
            hasComplaints: $snapshot->has_complaints,
            openComplaintsCount: $snapshot->open_complaints_count,
            complaintsLabel: $snapshot->has_complaints
                ? trans_choice('current_occupants.values.complaints_count', $snapshot->open_complaints_count, ['count' => $snapshot->open_complaints_count])
                : __('current_occupants.values.no_complaints'),
            needsCheckout: $snapshot->needs_checkout,
            needsCheckoutLabel: self::booleanLabel($snapshot->needs_checkout),
            needsExtension: $snapshot->needs_extension,
            needsExtensionLabel: self::booleanLabel($snapshot->needs_extension),
            hostComment: $snapshot->last_host_note ?: __('current_occupants.values.no_host_comment'),
            flags: $flags,
            notes: $notes,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'guest_user_id' => $this->guestUserId,
            'guest_display_name' => $this->guestDisplayName,
            'guest_avatar_url' => $this->guestAvatarUrl,
            'guest_avatar_alt' => $this->guestAvatarAlt,
            'guest_avatar_initial' => $this->guestAvatarInitial,
            'room_label' => $this->roomLabel,
            'sleeping_place_label' => $this->sleepingPlaceLabel,
            'check_in_date' => $this->checkInDate,
            'check_in_date_label' => $this->checkInDateLabel,
            'check_out_date' => $this->checkOutDate,
            'check_out_date_label' => $this->checkOutDateLabel,
            'nights_count' => $this->nightsCount,
            'nights_count_label' => $this->nightsCountLabel,
            'nights_left' => $this->nightsLeft,
            'nights_left_label' => $this->nightsLeftLabel,
            'payment_status' => $this->paymentStatus,
            'payment_status_label' => $this->paymentStatusLabel,
            'stay_status' => $this->stayStatus,
            'stay_status_label' => $this->stayStatusLabel,
            'check_in_status' => $this->checkInStatus,
            'guest_contact_label' => $this->guestContactLabel,
            'special_requests_summary' => $this->specialRequestsSummary,
            'special_requests_label' => $this->specialRequestsLabel,
            'guest_rating' => $this->guestRating,
            'guest_rating_label' => $this->guestRatingLabel,
            'has_complaints' => $this->hasComplaints,
            'open_complaints_count' => $this->openComplaintsCount,
            'complaints_label' => $this->complaintsLabel,
            'needs_checkout' => $this->needsCheckout,
            'needs_checkout_label' => $this->needsCheckoutLabel,
            'needs_extension' => $this->needsExtension,
            'needs_extension_label' => $this->needsExtensionLabel,
            'host_comment' => $this->hostComment,
            'flags' => $this->flags
                ->map(fn ($flag): array => [
                    'key' => $flag->flag_key,
                    'severity' => $flag->severity,
                    'label' => __($flag->message_key),
                ])
                ->values()
                ->all(),
            'notes_count' => $this->notes->count(),
        ];
    }

    private static function avatarUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset('storage/'.$path);
    }

    private static function avatarInitial(string $guestName): string
    {
        $name = trim($guestName);

        if ($name === '') {
            return '?';
        }

        return mb_strtoupper(mb_substr($name, 0, 1));
    }

    private static function dateLabel(mixed $date): string
    {
        return $date ? $date->translatedFormat('d M Y') : __('current_occupants.values.not_available');
    }

    private static function numberLabel(?int $value): string
    {
        return $value === null ? __('current_occupants.values.not_available') : (string) $value;
    }

    private static function statusLabel(string $group, ?string $status): string
    {
        if (! $status) {
            return __('current_occupants.values.not_available');
        }

        $key = 'current_occupants.'.$group.'.'.$status;
        $translated = __($key);

        return $translated === $key ? str((string) $status)->replace('_', ' ')->headline()->toString() : $translated;
    }

    /**
     * @param  array{chat?:bool, phone?:?string, email?:?string}  $contact
     */
    private static function contactLabel(array $contact): string
    {
        if ($contact['chat'] ?? false) {
            return __('current_occupants.values.chat_available');
        }

        return __('current_occupants.values.contact_hidden');
    }

    private static function ratingLabel(mixed $rating): string
    {
        if ($rating === null || $rating === '') {
            return __('current_occupants.values.no_rating');
        }

        return __('current_occupants.values.rating', ['rating' => number_format((float) $rating, 1)]);
    }

    private static function booleanLabel(bool $value): string
    {
        return __('current_occupants.values.'.($value ? 'yes' : 'no'));
    }
}
