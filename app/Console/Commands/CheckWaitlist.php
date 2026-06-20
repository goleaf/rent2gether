<?php

namespace App\Console\Commands;

use App\Models\WaitlistItem;
use App\Services\Waitlist\WaitlistAvailabilityService;
use App\Services\Waitlist\WaitlistQueueService;
use App\Services\Waitlist\WaitlistService;
use Illuminate\Console\Command;

class CheckWaitlist extends Command
{
    protected $signature = 'waitlist:check {--limit=100 : Maximum waitlist items to check}';

    protected $description = 'Check active waitlist items and create offers for eligible guests.';

    public function handle(
        WaitlistAvailabilityService $availability,
        WaitlistQueueService $queue,
        WaitlistService $waitlist,
    ): int {
        $expired = $waitlist->expireOldItems();
        $created = 0;

        WaitlistItem::query()
            ->select([
                'id',
                'user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'desired_check_in_date',
                'desired_check_out_date',
                'last_checked_at',
            ])
            ->with('sleepingPlace:id,room_id,property_id,status,base_price_per_night,weekend_price,weekly_price,monthly_price,cleaning_fee,deposit_amount,currency,max_guests,min_nights,max_nights')
            ->dueForCheck()
            ->limit(max(1, (int) $this->option('limit')))
            ->get()
            ->each(function (WaitlistItem $item) use ($availability, $queue, &$created): void {
                if (! $item->sleepingPlace || ! $item->desired_check_in_date || ! $item->desired_check_out_date) {
                    return;
                }

                $offer = $availability->handlePlaceBecameAvailable(
                    $item->sleepingPlace,
                    $queue->rangeForItem($item),
                );

                if ($offer !== null) {
                    $created++;
                }
            });

        $this->components->info(__('waitlist.command.finished', [
            'expired' => $expired,
            'offers' => $created,
        ]));

        return self::SUCCESS;
    }
}
