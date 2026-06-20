<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkin_records', function (Blueprint $table): void {
            if (! Schema::hasColumn('checkin_records', 'property_found')) {
                $table->boolean('property_found')->default(false)->after('met_by');
            }
            if (! Schema::hasColumn('checkin_records', 'keys_received')) {
                $table->boolean('keys_received')->default(false)->after('keys_handed');
            }
            if (! Schema::hasColumn('checkin_records', 'code_received')) {
                $table->boolean('code_received')->default(false)->after('keys_received');
            }
            if (! Schema::hasColumn('checkin_records', 'sleeping_place_shown')) {
                $table->boolean('sleeping_place_shown')->default(false)->after('room_shown');
            }
            if (! Schema::hasColumn('checkin_records', 'everything_ok')) {
                $table->boolean('everything_ok')->default(false)->after('rules_explained');
            }
            if (! Schema::hasColumn('checkin_records', 'guest_confirmed_at')) {
                $table->timestamp('guest_confirmed_at')->nullable()->after('guest_confirmed');
            }
            if (! Schema::hasColumn('checkin_records', 'host_confirmed_at')) {
                $table->timestamp('host_confirmed_at')->nullable()->after('host_confirmed');
            }
            if (! Schema::hasColumn('checkin_records', 'problem_reported')) {
                $table->boolean('problem_reported')->default(false)->after('has_issue');
            }
            if (! Schema::hasColumn('checkin_records', 'problem_description')) {
                $table->text('problem_description')->nullable()->after('issue_description');
            }
            if (! Schema::hasColumn('checkin_records', 'problem_media')) {
                $table->json('problem_media')->nullable()->after('issue_photos');
            }
        });

        Schema::table('checkout_records', function (Blueprint $table): void {
            if (! Schema::hasColumn('checkout_records', 'planned_checkout_time')) {
                $table->time('planned_checkout_time')->nullable()->after('planned_time');
            }
            if (! Schema::hasColumn('checkout_records', 'actual_checkout_at')) {
                $table->timestamp('actual_checkout_at')->nullable()->after('actual_departure_at');
            }
            if (! Schema::hasColumn('checkout_records', 'belongings_removed')) {
                $table->boolean('belongings_removed')->default(false)->after('belongings_collected');
            }
            if (! Schema::hasColumn('checkout_records', 'guest_confirmed_checkout_at')) {
                $table->timestamp('guest_confirmed_checkout_at')->nullable()->after('guest_confirmed');
            }
            if (! Schema::hasColumn('checkout_records', 'host_confirmed_checkout_at')) {
                $table->timestamp('host_confirmed_checkout_at')->nullable()->after('host_confirmed');
            }
            if (! Schema::hasColumn('checkout_records', 'no_damage')) {
                $table->boolean('no_damage')->default(false)->after('has_damage');
            }
            if (! Schema::hasColumn('checkout_records', 'damage_found')) {
                $table->boolean('damage_found')->default(false)->after('no_damage');
            }
            if (! Schema::hasColumn('checkout_records', 'damage_description')) {
                $table->text('damage_description')->nullable()->after('damage_found');
            }
            if (! Schema::hasColumn('checkout_records', 'damage_media')) {
                $table->json('damage_media')->nullable()->after('photos_after');
            }
            if (! Schema::hasColumn('checkout_records', 'deposit_action')) {
                $table->string('deposit_action')->nullable()->after('deposit_withheld');
            }
        });
    }

    public function down(): void
    {
        Schema::table('checkin_records', function (Blueprint $table): void {
            foreach ([
                'property_found',
                'keys_received',
                'code_received',
                'sleeping_place_shown',
                'everything_ok',
                'guest_confirmed_at',
                'host_confirmed_at',
                'problem_reported',
                'problem_description',
                'problem_media',
            ] as $column) {
                if (Schema::hasColumn('checkin_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('checkout_records', function (Blueprint $table): void {
            foreach ([
                'planned_checkout_time',
                'actual_checkout_at',
                'belongings_removed',
                'guest_confirmed_checkout_at',
                'host_confirmed_checkout_at',
                'no_damage',
                'damage_found',
                'damage_description',
                'damage_media',
                'deposit_action',
            ] as $column) {
                if (Schema::hasColumn('checkout_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
