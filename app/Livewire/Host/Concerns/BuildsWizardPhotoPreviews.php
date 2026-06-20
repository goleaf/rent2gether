<?php

namespace App\Livewire\Host\Concerns;

trait BuildsWizardPhotoPreviews
{
    /**
     * @return list<array{field:string,slot:string,preview:?array{url:string,caption:string,label:string,saved:bool}}>
     */
    public function wizardPhotoFields(): array
    {
        return collect(static::PHOTO_FIELDS)
            ->map(fn (string $slot, string $field): array => [
                'field' => $field,
                'slot' => $slot,
                'preview' => $this->wizardPhotoPreview($field, $slot),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{url:string,caption:string,label:string,saved:bool}|null
     */
    public function wizardPhotoPreview(string $field, string $slot): ?array
    {
        $pendingPhoto = $this->{$field} ?? null;

        if ($pendingPhoto
            && method_exists($pendingPhoto, 'getMimeType')
            && method_exists($pendingPhoto, 'temporaryUrl')
            && str_starts_with((string) $pendingPhoto->getMimeType(), 'image/')) {
            return [
                'url' => $pendingPhoto->temporaryUrl(),
                'caption' => __('media.manager.preview_alt'),
                'label' => __('media.manager.preview_alt'),
                'saved' => false,
            ];
        }

        $savedPreview = $this->savedPhotoPreviews[$slot] ?? null;

        if (! is_array($savedPreview)) {
            return null;
        }

        return [
            'url' => (string) $savedPreview['url'],
            'caption' => (string) $savedPreview['caption'],
            'label' => __('media.manager.optimized'),
            'saved' => true,
        ];
    }
}
