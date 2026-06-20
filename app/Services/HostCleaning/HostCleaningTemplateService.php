<?php

namespace App\Services\HostCleaning;

use App\Models\HostCleaningTemplate;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;

class HostCleaningTemplateService
{
    public function createTemplate(User $host, array $data): HostCleaningTemplate
    {
        $validated = $this->validate($data);

        if ($validated['is_default'] ?? false) {
            $this->clearDefault($host, $validated['cleaning_type'], $validated['target_type']);
        }

        return HostCleaningTemplate::query()->create([
            'user_id' => $host->id,
            'name' => $validated['name'],
            'cleaning_type' => $validated['cleaning_type'],
            'target_type' => $validated['target_type'],
            'items_json' => $validated['items'] ?? [],
            'is_default' => (bool) ($validated['is_default'] ?? false),
        ]);
    }

    public function updateTemplate(User $host, HostCleaningTemplate $template, array $data): HostCleaningTemplate
    {
        $this->authorize($host, $template);
        $validated = $this->validate($data);

        if ($validated['is_default'] ?? false) {
            $this->clearDefault($host, $validated['cleaning_type'], $validated['target_type'], $template->id);
        }

        $template->forceFill([
            'name' => $validated['name'],
            'cleaning_type' => $validated['cleaning_type'],
            'target_type' => $validated['target_type'],
            'items_json' => $validated['items'] ?? [],
            'is_default' => (bool) ($validated['is_default'] ?? false),
        ])->save();

        return $template->refresh();
    }

    public function deleteTemplate(User $host, HostCleaningTemplate $template): void
    {
        $this->authorize($host, $template);
        $template->delete();
    }

    public function getDefaultTemplate(User $host, string $cleaningType, string $targetType): ?HostCleaningTemplate
    {
        return HostCleaningTemplate::query()
            ->where('user_id', $host->id)
            ->where('cleaning_type', $cleaningType)
            ->where('target_type', $targetType)
            ->where('is_default', true)
            ->latest('id')
            ->first();
    }

    private function validate(array $data): array
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:120'],
            'cleaning_type' => ['required', 'string', 'max:80'],
            'target_type' => ['required', 'string', 'max:80'],
            'items' => ['nullable', 'array'],
            'items.*.item_key' => ['required_with:items', 'string', 'max:120'],
            'items.*.label_key' => ['nullable', 'string', 'max:160'],
            'items.*.required' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ])->validate();
    }

    private function clearDefault(User $host, string $type, string $targetType, ?int $exceptId = null): void
    {
        HostCleaningTemplate::query()
            ->where('user_id', $host->id)
            ->where('cleaning_type', $type)
            ->where('target_type', $targetType)
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->update(['is_default' => false]);
    }

    private function authorize(User $host, HostCleaningTemplate $template): void
    {
        if ((int) $template->user_id !== (int) $host->id) {
            throw new AuthorizationException;
        }
    }
}
