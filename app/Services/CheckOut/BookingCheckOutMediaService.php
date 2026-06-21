<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutMedia;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BookingCheckOutMediaService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function addMedia(User $user, BookingCheckOut $checkOut, array $data): BookingCheckOutMedia
    {
        $this->ensureParticipant($user, $checkOut);

        $validated = Validator::make($data, [
            'media_type' => ['required', 'string', 'max:40'],
            'media_role' => ['required', 'string', 'max:80'],
            'path' => ['required', 'string', 'max:255'],
            'thumbnail_path' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'visibility' => ['nullable', 'string', 'max:40'],
        ], [], trans('check_out.validation.attributes'))->validate();

        return BookingCheckOutMedia::query()->create([
            'booking_check_out_id' => $checkOut->id,
            'booking_id' => $checkOut->booking_id,
            'uploaded_by_user_id' => $user->id,
            'media_type' => $validated['media_type'],
            'media_role' => $validated['media_role'],
            'path' => $validated['path'],
            'thumbnail_path' => $validated['thumbnail_path'] ?? null,
            'caption' => $validated['caption'] ?? null,
            'visibility' => $validated['visibility'] ?? 'guest_and_host',
        ]);
    }

    /**
     * @return Collection<int, BookingCheckOutMedia>
     */
    public function getVisibleMedia(User $user, BookingCheckOut $checkOut): Collection
    {
        return $checkOut->media()
            ->orderByDesc('id')
            ->get()
            ->filter(fn (BookingCheckOutMedia $media): bool => app(BookingCheckOutPrivacyService::class)->canViewMedia($user, $media))
            ->values();
    }

    private function ensureParticipant(User $user, BookingCheckOut $checkOut): void
    {
        if (! in_array((int) $user->id, [(int) $checkOut->guest_user_id, (int) $checkOut->host_user_id], true)) {
            throw ValidationException::withMessages([
                'booking' => __('check_out.validation.not_participant'),
            ]);
        }
    }
}
