<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addTextColumns('property_translations', [
            'short_description',
            'full_description',
            'why_convenient',
            'main_pros',
            'important_cons',
            'what_to_know_beforehand',
            'what_is_included',
            'what_is_not_included',
            'what_to_bring',
            'where_to_store_belongings',
            'where_to_store_food',
            'kitchen_instructions',
            'bathroom_instructions',
            'laundry_instructions',
            'key_pickup_instructions',
            'night_entry_instructions',
            'host_contact_instructions',
            'problem_instructions',
            'lost_key_instructions',
            'neighbor_conflict_instructions',
            'repair_problem_instructions',
        ], 'safety_notes');

        $this->addTextColumns('room_translations', [
            'room_description',
            'room_rules_text',
            'room_pros',
            'room_cons',
            'who_lives_nearby_text',
            'quiet_hours_text',
            'storage_instructions',
            'shared_space_instructions',
        ], 'privacy_notes');

        $this->addTextColumns('sleeping_place_translations', [
            'sleeping_place_title',
            'sleeping_place_description',
            'sleeping_place_pros',
            'sleeping_place_cons',
            'sleeping_place_special_notes',
            'what_is_included_for_place',
            'what_guest_should_bring_for_place',
        ], 'accessibility_notes');
    }

    public function down(): void
    {
        $this->dropColumns('sleeping_place_translations', [
            'sleeping_place_title',
            'sleeping_place_description',
            'sleeping_place_pros',
            'sleeping_place_cons',
            'sleeping_place_special_notes',
            'what_is_included_for_place',
            'what_guest_should_bring_for_place',
        ]);

        $this->dropColumns('room_translations', [
            'room_description',
            'room_rules_text',
            'room_pros',
            'room_cons',
            'who_lives_nearby_text',
            'quiet_hours_text',
            'storage_instructions',
            'shared_space_instructions',
        ]);

        $this->dropColumns('property_translations', [
            'short_description',
            'full_description',
            'why_convenient',
            'main_pros',
            'important_cons',
            'what_to_know_beforehand',
            'what_is_included',
            'what_is_not_included',
            'what_to_bring',
            'where_to_store_belongings',
            'where_to_store_food',
            'kitchen_instructions',
            'bathroom_instructions',
            'laundry_instructions',
            'key_pickup_instructions',
            'night_entry_instructions',
            'host_contact_instructions',
            'problem_instructions',
            'lost_key_instructions',
            'neighbor_conflict_instructions',
            'repair_problem_instructions',
        ]);
    }

    /**
     * @param  list<string>  $columns
     */
    private function addTextColumns(string $table, array $columns, string $after): void
    {
        Schema::table($table, function (Blueprint $schema) use ($table, $columns, $after): void {
            $previous = $after;

            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    $previous = $column;

                    continue;
                }

                $schema->text($column)->nullable()->after($previous);
                $previous = $column;
            }
        });
    }

    /**
     * @param  list<string>  $columns
     */
    private function dropColumns(string $table, array $columns): void
    {
        $existing = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($table, $column),
        ));

        if ($existing === []) {
            return;
        }

        Schema::table($table, function (Blueprint $schema) use ($existing): void {
            $schema->dropColumn($existing);
        });
    }
};
