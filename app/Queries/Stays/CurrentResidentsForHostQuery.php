<?php

namespace App\Queries\Stays;

use App\Models\BookingStay;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class CurrentResidentsForHostQuery
{
    /**
     * Build the host-facing current residents query with card relations and shareable filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<BookingStay>
     */
    public function handle(User $host, array $filters = []): Builder
    {
        $query = BookingStay::query()
            ->select($this->residentColumns())
            ->forHost($host);

        $this->applyStatusFilter($query, $filters);
        $this->applyAttentionScope($query, $this->stringFilter($filters, 'scope'));
        $this->applyLocationFilters($query, $filters);

        return $query
            ->with($this->residentCardRelations())
            ->orderBy('planned_check_out_date')
            ->orderBy('id');
    }

    /**
     * @param  Builder<BookingStay>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyStatusFilter(Builder $query, array $filters): void
    {
        $status = $this->stringFilter($filters, 'status');

        if ($status === null) {
            $query->active();

            return;
        }

        $query->where('status', $status);
    }

    /**
     * @param  Builder<BookingStay>  $query
     */
    private function applyAttentionScope(Builder $query, ?string $scope): void
    {
        match ($scope) {
            'checkout_today' => $query->whereDate('planned_check_out_date', today()),
            'checkout_soon' => $query->where('checkout_soon', true),
            'complaints' => $query->where('has_open_complaint', true),
            'payment_issue' => $query->where('has_payment_problem', true),
            'extension_requested' => $query->where('extension_requested', true),
            'relocation_requested' => $query->where('relocation_requested', true),
            default => null,
        };
    }

    /**
     * @param  Builder<BookingStay>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyLocationFilters(Builder $query, array $filters): void
    {
        $propertyId = $this->integerFilter($filters, 'property_id');
        $roomId = $this->integerFilter($filters, 'room_id');

        if ($propertyId !== null) {
            $query->where('property_id', $propertyId);
        }

        if ($roomId !== null) {
            $query->where('room_id', $roomId);
        }
    }

    /**
     * @return list<string>
     */
    private function residentColumns(): array
    {
        return [
            'id',
            'stay_number',
            'booking_id',
            'guest_user_id',
            'host_user_id',
            'property_id',
            'room_id',
            'sleeping_place_id',
            'status',
            'payment_status',
            'check_in_date',
            'planned_check_out_date',
            'nights_remaining',
            'checkout_soon',
            'has_open_complaint',
            'has_payment_problem',
            'extension_requested',
            'relocation_requested',
        ];
    }

    /**
     * @return list<string>
     */
    private function residentCardRelations(): array
    {
        return [
            'guest:id,name,avatar,rating_as_guest,preferred_locale',
            'property:id,title',
            'room:id,title',
            'sleepingPlace:id,display_name,place_number',
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function stringFilter(array $filters, string $key): ?string
    {
        $value = $filters[$key] ?? null;

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function integerFilter(array $filters, string $key): ?int
    {
        $value = $filters[$key] ?? null;

        if (! is_numeric($value)) {
            return null;
        }

        $value = (int) $value;

        return $value > 0 ? $value : null;
    }
}
