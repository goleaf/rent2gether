<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('template_key')->unique();
            $table->string('notification_category')->index();
            $table->string('title_translation_key');
            $table->string('body_translation_key');
            $table->string('short_body_translation_key')->nullable();
            $table->string('default_priority')->default('normal')->index();
            $table->string('default_action_type')->nullable();
            $table->boolean('supports_in_app')->default(true);
            $table->boolean('supports_email')->default(false);
            $table->boolean('supports_sms_future')->default(false);
            $table->boolean('supports_push_future')->default(false);
            $table->boolean('supports_conversation_event')->default(false);
            $table->boolean('requires_booking')->default(false);
            $table->boolean('requires_action')->default(false);
            $table->boolean('is_critical')->default(false);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('notification_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_number')->unique();
            $table->string('event_key')->index();
            $table->string('event_type')->default('system')->index();
            $table->string('notification_category')->index();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('booking_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('booking_stay_id')->nullable()->index()->constrained('booking_stays')->nullOnDelete();
            $table->foreignId('booking_check_in_id')->nullable()->index()->constrained('booking_check_ins')->nullOnDelete();
            $table->foreignId('booking_check_out_id')->nullable()->index()->constrained('booking_check_outs')->nullOnDelete();
            $table->foreignId('booking_extension_id')->nullable()->index()->constrained('booking_extensions')->nullOnDelete();
            $table->foreignId('booking_relocation_id')->nullable()->index()->constrained('booking_relocations')->nullOnDelete();
            $table->foreignId('booking_cancellation_id')->nullable()->index()->constrained('booking_cancellations')->nullOnDelete();
            $table->foreignId('booking_no_show_id')->nullable()->index()->constrained('booking_no_shows')->nullOnDelete();
            $table->foreignId('host_unresponsive_case_id')->nullable()->index()->constrained('booking_host_unresponsive_cases')->nullOnDelete();
            $table->foreignId('listing_mismatch_report_id')->nullable()->index()->constrained('booking_listing_mismatch_reports')->nullOnDelete();
            $table->foreignId('complaint_case_id')->nullable()->index()->constrained('complaint_cases')->nullOnDelete();
            $table->foreignId('dispute_case_id')->nullable()->index()->constrained('dispute_cases')->nullOnDelete();
            $table->unsignedBigInteger('booking_deposit_id')->nullable()->index();
            $table->unsignedBigInteger('maintenance_request_id')->nullable()->index();
            $table->foreignId('inventory_issue_id')->nullable()->index()->constrained('inventory_issues')->nullOnDelete();
            $table->foreignId('cleaning_task_id')->nullable()->index()->constrained('cleaning_tasks')->nullOnDelete();
            $table->foreignId('inspection_task_id')->nullable()->index()->constrained('inspection_tasks')->nullOnDelete();
            $table->foreignId('saved_search_id')->nullable()->index()->constrained('saved_searches')->nullOnDelete();
            $table->foreignId('favorite_id')->nullable()->index()->constrained('favorites')->nullOnDelete();
            $table->foreignId('waitlist_entry_id')->nullable()->index()->constrained('waitlist_items')->nullOnDelete();
            $table->foreignId('property_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->index()->constrained('sleeping_places')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->text('payload_json')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index(['property_id', 'room_id', 'sleeping_place_id'], 'notification_events_place_context_index');
            $table->index('created_at');
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $this->addString($table, 'notification_number', unique: true, after: 'id');
            $this->addUnsignedColumn($table, 'notification_event_id', after: 'notification_number');
            $this->addUnsignedColumn($table, 'notification_template_id', after: 'notification_event_id');
            $this->addUnsignedColumn($table, 'recipient_user_id', after: 'notification_template_id');
            $this->addString($table, 'recipient_type', default: 'guest', after: 'recipient_user_id');
            $this->addString($table, 'notification_category', default: 'system', after: 'recipient_type');
            $this->addString($table, 'notification_type', default: 'info', after: 'notification_category');
            $this->addString($table, 'priority', default: 'normal', after: 'notification_type');
            $this->addString($table, 'title_translation_key', after: 'priority');
            $this->addString($table, 'body_translation_key', after: 'title_translation_key');
            $this->addString($table, 'short_body_translation_key', after: 'body_translation_key');
            $this->addText($table, 'translation_params_json', after: 'short_body_translation_key');
            $this->addString($table, 'locale', default: 'en', after: 'translation_params_json');
            $this->addString($table, 'source_type', after: 'locale');
            $this->addUnsignedColumn($table, 'source_id', after: 'source_type');
            $this->addUnsignedColumn($table, 'booking_id', after: 'source_id');
            $this->addUnsignedColumn($table, 'property_id', after: 'booking_id');
            $this->addUnsignedColumn($table, 'room_id', after: 'property_id');
            $this->addUnsignedColumn($table, 'sleeping_place_id', after: 'room_id');
            $this->addString($table, 'action_type', after: 'action_url');
            $this->addString($table, 'action_label_translation_key', after: 'action_type');
            $this->addString($table, 'deduplication_key', after: 'action_label_translation_key');
            $this->addString($table, 'throttle_key', after: 'deduplication_key');
            $this->addTimestamp($table, 'scheduled_at', after: 'throttle_key');
            $this->addTimestamp($table, 'ready_at', after: 'scheduled_at');
            $this->addTimestamp($table, 'sent_at', after: 'ready_at');
            $this->addTimestamp($table, 'dismissed_at', after: 'read_at');
            $this->addTimestamp($table, 'action_taken_at', after: 'dismissed_at');
            $this->addTimestamp($table, 'expired_at', after: 'action_taken_at');
            $this->addTimestamp($table, 'expires_at', after: 'expired_at');
            $this->addTimestamp($table, 'cancelled_at', after: 'expires_at');
            $this->addTimestamp($table, 'archived_at', after: 'cancelled_at');
            $this->addBoolean($table, 'is_read', default: false, after: 'archived_at');
            $this->addBoolean($table, 'is_dismissed', default: false, after: 'is_read');
            $this->addBoolean($table, 'is_action_required', default: false, after: 'is_dismissed');
            $this->addBoolean($table, 'is_urgent', default: false, after: 'is_action_required');
            $this->addBoolean($table, 'is_critical', default: false, after: 'is_urgent');
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $this->indexIfMissing($table, 'notifications_notification_event_id_index', ['notification_event_id']);
            $this->indexIfMissing($table, 'notifications_notification_template_id_index', ['notification_template_id']);
            $this->indexIfMissing($table, 'notifications_recipient_status_index', ['recipient_user_id', 'status']);
            $this->indexIfMissing($table, 'notifications_recipient_is_read_index', ['recipient_user_id', 'is_read']);
            $this->indexIfMissing($table, 'notifications_recipient_priority_index', ['recipient_user_id', 'priority']);
            $this->indexIfMissing($table, 'notifications_recipient_created_at_index', ['recipient_user_id', 'created_at']);
            $this->indexIfMissing($table, 'notifications_recipient_type_index', ['recipient_type']);
            $this->indexIfMissing($table, 'notifications_notification_category_index', ['notification_category']);
            $this->indexIfMissing($table, 'notifications_notification_type_index', ['notification_type']);
            $this->indexIfMissing($table, 'notifications_priority_index', ['priority']);
            $this->indexIfMissing($table, 'notifications_source_type_source_id_index', ['source_type', 'source_id']);
            $this->indexIfMissing($table, 'notifications_booking_id_index', ['booking_id']);
            $this->indexIfMissing($table, 'notifications_property_id_index', ['property_id']);
            $this->indexIfMissing($table, 'notifications_room_id_index', ['room_id']);
            $this->indexIfMissing($table, 'notifications_action_type_index', ['action_type']);
            $this->indexIfMissing($table, 'notifications_scheduled_at_index', ['scheduled_at']);
            $this->indexIfMissing($table, 'notifications_expires_at_index', ['expires_at']);
            $this->indexIfMissing($table, 'notifications_deduplication_key_index', ['deduplication_key']);
            $this->indexIfMissing($table, 'notifications_throttle_key_index', ['throttle_key']);
            $this->indexIfMissing($table, 'notifications_is_urgent_index', ['is_urgent']);
            $this->indexIfMissing($table, 'notifications_is_critical_index', ['is_critical']);
        });

        Schema::create('notification_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->string('notification_id')->index();
            $table->foreignId('recipient_user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->string('channel')->index();
            $table->string('status')->default('pending')->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->text('provider_response_json')->nullable();
            $table->text('failure_reason')->nullable();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->timestamp('next_retry_at')->nullable()->index();
            $table->timestamps();

            $table->foreign('notification_id')->references('id')->on('notifications')->cascadeOnDelete();
            $table->index(['notification_id', 'channel']);
            $table->index(['recipient_user_id', 'status']);
        });

        Schema::create('notification_delivery_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('notification_delivery_id')->index()->constrained('notification_deliveries')->cascadeOnDelete();
            $table->string('notification_id')->index();
            $table->string('channel')->index();
            $table->unsignedInteger('attempt_number')->default(1)->index();
            $table->string('status')->index();
            $table->timestamp('attempted_at')->index();
            $table->string('provider')->nullable();
            $table->text('provider_response_json')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->foreign('notification_id')->references('id')->on('notifications')->cascadeOnDelete();
        });

        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_future_enabled')->default(false);
            $table->boolean('push_future_enabled')->default(false);
            $table->boolean('urgent_always_in_app')->default(true);
            $table->boolean('critical_ignore_quiet_hours')->default(true);
            $table->boolean('quiet_hours_enabled')->default(false);
            $table->string('quiet_hours_start')->nullable();
            $table->string('quiet_hours_end')->nullable();
            $table->string('timezone')->nullable();
            $table->string('language_locale')->default('en')->index();
            $table->string('digest_type')->default('none')->index();
            $table->string('digest_time')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_category_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->string('notification_category')->index();
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_future_enabled')->default(false);
            $table->boolean('push_future_enabled')->default(false);
            $table->boolean('digest_only')->default(false);
            $table->boolean('urgent_allowed')->default(true);
            $table->boolean('critical_allowed')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'notification_category'], 'notification_category_preferences_user_category_unique');
        });

        Schema::create('notification_reminders', function (Blueprint $table): void {
            $table->id();
            $table->string('reminder_number')->unique();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->string('recipient_type')->index();
            $table->string('reminder_type')->index();
            $table->string('status')->default('scheduled')->index();
            $table->string('priority')->default('normal')->index();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('booking_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->index()->constrained('sleeping_places')->nullOnDelete();
            $table->foreignId('notification_template_id')->nullable()->index()->constrained('notification_templates')->nullOnDelete();
            $table->timestamp('scheduled_for')->index();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->text('translation_params_json')->nullable();
            $table->string('action_type')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('notification_actions', function (Blueprint $table): void {
            $table->id();
            $table->string('notification_id')->index();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->string('action_type')->index();
            $table->string('status')->default('available')->index();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamp('performed_at')->nullable()->index();
            $table->string('result_message_key')->nullable();
            $table->text('result_context_json')->nullable();
            $table->timestamps();

            $table->foreign('notification_id')->references('id')->on('notifications')->cascadeOnDelete();
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('notification_digests', function (Blueprint $table): void {
            $table->id();
            $table->string('digest_number')->unique();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->string('digest_type')->index();
            $table->string('status')->default('created')->index();
            $table->timestamp('period_start')->index();
            $table->timestamp('period_end')->index();
            $table->unsignedInteger('notification_count')->default(0);
            $table->unsignedInteger('urgent_count')->default(0);
            $table->unsignedInteger('important_count')->default(0);
            $table->string('title_translation_key');
            $table->string('body_translation_key');
            $table->text('translation_params_json')->nullable();
            $table->timestamp('created_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['user_id', 'status']);
        });

        Schema::create('notification_digest_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('notification_digest_id')->index()->constrained('notification_digests')->cascadeOnDelete();
            $table->string('notification_id')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->foreign('notification_id')->references('id')->on('notifications')->cascadeOnDelete();
        });

        Schema::create('notification_device_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->string('platform')->index();
            $table->string('device_name')->nullable();
            $table->string('token_hash');
            $table->text('token_encrypted')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamp('last_used_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('notification_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('notification_id')->nullable()->index();
            $table->foreignId('notification_delivery_id')->nullable()->index()->constrained('notification_deliveries')->cascadeOnDelete();
            $table->foreignId('notification_reminder_id')->nullable()->index()->constrained('notification_reminders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status')->index();
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->text('context_json')->nullable();
            $table->timestamps();

            $table->foreign('notification_id')->references('id')->on('notifications')->cascadeOnDelete();
            $table->index('created_at');
        });

        Schema::create('notification_system_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_key')->index();
            $table->string('event_type')->default('system')->index();
            $table->string('notification_id')->nullable()->index();
            $table->foreignId('notification_event_id')->nullable()->index()->constrained('notification_events')->cascadeOnDelete();
            $table->foreignId('notification_delivery_id')->nullable()->index()->constrained('notification_deliveries')->cascadeOnDelete();
            $table->foreignId('notification_reminder_id')->nullable()->index()->constrained('notification_reminders')->cascadeOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at')->index();
            $table->text('context_json')->nullable();
            $table->timestamps();

            $table->foreign('notification_id')->references('id')->on('notifications')->cascadeOnDelete();
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_system_events');
        Schema::dropIfExists('notification_status_logs');
        Schema::dropIfExists('notification_device_tokens');
        Schema::dropIfExists('notification_digest_items');
        Schema::dropIfExists('notification_digests');
        Schema::dropIfExists('notification_actions');
        Schema::dropIfExists('notification_reminders');
        Schema::dropIfExists('notification_category_preferences');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_delivery_attempts');
        Schema::dropIfExists('notification_deliveries');

        Schema::table('notifications', function (Blueprint $table): void {
            foreach ([
                'notification_number',
                'notification_event_id',
                'notification_template_id',
                'recipient_user_id',
                'recipient_type',
                'notification_category',
                'notification_type',
                'priority',
                'title_translation_key',
                'body_translation_key',
                'short_body_translation_key',
                'translation_params_json',
                'locale',
                'source_type',
                'source_id',
                'booking_id',
                'property_id',
                'room_id',
                'action_type',
                'action_label_translation_key',
                'deduplication_key',
                'throttle_key',
                'scheduled_at',
                'ready_at',
                'sent_at',
                'dismissed_at',
                'action_taken_at',
                'expired_at',
                'expires_at',
                'cancelled_at',
                'archived_at',
                'is_read',
                'is_dismissed',
                'is_action_required',
                'is_urgent',
                'is_critical',
            ] as $column) {
                if (Schema::hasColumn('notifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('notification_events');
        Schema::dropIfExists('notification_templates');
    }

    private function addString(Blueprint $table, string $column, ?string $default = null, bool $unique = false, ?string $after = null): void
    {
        if (Schema::hasColumn('notifications', $column)) {
            return;
        }

        $definition = $table->string($column)->nullable();

        if ($default !== null) {
            $definition->default($default);
        }

        if ($unique) {
            $definition->unique();
        }

        if ($after !== null) {
            $definition->after($after);
        }
    }

    private function addText(Blueprint $table, string $column, ?string $after = null): void
    {
        if (Schema::hasColumn('notifications', $column)) {
            return;
        }

        $definition = $table->text($column)->nullable();

        if ($after !== null) {
            $definition->after($after);
        }
    }

    private function addUnsignedColumn(Blueprint $table, string $column, ?string $after = null): void
    {
        if (Schema::hasColumn('notifications', $column)) {
            return;
        }

        $definition = $table->unsignedBigInteger($column)->nullable();

        if ($after !== null) {
            $definition->after($after);
        }
    }

    private function addTimestamp(Blueprint $table, string $column, ?string $after = null): void
    {
        if (Schema::hasColumn('notifications', $column)) {
            return;
        }

        $definition = $table->timestamp($column)->nullable();

        if ($after !== null) {
            $definition->after($after);
        }
    }

    private function addBoolean(Blueprint $table, string $column, bool $default, ?string $after = null): void
    {
        if (Schema::hasColumn('notifications', $column)) {
            return;
        }

        $definition = $table->boolean($column)->default($default);

        if ($after !== null) {
            $definition->after($after);
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function indexIfMissing(Blueprint $table, string $name, array $columns): void
    {
        if (Schema::hasIndex('notifications', $name)) {
            return;
        }

        $table->index($columns, $name);
    }
};
