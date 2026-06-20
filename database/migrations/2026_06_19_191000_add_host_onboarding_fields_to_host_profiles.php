<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('host_profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('host_profiles', 'response_style')) {
                $table->string('response_style')->nullable()->after('response_rate');
            }

            if (! Schema::hasColumn('host_profiles', 'can_help_with_check_in')) {
                $table->boolean('can_help_with_check_in')->default(false)->after('lives_nearby');
            }

            if (! Schema::hasColumn('host_profiles', 'emergency_contact_available')) {
                $table->boolean('emergency_contact_available')->default(false)->after('can_help_with_check_in');
            }

            if (! Schema::hasColumn('host_profiles', 'hosting_experience')) {
                $table->string('hosting_experience')->nullable()->after('emergency_contact_available');
            }

            if (! Schema::hasColumn('host_profiles', 'default_check_in_time')) {
                $table->time('default_check_in_time')->nullable()->after('hosting_experience');
            }

            if (! Schema::hasColumn('host_profiles', 'default_check_out_time')) {
                $table->time('default_check_out_time')->nullable()->after('default_check_in_time');
            }

            if (! Schema::hasColumn('host_profiles', 'default_cancellation_policy')) {
                $table->string('default_cancellation_policy')->default('flexible')->after('default_check_out_time');
            }

            if (! Schema::hasColumn('host_profiles', 'default_deposit_setting')) {
                $table->string('default_deposit_setting')->default('none')->after('default_cancellation_policy');
            }

            if (! Schema::hasColumn('host_profiles', 'default_house_rules')) {
                $table->text('default_house_rules')->nullable()->after('default_deposit_setting');
            }
        });
    }

    public function down(): void
    {
        Schema::table('host_profiles', function (Blueprint $table): void {
            $columns = collect([
                'default_house_rules',
                'default_deposit_setting',
                'default_cancellation_policy',
                'default_check_out_time',
                'default_check_in_time',
                'hosting_experience',
                'emergency_contact_available',
                'can_help_with_check_in',
                'response_style',
            ])->filter(fn (string $column): bool => Schema::hasColumn('host_profiles', $column))->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
