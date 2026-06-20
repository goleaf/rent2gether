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
        foreach ($this->indexes() as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $indexes): void {
                foreach ($indexes as $columns) {
                    if ($this->columnsExist($table, $columns) && ! Schema::hasIndex($table, $columns)) {
                        $blueprint->index($columns);
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (array_reverse($this->indexes()) as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $indexes): void {
                foreach (array_reverse($indexes) as $columns) {
                    if ($this->columnsExist($table, $columns) && Schema::hasIndex($table, $columns)) {
                        $blueprint->dropIndex($columns);
                    }
                }
            });
        }
    }

    /**
     * @return array<string, list<list<string>>>
     */
    private function indexes(): array
    {
        return [
            'conversations' => [
                ['bed_id'],
                ['booking_id'],
                ['participant_two_id'],
            ],
            'payouts' => [
                ['booking_id'],
                ['host_id'],
            ],
            'booking_extensions' => [
                ['booking_id'],
            ],
            'checkin_records' => [
                ['booking_id'],
            ],
            'checkout_records' => [
                ['booking_id'],
            ],
            'waitlist_entries' => [
                ['bed_id'],
            ],
            'user_profiles' => [
                ['country_id'],
            ],
            'properties' => [
                ['country_id', 'city_id'],
                ['region_id'],
            ],
            'media_items' => [
                ['owner_user_id'],
            ],
            'property_amenity' => [
                ['amenity_id'],
            ],
            'property_rule' => [
                ['rule_id'],
            ],
            'room_amenity' => [
                ['amenity_id'],
            ],
            'room_rule' => [
                ['rule_id'],
            ],
            'sleeping_place_amenity' => [
                ['amenity_id'],
            ],
            'sleeping_place_rule' => [
                ['rule_id'],
            ],
            'bookings' => [
                ['host_id'],
                ['property_id'],
                ['room_id'],
            ],
            'booking_guests' => [
                ['user_id'],
            ],
            'booking_price_lines' => [
                ['booking_id'],
            ],
            'booking_status_histories' => [
                ['changed_by_user_id'],
            ],
            'payment_records' => [
                ['booking_id'],
                ['payer_user_id'],
            ],
            'deposit_records' => [
                ['booking_id'],
            ],
            'refund_requests' => [
                ['booking_id'],
                ['requested_by_user_id'],
            ],
            'favorites' => [
                ['sleeping_place_id'],
                ['bed_id'],
            ],
            'waitlist_items' => [
                ['sleeping_place_id'],
            ],
            'message_threads' => [
                ['booking_id'],
                ['sleeping_place_id'],
            ],
            'messages' => [
                ['sender_id'],
            ],
            'reviews' => [
                ['reviewer_id'],
                ['reviewee_id'],
                ['property_id'],
                ['room_id'],
                ['bed_id'],
                ['sleeping_place_id'],
            ],
            'complaints' => [
                ['reporter_id'],
                ['reported_user_id'],
                ['booking_id'],
                ['property_id'],
                ['room_id'],
                ['bed_id'],
                ['sleeping_place_id'],
            ],
        ];
    }

    /**
     * @param  list<string>  $columns
     */
    private function columnsExist(string $table, array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }
};
