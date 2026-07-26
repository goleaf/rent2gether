<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if ($this->hasColumns('notification_reminders', ['booking_id', 'user_id', 'recipient_type', 'reminder_type', 'status'])
            && ! Schema::hasIndex('notification_reminders', 'notification_reminders_check_in_lookup_idx')) {
            Schema::table('notification_reminders', function (Blueprint $table): void {
                $table->index(
                    ['booking_id', 'user_id', 'recipient_type', 'reminder_type', 'status'],
                    'notification_reminders_check_in_lookup_idx',
                );
            });
        }

        if ($this->hasColumns('notifications', ['recipient_user_id', 'booking_id', 'recipient_type', 'type'])
            && ! Schema::hasIndex('notifications', 'notifications_recipient_booking_type_idx')) {
            Schema::table('notifications', function (Blueprint $table): void {
                $table->index(
                    ['recipient_user_id', 'booking_id', 'recipient_type', 'type'],
                    'notifications_recipient_booking_type_idx',
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notifications') && Schema::hasIndex('notifications', 'notifications_recipient_booking_type_idx')) {
            Schema::table('notifications', function (Blueprint $table): void {
                $table->dropIndex('notifications_recipient_booking_type_idx');
            });
        }

        if (Schema::hasTable('notification_reminders') && Schema::hasIndex('notification_reminders', 'notification_reminders_check_in_lookup_idx')) {
            Schema::table('notification_reminders', function (Blueprint $table): void {
                $table->dropIndex('notification_reminders_check_in_lookup_idx');
            });
        }
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
};
