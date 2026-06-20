<?php

namespace App\View\Components\Host;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class PublicCard extends Component
{
    public readonly ?Model $profile;

    public readonly ?string $displayName;

    public readonly ?string $avatarUrl;

    public readonly string $languages;

    public readonly ?string $responseLabel;

    public readonly string $initial;

    public function __construct(
        public readonly ?Model $host = null,
        ?Model $hostProfile = null,
    ) {
        $loadedProfile = $host?->relationLoaded('hostProfile') ? $host->hostProfile : null;
        $this->profile = $hostProfile ?: $loadedProfile;
        $this->displayName = $this->profile?->display_name ?: $host?->name;
        $this->avatarUrl = $this->avatarUrl();
        $this->languages = $this->languageSummary();
        $this->responseLabel = $this->responseLabel();
        $this->initial = Str::of((string) $this->displayName)->substr(0, 1)->upper()->toString();
    }

    private function avatarUrl(): ?string
    {
        $avatarPath = $this->profile?->avatar_path ?: $this->host?->avatar;

        return $avatarPath ? Storage::disk('public')->url($avatarPath) : null;
    }

    private function languageSummary(): string
    {
        return collect($this->profile?->languages_json ?: $this->host?->languages ?: [])
            ->filter()
            ->take(4)
            ->map(fn (string $language): string => Lang::has('navigation.languages.'.$language)
                ? Lang::get('navigation.languages.'.$language)
                : strtoupper($language))
            ->join(', ');
    }

    private function responseLabel(): ?string
    {
        $responseMinutes = $this->profile?->response_time_minutes;

        if (! $responseMinutes) {
            return null;
        }

        return $responseMinutes < 60
            ? __('host.profile.public_card.response_time_minutes', ['count' => $responseMinutes])
            : __('host.profile.public_card.response_time_hours', ['count' => (int) ceil($responseMinutes / 60)]);
    }

    public function render(): View
    {
        return view('components.host.public-card');
    }
}
