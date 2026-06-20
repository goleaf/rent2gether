<?php

namespace App\Livewire\Complaints;

use App\Models\Complaint;
use App\Models\User;
use App\Services\ComplaintService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ComplaintDetail extends Component
{
    #[Locked]
    public int $complaintId;

    public string $otherSideResponse = '';

    public function mount(Complaint $complaint, ComplaintService $complaints): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && $complaints->canView($complaint, $user), 403);

        $this->complaintId = $complaint->id;
    }

    public function respond(ComplaintService $complaints): void
    {
        $validated = $this->validate([
            'otherSideResponse' => ['required', 'string', 'min:10', 'max:2000'],
        ], [], app('translator')->get('booking.complaint.validation_attributes'));

        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $complaints->respondAsOtherSide($this->complaint(), $user, $validated['otherSideResponse']);

        $this->otherSideResponse = '';
        session()->flash('complaint-status', __('notifications.flash.complaint_response_saved'));
    }

    public function render(ComplaintService $complaints): View
    {
        $complaint = $this->complaint();
        $user = auth()->user();

        return view('livewire.complaints.complaint-detail', [
            'complaint' => $complaint,
            'canRespond' => $user instanceof User && $complaints->canRespond($complaint, $user),
            'timeline' => $this->timeline($complaint),
        ])->layout('layouts.app', [
            'title' => __('booking.complaint.detail_title'),
        ]);
    }

    private function complaint(): Complaint
    {
        return Complaint::query()
            ->select([
                'id',
                'complaint_number',
                'reference',
                'reporter_user_id',
                'reporter_id',
                'reported_user_id',
                'booking_id',
                'property_id',
                'room_id',
                'bed_id',
                'sleeping_place_id',
                'type',
                'priority',
                'urgency',
                'description',
                'desired_resolution',
                'refund_requested',
                'deposit_hold_requested',
                'media',
                'photos',
                'status',
                'other_side_response',
                'respondent_reply',
                'resolution_text',
                'resolution_notes',
                'refund_amount',
                'deposit_hold_amount',
                'deposit_withheld',
                'created_at',
                'updated_at',
            ])
            ->with([
                'reporter:id,name',
                'reporterUser:id,name',
                'reportedUser:id,name',
                'booking:id,reference,guest_id,guest_user_id,host_id,host_user_id,check_in_date,check_out_date,status',
                'property:id,title,city,district',
                'room:id,title,room_number',
                'sleepingPlace:id,display_name,place_number',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
                'statusHistories' => fn ($query) => $query
                    ->select(['id', 'complaint_id', 'actor_user_id', 'status', 'note_key', 'note', 'created_at'])
                    ->with(['actor:id,name'])
                    ->orderBy('created_at')
                    ->orderBy('id'),
            ])
            ->forParticipant((int) auth()->id())
            ->findOrFail($this->complaintId);
    }

    /**
     * @return list<array{status: string, note: string, actor: string|null, date: string}>
     */
    private function timeline(Complaint $complaint): array
    {
        return $complaint->statusHistories
            ->map(fn ($history): array => [
                'status' => $history->status->label(),
                'note' => __($history->note_key ?: 'booking.complaint.timeline.updated'),
                'actor' => $history->actor?->name,
                'date' => $history->created_at?->translatedFormat('d M Y H:i') ?: '',
            ])
            ->values()
            ->all();
    }
}
