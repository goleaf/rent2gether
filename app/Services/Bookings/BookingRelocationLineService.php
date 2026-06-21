<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationPriceLine;
use Illuminate\Support\Collection;

class BookingRelocationLineService
{
    /**
     * @return Collection<int, BookingRelocationPriceLine>
     */
    public function rebuildLines(BookingRelocation $relocation): Collection
    {
        $relocation->priceLines()->delete();

        $lines = collect();
        $lines->push($this->createOldRemainingValueLine($relocation));
        $lines->push($this->createNewRemainingValueLine($relocation));

        foreach ($this->createDifferenceLines($relocation) as $line) {
            $lines->push($line);
        }

        if ($line = $this->createDepositDifferenceLine($relocation)) {
            $lines->push($line);
        }

        if ($line = $this->createRefundLine($relocation)) {
            $lines->push($line);
        }

        return $lines;
    }

    public function createOldRemainingValueLine(BookingRelocation $relocation): BookingRelocationPriceLine
    {
        return $this->line($relocation, 'old_remaining_value', (float) $relocation->old_remaining_value_amount, 10);
    }

    public function createNewRemainingValueLine(BookingRelocation $relocation): BookingRelocationPriceLine
    {
        return $this->line($relocation, 'new_remaining_value', (float) $relocation->new_remaining_value_amount, 20);
    }

    /**
     * @return Collection<int, BookingRelocationPriceLine>
     */
    public function createDifferenceLines(BookingRelocation $relocation): Collection
    {
        return collect([
            $this->line($relocation, 'price_difference', (float) $relocation->price_difference_amount, 30),
            $this->line($relocation, 'additional_payment', (float) $relocation->additional_payment_amount, 40, isPayableNow: (float) $relocation->additional_payment_amount > 0),
        ]);
    }

    public function createDepositDifferenceLine(BookingRelocation $relocation): ?BookingRelocationPriceLine
    {
        if ((float) $relocation->additional_deposit_amount <= 0) {
            return null;
        }

        return $this->line($relocation, 'deposit_difference', (float) $relocation->additional_deposit_amount, 50, isDeposit: true);
    }

    public function createRefundLine(BookingRelocation $relocation): ?BookingRelocationPriceLine
    {
        if ((float) $relocation->refund_amount <= 0) {
            return null;
        }

        return $this->line($relocation, 'refund', (float) $relocation->refund_amount, 60, isRefundable: true, isPayableNow: false);
    }

    private function line(
        BookingRelocation $relocation,
        string $type,
        float $amount,
        int $sortOrder,
        bool $isDeposit = false,
        bool $isRefundable = true,
        bool $isPayableNow = true,
    ): BookingRelocationPriceLine {
        return BookingRelocationPriceLine::query()->create([
            'booking_relocation_id' => $relocation->id,
            'line_type' => $type,
            'label_key' => 'booking_relocations.lines.'.$type,
            'date' => null,
            'quantity' => 1,
            'unit_amount' => $amount,
            'amount' => $amount,
            'currency' => $relocation->currency,
            'is_discount' => $amount < 0,
            'is_fee' => in_array($type, ['additional_payment', 'service_fee_difference', 'cleaning_fee_difference'], true),
            'is_deposit' => $isDeposit,
            'is_refundable' => $isRefundable,
            'is_payable_now' => $isPayableNow,
            'sort_order' => $sortOrder,
        ]);
    }
}
