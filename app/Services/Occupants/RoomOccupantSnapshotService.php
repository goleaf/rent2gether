<?php

namespace App\Services\Occupants;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\CoLivingProfile;
use App\Models\CoLivingVisibilitySetting;
use App\Models\RoomOccupantSnapshot;
use App\Models\User;

class RoomOccupantSnapshotService
{
    public function createFromBooking(Booking $booking): RoomOccupantSnapshot
    {
        return $this->refreshFromBooking($booking);
    }

    public function refreshFromBooking(Booking $booking): RoomOccupantSnapshot
    {
        $booking->loadMissing([
            'guest.coLivingProfile.country',
            'guest.coLivingProfile.city',
            'guest.coLivingVisibilitySetting',
            'room:id',
            'sleepingPlace:id',
        ]);

        $guest = $booking->guest;
        $profile = $guest?->coLivingProfile;
        $settings = $guest?->coLivingVisibilitySetting;
        $status = $this->snapshotStatus($booking);
        $visible = in_array($status, RoomOccupantSnapshot::visibleStatuses(), true);

        return RoomOccupantSnapshot::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'room_id' => $booking->room_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'user_id' => $booking->guest_user_id,
                'status' => $status,
                'check_in_date' => $booking->check_in_date?->toDateString() ?: $booking->check_in?->toDateString(),
                'check_out_date' => $booking->check_out_date?->toDateString() ?: $booking->check_out?->toDateString(),
                'public_alias_snapshot' => $this->alias($guest, $profile, $settings),
                'age_range_snapshot' => $settings?->show_age_range === false ? null : $profile?->age_range,
                'gender_for_room_policy_snapshot' => $settings?->show_gender_if_room_policy === false ? null : $profile?->gender_for_room_policy,
                'country_label_snapshot' => $settings?->show_country ? $profile?->country?->name : null,
                'city_label_snapshot' => $settings?->show_city ? $profile?->city?->name : null,
                'languages_json_snapshot' => $settings?->show_languages === false ? [] : $this->languages($profile?->languages_json),
                'stay_purpose_snapshot' => $settings?->show_stay_purpose === false ? null : $profile?->stay_purpose,
                'guest_type_snapshot' => $settings?->show_guest_type === false ? null : $profile?->guest_type,
                'tourist_snapshot' => $settings?->show_guest_type === false ? null : $profile?->tourist,
                'student_snapshot' => $settings?->show_guest_type === false ? null : $profile?->student,
                'working_snapshot' => $settings?->show_guest_type === false ? null : $profile?->working,
                'remote_worker_snapshot' => $settings?->show_guest_type === false ? null : $profile?->remote_worker,
                'long_term_guest_snapshot' => $settings?->show_guest_type === false ? null : $profile?->long_term_guest,
                'short_term_guest_snapshot' => $settings?->show_guest_type === false ? null : $profile?->short_term_guest,
                'sleep_schedule_snapshot' => $settings?->show_sleep_schedule === false ? null : $profile?->sleep_schedule,
                'wake_schedule_snapshot' => $settings?->show_wake_schedule ? $profile?->wake_schedule : null,
                'home_presence_level_snapshot' => $settings?->show_home_presence === false ? null : $profile?->home_presence_level,
                'smokes_snapshot' => $settings?->show_smoking_status === false ? null : $profile?->smokes,
                'social_level_snapshot' => $settings?->show_social_level === false ? null : $profile?->social_level,
                'prefers_quiet_snapshot' => $settings?->show_quiet_preference === false ? null : $profile?->prefers_quiet,
                'roommate_rating_average_snapshot' => $settings?->show_roommate_rating === false ? null : $profile?->roommate_rating_average,
                'roommate_reviews_count_snapshot' => $settings?->show_roommate_rating === false ? 0 : (int) $profile?->roommate_reviews_count,
                'privacy_level' => $settings?->allow_profile_after_confirmed_booking ? 'confirmed' : 'summary',
                'can_show_before_booking' => $visible && (bool) ($settings?->allow_profile_in_prebooking_summary ?? true),
                'can_show_after_booking' => $visible && (bool) ($settings?->allow_profile_after_confirmed_booking ?? true),
            ],
        );
    }

    public function refreshForUser(User $user): int
    {
        $bookings = Booking::query()
            ->where('guest_user_id', $user->id)
            ->with(['guest.coLivingProfile', 'guest.coLivingVisibilitySetting'])
            ->get();

        $bookings->each(fn (Booking $booking): RoomOccupantSnapshot => $this->refreshFromBooking($booking));

        return $bookings->count();
    }

    public function markCheckedOut(Booking $booking): void
    {
        RoomOccupantSnapshot::query()
            ->where('booking_id', $booking->id)
            ->update(['status' => RoomOccupantSnapshot::STATUS_CHECKED_OUT]);
    }

    public function markCancelled(Booking $booking): void
    {
        RoomOccupantSnapshot::query()
            ->where('booking_id', $booking->id)
            ->update([
                'status' => RoomOccupantSnapshot::STATUS_CANCELLED,
                'can_show_before_booking' => false,
                'can_show_after_booking' => false,
            ]);
    }

    public function deleteForBooking(Booking $booking): void
    {
        RoomOccupantSnapshot::query()->where('booking_id', $booking->id)->delete();
    }

    private function alias(?User $guest, ?CoLivingProfile $profile, ?CoLivingVisibilitySetting $settings): ?string
    {
        if ($settings?->show_public_alias !== false && $profile?->public_alias) {
            return $profile->public_alias;
        }

        if ($settings?->show_real_first_name && $guest?->name) {
            return str($guest->name)->before(' ')->toString();
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function languages(mixed $languages): array
    {
        if (! is_array($languages)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $language): string => strtolower((string) $language),
            $languages,
        )));
    }

    private function snapshotStatus(Booking $booking): string
    {
        $status = $booking->status instanceof BookingStatus ? $booking->status : BookingStatus::tryFrom((string) $booking->status);

        if ($status?->isCancelled() || $status === BookingStatus::DeclinedByHost || $status === BookingStatus::Expired) {
            return RoomOccupantSnapshot::STATUS_CANCELLED;
        }

        if (in_array($status, [BookingStatus::NoShow, BookingStatus::HostNoShow], true)) {
            return RoomOccupantSnapshot::STATUS_NO_SHOW;
        }

        return match ($status) {
            BookingStatus::CheckedIn => RoomOccupantSnapshot::STATUS_CHECKED_IN,
            BookingStatus::InProgress, BookingStatus::ActiveStay => RoomOccupantSnapshot::STATUS_IN_PROGRESS,
            BookingStatus::LeavingSoon => RoomOccupantSnapshot::STATUS_LEAVING_SOON,
            BookingStatus::CheckedOut, BookingStatus::Completed, BookingStatus::AwaitingReview, BookingStatus::Closed => RoomOccupantSnapshot::STATUS_CHECKED_OUT,
            default => RoomOccupantSnapshot::STATUS_UPCOMING,
        };
    }
}
