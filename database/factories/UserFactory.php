<?php

namespace Database\Factories;

use App\Enums\UserRoleMode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_mode' => UserRoleMode::Guest->value,
            'preferred_locale' => 'en',
            'timezone' => 'UTC',
            'is_guest' => true,
            'is_host' => false,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function host(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_mode' => UserRoleMode::Host->value,
            'is_guest' => false,
            'is_host' => true,
        ]);
    }

    public function guestHost(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_mode' => UserRoleMode::GuestHost->value,
            'is_guest' => true,
            'is_host' => true,
        ]);
    }
}
