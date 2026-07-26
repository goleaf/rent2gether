<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->hasColumns('roommate_experience_reviews', [
            'room_id',
            'quiet_roommates',
            'clean_roommates',
            'friendly_roommates',
            'roommate_experience_rating',
        ])) {
            return;
        }

        $this->addIndex(['room_id', 'quiet_roommates'], 'roommate_reviews_room_quiet_idx');
        $this->addIndex(['room_id', 'clean_roommates'], 'roommate_reviews_room_clean_idx');
        $this->addIndex(['room_id', 'friendly_roommates'], 'roommate_reviews_room_friendly_idx');
        $this->addIndex(['room_id', 'roommate_experience_rating'], 'roommate_reviews_room_rating_idx');
    }

    public function down(): void
    {
        $this->dropIndex('roommate_reviews_room_rating_idx');
        $this->dropIndex('roommate_reviews_room_friendly_idx');
        $this->dropIndex('roommate_reviews_room_clean_idx');
        $this->dropIndex('roommate_reviews_room_quiet_idx');
    }

    /**
     * @param  list<string>  $columns
     */
    private function hasColumns(string $table, array $columns): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string>  $columns
     */
    private function addIndex(array $columns, string $name): void
    {
        if (
            Schema::hasIndex('roommate_experience_reviews', $name)
            || Schema::hasIndex('roommate_experience_reviews', $columns)
        ) {
            return;
        }

        Schema::table('roommate_experience_reviews', function (Blueprint $table) use ($columns, $name): void {
            $table->index($columns, $name);
        });
    }

    private function dropIndex(string $name): void
    {
        if (! Schema::hasTable('roommate_experience_reviews') || ! Schema::hasIndex('roommate_experience_reviews', $name)) {
            return;
        }

        Schema::table('roommate_experience_reviews', function (Blueprint $table) use ($name): void {
            $table->dropIndex($name);
        });
    }
};
