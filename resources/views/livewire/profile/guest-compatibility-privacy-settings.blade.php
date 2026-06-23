<form wire:submit="save" class="space-y-5">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="scale" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('compatibility.privacy_title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('compatibility.privacy_helper') }}</flux:text>
    </div>

    @if(session('compatibility-privacy-status'))
        <flux:callout color="green" icon="check-circle">
            {{ session('compatibility-privacy-status') }}
        </flux:callout>
    @endif

    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid gap-3">
            <flux:checkbox wire:model.change="allowUseForMatching" label="{{ __('compatibility.privacy.allow_use_for_matching') }}" />
            <flux:checkbox wire:model.change="allowShowToHosts" label="{{ __('compatibility.privacy.allow_show_to_hosts') }}" />
            <flux:checkbox wire:model.change="allowShowToFutureRoommates" label="{{ __('compatibility.privacy.allow_show_to_future_roommates') }}" />
            <flux:checkbox wire:model.change="showSleepSchedule" label="{{ __('compatibility.privacy.show_sleep_schedule') }}" />
            <flux:checkbox wire:model.change="showWorkStudyStatus" label="{{ __('compatibility.privacy.show_work_study_status') }}" />
            <flux:checkbox wire:model.change="showSocialLevel" label="{{ __('compatibility.privacy.show_social_level') }}" />
            <flux:checkbox wire:model.change="showRoomPreferences" label="{{ __('compatibility.privacy.show_room_preferences') }}" />
            <flux:checkbox wire:model.change="showWorkspaceNeeds" label="{{ __('compatibility.privacy.show_workspace_needs') }}" />
            <flux:checkbox wire:model.change="showSmokingPreference" label="{{ __('compatibility.privacy.show_smoking_preference') }}" />
            <flux:checkbox wire:model.change="showPetPreference" label="{{ __('compatibility.privacy.show_pet_preference') }}" />
        </div>
    </div>

    <flux:button type="submit" variant="primary" class="w-full" icon="check" wire:loading.attr="disabled">
        {{ __('compatibility.actions.save_privacy') }}
    </flux:button>
</form>
