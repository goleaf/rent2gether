<?php

namespace App\Livewire\Host\Occupants;

use App\Models\User;
use App\Services\HostOccupants\Data\HostOccupantFilters;
use App\Services\HostOccupants\Data\HostOccupantSummaryData;
use App\Services\HostOccupants\HostCurrentOccupantsService;
use App\Services\HostOccupants\HostOccupantSummaryService;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\Paginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

abstract class BaseHostOccupantsComponent extends Component
{
    use WithPagination;

    private const OCCUPANTS_PAGE = 'currentOccupantsPage';

    private const OCCUPANTS_PAGE_SIZE = 10;

    /**
     * @var list<string>
     */
    private const SCOPES = [
        'all',
        'check_ins_today',
        'check_outs_today',
        'leaving_soon',
        'checkout_overdue',
        'payment_pending',
        'complaints',
        'needs_extension',
        'needs_checkout',
        'needs_cleaning',
    ];

    public string $section = 'page';

    #[Url(as: 'occupants_scope', except: 'all')]
    public string $scope = 'all';

    #[Url(as: 'occupants_attention', except: false)]
    public bool $onlyNeedsAttention = false;

    public function mount(): void
    {
        $this->scope = $this->normalizedScope($this->scope);
        $this->onlyNeedsAttention = (bool) $this->onlyNeedsAttention;
    }

    public function setScope(string $scope): void
    {
        $this->scope = $this->normalizedScope($scope);
        $this->resetPage(pageName: self::OCCUPANTS_PAGE);
        $this->flushOccupantState();
    }

    public function resetOccupantFilters(): void
    {
        $this->scope = 'all';
        $this->onlyNeedsAttention = false;
        $this->resetPage(pageName: self::OCCUPANTS_PAGE);
        $this->flushOccupantState();
    }

    public function updatedScope(): void
    {
        $this->scope = $this->normalizedScope($this->scope);
        $this->resetPage(pageName: self::OCCUPANTS_PAGE);
        $this->flushOccupantState();
    }

    public function updatedOnlyNeedsAttention(): void
    {
        $this->onlyNeedsAttention = (bool) $this->onlyNeedsAttention;
        $this->resetPage(pageName: self::OCCUPANTS_PAGE);
        $this->flushOccupantState();
    }

    #[Computed]
    public function occupants(): Paginator
    {
        $host = auth()->user();

        if (! $host instanceof User) {
            return new Paginator(collect(), self::OCCUPANTS_PAGE_SIZE, 1, [
                'pageName' => self::OCCUPANTS_PAGE,
            ]);
        }

        return app(HostCurrentOccupantsService::class)->paginateCurrentOccupants(
            $host,
            $this->filters(),
            self::OCCUPANTS_PAGE_SIZE,
            self::OCCUPANTS_PAGE,
        );
    }

    #[Computed]
    public function scopeOptions(): array
    {
        return collect(self::SCOPES)
            ->map(fn (string $scope): array => [
                'value' => $scope,
                'label' => __('current_occupants.filters.'.$scope),
            ])
            ->all();
    }

    public function render(): View
    {
        return view('livewire.host.occupants.shell', [
            'section' => $this->section,
            'summary' => $this->summary(),
            'occupants' => $this->occupants,
            'scopeOptions' => $this->scopeOptions,
        ]);
    }

    private function filters(): HostOccupantFilters
    {
        return new HostOccupantFilters(
            scope: $this->normalizedScope($this->scope),
            onlyNeedsAttention: $this->onlyNeedsAttention,
        );
    }

    private function summary(): HostOccupantSummaryData
    {
        $host = auth()->user();

        if (! $host) {
            return new HostOccupantSummaryData;
        }

        return app(HostOccupantSummaryService::class)->getSummary($host);
    }

    private function normalizedScope(string $scope): string
    {
        return in_array($scope, self::SCOPES, true) ? $scope : 'all';
    }

    private function flushOccupantState(): void
    {
        unset($this->occupants);
    }
}
