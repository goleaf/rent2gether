<?php

namespace App\Services\CheckIn;

use App\Models\BookingCheckIn;
use App\Models\BookingCheckInMedia;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BookingCheckInMediaService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function recordMedia(User $user, BookingCheckIn $checkIn, array $data): BookingCheckInMedia
    {
        $this->ensureParticipant($user, $checkIn);

        $validated = Validator::make($data, [
            'media_type' => ['required', 'string', 'in:photo,video_future,document_future'],
            'media_role' => ['required', 'string', 'max:80'],
            'path' => ['required', 'string', 'max:255'],
            'thumbnail_path' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'visibility' => ['nullable', 'string', 'in:guest_and_host,host_only,guest_only,internal'],
        ], [], __('check_in.validation.attributes'))->validate();

        $media = BookingCheckInMedia::query()->create([
            'booking_check_in_id' => $checkIn->id,
            'booking_id' => $checkIn->booking_id,
            'uploaded_by_user_id' => $user->id,
            'media_type' => $validated['media_type'],
            'media_role' => $validated['media_role'],
            'path' => $validated['path'],
            'thumbnail_path' => $validated['thumbnail_path'] ?? null,
            'caption' => $validated['caption'] ?? null,
            'visibility' => $validated['visibility'] ?? 'guest_and_host',
        ]);

        $this->syncCheckInMediaFields($user, $checkIn, $media);

        return $media->refresh();
    }

    private function ensureParticipant(User $user, BookingCheckIn $checkIn): void
    {
        if (! in_array((int) $user->id, [(int) $checkIn->guest_user_id, (int) $checkIn->host_user_id], true)) {
            throw ValidationException::withMessages([
                'booking' => __('check_in.validation.not_participant'),
            ]);
        }
    }

    private function syncCheckInMediaFields(User $user, BookingCheckIn $checkIn, BookingCheckInMedia $media): void
    {
        $column = match ($media->media_role) {
            'before_check_in_sleeping_place' => 'before_place_photo_path',
            'before_check_in_room' => 'before_room_photo_path',
            default => null,
        };

        if ($column === null) {
            return;
        }

        $checkIn->forceFill([$column => $media->path])->save();
        app(BookingCheckInChecklistService::class)->markItemCompleted($user, $checkIn->refresh(), 'before_photo_uploaded');
    }
}
