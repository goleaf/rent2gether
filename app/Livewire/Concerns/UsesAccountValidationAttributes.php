<?php

namespace App\Livewire\Concerns;

trait UsesAccountValidationAttributes
{
    /**
     * @return array<string, string>
     */
    protected function accountValidationAttributes(): array
    {
        $attributes = app('translator')->get('account.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }
}
