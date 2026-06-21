<?php

namespace App\Services\Bookings;

use App\Models\BookingPayment;
use App\Models\BookingRefund;
use App\Models\PaymentReceipt;

class BookingPaymentNumberService
{
    public function generatePaymentNumber(): string
    {
        return $this->ensureUnique($this->nextNumber('PAY', BookingPayment::query()->where('payment_number', 'like', $this->prefix('PAY').'%')->count() + 1));
    }

    public function generateRefundNumber(): string
    {
        return $this->ensureUnique($this->nextNumber('REF', BookingRefund::query()->where('refund_number', 'like', $this->prefix('REF').'%')->count() + 1));
    }

    public function generateReceiptNumber(): string
    {
        return $this->ensureUnique($this->nextNumber('RCT', PaymentReceipt::query()->where('receipt_number', 'like', $this->prefix('RCT').'%')->count() + 1));
    }

    public function ensureUnique(string $number): string
    {
        [$prefix] = explode('-', $number);
        $next = (int) substr($number, -6);
        $candidate = $number;

        while ($this->exists($candidate)) {
            $next++;
            $candidate = $this->nextNumber($prefix, $next);
        }

        return $candidate;
    }

    private function prefix(string $prefix): string
    {
        return sprintf('%s-%s-', $prefix, now()->format('Y'));
    }

    private function nextNumber(string $prefix, int $sequence): string
    {
        return sprintf('%s%06d', $this->prefix($prefix), $sequence);
    }

    private function exists(string $number): bool
    {
        return BookingPayment::query()->where('payment_number', $number)->exists()
            || BookingRefund::query()->where('refund_number', $number)->exists()
            || PaymentReceipt::query()->where('receipt_number', $number)->exists();
    }
}
