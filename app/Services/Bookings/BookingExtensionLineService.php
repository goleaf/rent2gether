<?php

namespace App\Services\Bookings;

use App\Models\BookingExtension;
use App\Models\BookingExtensionLine;
use Illuminate\Support\Collection;

class BookingExtensionLineService
{
    /**
     * @return Collection<int, BookingExtensionLine>
     */
    public function rebuildLines(BookingExtension $extension): Collection
    {
        $extension->lines()->delete();

        return collect()
            ->merge($this->createNightLines($extension))
            ->merge($this->createDiscountLines($extension))
            ->merge($this->createFeeLines($extension))
            ->merge(array_filter([$this->createAdditionalDepositLine($extension)]))
            ->values();
    }

    /**
     * @return Collection<int, BookingExtensionLine>
     */
    public function createNightLines(BookingExtension $extension): Collection
    {
        $prices = app(BookingExtensionPriceService::class)->priceData($extension)['date_prices'] ?? [];

        return collect($prices)
            ->values()
            ->map(function (array $price, int $index) use ($extension): BookingExtensionLine {
                return BookingExtensionLine::query()->create([
                    'booking_extension_id' => $extension->id,
                    'line_type' => 'extension_night',
                    'label_key' => 'booking_extensions.lines.extension_night',
                    'date' => $price['date'],
                    'quantity' => 1,
                    'unit_amount' => $price['price'],
                    'amount' => $price['price'],
                    'currency' => $extension->currency ?: ($price['currency'] ?? 'EUR'),
                    'is_discount' => false,
                    'is_fee' => false,
                    'is_deposit' => false,
                    'is_refundable' => false,
                    'is_payable_now' => true,
                    'sort_order' => ($index + 1) * 10,
                ]);
            });
    }

    /**
     * @return Collection<int, BookingExtensionLine>
     */
    public function createDiscountLines(BookingExtension $extension): Collection
    {
        if ((float) $extension->discount_amount <= 0) {
            return collect();
        }

        return collect([
            BookingExtensionLine::query()->create([
                'booking_extension_id' => $extension->id,
                'line_type' => 'extension_discount',
                'label_key' => 'booking_extensions.lines.extension_discount',
                'date' => null,
                'quantity' => 1,
                'unit_amount' => -1 * (float) $extension->discount_amount,
                'amount' => -1 * (float) $extension->discount_amount,
                'currency' => $extension->currency ?: 'EUR',
                'is_discount' => true,
                'is_fee' => false,
                'is_deposit' => false,
                'is_refundable' => false,
                'is_payable_now' => true,
                'sort_order' => 500,
            ]),
        ]);
    }

    /**
     * @return Collection<int, BookingExtensionLine>
     */
    public function createFeeLines(BookingExtension $extension): Collection
    {
        $lines = collect();

        foreach ([
            'service_fee' => $extension->service_fee_amount,
            'cleaning_fee' => $extension->cleaning_fee_amount,
        ] as $type => $amount) {
            if ((float) $amount <= 0) {
                continue;
            }

            $lines->push(BookingExtensionLine::query()->create([
                'booking_extension_id' => $extension->id,
                'line_type' => $type,
                'label_key' => 'booking_extensions.lines.'.$type,
                'date' => null,
                'quantity' => 1,
                'unit_amount' => $amount,
                'amount' => $amount,
                'currency' => $extension->currency ?: 'EUR',
                'is_discount' => false,
                'is_fee' => true,
                'is_deposit' => false,
                'is_refundable' => false,
                'is_payable_now' => true,
                'sort_order' => $type === 'service_fee' ? 700 : 650,
            ]));
        }

        return $lines;
    }

    public function createAdditionalDepositLine(BookingExtension $extension): ?BookingExtensionLine
    {
        if ((float) $extension->additional_deposit_amount <= 0) {
            return null;
        }

        return BookingExtensionLine::query()->create([
            'booking_extension_id' => $extension->id,
            'line_type' => 'additional_deposit',
            'label_key' => 'booking_extensions.lines.additional_deposit',
            'date' => null,
            'quantity' => 1,
            'unit_amount' => $extension->additional_deposit_amount,
            'amount' => $extension->additional_deposit_amount,
            'currency' => $extension->currency ?: 'EUR',
            'is_discount' => false,
            'is_fee' => false,
            'is_deposit' => true,
            'is_refundable' => true,
            'is_payable_now' => true,
            'sort_order' => 800,
        ]);
    }
}
