<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            if (! Schema::hasColumn('rooms', 'is_private')) {
                $table->boolean('is_private')->default(false)->after('type');
            }

            if (! Schema::hasColumn('rooms', 'is_pass_through')) {
                $table->boolean('is_pass_through')->default(false)->after('is_private');
            }

            if (! Schema::hasColumn('rooms', 'windows_count')) {
                $table->unsignedTinyInteger('windows_count')->default(0)->after('has_window');
            }

            if (! Schema::hasColumn('rooms', 'ventilation_level')) {
                $table->string('ventilation_level')->nullable()->after('light_level');
            }

            if (! Schema::hasColumn('rooms', 'can_talk_at_night')) {
                $table->boolean('can_talk_at_night')->default(false)->after('can_turn_light_at_night');
            }

            if (! Schema::hasColumn('rooms', 'room_rules_text')) {
                $table->text('room_rules_text')->nullable()->after('rules');
            }
        });

        Schema::table('room_translations', function (Blueprint $table): void {
            if (! Schema::hasColumn('room_translations', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('room_translations', function (Blueprint $table): void {
            if (Schema::hasColumn('room_translations', 'notes')) {
                $table->dropColumn('notes');
            }
        });

        Schema::table('rooms', function (Blueprint $table): void {
            foreach ([
                'is_private',
                'is_pass_through',
                'windows_count',
                'ventilation_level',
                'can_talk_at_night',
                'room_rules_text',
            ] as $column) {
                if (Schema::hasColumn('rooms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
