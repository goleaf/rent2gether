@switch($section)
    @case('identity')
        <div class="space-y-4">
            <div class="flex items-center gap-4">
                <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                    @if($avatar && str_starts_with((string) $avatar->getMimeType(), 'image/'))
                        <img src="{{ $avatar->temporaryUrl() }}" alt="{{ __('host.profile.fields.avatar') }}" class="size-full object-cover">
                    @elseif($this->currentAvatarUrl())
                        <img src="{{ $this->currentAvatarUrl() }}" alt="{{ __('host.profile.fields.avatar') }}" class="size-full object-cover">
                    @else
                        <flux:icon name="user" class="size-8 text-zinc-400" />
                    @endif
                </div>

                <div class="flex-1 space-y-2">
                    <flux:file-upload
                        wire:model="avatar"
                        :label="__('host.profile.fields.avatar')"
                        :description="__('host.profile.helpers.avatar')"
                        :error="$errors->first('avatar')"
                    >
                        <flux:file-upload.dropzone
                            :heading="__('host.profile.fields.avatar')"
                            :text="__('host.profile.helpers.avatar')"
                            with-progress
                            inline
                        />
                    </flux:file-upload>
                    <flux:text wire:loading wire:target="avatar" size="sm" class="text-zinc-500 dark:text-zinc-400">
                        {{ __('media.manager.uploading') }}
                    </flux:text>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('host.profile.fields.display_name') }}</flux:label>
                    <flux:input wire:model.blur="displayName" autocomplete="name" />
                    <flux:error name="displayName" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('host.profile.fields.languages') }}</flux:label>
                    <flux:input wire:model.blur="languages" placeholder="{{ __('host.profile.helpers.languages_placeholder') }}" />
                    <flux:description>{{ __('host.profile.helpers.languages') }}</flux:description>
                    <flux:error name="languages" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('host.profile.fields.about') }}</flux:label>
                <flux:textarea wire:model.blur="about" rows="4" />
                <flux:description>{{ __('host.profile.helpers.about') }}</flux:description>
                <flux:error name="about" />
            </flux:field>
        </div>
        @break

    @case('style')
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('host.profile.fields.response_style') }}</flux:label>
                <flux:select wire:model.change="responseStyle">
                    <flux:select.option value="friendly">{{ __('host.profile.options.response_style.friendly') }}</flux:select.option>
                    <flux:select.option value="quick">{{ __('host.profile.options.response_style.quick') }}</flux:select.option>
                    <flux:select.option value="detailed">{{ __('host.profile.options.response_style.detailed') }}</flux:select.option>
                </flux:select>
                <flux:error name="responseStyle" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('host.profile.fields.hosting_experience') }}</flux:label>
                <flux:select wire:model.change="hostingExperience">
                    <flux:select.option value="">{{ __('host.profile.options.not_specified') }}</flux:select.option>
                    <flux:select.option value="new_host">{{ __('host.profile.options.hosting_experience.new_host') }}</flux:select.option>
                    <flux:select.option value="some_experience">{{ __('host.profile.options.hosting_experience.some_experience') }}</flux:select.option>
                    <flux:select.option value="experienced">{{ __('host.profile.options.hosting_experience.experienced') }}</flux:select.option>
                </flux:select>
                <flux:error name="hostingExperience" />
            </flux:field>

            <div class="grid gap-3 sm:col-span-2">
                <flux:checkbox wire:model.change="livesInProperty" label="{{ __('host.profile.fields.lives_in_property') }}" />
                <flux:checkbox wire:model.change="livesNearby" label="{{ __('host.profile.fields.lives_nearby') }}" />
                <flux:checkbox wire:model.change="canHelpWithCheckIn" label="{{ __('host.profile.fields.can_help_with_check_in') }}" />
                <flux:checkbox wire:model.change="emergencyContactAvailable" label="{{ __('host.profile.fields.emergency_contact_available') }}" />
            </div>
        </div>
        @break

    @case('defaults')
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('host.profile.fields.default_check_in_time') }}</flux:label>
                <flux:input type="time" wire:model.change="defaultCheckInTime" />
                <flux:error name="defaultCheckInTime" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('host.profile.fields.default_check_out_time') }}</flux:label>
                <flux:input type="time" wire:model.change="defaultCheckOutTime" />
                <flux:error name="defaultCheckOutTime" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('host.profile.fields.default_cancellation_policy') }}</flux:label>
                <flux:select wire:model.change="defaultCancellationPolicy">
                    <flux:select.option value="flexible">{{ __('host.profile.options.cancellation_policy.flexible') }}</flux:select.option>
                    <flux:select.option value="moderate">{{ __('host.profile.options.cancellation_policy.moderate') }}</flux:select.option>
                    <flux:select.option value="strict">{{ __('host.profile.options.cancellation_policy.strict') }}</flux:select.option>
                    <flux:select.option value="non_refundable">{{ __('host.profile.options.cancellation_policy.non_refundable') }}</flux:select.option>
                </flux:select>
                <flux:error name="defaultCancellationPolicy" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('host.profile.fields.default_deposit_setting') }}</flux:label>
                <flux:select wire:model.change="defaultDepositSetting">
                    <flux:select.option value="none">{{ __('host.profile.options.deposit_setting.none') }}</flux:select.option>
                    <flux:select.option value="small">{{ __('host.profile.options.deposit_setting.small') }}</flux:select.option>
                    <flux:select.option value="standard">{{ __('host.profile.options.deposit_setting.standard') }}</flux:select.option>
                    <flux:select.option value="custom">{{ __('host.profile.options.deposit_setting.custom') }}</flux:select.option>
                </flux:select>
                <flux:error name="defaultDepositSetting" />
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>{{ __('host.profile.fields.default_house_rules') }}</flux:label>
                <flux:textarea wire:model.blur="defaultHouseRules" rows="4" />
                <flux:description>{{ __('host.profile.helpers.default_house_rules') }}</flux:description>
                <flux:error name="defaultHouseRules" />
            </flux:field>
        </div>
        @break
@endswitch
