<?php

namespace App\Livewire\Host\Cleaning;

use App\Models\HostCleaningTask;
use App\Services\HostCleaning\HostCleaningService;
use App\Services\HostCleaning\HostCleaningTaskService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

abstract class BaseHostCleaningComponent extends Component
{
    public string $section = 'page';

    public ?int $taskId = null;

    public function startTask(): void
    {
        $task = $this->task();

        if (! $task || ! auth()->user()) {
            return;
        }

        app(HostCleaningTaskService::class)->start(auth()->user(), $task);
    }

    public function completeTask(): void
    {
        $task = $this->task();

        if (! $task || ! auth()->user()) {
            return;
        }

        app(HostCleaningTaskService::class)->complete(auth()->user(), $task);
    }

    public function render(): View
    {
        $host = auth()->user();

        return view('livewire.host.cleaning.shell', [
            'section' => $this->section,
            'task' => $this->task(),
            'tasks' => $host ? app(HostCleaningService::class)->getTasks($host) : null,
            'summary' => $host ? app(HostCleaningService::class)->summary($host) : [
                'today' => 0,
                'overdue' => 0,
                'after_check_out' => 0,
                'before_check_in' => 0,
            ],
        ]);
    }

    protected function task(): ?HostCleaningTask
    {
        if (! $this->taskId || ! auth()->id()) {
            return null;
        }

        return HostCleaningTask::query()
            ->select([
                'id',
                'user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'booking_id',
                'booking_check_out_id',
                'cleaning_type',
                'reason',
                'status',
                'priority',
                'scheduled_date',
                'scheduled_time',
                'due_at',
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
            ])
            ->with([
                'property:id,title',
                'room:id,title,room_number',
                'sleepingPlace:id,display_name,place_number',
                'items:id,host_cleaning_task_id,item_key,label_key,status,required,sort_order',
                'photos:id,host_cleaning_task_id,photo_type,path',
                'findings:id,host_cleaning_task_id,finding_type,severity,status,needs_repair,needs_deposit_review',
            ])
            ->where('user_id', auth()->id())
            ->find($this->taskId);
    }
}
