<?php

namespace Database\Factories;

use App\Models\Rule;
use App\Models\RuleTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RuleTranslation>
 */
class RuleTranslationFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'rule_id' => Rule::factory(),
            'locale' => 'en',
            'name' => $name,
            'name_normalized' => Str::lower($name),
            'description' => $this->faker->sentence(),
        ];
    }
}
