<?php

namespace App\Livewire\Host\Calendar;

use App\Models\HostCleaningTask;
use App\Models\SleepingPlace;
use App\Services\HostCalendar\HostCalendarCleaningService;
use App\Services\HostCalendar\HostCalendarPriceService;
use Illuminate\Auth\Access\AuthorizationException;

class HostCalendarQuickActions extends BaseHostCalendarComponent
{
    public string $section = 'quick_actions';

    public bool $needsConfirmation = false;

    public ?string $preparedAction = null;

    /**
     * @var list<string>
     */
    private array $dangerousActions = [
        'close_date',
        'change_price',
        'mark_place_free',
        'create_repair',
        'hide_place',
        'activate_place',
    ];

    public function prepareAction(string $action): void
    {
        $this->preparedAction = $action;
        $this->needsConfirmation = in_array($action, $this->dangerousActions, true);
    }

    public function changePrice(int $sleepingPlaceId, string $date, int|float|string $price, string $currency = 'EUR'): void
    {
        $host = auth()->user();
        $place = SleepingPlace::query()->findOrFail($sleepingPlaceId);

        app(HostCalendarPriceService::class)->changePrice($host, $place, $date, $price, $currency);

        $this->preparedAction = null;
        $this->needsConfirmation = false;
    }

    public function markCleaningDone(int $taskId): void
    {
        $host = auth()->user();
        $task = HostCleaningTask::query()->findOrFail($taskId);

        if ((int) $task->user_id !== (int) $host->id) {
            throw new AuthorizationException;
        }

        app(HostCalendarCleaningService::class)->markCleaningDone($task);
    }
}
