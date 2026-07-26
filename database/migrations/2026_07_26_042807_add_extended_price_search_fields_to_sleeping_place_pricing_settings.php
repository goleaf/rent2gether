<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addPricingColumns();
        $this->addIndexes('sleeping_places', $this->sleepingPlaceIndexes());
        $this->addIndexes('sleeping_place_pricing_settings', $this->pricingSettingIndexes());
        $this->addIndexes('sleeping_place_discount_rules', $this->discountRuleIndexes());
        $this->addIndexes('promo_codes', $this->promoCodeIndexes());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexes('promo_codes', $this->promoCodeIndexes());
        $this->dropIndexes('sleeping_place_discount_rules', $this->discountRuleIndexes());
        $this->dropIndexes('sleeping_place_pricing_settings', $this->pricingSettingIndexes());
        $this->dropIndexes('sleeping_places', $this->sleepingPlaceIndexes());
        $this->dropPricingColumns();
    }

    private function addPricingColumns(): void
    {
        if (! Schema::hasTable('sleeping_place_pricing_settings')) {
            return;
        }

        $missingColumns = array_values(array_filter(
            $this->pricingColumns(),
            fn (string $column): bool => ! Schema::hasColumn('sleeping_place_pricing_settings', $column),
        ));

        if ($missingColumns === []) {
            return;
        }

        Schema::table('sleeping_place_pricing_settings', function (Blueprint $table) use ($missingColumns): void {
            foreach ($missingColumns as $column) {
                $table->boolean($column)->default(false);
            }
        });
    }

    private function dropPricingColumns(): void
    {
        if (! Schema::hasTable('sleeping_place_pricing_settings')) {
            return;
        }

        $existingColumns = array_values(array_filter(
            $this->pricingColumns(),
            fn (string $column): bool => Schema::hasColumn('sleeping_place_pricing_settings', $column),
        ));

        if ($existingColumns === []) {
            return;
        }

        Schema::table('sleeping_place_pricing_settings', function (Blueprint $table) use ($existingColumns): void {
            $table->dropColumn($existingColumns);
        });
    }

    /**
     * @return list<string>
     */
    private function pricingColumns(): array
    {
        return [
            'installment_payment_allowed',
            'pay_later_allowed',
            'pay_on_arrival_allowed',
            'all_fees_included',
            'show_total_price_upfront',
            'hidden_fees_disclosed',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function sleepingPlaceIndexes(): array
    {
        return [
            'sp_status_weekly_price_idx' => ['status', 'weekly_price'],
            'sp_status_monthly_price_idx' => ['status', 'monthly_price'],
            'sp_status_cleaning_fee_idx' => ['status', 'cleaning_fee'],
            'sp_status_cancel_policy_idx' => ['status', 'cancellation_policy'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function pricingSettingIndexes(): array
    {
        return [
            'spps_active_weekly_place_idx' => ['active', 'weekly_price', 'sleeping_place_id'],
            'spps_active_monthly_place_idx' => ['active', 'monthly_price', 'sleeping_place_id'],
            'spps_active_deposit_place_idx' => ['active', 'deposit_amount', 'sleeping_place_id'],
            'spps_active_cleaning_place_idx' => ['active', 'cleaning_fee', 'sleeping_place_id'],
            'spps_payment_modes_place_idx' => ['active', 'installment_payment_allowed', 'pay_later_allowed', 'pay_on_arrival_allowed', 'sleeping_place_id'],
            'spps_transparent_price_place_idx' => ['active', 'all_fees_included', 'show_total_price_upfront', 'hidden_fees_disclosed', 'sleeping_place_id'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function discountRuleIndexes(): array
    {
        return [
            'spdr_active_place_type_idx' => ['active', 'sleeping_place_id', 'discount_type'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function promoCodeIndexes(): array
    {
        return [
            'promo_active_place_dates_idx' => ['active', 'sleeping_place_id', 'starts_at', 'ends_at'],
        ];
    }

    /**
     * @param  array<string, list<string>>  $indexes
     */
    private function addIndexes(string $tableName, array $indexes): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        foreach ($indexes as $indexName => $columns) {
            if ($this->hasAllColumns($tableName, $columns) && ! Schema::hasIndex($tableName, $indexName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName): void {
                    $table->index($columns, $indexName);
                });
            }
        }
    }

    /**
     * @param  array<string, list<string>>  $indexes
     */
    private function dropIndexes(string $tableName, array $indexes): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        foreach (array_keys($indexes) as $indexName) {
            if (Schema::hasIndex($tableName, $indexName)) {
                Schema::table($tableName, function (Blueprint $table) use ($indexName): void {
                    $table->dropIndex($indexName);
                });
            }
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function hasAllColumns(string $tableName, array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return false;
            }
        }

        return true;
    }
};
