<flux:card class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('auth.register.heading') }}</flux:heading>
        <flux:text class="mt-1 text-zinc-500">{{ __('auth.register.helper') }}</flux:text>
    </div>

    <form wire:submit="register" class="space-y-4">
        <flux:field>
            <flux:label>{{ __('account.fields.display_name') }}</flux:label>
            <flux:input wire:model.blur="displayName" autocomplete="name" icon="user" autofocus />
            <flux:error name="displayName" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('auth.email') }}</flux:label>
            <flux:input type="email" wire:model.blur="email" autocomplete="email" icon="envelope" />
            <flux:error name="email" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('auth.password_label') }}</flux:label>
            <flux:input type="password" wire:model.blur="password" autocomplete="new-password" icon="lock-closed" />
            <flux:error name="password" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('auth.register.confirm_password') }}</flux:label>
            <flux:input type="password" wire:model.blur="passwordConfirmation" autocomplete="new-password" icon="lock-closed" />
            <flux:error name="passwordConfirmation" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('account.roles.label') }}</flux:label>
            <flux:select wire:model.change="accountRole">
                <flux:select.option value="guest">{{ __('account.roles.guest') }}</flux:select.option>
                <flux:select.option value="host">{{ __('account.roles.host') }}</flux:select.option>
                <flux:select.option value="both">{{ __('account.roles.both') }}</flux:select.option>
            </flux:select>
            <flux:description>
                @if($accountRole === 'host')
                    {{ __('account.roles.host_helper') }}
                @elseif($accountRole === 'both')
                    {{ __('account.roles.both_helper') }}
                @else
                    {{ __('account.roles.guest_helper') }}
                @endif
            </flux:description>
            <flux:error name="accountRole" />
        </flux:field>

        <flux:button type="submit" variant="primary" class="w-full data-loading:opacity-70">
            <span wire:loading.remove wire:target="register">{{ __('auth.register.submit') }}</span>
            <span wire:loading wire:target="register">{{ __('account.actions.creating') }}</span>
        </flux:button>
    </form>

    <flux:separator :text="__('auth.or')" />

    <flux:text class="text-center text-sm text-zinc-500">
        {{ __('auth.register.has_account') }}
        <flux:link href="{{ route('auth.login') }}" wire:navigate class="font-medium">{{ __('auth.register.sign_in') }}</flux:link>
    </flux:text>
</flux:card>
