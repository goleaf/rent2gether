<x-layouts.guest title="Create account">

    <flux:card class="space-y-6">
        <div>
            <flux:heading size="lg">Create your account</flux:heading>
            <flux:text class="text-zinc-500 mt-1">Start booking or listing beds today.</flux:text>
        </div>

        <form method="POST" action="{{ route('auth.register.store') }}" class="space-y-4">
            @csrf

            <flux:field>
                <flux:label>Full name</flux:label>
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
                <flux:label>Email</flux:label>
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
                <flux:label>Password</flux:label>
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
                <flux:label>Confirm password</flux:label>
                <flux:input
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    icon="lock-closed"
                />
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full">
                Create account
            </flux:button>
        </form>

        <flux:separator text="or" />

        <flux:text class="text-center text-sm text-zinc-500">
            Already have an account?
            <flux:link href="{{ route('auth.login') }}" class="font-medium">Sign in</flux:link>
        </flux:text>
    </flux:card>

</x-layouts.guest>
