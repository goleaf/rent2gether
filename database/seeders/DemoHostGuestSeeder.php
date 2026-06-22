<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckOut;
use App\Models\BookingPriceLine;
use App\Models\BookingQuote;
use App\Models\BookingRequest;
use App\Models\BookingStay;
use App\Models\Conversation;
use App\Models\Favorite;
use App\Models\HostProfile;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Notification;
use App\Models\PaymentRecord;
use App\Models\Property;
use App\Models\PropertyTranslation;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoomTranslation;
use App\Models\SavedSearch;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarDay;
use App\Models\SleepingPlaceTranslation;
use App\Models\User;
use App\Models\UserActivitySummary;
use App\Models\UserDocument;
use App\Models\UserLanguage;
use App\Models\UserNotificationPreference;
use App\Models\UserPrivacySetting;
use App\Models\UserSavedPreference;
use App\Models\UserSetting;
use App\Models\UserVerification;
use App\Models\WaitlistItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DemoHostGuestSeeder extends Seeder
{
    public function run(): void
    {
        $host = $this->host();
        $guest = $this->guest();

        $this->seedUserSupportRecords($host, $guest);

        $property = $this->property($host);
        $room = $this->room($property);
        $place = $this->sleepingPlace($property, $room);

        $this->seedListingTranslations($property, $room, $place);
        $this->seedBookings($host, $guest, $property, $room, $place);
        $this->seedGuestDiscoveryRecords($guest, $property, $room, $place);
        $this->seedNotifications($host, $guest);
    }

    private function host(): User
    {
        return User::query()->updateOrCreate([
            'email' => 'host@example.com',
        ], [
            'name' => 'Demo Host',
            'password' => 'password',
            'email_verified_at' => now(),
            'role_mode' => 'host',
            'preferred_locale' => 'en',
            'timezone' => 'Europe/Vilnius',
            'is_guest' => false,
            'is_host' => true,
            'is_active' => true,
            'last_seen_at' => now(),
        ]);
    }

    private function guest(): User
    {
        return User::query()->updateOrCreate([
            'email' => 'guest@example.com',
        ], [
            'name' => 'Demo Guest',
            'password' => 'example',
            'email_verified_at' => now(),
            'role_mode' => 'guest',
            'preferred_locale' => 'en',
            'timezone' => 'Europe/Vilnius',
            'is_guest' => true,
            'is_host' => false,
            'is_active' => true,
            'last_seen_at' => now(),
        ]);
    }

    private function seedUserSupportRecords(User $host, User $guest): void
    {
        foreach ([$host, $guest] as $user) {
            HostProfile::query()->firstOrCreate(['user_id' => $host->id], HostProfile::factory()->make(['user_id' => $host->id])->getAttributes());
            UserSetting::query()->firstOrCreate(['user_id' => $user->id], UserSetting::factory()->make(['user_id' => $user->id])->getAttributes());
            UserSavedPreference::query()->firstOrCreate(['user_id' => $user->id], UserSavedPreference::factory()->make(['user_id' => $user->id])->getAttributes());
            UserPrivacySetting::query()->firstOrCreate(['user_id' => $user->id], UserPrivacySetting::factory()->make(['user_id' => $user->id])->getAttributes());
            UserActivitySummary::query()->firstOrCreate(['user_id' => $user->id], UserActivitySummary::factory()->make(['user_id' => $user->id])->getAttributes());
            UserDocument::query()->firstOrCreate([
                'user_id' => $user->id,
                'document_type' => 'identity_document',
            ], UserDocument::factory()->make(['user_id' => $user->id])->getAttributes());

            foreach (['en', 'ru'] as $locale) {
                UserLanguage::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'language_code' => $locale,
                ], UserLanguage::factory()->make([
                    'user_id' => $user->id,
                    'language_code' => $locale,
                    'is_primary' => $locale === 'en',
                ])->getAttributes());
            }

            foreach (['phone', 'identity'] as $type) {
                UserVerification::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'verification_type' => $type,
                ], UserVerification::factory()->verified()->make([
                    'user_id' => $user->id,
                    'verification_type' => $type,
                ])->getAttributes());
            }

            foreach (['bookings', 'messages', 'payments'] as $category) {
                UserNotificationPreference::query()->firstOrCreate([
                    'user_id' => $user->id,
                    'category' => $category,
                    'channel' => 'in_app',
                ], UserNotificationPreference::factory()->make([
                    'user_id' => $user->id,
                    'category' => $category,
                    'channel' => 'in_app',
                ])->getAttributes());
            }
        }
    }

    private function property(User $host): Property
    {
        return Property::query()->firstOrCreate([
            'host_user_id' => $host->id,
            'title' => 'Demo Host Home',
        ], Property::factory()->make([
            'host_user_id' => $host->id,
            'title' => 'Demo Host Home',
        ])->getAttributes());
    }

    private function room(Property $property): Room
    {
        return Room::query()->firstOrCreate([
            'property_id' => $property->id,
            'title' => 'Demo Shared Room',
        ], Room::factory()->make([
            'property_id' => $property->id,
            'title' => 'Demo Shared Room',
        ])->getAttributes());
    }

    private function sleepingPlace(Property $property, Room $room): SleepingPlace
    {
        return SleepingPlace::query()->firstOrCreate([
            'property_id' => $property->id,
            'room_id' => $room->id,
            'display_name' => 'Demo Bed 1',
        ], SleepingPlace::factory()->make([
            'property_id' => $property->id,
            'room_id' => $room->id,
            'display_name' => 'Demo Bed 1',
        ])->getAttributes());
    }

    private function seedListingTranslations(Property $property, Room $room, SleepingPlace $place): void
    {
        foreach (['en', 'ru'] as $locale) {
            PropertyTranslation::query()->updateOrCreate([
                'property_id' => $property->id,
                'locale' => $locale,
            ], PropertyTranslation::factory()->make([
                'property_id' => $property->id,
                'locale' => $locale,
                'title' => $locale === 'ru' ? 'Дом демо-хозяина' : 'Demo Host Home',
            ])->getAttributes());
            RoomTranslation::query()->updateOrCreate([
                'room_id' => $room->id,
                'locale' => $locale,
            ], RoomTranslation::factory()->make([
                'room_id' => $room->id,
                'locale' => $locale,
                'title' => $locale === 'ru' ? 'Общая демо-комната' : 'Demo Shared Room',
            ])->getAttributes());
            SleepingPlaceTranslation::query()->updateOrCreate([
                'sleeping_place_id' => $place->id,
                'locale' => $locale,
            ], SleepingPlaceTranslation::factory()->make([
                'sleeping_place_id' => $place->id,
                'locale' => $locale,
                'title' => $locale === 'ru' ? 'Демо-кровать 1' : 'Demo Bed 1',
            ])->getAttributes());
        }
    }

    private function seedBookings(User $host, User $guest, Property $property, Room $room, SleepingPlace $place): void
    {
        for ($index = 1; $index <= 20; $index++) {
            $checkIn = CarbonImmutable::now()->subDays(180 - ($index * 7))->startOfDay();
            $checkOut = $checkIn->addDays(4);
            $quote = $this->bookingQuote($index, $host, $guest, $property, $room, $place, $checkIn, $checkOut);
            $booking = Booking::query()->updateOrCreate([
                'booking_number' => sprintf('DEMO-BK-%02d', $index),
            ], Booking::factory()->make([
                'booking_number' => sprintf('DEMO-BK-%02d', $index),
                'booking_quote_id' => $quote->id,
                'guest_user_id' => $guest->id,
                'host_user_id' => $host->id,
                'property_id' => $property->id,
                'room_id' => $room->id,
                'sleeping_place_id' => $place->id,
                'status' => BookingStatus::Completed->value,
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'nights' => 4,
                'nights_count' => 4,
                'chargeable_days_count' => 4,
                'calendar_days_count' => 5,
                'calendar_presence_days_count' => 5,
                'paid_at' => $checkIn->subDays(2),
                'payment_paid_at' => $checkIn->subDays(2),
                'guest_checked_in_at' => $checkIn->setTime(15, 0),
                'guest_checked_out_at' => $checkOut->setTime(10, 0),
            ])->getAttributes());

            $this->seedBookingChildren($index, $booking, $host, $guest, $property, $room, $place, $checkIn, $checkOut);
        }
    }

    private function bookingQuote(int $index, User $host, User $guest, Property $property, Room $room, SleepingPlace $place, CarbonImmutable $checkIn, CarbonImmutable $checkOut): BookingQuote
    {
        return BookingQuote::query()->updateOrCreate([
            'quote_number' => sprintf('DEMO-QT-%02d', $index),
        ], BookingQuote::factory()->make([
            'quote_number' => sprintf('DEMO-QT-%02d', $index),
            'user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'nights_count' => 4,
            'chargeable_days_count' => 4,
            'calendar_presence_days_count' => 5,
        ])->getAttributes());
    }

    private function seedBookingChildren(int $index, Booking $booking, User $host, User $guest, Property $property, Room $room, SleepingPlace $place, CarbonImmutable $checkIn, CarbonImmutable $checkOut): void
    {
        if (BookingPriceLine::query()->where('booking_id', $booking->id)->count() < 4) {
            foreach (['nightly_subtotal', 'cleaning_fee', 'service_fee', 'deposit'] as $lineIndex => $type) {
                BookingPriceLine::factory()->create([
                    'booking_id' => $booking->id,
                    'type' => $type,
                    'label_key' => 'booking.price_lines.'.$type,
                    'amount' => $lineIndex === 0 ? 80 : 5 + $lineIndex,
                ]);
            }
        }

        PaymentRecord::query()->firstOrCreate(['booking_id' => $booking->id], PaymentRecord::factory()->make([
            'booking_id' => $booking->id,
            'payer_user_id' => $guest->id,
            'amount' => $booking->total_amount,
            'currency' => $booking->currency,
            'paid_at' => $checkIn->subDays(2),
        ])->getAttributes());
        BookingStay::query()->firstOrCreate(['booking_id' => $booking->id], BookingStay::factory()->make([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'status' => 'completed',
            'check_in_date' => $checkIn->toDateString(),
            'planned_check_out_date' => $checkOut->toDateString(),
            'actual_check_in_at' => $checkIn->setTime(15, 0),
            'actual_check_out_at' => $checkOut->setTime(10, 0),
            'ended_at' => $checkOut,
            'closed_at' => $checkOut,
        ])->getAttributes());
        BookingCheckIn::query()->firstOrCreate(['booking_id' => $booking->id], BookingCheckIn::factory()->make([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'check_in_date' => $checkIn->toDateString(),
            'actual_check_in_at' => $checkIn->setTime(15, 0),
            'guest_confirmed_at' => $checkIn->setTime(15, 5),
            'host_confirmed_at' => $checkIn->setTime(15, 10),
            'status' => 'completed',
        ])->getAttributes());
        BookingCheckOut::query()->firstOrCreate(['booking_id' => $booking->id], BookingCheckOut::factory()->make([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'check_out_date' => $checkOut->toDateString(),
            'actual_check_out_at' => $checkOut->setTime(10, 0),
            'guest_confirmed_at' => $checkOut->setTime(10, 5),
            'host_confirmed_at' => $checkOut->setTime(10, 30),
            'status' => 'completed',
            'completed_at' => $checkOut->setTime(10, 30),
            'closed_at' => $checkOut->setTime(10, 45),
        ])->getAttributes());

        $this->seedBookingReviews($booking, $host, $guest, $property, $room, $place);
        $this->seedBookingMessages($index, $booking, $host, $guest, $property, $place);
        $this->seedBookingCalendarRows($booking, $place, $checkIn);
    }

    private function seedBookingReviews(Booking $booking, User $host, User $guest, Property $property, Room $room, SleepingPlace $place): void
    {
        if (Review::query()->where('booking_id', $booking->id)->count() >= 2) {
            return;
        }

        Review::factory()->create([
            'booking_id' => $booking->id,
            'reviewer_id' => $guest->id,
            'reviewee_id' => $host->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'type' => 'guest_to_place',
        ]);
        Review::factory()->create([
            'booking_id' => $booking->id,
            'reviewer_id' => $host->id,
            'reviewee_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'type' => 'host_to_guest',
        ]);
    }

    private function seedBookingMessages(int $index, Booking $booking, User $host, User $guest, Property $property, SleepingPlace $place): void
    {
        $conversation = Conversation::query()->firstOrCreate([
            'booking_id' => $booking->id,
            'conversation_type' => 'booking',
        ], Conversation::factory()->make([
            'participant_one_id' => min($guest->id, $host->id),
            'participant_two_id' => max($guest->id, $host->id),
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'sleeping_place_id' => $place->id,
            'booking_id' => $booking->id,
        ])->getAttributes());
        $thread = MessageThread::query()->firstOrCreate(['booking_id' => $booking->id], MessageThread::factory()->make([
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'booking_id' => $booking->id,
            'property_id' => $property->id,
            'sleeping_place_id' => $place->id,
        ])->getAttributes());

        if (Message::query()->where('booking_id', $booking->id)->count() >= 3) {
            return;
        }

        foreach ([$guest, $host, $guest] as $messageIndex => $sender) {
            Message::factory()->create([
                'conversation_id' => $conversation->id,
                'thread_id' => $thread->id,
                'sender_id' => $sender->id,
                'sender_user_id' => $sender->id,
                'recipient_user_id' => $sender->id === $guest->id ? $host->id : $guest->id,
                'booking_id' => $booking->id,
                'property_id' => $property->id,
                'sleeping_place_id' => $place->id,
                'body' => sprintf('Demo stay %02d message %02d', $index, $messageIndex + 1),
            ]);
        }
    }

    private function seedBookingCalendarRows(Booking $booking, SleepingPlace $place, CarbonImmutable $checkIn): void
    {
        for ($day = 0; $day < 4; $day++) {
            $date = $checkIn->addDays($day)->toDateString();

            AvailabilityDay::query()->firstOrCreate([
                'sleeping_place_id' => $place->id,
                'date' => $date,
            ], AvailabilityDay::factory()->make([
                'sleeping_place_id' => $place->id,
                'booking_id' => $booking->id,
                'date' => $date,
                'status' => 'booked',
            ])->getAttributes());
            SleepingPlaceCalendarDay::query()->firstOrCreate([
                'sleeping_place_id' => $place->id,
                'date' => $date,
            ], SleepingPlaceCalendarDay::factory()->make([
                'sleeping_place_id' => $place->id,
                'booking_id' => $booking->id,
                'date' => $date,
                'status' => 'booked',
            ])->getAttributes());
        }
    }

    private function seedGuestDiscoveryRecords(User $guest, Property $property, Room $room, SleepingPlace $place): void
    {
        for ($index = 1; $index <= 4; $index++) {
            Favorite::query()->firstOrCreate([
                'user_id' => $guest->id,
                'sleeping_place_id' => $place->id,
                'source' => 'demo_'.$index,
            ], Favorite::factory()->make([
                'user_id' => $guest->id,
                'property_id' => $property->id,
                'room_id' => $room->id,
                'sleeping_place_id' => $place->id,
                'source' => 'demo_'.$index,
            ])->getAttributes());
        }

        for ($index = 1; $index <= 2; $index++) {
            SavedSearch::query()->firstOrCreate([
                'user_id' => $guest->id,
                'name' => 'Demo search '.$index,
            ], SavedSearch::factory()->make([
                'user_id' => $guest->id,
                'name' => 'Demo search '.$index,
            ])->getAttributes());
            WaitlistItem::query()->firstOrCreate([
                'user_id' => $guest->id,
                'sleeping_place_id' => $place->id,
                'desired_check_in_date' => CarbonImmutable::now()->addDays($index * 10)->toDateString(),
            ], WaitlistItem::factory()->make([
                'user_id' => $guest->id,
                'sleeping_place_id' => $place->id,
                'desired_check_in_date' => CarbonImmutable::now()->addDays($index * 10)->toDateString(),
            ])->getAttributes());
        }

        for ($index = 1; $index <= 4; $index++) {
            BookingRequest::query()->firstOrCreate([
                'guest_user_id' => $guest->id,
                'guest_message' => 'Demo booking request '.$index,
            ], BookingRequest::factory()->make([
                'guest_user_id' => $guest->id,
                'host_user_id' => $property->host_user_id,
                'property_id' => $property->id,
                'room_id' => $room->id,
                'sleeping_place_id' => $place->id,
                'guest_message' => 'Demo booking request '.$index,
            ])->getAttributes());
        }
    }

    private function seedNotifications(User $host, User $guest): void
    {
        for ($index = 1; $index <= 10; $index++) {
            $user = $index % 2 === 0 ? $host : $guest;

            Notification::query()->firstOrCreate([
                'id' => sprintf('demo-notification-%02d', $index),
            ], Notification::factory()->make([
                'id' => sprintf('demo-notification-%02d', $index),
                'notifiable_id' => $user->id,
                'user_id' => $user->id,
                'type' => 'demo_message',
            ])->getAttributes());
        }
    }
}
