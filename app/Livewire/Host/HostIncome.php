<?php

namespace App\Livewire\Host;

use App\Models\User;
use App\Services\HostIncome\IncomeSummaryService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HostIncome extends Component
{
    public string $datePreset = 'this_month';

    public string $customStart = '';

    public string $customEnd = '';

    public function mount(): void
    {
        $this->setPresetRange('this_month');
    }

    public function updatedDatePreset(): void
    {
        $this->validateOnly('datePreset', [
            'datePreset' => ['required', Rule::in(['this_month', 'last_month', 'custom'])],
        ], attributes: $this->validationAttributes());

        if ($this->datePreset !== 'custom') {
            $this->setPresetRange($this->datePreset);
        }

        $this->flushSummary();
    }

    public function applyFilters(): void
    {
        $this->validate([
            'datePreset' => ['required', Rule::in(['this_month', 'last_month', 'custom'])],
            'customStart' => ['required', 'date'],
            'customEnd' => ['required', 'date', 'after_or_equal:customStart'],
        ], attributes: $this->validationAttributes());

        $this->flushSummary();
    }

    #[Computed]
    public function summary(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return app(IncomeSummaryService::class)->summarize(new User, CarbonImmutable::today(), CarbonImmutable::today());
        }

        [$start, $end] = $this->dateRange();

        return app(IncomeSummaryService::class)->summarize($user, $start, $end);
    }

    public function money(mixed $amount, ?string $currency = null): string
    {
        return Number::currency((float) ($amount ?: 0), strtoupper($currency ?: $this->summary['currency'] ?? 'EUR'), app()->getLocale());
    }

    public function render(): View
    {
        return view('livewire.host.host-income')
            ->layout('layouts.app', ['title' => __('host.income.title')]);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function dateRange(): array
    {
        return [
            CarbonImmutable::parse($this->customStart)->startOfDay(),
            CarbonImmutable::parse($this->customEnd)->startOfDay(),
        ];
    }

    private function setPresetRange(string $preset): void
    {
        $today = CarbonImmutable::today();

        if ($preset === 'last_month') {
            $month = $today->subMonthNoOverflow();
            $this->customStart = $month->startOfMonth()->toDateString();
            $this->customEnd = $month->endOfMonth()->toDateString();

            return;
        }

        $this->customStart = $today->startOfMonth()->toDateString();
        $this->customEnd = $today->endOfMonth()->toDateString();
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('host.income.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }

    private function flushSummary(): void
    {
        unset($this->summary);
    }
}
