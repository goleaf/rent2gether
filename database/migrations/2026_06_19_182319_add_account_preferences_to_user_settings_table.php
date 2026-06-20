<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_settings', 'currency')) {
                $table->string('currency', 3)->default('EUR')->after('locale');
            }

            if (! Schema::hasColumn('user_settings', 'active_mode')) {
                $table->string('active_mode')->default('guest')->after('currency');
            }

            if (! Schema::hasColumn('user_settings', 'account_role')) {
                $table->string('account_role')->default('guest')->after('active_mode');
            }

            if (! Schema::hasColumn('user_settings', 'notification_preferences_json')) {
                $table->json('notification_preferences_json')->nullable()->after('account_role');
            }

            if (! Schema::hasColumn('user_settings', 'privacy_preferences_json')) {
                $table->json('privacy_preferences_json')->nullable()->after('notification_preferences_json');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table): void {
            $columns = collect([
                'privacy_preferences_json',
                'notification_preferences_json',
                'account_role',
                'active_mode',
                'currency',
            ])->filter(fn (string $column): bool => Schema::hasColumn('user_settings', $column))->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
