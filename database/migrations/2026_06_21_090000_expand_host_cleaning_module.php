<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('host_cleaning_tasks', function (Blueprint $table): void {
            $table->foreignId('booking_check_out_id')->nullable()->after('booking_id')->constrained('booking_check_outs')->nullOnDelete();
            $table->string('cleaning_type')->default('after_check_out')->after('booking_check_out_id');
            $table->string('priority')->default('normal')->after('status');
            $table->timestamp('due_at')->nullable()->after('scheduled_time');
            $table->timestamp('started_at')->nullable()->after('due_at');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            $table->string('assigned_to_type')->nullable()->after('cancelled_at');
            $table->foreignId('assigned_to_user_id')->nullable()->after('assigned_to_type')->constrained('users')->nullOnDelete();
            $table->string('assigned_person_name')->nullable()->after('assigned_to_user_id');
            $table->string('assigned_person_contact')->nullable()->after('assigned_person_name');
            $table->text('host_note')->nullable()->after('note');
            $table->text('cleaner_comment')->nullable()->after('host_note');
            $table->boolean('before_photos_required')->default(false)->after('cleaner_comment');
            $table->boolean('after_photos_required')->default(true)->after('before_photos_required');
            $table->boolean('has_before_photos')->default(false)->after('after_photos_required');
            $table->boolean('has_after_photos')->default(false)->after('has_before_photos');
            $table->boolean('has_damage_found')->default(false)->after('has_after_photos');
            $table->boolean('has_forgotten_items')->default(false)->after('has_damage_found');
            $table->boolean('has_extra_dirty')->default(false)->after('has_forgotten_items');
            $table->boolean('needs_repair')->default(false)->after('has_extra_dirty');
            $table->boolean('needs_repeat_cleaning')->default(false)->after('needs_repair');
            $table->boolean('place_ready_after_cleaning')->default(false)->after('needs_repeat_cleaning');

            $table->index(['user_id', 'scheduled_date'], 'host_cleaning_tasks_user_scheduled_index');
            $table->index('booking_check_out_id', 'host_cleaning_tasks_checkout_index');
            $table->index('cleaning_type', 'host_cleaning_tasks_type_index');
            $table->index('priority', 'host_cleaning_tasks_priority_index');
            $table->index('due_at', 'host_cleaning_tasks_due_at_index');
            $table->index('completed_at', 'host_cleaning_tasks_completed_index');
            $table->index('has_damage_found', 'host_cleaning_tasks_damage_index');
            $table->index('has_forgotten_items', 'host_cleaning_tasks_forgotten_index');
            $table->index('needs_repair', 'host_cleaning_tasks_repair_index');
            $table->index('assigned_to_user_id', 'host_cleaning_tasks_assigned_user_index');
        });

        Schema::create('host_cleaning_task_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('host_cleaning_task_id')->constrained('host_cleaning_tasks')->cascadeOnDelete();
            $table->string('item_key');
            $table->string('label_key');
            $table->string('status')->default('pending');
            $table->boolean('required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['host_cleaning_task_id', 'status'], 'host_cleaning_items_task_status_index');
            $table->index(['host_cleaning_task_id', 'item_key'], 'host_cleaning_items_task_key_index');
            $table->index('required', 'host_cleaning_items_required_index');
            $table->index('completed_by_user_id', 'host_cleaning_items_completed_by_index');
        });

        Schema::create('host_cleaning_task_photos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('host_cleaning_task_id')->constrained('host_cleaning_tasks')->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('photo_type');
            $table->string('path');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['host_cleaning_task_id', 'photo_type'], 'host_cleaning_photos_task_type_index');
            $table->index('uploaded_by_user_id', 'host_cleaning_photos_uploaded_by_index');
        });

        Schema::create('host_cleaning_findings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('host_cleaning_task_id')->constrained('host_cleaning_tasks')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('finding_type');
            $table->string('severity')->default('medium');
            $table->text('description')->nullable();
            $table->json('photo_paths_json')->nullable();
            $table->boolean('needs_host_action')->default(false);
            $table->boolean('needs_guest_notification')->default(false);
            $table->boolean('needs_repair')->default(false);
            $table->boolean('needs_deposit_review')->default(false);
            $table->string('status')->default('open');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['host_cleaning_task_id', 'status'], 'host_cleaning_findings_task_status_index');
            $table->index('booking_id', 'host_cleaning_findings_booking_index');
            $table->index('finding_type', 'host_cleaning_findings_type_index');
            $table->index('severity', 'host_cleaning_findings_severity_index');
            $table->index('needs_host_action', 'host_cleaning_findings_host_action_index');
            $table->index('needs_repair', 'host_cleaning_findings_repair_index');
            $table->index('needs_deposit_review', 'host_cleaning_findings_deposit_index');
        });

        Schema::create('host_cleaning_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('cleaning_type');
            $table->string('target_type');
            $table->json('items_json')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'cleaning_type'], 'host_cleaning_templates_user_type_index');
            $table->index(['user_id', 'is_default'], 'host_cleaning_templates_user_default_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('host_cleaning_templates');
        Schema::dropIfExists('host_cleaning_findings');
        Schema::dropIfExists('host_cleaning_task_photos');
        Schema::dropIfExists('host_cleaning_task_items');

        Schema::table('host_cleaning_tasks', function (Blueprint $table): void {
            $table->dropIndex('host_cleaning_tasks_user_scheduled_index');
            $table->dropIndex('host_cleaning_tasks_checkout_index');
            $table->dropIndex('host_cleaning_tasks_type_index');
            $table->dropIndex('host_cleaning_tasks_priority_index');
            $table->dropIndex('host_cleaning_tasks_due_at_index');
            $table->dropIndex('host_cleaning_tasks_completed_index');
            $table->dropIndex('host_cleaning_tasks_damage_index');
            $table->dropIndex('host_cleaning_tasks_forgotten_index');
            $table->dropIndex('host_cleaning_tasks_repair_index');
            $table->dropIndex('host_cleaning_tasks_assigned_user_index');
            $table->dropConstrainedForeignId('booking_check_out_id');
            $table->dropConstrainedForeignId('assigned_to_user_id');
            $table->dropColumn([
                'cleaning_type',
                'priority',
                'due_at',
                'started_at',
                'cancelled_at',
                'assigned_to_type',
                'assigned_person_name',
                'assigned_person_contact',
                'host_note',
                'cleaner_comment',
                'before_photos_required',
                'after_photos_required',
                'has_before_photos',
                'has_after_photos',
                'has_damage_found',
                'has_forgotten_items',
                'has_extra_dirty',
                'needs_repair',
                'needs_repeat_cleaning',
                'place_ready_after_cleaning',
            ]);
        });
    }
};
