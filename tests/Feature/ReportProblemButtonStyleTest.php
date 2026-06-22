<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ReportProblemButtonStyleTest extends TestCase
{
    public function test_report_problem_button_uses_a_light_global_style(): void
    {
        $component = File::get(resource_path('views/components/ui/report-problem-button.blade.php'));

        $this->assertStringContainsString('<flux:button', $component);
        $this->assertStringContainsString('variant="{{ $variant }}"', $component);
        $this->assertStringContainsString('bg-amber-50!', $component);
        $this->assertStringContainsString('text-amber-700!', $component);
        $this->assertStringContainsString('ring-amber-200!', $component);
        $this->assertStringContainsString('dark:bg-amber-400/10!', $component);
    }

    public function test_report_problem_actions_use_the_global_component(): void
    {
        $views = [
            resource_path('views/livewire/checkin/check-in.blade.php'),
            resource_path('views/livewire/host/manage-booking.blade.php'),
            resource_path('views/livewire/stays/card.blade.php'),
            resource_path('views/livewire/trips/booking-detail.blade.php'),
            resource_path('views/livewire/trips/current-stay.blade.php'),
        ];

        foreach ($views as $viewPath) {
            $this->assertStringContainsString(
                '<x-ui.report-problem-button',
                File::get($viewPath),
                $viewPath,
            );
        }
    }
}
