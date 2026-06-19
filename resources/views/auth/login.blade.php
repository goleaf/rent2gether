<x-layouts.guest :title="__('auth.login.title')">

    <flux:card class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('auth.login.heading') }}</flux:heading>
            <flux:text class="text-zinc-500 mt-1">{{ __('auth.login.helper') }}</flux:text>
        </div>

        <form method="POST" action="{{ route('auth.login.store') }}" class="space-y-4">
            @csrf

            <flux:field>
                <flux:label>{{ __('auth.email') }}</flux:label>
                <flux:input
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="email"
                    icon="envelope"
                />
                @error('email')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('auth.password_label') }}</flux:label>
                <flux:input
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    icon="lock-closed"
                />
                @error('password')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <div class="flex items-center justify-between">
                <flux:checkbox name="remember" :label="__('auth.login.remember')" />
            </div>

            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('auth.login.submit') }}
            </flux:button>
        </form>

        <flux:separator :text="__('auth.or')" />

        <flux:text class="text-center text-sm text-zinc-500">
            {{ __('auth.login.no_account') }}
            <flux:link href="{{ route('auth.register') }}" class="font-medium">{{ __('auth.login.create_account') }}</flux:link>
        </flux:text>
    </flux:card>

</x-layouts.guest>
