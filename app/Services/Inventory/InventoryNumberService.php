<?php

namespace App\Services\Inventory;

use App\Models\BookingInventoryAssignment;
use App\Models\InventoryCheck;
use App\Models\InventoryIssue;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\InventoryReplacement;

class InventoryNumberService
{
    public function generateInventoryNumber(): string
    {
        return $this->next('INV', InventoryItem::class, 'inventory_number');
    }

    public function generateAssignmentNumber(): string
    {
        return $this->next('INVA', BookingInventoryAssignment::class, 'assignment_number');
    }

    public function generateMovementNumber(): string
    {
        return $this->next('INVM', InventoryMovement::class, 'movement_number');
    }

    public function generateCheckNumber(): string
    {
        return $this->next('INVC', InventoryCheck::class, 'inventory_check_number');
    }

    public function generateIssueNumber(): string
    {
        return $this->next('INVI', InventoryIssue::class, 'inventory_issue_number');
    }

    public function generateReplacementNumber(): string
    {
        return $this->next('INVR', InventoryReplacement::class, 'replacement_number');
    }

    public function ensureUnique(string $number): string
    {
        if (! $this->existsAnywhere($number)) {
            return $number;
        }

        [$prefix, $year, $sequence] = explode('-', $number);

        do {
            $sequence = (int) $sequence + 1;
            $candidate = sprintf('%s-%s-%06d', $prefix, $year, $sequence);
        } while ($this->existsAnywhere($candidate));

        return $candidate;
    }

    private function next(string $prefix, string $modelClass, string $column): string
    {
        $number = sprintf('%s-%s-%06d', $prefix, now()->format('Y'), $modelClass::query()->count() + 1);

        while ($modelClass::query()->where($column, $number)->exists()) {
            $number = $this->ensureUnique($number);
        }

        return $number;
    }

    private function existsAnywhere(string $number): bool
    {
        return InventoryItem::query()->where('inventory_number', $number)->exists()
            || BookingInventoryAssignment::query()->where('assignment_number', $number)->exists()
            || InventoryMovement::query()->where('movement_number', $number)->exists()
            || InventoryCheck::query()->where('inventory_check_number', $number)->exists()
            || InventoryIssue::query()->where('inventory_issue_number', $number)->exists()
            || InventoryReplacement::query()->where('replacement_number', $number)->exists();
    }
}
