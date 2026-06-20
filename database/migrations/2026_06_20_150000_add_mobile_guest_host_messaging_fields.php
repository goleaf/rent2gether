<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_threads', function (Blueprint $table): void {
            if (! Schema::hasColumn('message_threads', 'type')) {
                $table->string('type')->default('pre_booking')->after('id')->index();
            }

            if (! Schema::hasColumn('message_threads', 'property_id')) {
                $table->foreignId('property_id')->nullable()->after('booking_id')->constrained()->nullOnDelete();
            }
        });

        Schema::table('message_threads', function (Blueprint $table): void {
            foreach ([
                ['property_id'],
                ['guest_user_id', 'status', 'last_message_at'],
                ['host_user_id', 'status', 'last_message_at'],
                ['type', 'last_message_at'],
            ] as $columns) {
                if ($this->columnsExist('message_threads', $columns) && ! Schema::hasIndex('message_threads', $columns)) {
                    $table->index($columns);
                }
            }
        });

        Schema::table('messages', function (Blueprint $table): void {
            if (! Schema::hasColumn('messages', 'sender_user_id')) {
                $table->foreignId('sender_user_id')->nullable()->after('thread_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('messages', 'recipient_user_id')) {
                $table->foreignId('recipient_user_id')->nullable()->after('sender_user_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('messages', 'booking_id')) {
                $table->foreignId('booking_id')->nullable()->after('recipient_user_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('messages', 'property_id')) {
                $table->foreignId('property_id')->nullable()->after('booking_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('messages', 'sleeping_place_id')) {
                $table->foreignId('sleeping_place_id')->nullable()->after('property_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('messages', 'attachments')) {
                $table->json('attachments')->nullable()->after('attachment_type');
            }

            if (! Schema::hasColumn('messages', 'important')) {
                $table->boolean('important')->default(false)->after('is_important');
            }

            if (! Schema::hasColumn('messages', 'system_message')) {
                $table->boolean('system_message')->default(false)->after('important');
            }

            if (! Schema::hasColumn('messages', 'locale')) {
                $table->string('locale', 12)->nullable()->after('system_message')->index();
            }
        });

        Schema::table('messages', function (Blueprint $table): void {
            foreach ([
                ['sender_user_id', 'created_at'],
                ['recipient_user_id', 'read_at', 'created_at'],
                ['booking_id'],
                ['property_id'],
                ['sleeping_place_id'],
            ] as $columns) {
                if ($this->columnsExist('messages', $columns) && ! Schema::hasIndex('messages', $columns)) {
                    $table->index($columns);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table): void {
            foreach ([
                ['sleeping_place_id'],
                ['property_id'],
                ['booking_id'],
                ['recipient_user_id', 'read_at', 'created_at'],
                ['sender_user_id', 'created_at'],
            ] as $columns) {
                if ($this->columnsExist('messages', $columns) && Schema::hasIndex('messages', $columns)) {
                    $table->dropIndex($columns);
                }
            }
        });

        Schema::table('messages', function (Blueprint $table): void {
            foreach ([
                'sender_user_id',
                'recipient_user_id',
                'booking_id',
                'property_id',
                'sleeping_place_id',
                'attachments',
                'important',
                'system_message',
                'locale',
            ] as $column) {
                if (Schema::hasColumn('messages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('message_threads', function (Blueprint $table): void {
            foreach ([
                ['type', 'last_message_at'],
                ['host_user_id', 'status', 'last_message_at'],
                ['guest_user_id', 'status', 'last_message_at'],
                ['property_id'],
            ] as $columns) {
                if ($this->columnsExist('message_threads', $columns) && Schema::hasIndex('message_threads', $columns)) {
                    $table->dropIndex($columns);
                }
            }
        });

        Schema::table('message_threads', function (Blueprint $table): void {
            foreach (['property_id', 'type'] as $column) {
                if (Schema::hasColumn('message_threads', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
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
