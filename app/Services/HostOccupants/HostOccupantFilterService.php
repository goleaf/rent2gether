<?php

namespace App\Services\HostOccupants;

use App\Models\HostCurrentStaySnapshot;
use App\Services\HostOccupants\Data\HostOccupantFilters;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class HostOccupantFilterService
{
    /**
     * @param  Builder<HostCurrentStaySnapshot>  $query
     * @return Builder<HostCurrentStaySnapshot>
     */
    public function apply(Builder $query, HostOccupantFilters $filters): Builder
    {
        $today = CarbonImmutable::today()->toDateString();
        $soon = CarbonImmutable::today()->addDays(3)->toDateString();

        $query
            ->when($filters->propertyId, fn (Builder $query) => $query->where('property_id', $filters->propertyId))
            ->when($filters->roomId, fn (Builder $query) => $query->where('room_id', $filters->roomId))
            ->when($filters->sleepingPlaceId, fn (Builder $query) => $query->where('sleeping_place_id', $filters->sleepingPlaceId))
            ->when($filters->paymentStatus, fn (Builder $query) => $query->where('payment_status', $filters->paymentStatus))
            ->when($filters->stayStatus, fn (Builder $query) => $query->where('stay_status', $filters->stayStatus));

        match ($filters->scope) {
            'check_ins_today' => $query->where('check_in_date', $today),
            'check_outs_today' => $query->where('check_out_date', $today),
            'leaving_soon' => $query->where('check_out_date', '>=', $today)->where('check_out_date', '<=', $soon),
            'checkout_overdue' => $query->where('checkout_overdue', true),
            'payment_pending' => $query->whereIn('payment_status', ['pending', 'partial', 'overdue']),
            'complaints' => $query->where('has_complaints', true),
            'needs_extension' => $query->where('needs_extension', true),
            'needs_checkout' => $query->where('needs_checkout', true),
            'needs_cleaning' => $query->where('needs_cleaning_after_checkout', true),
            default => null,
        };

        if ($filters->onlyNeedsAttention) {
            $query->where(function (Builder $attention): void {
                $attention
                    ->where('has_complaints', true)
                    ->orWhere('needs_extension', true)
                    ->orWhere('needs_checkout', true)
                    ->orWhere('checkout_due_today', true)
                    ->orWhere('checkout_overdue', true)
                    ->orWhere('needs_cleaning_after_checkout', true)
                    ->orWhereIn('payment_status', ['pending', 'partial', 'overdue']);
            });
        }

        return $query;
    }
}
