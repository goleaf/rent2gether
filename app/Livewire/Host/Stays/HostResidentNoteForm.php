<?php

namespace App\Livewire\Host\Stays;

use App\Livewire\Stays\Concerns\LoadsBookingStay;
use App\Services\Stays\StayNoteService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HostResidentNoteForm extends Component
{
    use LoadsBookingStay;

    public string $note = '';

    public function save(): void
    {
        $stay = $this->stay();

        if ($stay && auth()->user()) {
            app(StayNoteService::class)->addHostNote(auth()->user(), $stay, $this->note);
            $this->note = '';
        }
    }

    public function render(): View
    {
        return view('livewire.host.stays.host-resident-note-form', $this->stayViewData('note_form'));
    }
}
