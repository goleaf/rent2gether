<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('favorite_collections')) {
            Schema::create('favorite_collections', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->string('slug')->nullable();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('color')->nullable();
                $table->string('type')->default('custom');
                $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
                $table->date('check_in_date')->nullable();
                $table->date('check_out_date')->nullable();
                $table->unsignedSmallInteger('nights_count')->nullable();
                $table->unsignedTinyInteger('guests_count')->default(1);
                $table->decimal('budget_min', 10, 2)->nullable();
                $table->decimal('budget_max', 10, 2)->nullable();
                $table->string('currency', 3)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_pinned')->default(false);
                $table->boolean('is_archived')->default(false);
                $table->timestamps();

                $table->index(['user_id', 'sort_order'], 'favorite_collections_user_sort_index');
                $table->index(['user_id', 'is_pinned'], 'favorite_collections_user_pinned_index');
                $table->index(['user_id', 'is_archived'], 'favorite_collections_user_archived_index');
                $table->index(['user_id', 'type'], 'favorite_collections_user_type_index');
                $table->index('city_id', 'favorite_collections_city_index');
                $table->index(['check_in_date', 'check_out_date'], 'favorite_collections_dates_index');
            });
        }

        Schema::table('favorites', function (Blueprint $table): void {
            if (! Schema::hasColumn('favorites', 'favorite_collection_id')) {
                $table->foreignId('favorite_collection_id')->nullable()->after('user_id')->constrained('favorite_collections')->nullOnDelete();
            }

            if (! Schema::hasColumn('favorites', 'property_id')) {
                $table->foreignId('property_id')->nullable()->after('favorite_collection_id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('favorites', 'room_id')) {
                $table->foreignId('room_id')->nullable()->after('property_id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('favorites', 'source')) {
                $table->string('source')->nullable()->after('sleeping_place_id');
            }

            if (! Schema::hasColumn('favorites', 'personal_note')) {
                $table->text('personal_note')->nullable()->after('note');
            }

            if (! Schema::hasColumn('favorites', 'short_label')) {
                $table->string('short_label')->nullable()->after('personal_note');
            }

            if (! Schema::hasColumn('favorites', 'label_color')) {
                $table->string('label_color')->nullable()->after('short_label');
            }

            if (! Schema::hasColumn('favorites', 'decision_status')) {
                $table->string('decision_status')->default('saved')->after('priority');
            }

            if (! Schema::hasColumn('favorites', 'check_in_date')) {
                $table->date('check_in_date')->nullable()->after('decision_status');
            }

            if (! Schema::hasColumn('favorites', 'check_out_date')) {
                $table->date('check_out_date')->nullable()->after('check_in_date');
            }

            if (! Schema::hasColumn('favorites', 'nights_count')) {
                $table->unsignedSmallInteger('nights_count')->nullable()->after('check_out_date');
            }

            if (! Schema::hasColumn('favorites', 'currency')) {
                $table->string('currency', 3)->nullable()->after('guests_count');
            }

            if (! Schema::hasColumn('favorites', 'price_per_night_snapshot')) {
                $table->decimal('price_per_night_snapshot', 10, 2)->nullable()->after('currency');
            }

            if (! Schema::hasColumn('favorites', 'total_price_snapshot')) {
                $table->decimal('total_price_snapshot', 10, 2)->nullable()->after('price_per_night_snapshot');
            }

            if (! Schema::hasColumn('favorites', 'deposit_snapshot')) {
                $table->decimal('deposit_snapshot', 10, 2)->nullable()->after('total_price_snapshot');
            }

            if (! Schema::hasColumn('favorites', 'discount_snapshot')) {
                $table->decimal('discount_snapshot', 10, 2)->nullable()->after('deposit_snapshot');
            }

            if (! Schema::hasColumn('favorites', 'current_price_per_night')) {
                $table->decimal('current_price_per_night', 10, 2)->nullable()->after('discount_snapshot');
            }

            if (! Schema::hasColumn('favorites', 'current_total_price')) {
                $table->decimal('current_total_price', 10, 2)->nullable()->after('current_price_per_night');
            }

            if (! Schema::hasColumn('favorites', 'current_deposit')) {
                $table->decimal('current_deposit', 10, 2)->nullable()->after('current_total_price');
            }

            if (! Schema::hasColumn('favorites', 'price_changed')) {
                $table->boolean('price_changed')->default(false)->after('current_deposit');
            }

            if (! Schema::hasColumn('favorites', 'price_change_amount')) {
                $table->decimal('price_change_amount', 10, 2)->nullable()->after('price_changed');
            }

            if (! Schema::hasColumn('favorites', 'price_change_percent')) {
                $table->decimal('price_change_percent', 8, 2)->nullable()->after('price_change_amount');
            }

            if (! Schema::hasColumn('favorites', 'price_last_checked_at')) {
                $table->timestamp('price_last_checked_at')->nullable()->after('price_change_percent');
            }

            if (! Schema::hasColumn('favorites', 'was_available_when_added')) {
                $table->boolean('was_available_when_added')->nullable()->after('price_last_checked_at');
            }

            if (! Schema::hasColumn('favorites', 'is_currently_available')) {
                $table->boolean('is_currently_available')->nullable()->after('was_available_when_added');
            }

            if (! Schema::hasColumn('favorites', 'became_unavailable')) {
                $table->boolean('became_unavailable')->default(false)->after('is_currently_available');
            }

            if (! Schema::hasColumn('favorites', 'became_available_again')) {
                $table->boolean('became_available_again')->default(false)->after('became_unavailable');
            }

            if (! Schema::hasColumn('favorites', 'partial_availability')) {
                $table->boolean('partial_availability')->default(false)->after('became_available_again');
            }

            if (! Schema::hasColumn('favorites', 'nearest_available_dates_json')) {
                $table->text('nearest_available_dates_json')->nullable()->after('partial_availability');
            }

            if (! Schema::hasColumn('favorites', 'availability_last_checked_at')) {
                $table->timestamp('availability_last_checked_at')->nullable()->after('nearest_available_dates_json');
            }

            if (! Schema::hasColumn('favorites', 'remind_at')) {
                $table->timestamp('remind_at')->nullable()->after('availability_last_checked_at');
            }

            if (! Schema::hasColumn('favorites', 'reminder_text')) {
                $table->text('reminder_text')->nullable()->after('remind_at');
            }

            if (! Schema::hasColumn('favorites', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable()->after('reminder_text');
            }

            if (! Schema::hasColumn('favorites', 'notify_price_increase')) {
                $table->boolean('notify_price_increase')->default(false)->after('notify_price_drop');
            }

            if (! Schema::hasColumn('favorites', 'notify_available_again')) {
                $table->boolean('notify_available_again')->default(true)->after('notify_price_increase');
            }

            if (! Schema::hasColumn('favorites', 'notify_unavailable')) {
                $table->boolean('notify_unavailable')->default(true)->after('notify_available_again');
            }

            if (! Schema::hasColumn('favorites', 'last_viewed_at')) {
                $table->timestamp('last_viewed_at')->nullable()->after('notify_unavailable');
            }

            if (! Schema::hasColumn('favorites', 'added_at')) {
                $table->timestamp('added_at')->nullable()->after('last_viewed_at');
            }
        });

        $this->addFavoriteIndexes();
    }

    public function down(): void
    {
        if (Schema::hasTable('favorites')) {
            Schema::table('favorites', function (Blueprint $table): void {
                foreach ([
                    'favorites_user_created_at_index',
                    'favorites_user_collection_index',
                    'favorites_collection_priority_index',
                    'favorites_collection_decision_status_index',
                    'favorites_user_remind_at_index',
                    'favorites_user_price_changed_index',
                    'favorites_user_currently_available_index',
                    'favorites_property_id_index',
                    'favorites_room_id_index',
                ] as $index) {
                    if (Schema::hasIndex('favorites', $index)) {
                        $table->dropIndex($index);
                    }
                }

                foreach ([
                    'added_at',
                    'last_viewed_at',
                    'notify_unavailable',
                    'notify_available_again',
                    'notify_price_increase',
                    'reminder_sent_at',
                    'reminder_text',
                    'remind_at',
                    'availability_last_checked_at',
                    'nearest_available_dates_json',
                    'partial_availability',
                    'became_available_again',
                    'became_unavailable',
                    'is_currently_available',
                    'was_available_when_added',
                    'price_last_checked_at',
                    'price_change_percent',
                    'price_change_amount',
                    'price_changed',
                    'current_deposit',
                    'current_total_price',
                    'current_price_per_night',
                    'discount_snapshot',
                    'deposit_snapshot',
                    'total_price_snapshot',
                    'price_per_night_snapshot',
                    'currency',
                    'nights_count',
                    'check_out_date',
                    'check_in_date',
                    'decision_status',
                    'label_color',
                    'short_label',
                    'personal_note',
                    'source',
                ] as $column) {
                    if (Schema::hasColumn('favorites', $column)) {
                        $table->dropColumn($column);
                    }
                }

                if (Schema::hasColumn('favorites', 'room_id')) {
                    $table->dropConstrainedForeignId('room_id');
                }

                if (Schema::hasColumn('favorites', 'property_id')) {
                    $table->dropConstrainedForeignId('property_id');
                }

                if (Schema::hasColumn('favorites', 'favorite_collection_id')) {
                    $table->dropConstrainedForeignId('favorite_collection_id');
                }
            });
        }

        Schema::dropIfExists('favorite_collections');
    }

    private function addFavoriteIndexes(): void
    {
        Schema::table('favorites', function (Blueprint $table): void {
            if (! Schema::hasIndex('favorites', 'favorites_user_created_at_index')) {
                $table->index(['user_id', 'created_at'], 'favorites_user_created_at_index');
            }

            if (! Schema::hasIndex('favorites', 'favorites_user_collection_index')) {
                $table->index(['user_id', 'favorite_collection_id'], 'favorites_user_collection_index');
            }

            if (! Schema::hasIndex('favorites', 'favorites_collection_priority_index')) {
                $table->index(['favorite_collection_id', 'priority'], 'favorites_collection_priority_index');
            }

            if (! Schema::hasIndex('favorites', 'favorites_collection_decision_status_index')) {
                $table->index(['favorite_collection_id', 'decision_status'], 'favorites_collection_decision_status_index');
            }

            if (! Schema::hasIndex('favorites', 'favorites_user_remind_at_index')) {
                $table->index(['user_id', 'remind_at'], 'favorites_user_remind_at_index');
            }

            if (! Schema::hasIndex('favorites', 'favorites_user_price_changed_index')) {
                $table->index(['user_id', 'price_changed'], 'favorites_user_price_changed_index');
            }

            if (! Schema::hasIndex('favorites', 'favorites_user_currently_available_index')) {
                $table->index(['user_id', 'is_currently_available'], 'favorites_user_currently_available_index');
            }

            if (! Schema::hasIndex('favorites', 'favorites_property_id_index')) {
                $table->index('property_id', 'favorites_property_id_index');
            }

            if (! Schema::hasIndex('favorites', 'favorites_room_id_index')) {
                $table->index('room_id', 'favorites_room_id_index');
            }
        });
    }
};
