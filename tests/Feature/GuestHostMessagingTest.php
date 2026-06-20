<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\MessageThreadType;
use App\Enums\PaymentStatus;
use App\Livewire\Messages\ChatWindow;
use App\Livewire\Shell\MessagesPage;
use App\Models\Booking;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceTranslation;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class GuestHostMessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_send_pre_booking_message_and_host_receives_notification(): void
    {
        Storage::fake('public');
        [$guest, $host, $property, $room, $sleepingPlace] = $this->createMessagingContext();
        $thread = $this->thread($guest, $host, MessageThreadType::PreBooking, null, $property, $sleepingPlace);

        Livewire::actingAs($guest)
            ->test(ChatWindow::class, ['thread' => $thread])
            ->set('body', 'Is this place available?')
            ->set('uploads', [UploadedFile::fake()->image('arrival.jpg', 600, 400)->size(256)])
            ->call('send')
            ->assertHasNoErrors();

        $message = Message::query()->where('thread_id', $thread->id)->firstOrFail();

        $this->assertSame($guest->id, $message->sender_user_id);
        $this->assertSame($host->id, $message->recipient_user_id);
        $this->assertSame($sleepingPlace->id, $message->sleeping_place_id);
        $this->assertSame('Is this place available?', $message->body);
        $this->assertSame('en', $message->locale);
        $this->assertCount(1, $message->attachments);
        Storage::disk('public')->assertExists($message->attachments[0]['path']);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $host->id,
            'type' => 'message_received',
            'title_key' => 'notifications.message_received.title',
            'body_key' => 'notifications.message_received.body',
        ]);

        $this->actingAs($guest)
            ->get(route('messages.index', ['locale' => 'en']))
            ->assertOk()
            ->assertSeeLivewire(MessagesPage::class)
            ->assertSee('Is this place available?');
    }

    public function test_recipient_opening_thread_marks_messages_as_read(): void
    {
        [$guest, $host, $property, $room, $sleepingPlace] = $this->createMessagingContext();
        $thread = $this->thread($guest, $host, MessageThreadType::PreBooking, null, $property, $sleepingPlace);
        $message = app(MessageService::class)->send($thread, $guest, 'I am arriving soon.');

        $this->assertNull($message->read_at);

        Livewire::actingAs($host)
            ->test(ChatWindow::class, ['thread' => $thread])
            ->assertOk();

        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_user_cannot_open_unrelated_private_booking_thread(): void
    {
        [$guest, $host, $property, $room, $sleepingPlace] = $this->createMessagingContext();
        $booking = $this->booking($guest, $host, $property, $room, $sleepingPlace);
        $thread = $this->thread($guest, $host, MessageThreadType::Booking, $booking, $property, $sleepingPlace);
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->get(route('messages.show', ['locale' => 'en', 'thread' => $thread]))
            ->assertForbidden();
    }

    public function test_quick_templates_are_localized_for_guest_and_host(): void
    {
        [$guest, $host, $property, $room, $sleepingPlace] = $this->createMessagingContext();
        $booking = $this->booking($guest, $host, $property, $room, $sleepingPlace);
        $thread = $this->thread($guest, $host, MessageThreadType::Booking, $booking, $property, $sleepingPlace);

        $this->actingAs($guest)
            ->get(route('messages.show', ['locale' => 'ru', 'thread' => $thread]))
            ->assertOk()
            ->assertSee(Lang::get('messages.templates.guest.available', [], 'ru'));

        $this->actingAs($host)
            ->get(route('messages.show', ['locale' => 'ru', 'thread' => $thread]))
            ->assertOk()
            ->assertSee(Lang::get('messages.templates.host.confirmed', [], 'ru'));
    }

    public function test_attachment_validation_rejects_unsupported_files(): void
    {
        Storage::fake('public');
        [$guest, $host, $property, $room, $sleepingPlace] = $this->createMessagingContext();
        $thread = $this->thread($guest, $host, MessageThreadType::PreBooking, null, $property, $sleepingPlace);

        Livewire::actingAs($guest)
            ->test(ChatWindow::class, ['thread' => $thread])
            ->set('uploads', [UploadedFile::fake()->create('unsafe.exe', 10, 'application/x-msdownload')])
            ->call('send')
            ->assertHasErrors(['uploads.0']);

        $this->assertDatabaseMissing('messages', [
            'thread_id' => $thread->id,
            'sender_user_id' => $guest->id,
        ]);
    }

    public function test_host_cannot_expose_exact_address_before_booking_rules_allow_it(): void
    {
        [$guest, $host, $property, $room, $sleepingPlace] = $this->createMessagingContext([
            'address_line_1' => 'Central Street',
            'show_exact_address_before_booking' => false,
            'show_exact_address_after_payment' => true,
        ]);
        $thread = $this->thread($guest, $host, MessageThreadType::PreBooking, null, $property, $sleepingPlace);

        Livewire::actingAs($host)
            ->test(ChatWindow::class, ['thread' => $thread])
            ->set('body', 'Please come to Central Street today.')
            ->call('send')
            ->assertHasErrors(['body']);

        $this->assertDatabaseMissing('messages', [
            'thread_id' => $thread->id,
            'sender_user_id' => $host->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $propertyOverrides
     * @return array{User, User, Property, Room, SleepingPlace}
     */
    private function createMessagingContext(array $propertyOverrides = []): array
    {
        $guest = User::factory()->create();
        $host = User::factory()->create(['is_host' => true]);
        $property = Property::factory()
            ->for($host, 'host')
            ->create($propertyOverrides);
        $room = Room::factory()
            ->for($property)
            ->create();
        $sleepingPlace = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create();

        SleepingPlaceTranslation::factory()->for($sleepingPlace)->create([
            'locale' => 'en',
            'title' => 'Quiet lower bed',
        ]);
        SleepingPlaceTranslation::factory()->for($sleepingPlace)->create([
            'locale' => 'ru',
            'title' => 'Тихое нижнее место',
        ]);

        return [$guest, $host, $property, $room, $sleepingPlace];
    }

    private function booking(User $guest, User $host, Property $property, Room $room, SleepingPlace $sleepingPlace): Booking
    {
        return Booking::factory()
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($property)
            ->for($room)
            ->for($sleepingPlace)
            ->create([
                'status' => BookingStatus::Confirmed,
                'payment_status' => PaymentStatus::Paid,
            ]);
    }

    private function thread(
        User $guest,
        User $host,
        MessageThreadType $type,
        ?Booking $booking,
        Property $property,
        SleepingPlace $sleepingPlace,
    ): MessageThread {
        return app(MessageService::class)->getOrCreateThread(
            guest: $guest,
            host: $host,
            type: $type,
            booking: $booking,
            property: $property,
            sleepingPlace: $sleepingPlace,
        );
    }
}
