<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('room_occupant_snapshots') && ! Schema::hasColumn('room_occupant_snapshots', 'has_pet_snapshot')) {
            Schema::table('room_occupant_snapshots', function (Blueprint $table): void {
                $table->boolean('has_pet_snapshot')->nullable();
            });
        }

        $this->indexIfMissing('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'check_in_date', 'check_out_date'], 'ros_room_prebooking_dates_idx');
        $this->indexIfMissing('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'age_range_snapshot'], 'ros_room_prebooking_age_idx');
        $this->indexIfMissing('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'gender_for_room_policy_snapshot'], 'ros_room_prebooking_gender_idx');
        $this->indexIfMissing('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'has_pet_snapshot'], 'ros_room_prebooking_pet_idx');
        $this->indexIfMissing('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'home_presence_level_snapshot'], 'ros_room_prebooking_home_idx');
        $this->indexIfMissing('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'social_level_snapshot'], 'ros_room_prebooking_social_idx');
        $this->indexIfMissing('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'sleep_schedule_snapshot'], 'ros_room_prebooking_sleep_idx');
        $this->indexIfMissing('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'wake_schedule_snapshot'], 'ros_room_prebooking_wake_idx');

        $this->indexIfMissing('room_current_occupancy_snapshots', ['students_count'], 'rcos_students_count_idx');
        $this->indexIfMissing('room_current_occupancy_snapshots', ['workers_count'], 'rcos_workers_count_idx');
        $this->indexIfMissing('room_current_occupancy_snapshots', ['tourists_count'], 'rcos_tourists_count_idx');
        $this->indexIfMissing('room_current_occupancy_snapshots', ['long_term_residents_count'], 'rcos_long_term_count_idx');
        $this->indexIfMissing('room_current_occupancy_snapshots', ['male_occupants_count'], 'rcos_male_count_idx');
        $this->indexIfMissing('room_current_occupancy_snapshots', ['female_occupants_count'], 'rcos_female_count_idx');
        $this->indexIfMissing('room_current_occupancy_snapshots', ['early_wakeup_count'], 'rcos_early_wakeup_count_idx');
        $this->indexIfMissing('room_current_occupancy_snapshots', ['late_sleep_count'], 'rcos_late_sleep_count_idx');
        $this->indexIfMissing('room_current_occupancy_snapshots', ['night_work_count'], 'rcos_night_work_count_idx');
        $this->indexIfMissing('room_current_occupancy_snapshots', ['smokers_count'], 'rcos_smokers_count_idx');
        $this->indexIfMissing('room_current_occupancy_snapshots', ['non_smokers_count'], 'rcos_non_smokers_count_idx');
        $this->indexIfMissing('room_current_occupancy_snapshots', ['quiet_preferring_count'], 'rcos_quiet_count_idx');
        $this->indexIfMissing('room_current_occupancy_snapshots', ['social_count'], 'rcos_social_count_idx');

        $this->indexIfMissing('room_rating_snapshots', ['roommate_experience_rating', 'reviews_count'], 'room_rating_roommate_exp_reviews_idx');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('room_rating_snapshots', ['roommate_experience_rating', 'reviews_count'], 'room_rating_roommate_exp_reviews_idx');

        $this->dropIndexIfExists('room_current_occupancy_snapshots', ['social_count'], 'rcos_social_count_idx');
        $this->dropIndexIfExists('room_current_occupancy_snapshots', ['quiet_preferring_count'], 'rcos_quiet_count_idx');
        $this->dropIndexIfExists('room_current_occupancy_snapshots', ['non_smokers_count'], 'rcos_non_smokers_count_idx');
        $this->dropIndexIfExists('room_current_occupancy_snapshots', ['smokers_count'], 'rcos_smokers_count_idx');
        $this->dropIndexIfExists('room_current_occupancy_snapshots', ['night_work_count'], 'rcos_night_work_count_idx');
        $this->dropIndexIfExists('room_current_occupancy_snapshots', ['late_sleep_count'], 'rcos_late_sleep_count_idx');
        $this->dropIndexIfExists('room_current_occupancy_snapshots', ['early_wakeup_count'], 'rcos_early_wakeup_count_idx');
        $this->dropIndexIfExists('room_current_occupancy_snapshots', ['female_occupants_count'], 'rcos_female_count_idx');
        $this->dropIndexIfExists('room_current_occupancy_snapshots', ['male_occupants_count'], 'rcos_male_count_idx');
        $this->dropIndexIfExists('room_current_occupancy_snapshots', ['long_term_residents_count'], 'rcos_long_term_count_idx');
        $this->dropIndexIfExists('room_current_occupancy_snapshots', ['tourists_count'], 'rcos_tourists_count_idx');
        $this->dropIndexIfExists('room_current_occupancy_snapshots', ['workers_count'], 'rcos_workers_count_idx');
        $this->dropIndexIfExists('room_current_occupancy_snapshots', ['students_count'], 'rcos_students_count_idx');

        $this->dropIndexIfExists('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'wake_schedule_snapshot'], 'ros_room_prebooking_wake_idx');
        $this->dropIndexIfExists('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'sleep_schedule_snapshot'], 'ros_room_prebooking_sleep_idx');
        $this->dropIndexIfExists('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'social_level_snapshot'], 'ros_room_prebooking_social_idx');
        $this->dropIndexIfExists('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'home_presence_level_snapshot'], 'ros_room_prebooking_home_idx');
        $this->dropIndexIfExists('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'has_pet_snapshot'], 'ros_room_prebooking_pet_idx');
        $this->dropIndexIfExists('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'gender_for_room_policy_snapshot'], 'ros_room_prebooking_gender_idx');
        $this->dropIndexIfExists('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'age_range_snapshot'], 'ros_room_prebooking_age_idx');
        $this->dropIndexIfExists('room_occupant_snapshots', ['room_id', 'can_show_before_booking', 'status', 'check_in_date', 'check_out_date'], 'ros_room_prebooking_dates_idx');

        if (Schema::hasTable('room_occupant_snapshots') && Schema::hasColumn('room_occupant_snapshots', 'has_pet_snapshot')) {
            Schema::table('room_occupant_snapshots', function (Blueprint $table): void {
                $table->dropColumn('has_pet_snapshot');
            });
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function indexIfMissing(string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || Schema::hasIndex($tableName, $columns)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName): void {
            $table->index($columns, $indexName);
        });
    }

    /**
     * @param  list<string>  $columns
     */
    private function dropIndexIfExists(string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasIndex($tableName, $columns)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName): void {
            $table->dropIndex($indexName);
        });
    }
};
