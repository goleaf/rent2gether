<x-layouts.guest title="Sign in">

    <flux:card class="space-y-6">
        <div>
            <flux:heading size="lg">Welcome back</flux:heading>
            <flux:text class="text-zinc-500 mt-1">Sign in to your rent2gether account.</flux:text>
        </div>

        <form method="POST" action="{{ route('auth.login.store') }}" class="space-y-4">
            @csrf

            <flux:field>
                <flux:label>Email</flux:label>
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
                <flux:label>Password</flux:label>
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
                <flux:checkbox name="remember" label="Remember me" />
            </div>

            <flux:button type="submit" variant="primary" class="w-full">
                Sign in
            </flux:button>
        </form>

        <flux:separator text="or" />

        <flux:text class="text-center text-sm text-zinc-500">
            No account yet?
            <flux:link href="{{ route('auth.register') }}" class="font-medium">Create one</flux:link>
        </flux:text>
    </flux:card>

</x-layouts.guest>
