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
        $this->addIndex('saved_searches', ['user_id', 'status', 'is_active', 'next_check_at'], 'saved_searches_user_status_active_next_idx');

        $this->addIndex('saved_search_results', ['saved_search_id', 'last_matched_at', 'id'], 'ss_results_search_matched_id_idx');
        $this->addIndex('saved_search_results', ['saved_search_id', 'is_new_match', 'last_matched_at', 'id'], 'ss_results_search_new_matched_idx');
        $this->addIndex('saved_search_results', ['saved_search_id', 'price_changed', 'last_matched_at', 'id'], 'ss_results_search_price_matched_idx');
        $this->addIndex('saved_search_results', ['saved_search_id', 'became_available_again', 'last_matched_at', 'id'], 'ss_results_search_available_matched_idx');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndex('saved_search_results', 'ss_results_search_available_matched_idx');
        $this->dropIndex('saved_search_results', 'ss_results_search_price_matched_idx');
        $this->dropIndex('saved_search_results', 'ss_results_search_new_matched_idx');
        $this->dropIndex('saved_search_results', 'ss_results_search_matched_id_idx');

        $this->dropIndex('saved_searches', 'saved_searches_user_status_active_next_idx');
    }

    /**
     * @param  list<string>  $columns
     */
    private function addIndex(string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || Schema::hasIndex($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName): void {
            $table->index($columns, $indexName);
        });
    }

    private function dropIndex(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasIndex($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName): void {
            $table->dropIndex($indexName);
        });
    }
};
