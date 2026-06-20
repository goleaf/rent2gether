<?php

namespace App\Console\Commands;

use App\Models\SavedSearch;
use App\Models\User;
use App\Services\SavedSearches\SavedSearchFrequencyService;
use App\Services\SavedSearches\SavedSearchService;
use Illuminate\Console\Command;

class CheckSavedSearches extends Command
{
    protected $signature = 'saved-searches:check {--limit=100}';

    protected $description = 'Check active saved searches and create in-app notifications for new matches and changes.';

    public function handle(SavedSearchService $savedSearches, SavedSearchFrequencyService $frequency): int
    {
        $limit = max(1, min(1000, (int) $this->option('limit')));
        $checked = 0;

        SavedSearch::query()
            ->active()
            ->with(['user:id'])
            ->orderBy('next_check_at')
            ->limit($limit)
            ->get()
            ->each(function (SavedSearch $search) use ($savedSearches, $frequency, &$checked): void {
                if (! $search->user instanceof User || ! $frequency->shouldCheck($search)) {
                    return;
                }

                $savedSearches->runNow($search->user, $search);
                $checked++;
            });

        $this->components->info(__('saved_searches.command.checked', ['count' => $checked]));

        return self::SUCCESS;
    }
}
