<x-layouts.guest :title="__('auth.register.title')">

    <flux:card class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('auth.register.heading') }}</flux:heading>
            <flux:text class="text-zinc-500 mt-1">{{ __('auth.register.helper') }}</flux:text>
        </div>

        <form method="POST" action="{{ route('auth.register.store') }}" class="space-y-4">
            @csrf

            <flux:field>
                <flux:label>{{ __('auth.register.full_name') }}</flux:label>
                <flux:input
                    type="text"
                    name="name"
                    :value="old('name')"
                    required
                    autofocus
                    autocomplete="name"
                    icon="user"
                />
                @error('name')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('auth.email') }}</flux:label>
                <flux:input
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
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
                    autocomplete="new-password"
                    icon="lock-closed"
                />
                @error('password')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <flux:field>
                <flux:label>{{ __('auth.register.confirm_password') }}</flux:label>
                <flux:input
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    icon="lock-closed"
                />
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('auth.register.submit') }}
            </flux:button>
        </form>

        <flux:separator :text="__('auth.or')" />

        <flux:text class="text-center text-sm text-zinc-500">
            {{ __('auth.register.has_account') }}
            <flux:link href="{{ route('auth.login') }}" class="font-medium">{{ __('auth.register.sign_in') }}</flux:link>
        </flux:text>
    </flux:card>

</x-layouts.guest>
