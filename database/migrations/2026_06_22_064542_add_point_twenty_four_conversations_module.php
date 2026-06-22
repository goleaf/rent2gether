<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table): void {
            if (! Schema::hasColumn('conversations', 'conversation_number')) {
                $table->string('conversation_number')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('conversations', 'conversation_type')) {
                $table->string('conversation_type')->default('booking')->index()->after('conversation_number');
            }

            if (! Schema::hasColumn('conversations', 'status')) {
                $table->string('status')->default('active')->index()->after('conversation_type');
            }

            $this->addUnsignedColumn($table, 'guest_user_id', after: 'status');
            $this->addUnsignedColumn($table, 'host_user_id', after: 'guest_user_id');
            $this->addUnsignedColumn($table, 'host_representative_id', after: 'host_user_id');
            $this->addUnsignedColumn($table, 'property_id', after: 'host_representative_id');
            $this->addUnsignedColumn($table, 'room_id', after: 'property_id');
            $this->addUnsignedColumn($table, 'sleeping_place_id', after: 'room_id');
            $this->addUnsignedColumn($table, 'listing_id', after: 'sleeping_place_id');
            $this->addUnsignedColumn($table, 'booking_stay_id', after: 'booking_id');
            $this->addUnsignedColumn($table, 'booking_check_in_id', after: 'booking_stay_id');
            $this->addUnsignedColumn($table, 'booking_check_out_id', after: 'booking_check_in_id');
            $this->addUnsignedColumn($table, 'booking_extension_id', after: 'booking_check_out_id');
            $this->addUnsignedColumn($table, 'booking_relocation_id', after: 'booking_extension_id');
            $this->addUnsignedColumn($table, 'booking_cancellation_id', after: 'booking_relocation_id');
            $this->addUnsignedColumn($table, 'booking_no_show_id', after: 'booking_cancellation_id');
            $this->addUnsignedColumn($table, 'host_unresponsive_case_id', after: 'booking_no_show_id');
            $this->addUnsignedColumn($table, 'listing_mismatch_report_id', after: 'host_unresponsive_case_id');
            $this->addUnsignedColumn($table, 'complaint_case_id', after: 'listing_mismatch_report_id');
            $this->addUnsignedColumn($table, 'dispute_case_id', after: 'complaint_case_id');
            $this->addUnsignedColumn($table, 'booking_deposit_id', after: 'dispute_case_id');
            $this->addUnsignedColumn($table, 'maintenance_request_id', after: 'booking_deposit_id');
            $this->addUnsignedColumn($table, 'inventory_issue_id', after: 'maintenance_request_id');
            $this->addUnsignedColumn($table, 'cleaning_task_id', after: 'inventory_issue_id');
            $this->addUnsignedColumn($table, 'inspection_task_id', after: 'cleaning_task_id');
            $this->addUnsignedColumn($table, 'last_message_id', after: 'inspection_task_id');
            $this->addUnsignedColumn($table, 'last_message_sender_user_id', after: 'last_message_at');

            if (! Schema::hasColumn('conversations', 'guest_unread_count')) {
                $table->unsignedInteger('guest_unread_count')->default(0)->after('last_message_sender_user_id');
            }

            if (! Schema::hasColumn('conversations', 'host_unread_count')) {
                $table->unsignedInteger('host_unread_count')->default(0)->after('guest_unread_count');
            }

            if (! Schema::hasColumn('conversations', 'has_urgent_messages')) {
                $table->boolean('has_urgent_messages')->default(false)->index()->after('host_unread_count');
            }

            if (! Schema::hasColumn('conversations', 'has_important_messages')) {
                $table->boolean('has_important_messages')->default(false)->index()->after('has_urgent_messages');
            }

            if (! Schema::hasColumn('conversations', 'guest_can_write')) {
                $table->boolean('guest_can_write')->default(true)->after('has_important_messages');
            }

            if (! Schema::hasColumn('conversations', 'host_can_write')) {
                $table->boolean('host_can_write')->default(true)->after('guest_can_write');
            }

            if (! Schema::hasColumn('conversations', 'is_read_only')) {
                $table->boolean('is_read_only')->default(false)->after('host_can_write');
            }

            if (! Schema::hasColumn('conversations', 'is_system_only')) {
                $table->boolean('is_system_only')->default(false)->after('is_read_only');
            }

            if (! Schema::hasColumn('conversations', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('is_system_only');
            }

            if (! Schema::hasColumn('conversations', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('closed_at');
            }
        });

        Schema::table('conversations', function (Blueprint $table): void {
            foreach ([
                ['guest_user_id', 'status'],
                ['host_user_id', 'status'],
                ['host_representative_id', 'status'],
                ['property_id', 'status'],
                ['room_id', 'status'],
                ['sleeping_place_id', 'status'],
                ['booking_stay_id'],
                ['booking_check_in_id'],
                ['booking_check_out_id'],
                ['complaint_case_id'],
                ['dispute_case_id'],
                ['booking_deposit_id'],
                ['maintenance_request_id'],
                ['inventory_issue_id'],
            ] as $columns) {
                if ($this->columnsExist('conversations', $columns) && ! Schema::hasIndex('conversations', $columns)) {
                    $table->index($columns);
                }
            }
        });

        Schema::create('conversation_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->index()->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('participant_type')->index();
            $table->string('display_name_snapshot')->nullable();
            $table->boolean('can_write')->default(true)->index();
            $table->boolean('can_read')->default(true);
            $table->boolean('can_upload')->default(true);
            $table->boolean('can_use_templates')->default(true);
            $table->boolean('muted')->default(false);
            $table->boolean('archived')->default(false);
            $table->unsignedBigInteger('last_read_message_id')->nullable();
            $table->timestamp('last_read_at')->nullable()->index();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();
        });

        Schema::create('conversation_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('message_number')->unique();
            $table->foreignId('conversation_id')->index()->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('sender_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('sender_type')->index();
            $table->foreignId('recipient_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('recipient_type')->nullable();
            $table->string('message_type')->index();
            $table->string('status')->default('sent')->index();
            $table->text('body')->nullable();
            $table->string('template_key')->nullable()->index();
            $table->string('translation_key')->nullable();
            $table->json('translation_params_json')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('booking_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->boolean('is_system')->default(false)->index();
            $table->boolean('is_important')->default(false)->index();
            $table->boolean('is_urgent')->default(false)->index();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_internal_note')->default(false)->index();
            $table->string('original_locale')->nullable();
            $table->string('translated_locale')->nullable();
            $table->text('translated_body')->nullable();
            $table->string('translation_status')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });

        Schema::create('conversation_message_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_message_id')->index()->constrained('conversation_messages')->cascadeOnDelete();
            $table->foreignId('conversation_id')->index()->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('attachment_type')->index();
            $table->string('media_type')->nullable();
            $table->string('path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->text('caption')->nullable();
            $table->string('linked_type')->nullable();
            $table->unsignedBigInteger('linked_id')->nullable();
            $table->string('visibility')->default('guest_and_host')->index();
            $table->timestamps();

            $table->index(['linked_type', 'linked_id']);
        });

        Schema::create('conversation_message_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->index()->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('conversation_message_id')->index()->constrained('conversation_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->index();
            $table->timestamps();

            $table->unique(['conversation_message_id', 'user_id']);
        });

        Schema::create('message_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('template_key')->unique();
            $table->string('template_category')->index();
            $table->string('sender_type')->index();
            $table->string('conversation_type')->nullable()->index();
            $table->string('title_translation_key');
            $table->string('body_translation_key');
            $table->boolean('visible_to_guest')->default(false)->index();
            $table->boolean('visible_to_host')->default(false)->index();
            $table->boolean('requires_booking')->default(false);
            $table->boolean('requires_check_in')->default(false);
            $table->boolean('requires_check_out')->default(false);
            $table->boolean('requires_active_stay')->default(false);
            $table->boolean('creates_action')->default(false);
            $table->string('action_type')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('message_template_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('message_template_id')->index()->constrained('message_templates')->cascadeOnDelete();
            $table->string('template_key')->nullable()->index();
            $table->foreignId('conversation_id')->index()->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('conversation_message_id')->index()->constrained('conversation_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamp('used_at')->index();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });

        Schema::create('conversation_system_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->index()->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('conversation_message_id')->nullable()->index()->constrained('conversation_messages')->nullOnDelete();
            $table->string('event_key')->index();
            $table->string('event_type')->default('system')->index();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('booking_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('translation_key');
            $table->json('translation_params_json')->nullable();
            $table->string('importance_level')->default('normal')->index();
            $table->foreignId('created_by_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });

        Schema::create('conversation_internal_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->nullable()->index()->constrained('conversations')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('booking_stay_id')->nullable()->index()->constrained('booking_stays')->nullOnDelete();
            $table->foreignId('guest_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->foreignId('host_user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->text('note');
            $table->string('note_type')->default('other')->index();
            $table->foreignId('created_by_user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->boolean('visible_to_host')->default(true);
            $table->boolean('visible_to_guest')->default(false);
            $table->boolean('internal')->default(true);
            $table->timestamps();
        });

        Schema::create('conversation_safety_warnings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->index()->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('conversation_message_id')->nullable()->index()->constrained('conversation_messages')->nullOnDelete();
            $table->string('warning_key')->index();
            $table->string('severity')->default('warning')->index();
            $table->foreignId('triggered_by_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->boolean('visible_to_sender')->default(true);
            $table->boolean('visible_to_recipient')->default(false);
            $table->string('message_key')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();
        });

        Schema::create('conversation_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->index()->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status')->index();
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();
        });

        Schema::create('conversation_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->index()->constrained('conversations')->cascadeOnDelete();
            $table->string('event_key')->index();
            $table->string('event_type')->default('system')->index();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at')->index();
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_events');
        Schema::dropIfExists('conversation_status_logs');
        Schema::dropIfExists('conversation_safety_warnings');
        Schema::dropIfExists('conversation_internal_notes');
        Schema::dropIfExists('conversation_system_events');
        Schema::dropIfExists('message_template_usages');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('conversation_message_reads');
        Schema::dropIfExists('conversation_message_attachments');
        Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('conversation_participants');

        Schema::table('conversations', function (Blueprint $table): void {
            foreach ([
                'conversation_number',
                'conversation_type',
                'status',
                'guest_user_id',
                'host_user_id',
                'host_representative_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'listing_id',
                'booking_stay_id',
                'booking_check_in_id',
                'booking_check_out_id',
                'booking_extension_id',
                'booking_relocation_id',
                'booking_cancellation_id',
                'booking_no_show_id',
                'host_unresponsive_case_id',
                'listing_mismatch_report_id',
                'complaint_case_id',
                'dispute_case_id',
                'booking_deposit_id',
                'maintenance_request_id',
                'inventory_issue_id',
                'cleaning_task_id',
                'inspection_task_id',
                'last_message_id',
                'last_message_sender_user_id',
                'guest_unread_count',
                'host_unread_count',
                'has_urgent_messages',
                'has_important_messages',
                'guest_can_write',
                'host_can_write',
                'is_read_only',
                'is_system_only',
                'closed_at',
                'archived_at',
            ] as $column) {
                if (Schema::hasColumn('conversations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function addUnsignedColumn(Blueprint $table, string $column, ?string $after = null): void
    {
        if (Schema::hasColumn('conversations', $column)) {
            return;
        }

        $definition = $table->unsignedBigInteger($column)->nullable();

        if ($after !== null) {
            $definition->after($after);
        }
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
