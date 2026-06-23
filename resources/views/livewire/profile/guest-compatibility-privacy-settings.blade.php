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
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="allowUseForMatching" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('compatibility.privacy.allow_use_for_matching') }}</span>
                    </span>
                </flux:label>
                <flux:error name="allowUseForMatching" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="allowShowToHosts" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('compatibility.privacy.allow_show_to_hosts') }}</span>
                    </span>
                </flux:label>
                <flux:error name="allowShowToHosts" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="allowShowToFutureRoommates" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('compatibility.privacy.allow_show_to_future_roommates') }}</span>
                    </span>
                </flux:label>
                <flux:error name="allowShowToFutureRoommates" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="showSleepSchedule" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('compatibility.privacy.show_sleep_schedule') }}</span>
                    </span>
                </flux:label>
                <flux:error name="showSleepSchedule" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="showWorkStudyStatus" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('compatibility.privacy.show_work_study_status') }}</span>
                    </span>
                </flux:label>
                <flux:error name="showWorkStudyStatus" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="showSocialLevel" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('compatibility.privacy.show_social_level') }}</span>
                    </span>
                </flux:label>
                <flux:error name="showSocialLevel" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="showRoomPreferences" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('compatibility.privacy.show_room_preferences') }}</span>
                    </span>
                </flux:label>
                <flux:error name="showRoomPreferences" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="showWorkspaceNeeds" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('compatibility.privacy.show_workspace_needs') }}</span>
                    </span>
                </flux:label>
                <flux:error name="showWorkspaceNeeds" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="showSmokingPreference" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('compatibility.privacy.show_smoking_preference') }}</span>
                    </span>
                </flux:label>
                <flux:error name="showSmokingPreference" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="showPetPreference" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('compatibility.privacy.show_pet_preference') }}</span>
                    </span>
                </flux:label>
                <flux:error name="showPetPreference" />
            </flux:field>
        </div>
    </div>

    <flux:button type="submit" variant="primary" class="w-full" icon="check" wire:loading.attr="disabled">
        {{ __('compatibility.actions.save_privacy') }}
    </flux:button>
</form>
