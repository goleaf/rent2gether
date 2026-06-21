<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendCheckOuts();
        $this->createSteps();
        $this->createMedia();
        $this->createInventoryChecks();
        $this->createIssues();
        $this->extendForgottenItems();
        $this->createStatusLogs();
        $this->createEvents();
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_check_out_events');
        Schema::dropIfExists('booking_check_out_status_logs');
        Schema::dropIfExists('booking_check_out_issues');
        Schema::dropIfExists('booking_check_out_inventory_checks');
        Schema::dropIfExists('booking_check_out_media');
        Schema::dropIfExists('booking_check_out_steps');

        if (Schema::hasTable('booking_forgotten_items')) {
            Schema::table('booking_forgotten_items', function (Blueprint $table): void {
                foreach ([
                    'property_id',
                    'room_id',
                    'sleeping_place_id',
                    'photo_path',
                    'return_method',
                    'returned_at',
                ] as $column) {
                    if (Schema::hasColumn('booking_forgotten_items', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('booking_check_outs')) {
            Schema::table('booking_check_outs', function (Blueprint $table): void {
                foreach ($this->checkOutColumns() as $column => $definition) {
                    if (Schema::hasColumn('booking_check_outs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function extendCheckOuts(): void
    {
        Schema::table('booking_check_outs', function (Blueprint $table): void {
            if (! Schema::hasColumn('booking_check_outs', 'checkout_number')) {
                $table->string('checkout_number')->nullable()->unique()->after('id');
            }

            foreach ($this->checkOutColumns() as $column => $definition) {
                if (! Schema::hasColumn('booking_check_outs', $column)) {
                    $definition($table);
                }
            }

            $this->indexIfMissing($table, 'booking_check_outs', ['booking_stay_id']);
            $this->indexIfMissing($table, 'booking_check_outs', ['guest_user_id', 'status']);
            $this->indexIfMissing($table, 'booking_check_outs', ['host_user_id', 'status']);
            $this->indexIfMissing($table, 'booking_check_outs', ['property_id', 'status']);
            $this->indexIfMissing($table, 'booking_check_outs', ['room_id', 'status']);
            $this->indexIfMissing($table, 'booking_check_outs', ['sleeping_place_id', 'status']);
            $this->indexIfMissing($table, 'booking_check_outs', ['actual_check_out_at']);
            $this->indexIfMissing($table, 'booking_check_outs', ['has_damage']);
            $this->indexIfMissing($table, 'booking_check_outs', ['has_forgotten_items']);
            $this->indexIfMissing($table, 'booking_check_outs', ['deposit_deduction_requested']);
            $this->indexIfMissing($table, 'booking_check_outs', ['cleaning_required']);
            $this->indexIfMissing($table, 'booking_check_outs', ['inspection_required']);
            $this->indexIfMissing($table, 'booking_check_outs', ['repair_required']);
        });
    }

    private function createSteps(): void
    {
        if (Schema::hasTable('booking_check_out_steps')) {
            return;
        }

        Schema::create('booking_check_out_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_out_id')->constrained('booking_check_outs')->cascadeOnDelete();
            $table->string('step_key');
            $table->string('status')->default('pending');
            $table->boolean('required')->default(true);
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['booking_check_out_id', 'step_key']);
            $table->index(['booking_check_out_id', 'status']);
            $table->index('completed_by_user_id');
            $table->index('required');
            $table->index('sort_order');
        });
    }

    private function createMedia(): void
    {
        if (Schema::hasTable('booking_check_out_media')) {
            return;
        }

        Schema::create('booking_check_out_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_out_id')->constrained('booking_check_outs')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('media_type');
            $table->string('media_role');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->text('caption')->nullable();
            $table->string('visibility')->default('guest_and_host');
            $table->timestamps();

            $table->index('booking_check_out_id');
            $table->index('booking_id');
            $table->index('uploaded_by_user_id');
            $table->index('media_role');
            $table->index('visibility');
        });
    }

    private function createInventoryChecks(): void
    {
        if (Schema::hasTable('booking_check_out_inventory_checks')) {
            return;
        }

        Schema::create('booking_check_out_inventory_checks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_out_id')->constrained('booking_check_outs')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('inventory_item_id')->nullable();
            $table->string('item_name_snapshot');
            $table->boolean('expected_return')->default(true);
            $table->boolean('returned')->default(false);
            $table->boolean('lost')->default(false);
            $table->boolean('damaged')->default(false);
            $table->boolean('needs_replacement')->default(false);
            $table->boolean('deduction_requested')->default(false);
            $table->decimal('deduction_amount', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('booking_check_out_id');
            $table->index('booking_id');
            $table->index('inventory_item_id');
            $table->index('returned');
            $table->index('lost');
            $table->index('damaged');
            $table->index('needs_replacement');
            $table->index('deduction_requested');
        });
    }

    private function createIssues(): void
    {
        if (Schema::hasTable('booking_check_out_issues')) {
            return;
        }

        Schema::create('booking_check_out_issues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_out_id')->constrained('booking_check_outs')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('issue_type');
            $table->string('severity')->default('medium');
            $table->string('status')->default('reported');
            $table->text('description')->nullable();
            $table->decimal('amount_requested', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->text('guest_response')->nullable();
            $table->text('host_response')->nullable();
            $table->unsignedBigInteger('source_created_deposit_deduction_id')->nullable();
            $table->unsignedBigInteger('source_created_maintenance_request_id')->nullable();
            $table->unsignedBigInteger('source_created_complaint_case_id')->nullable();
            $table->unsignedBigInteger('source_created_inventory_issue_id')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('booking_check_out_id');
            $table->index('booking_id');
            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index('property_id');
            $table->index('room_id');
            $table->index('sleeping_place_id');
            $table->index('issue_type');
            $table->index('severity');
            $table->index('status');
        });
    }

    private function extendForgottenItems(): void
    {
        Schema::table('booking_forgotten_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('booking_forgotten_items', 'property_id')) {
                $table->unsignedBigInteger('property_id')->nullable()->after('host_user_id');
            }

            if (! Schema::hasColumn('booking_forgotten_items', 'room_id')) {
                $table->unsignedBigInteger('room_id')->nullable()->after('property_id');
            }

            if (! Schema::hasColumn('booking_forgotten_items', 'sleeping_place_id')) {
                $table->unsignedBigInteger('sleeping_place_id')->nullable()->after('room_id');
            }

            if (! Schema::hasColumn('booking_forgotten_items', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('description');
            }

            if (! Schema::hasColumn('booking_forgotten_items', 'return_method')) {
                $table->string('return_method')->nullable()->after('storage_location');
            }

            if (! Schema::hasColumn('booking_forgotten_items', 'returned_at')) {
                $table->timestamp('returned_at')->nullable()->after('guest_notified_at');
            }

            $this->indexIfMissing($table, 'booking_forgotten_items', ['booking_check_out_id']);
            $this->indexIfMissing($table, 'booking_forgotten_items', ['booking_id']);
            $this->indexIfMissing($table, 'booking_forgotten_items', ['guest_user_id']);
            $this->indexIfMissing($table, 'booking_forgotten_items', ['host_user_id']);
            $this->indexIfMissing($table, 'booking_forgotten_items', ['status']);
        });
    }

    private function createStatusLogs(): void
    {
        if (Schema::hasTable('booking_check_out_status_logs')) {
            return;
        }

        Schema::create('booking_check_out_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_out_id')->constrained('booking_check_outs')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_check_out_id');
            $table->index('booking_id');
            $table->index('new_status');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    private function createEvents(): void
    {
        if (Schema::hasTable('booking_check_out_events')) {
            return;
        }

        Schema::create('booking_check_out_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_out_id')->constrained('booking_check_outs')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('event_key');
            $table->string('event_type')->default('system');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_check_out_id');
            $table->index('booking_id');
            $table->index('event_key');
            $table->index('event_type');
            $table->index(['source_type', 'source_id']);
            $table->index('user_id');
            $table->index('occurred_at');
        });
    }

    /**
     * @return array<string, callable(Blueprint): void>
     */
    private function checkOutColumns(): array
    {
        return [
            'booking_stay_id' => fn (Blueprint $table): mixed => $table->unsignedBigInteger('booking_stay_id')->nullable()->after('booking_id'),
            'check_out_window' => fn (Blueprint $table): mixed => $table->string('check_out_window')->nullable()->after('planned_check_out_time'),
            'guest_preparing_at' => fn (Blueprint $table): mixed => $table->timestamp('guest_preparing_at')->nullable()->after('status'),
            'guest_confirmed_checkout_at' => fn (Blueprint $table): mixed => $table->timestamp('guest_confirmed_checkout_at')->nullable()->after('guest_preparing_at'),
            'host_notified_guest_checkout_at' => fn (Blueprint $table): mixed => $table->timestamp('host_notified_guest_checkout_at')->nullable()->after('guest_confirmed_checkout_at'),
            'host_confirmed_checkout_at' => fn (Blueprint $table): mixed => $table->timestamp('host_confirmed_checkout_at')->nullable()->after('host_notified_guest_checkout_at'),
            'locker_cleared' => fn (Blueprint $table): mixed => $table->boolean('locker_cleared')->default(false)->after('locker_emptied'),
            'personal_items_removed' => fn (Blueprint $table): mixed => $table->boolean('personal_items_removed')->default(false)->after('personal_items_taken'),
            'sleeping_place_cleared' => fn (Blueprint $table): mixed => $table->boolean('sleeping_place_cleared')->default(false)->after('sleeping_place_free'),
            'property_checked' => fn (Blueprint $table): mixed => $table->boolean('property_checked')->default(false)->after('room_checked'),
            'has_extra_dirt' => fn (Blueprint $table): mixed => $table->boolean('has_extra_dirt')->default(false)->after('has_damage'),
            'has_lost_items' => fn (Blueprint $table): mixed => $table->boolean('has_lost_items')->default(false)->after('has_forgotten_items'),
            'has_lost_key' => fn (Blueprint $table): mixed => $table->boolean('has_lost_key')->default(false)->after('has_lost_items'),
            'has_inventory_issue' => fn (Blueprint $table): mixed => $table->boolean('has_inventory_issue')->default(false)->after('has_lost_key'),
            'has_complaint' => fn (Blueprint $table): mixed => $table->boolean('has_complaint')->default(false)->after('has_inventory_issue'),
            'has_dispute' => fn (Blueprint $table): mixed => $table->boolean('has_dispute')->default(false)->after('has_complaint'),
            'deposit_review_required' => fn (Blueprint $table): mixed => $table->boolean('deposit_review_required')->default(false)->after('has_dispute'),
            'deposit_deduction_requested' => fn (Blueprint $table): mixed => $table->boolean('deposit_deduction_requested')->default(false)->after('deposit_review_required'),
            'guest_comment' => fn (Blueprint $table): mixed => $table->text('guest_comment')->nullable()->after('deposit_deduction_reason'),
            'host_comment' => fn (Blueprint $table): mixed => $table->text('host_comment')->nullable()->after('guest_comment'),
            'internal_host_note' => fn (Blueprint $table): mixed => $table->text('internal_host_note')->nullable()->after('host_comment'),
            'cleaning_required' => fn (Blueprint $table): mixed => $table->boolean('cleaning_required')->default(true)->after('internal_host_note'),
            'inspection_required' => fn (Blueprint $table): mixed => $table->boolean('inspection_required')->default(false)->after('cleaning_required'),
            'repair_required' => fn (Blueprint $table): mixed => $table->boolean('repair_required')->default(false)->after('inspection_required'),
            'cleaning_task_id' => fn (Blueprint $table): mixed => $table->unsignedBigInteger('cleaning_task_id')->nullable()->after('repair_required'),
            'maintenance_request_id' => fn (Blueprint $table): mixed => $table->unsignedBigInteger('maintenance_request_id')->nullable()->after('cleaning_task_id'),
            'deposit_case_id' => fn (Blueprint $table): mixed => $table->unsignedBigInteger('deposit_case_id')->nullable()->after('maintenance_request_id'),
            'complaint_case_id' => fn (Blueprint $table): mixed => $table->unsignedBigInteger('complaint_case_id')->nullable()->after('deposit_case_id'),
            'completed_at' => fn (Blueprint $table): mixed => $table->timestamp('completed_at')->nullable()->after('complaint_case_id'),
            'closed_at' => fn (Blueprint $table): mixed => $table->timestamp('closed_at')->nullable()->after('completed_at'),
        ];
    }

    /**
     * @param  list<string>  $columns
     */
    private function indexIfMissing(Blueprint $table, string $tableName, array $columns): void
    {
        if (! Schema::hasIndex($tableName, $columns)) {
            $table->index($columns);
        }
    }
};
