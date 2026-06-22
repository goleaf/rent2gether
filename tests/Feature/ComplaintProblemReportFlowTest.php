<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\ComplaintStatus;
use App\Enums\ComplaintType;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Complaints\ComplaintDetail;
use App\Livewire\Complaints\CreateComplaint;
use App\Models\Booking;
use App\Models\Complaint;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\Complaints\ComplaintService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ComplaintProblemReportFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-20 10:00:00');
        CarbonImmutable::setTestNow('2026-06-20 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_guest_can_create_complaint_for_booking(): void
    {
        [$booking, $guest, $host] = $this->createStayBooking();

        $component = Livewire::actingAs($guest)
            ->test(CreateComplaint::class, ['booking' => $booking])
            ->set('type', ComplaintType::CannotCheckIn->value)
            ->set('priority', 'high')
            ->set('description', 'The entrance code does not work and I cannot check in.')
            ->set('desiredResolution', 'Please help me check in or review cancellation.')
            ->set('refundRequested', true)
            ->call('submit')
            ->assertHasNoErrors();

        $complaint = Complaint::query()->firstOrFail();

        $component->assertRedirect(route('complaints.show', ['locale' => 'en', 'complaint' => $complaint]));

        $this->assertTrue($complaint->type === ComplaintType::CannotCheckIn);
        $this->assertTrue($complaint->status === ComplaintStatus::WaitingForOtherSide);
        $this->assertSame($guest->id, $complaint->reporter_user_id);
        $this->assertSame($host->id, $complaint->reported_user_id);
        $this->assertSame('high', $complaint->priority);
        $this->assertTrue($complaint->refund_requested);
        $this->assertNotNull($complaint->complaint_number);
        $this->assertSame(2, $complaint->statusHistories()->count());
        $this->assertTrue($booking->refresh()->has_complaint);
        $this->assertTrue($booking->status === BookingStatus::ProblemReported);
    }

    public function test_host_can_create_complaint_for_booking(): void
    {
        [$booking, $guest, $host] = $this->createStayBooking(BookingStatus::InProgress);

        Livewire::actingAs($host)
            ->test(CreateComplaint::class, ['booking' => $booking])
            ->set('type', ComplaintType::GuestDamagedProperty->value)
            ->set('priority', 'normal')
            ->set('description', 'The locker door was damaged during the stay.')
            ->set('depositHoldRequested', true)
            ->call('submit')
            ->assertHasNoErrors();

        $complaint = Complaint::query()->firstOrFail();

        $this->assertTrue($complaint->type === ComplaintType::GuestDamagedProperty);
        $this->assertSame($host->id, $complaint->reporter_user_id);
        $this->assertSame($guest->id, $complaint->reported_user_id);
        $this->assertTrue($complaint->deposit_hold_requested);
        $this->assertSame(1, $guest->refresh()->complaints_count);
        $this->assertSame(1, $guest->profile->refresh()->complaints_count);
    }

    public function test_complaint_media_upload_validation(): void
    {
        Storage::fake('public');

        [$booking, $guest] = $this->createStayBooking();

        Livewire::actingAs($guest)
            ->test(CreateComplaint::class, ['booking' => $booking])
            ->set('type', ComplaintType::DirtyRoom->value)
            ->set('priority', 'normal')
            ->set('description', 'The room was not cleaned before my arrival.')
            ->set('media', [UploadedFile::fake()->create('notes.txt', 8, 'text/plain')])
            ->call('submit')
            ->assertHasErrors(['media.0' => 'image']);

        Livewire::actingAs($guest)
            ->test(CreateComplaint::class, ['booking' => $booking])
            ->set('type', ComplaintType::DirtyRoom->value)
            ->set('priority', 'normal')
            ->set('description', 'The room was not cleaned before my arrival.')
            ->set('media', [UploadedFile::fake()->image('room.jpg', 800, 600)])
            ->call('submit')
            ->assertHasNoErrors();

        $complaint = Complaint::query()->firstOrFail();

        $this->assertCount(1, $complaint->media);
        $this->assertStringEndsWith('.webp', $complaint->media[0]);
        Storage::disk('public')->assertExists($complaint->media[0]);

        $this->actingAs($guest)
            ->get(route('complaints.show', ['locale' => 'en', 'complaint' => $complaint]))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($complaint->media[0]), false);
    }

    public function test_complaint_detail_shows_status_timeline(): void
    {
        [$booking, $guest] = $this->createStayBooking();

        $complaint = app(ComplaintService::class)->createForBooking(
            booking: $booking,
            reporter: $guest,
            type: ComplaintType::WrongAddress->value,
            priority: 'critical',
            description: 'The address in the booking led me to another building.',
        );

        $this->actingAs($guest)
            ->get(route('complaints.show', ['locale' => 'en', 'complaint' => $complaint]))
            ->assertOk()
            ->assertSeeLivewire(ComplaintDetail::class)
            ->assertSee(__('booking.complaint.timeline.title', [], 'en'))
            ->assertSee(__('booking.complaint.timeline.created', [], 'en'))
            ->assertSee(__('booking.complaint.timeline.waiting_for_other_side', [], 'en'));
    }

    public function test_other_side_can_respond_once(): void
    {
        [$booking, $guest, $host] = $this->createStayBooking();

        $complaint = app(ComplaintService::class)->createForBooking(
            booking: $booking,
            reporter: $guest,
            type: ComplaintType::HostNotResponding->value,
            priority: 'high',
            description: 'I sent messages but did not receive check-in help.',
        );

        Livewire::actingAs($host)
            ->test(ComplaintDetail::class, ['complaint' => $complaint])
            ->set('otherSideResponse', 'I am sorry, my phone was offline. I sent the access details now.')
            ->call('respond')
            ->assertHasNoErrors()
            ->assertSee(__('notifications.flash.complaint_response_saved', [], 'en'));

        $complaint->refresh();

        $this->assertTrue($complaint->status === ComplaintStatus::UnderReviewBySystem);
        $this->assertSame('I am sorry, my phone was offline. I sent the access details now.', $complaint->other_side_response);
        $this->assertSame(3, $complaint->statusHistories()->count());
    }

    public function test_complaint_type_labels_are_localized(): void
    {
        [$booking, $guest, $host] = $this->createStayBooking();

        $this->actingAs($guest)
            ->get(route('complaints.create', ['locale' => 'en', 'booking' => $booking]))
            ->assertOk()
            ->assertSeeLivewire(CreateComplaint::class)
            ->assertSee(__('statuses.complaint_type.cannot_check_in', [], 'en'))
            ->assertSee(__('statuses.complaint_type.wants_refund', [], 'en'));

        $this->actingAs($host)
            ->get(route('complaints.create', ['locale' => 'ru', 'booking' => $booking]))
            ->assertOk()
            ->assertSeeLivewire(CreateComplaint::class)
            ->assertSee(__('statuses.complaint_type.guest_damaged_property', [], 'ru'))
            ->assertSee(__('statuses.complaint_type.wants_deposit_hold', [], 'ru'));
    }

    /**
     * @return array{0: Booking, 1: User, 2: User, 3: SleepingPlace}
     */
    private function createStayBooking(BookingStatus $status = BookingStatus::Confirmed): array
    {
        $guest = User::factory()->create(['name' => 'Calm Guest']);
        UserProfile::factory()->for($guest, 'user')->create([
            'display_name' => 'Calm Guest',
        ]);

        $host = User::factory()->create(['name' => 'Kind Host', 'is_host' => true]);
        UserProfile::factory()->for($host, 'user')->create([
            'display_name' => 'Kind Host',
        ]);
        HostProfile::factory()->for($host, 'user')->create([
            'display_name' => 'Kind Host',
        ]);

        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'status' => PropertyStatus::Active,
                'city' => 'Vilnius',
                'district' => 'Old Town',
            ]);

        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
        ]);

        $place = SleepingPlace::factory()->for($room)->for($property)->create([
            'status' => SleepingPlaceStatus::Active,
            'display_name' => 'Quiet lower bed',
            'place_number' => 'L1',
        ]);
        $place->translations()->createMany([
            [
                'locale' => 'en',
                'title' => 'Quiet lower bed',
                'summary' => 'A quiet bed.',
                'description' => 'A quiet bed.',
            ],
            [
                'locale' => 'ru',
                'title' => 'Тихое нижнее место',
                'summary' => 'Тихое место.',
                'description' => 'Тихое место.',
            ],
        ]);

        $booking = Booking::factory()->create([
            'guest_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'host_id' => $host->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'bed_id' => null,
            'sleeping_place_id' => $place->id,
            'status' => $status,
            'payment_status' => PaymentStatus::Paid,
            'check_in' => '2026-06-18',
            'check_out' => '2026-06-24',
            'check_in_date' => '2026-06-18',
            'check_out_date' => '2026-06-24',
            'nights' => 6,
            'nights_count' => 6,
            'calendar_days_count' => 7,
        ]);

        return [$booking, $guest, $host, $place];
    }
}
