<?php

namespace App\Livewire\Extensions;

use App\Models\BookingExtension;
use App\Services\ExtensionService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ManageExtension extends Component
{
    #[Locked]
    public BookingExtension $extension;

    public function mount(BookingExtension $extension): void
    {
        $this->extension = $extension->load('booking.guest', 'booking.bed');
    }

    public function approve(): void
    {
        app(ExtensionService::class)->approve($this->extension);
        $this->extension->refresh();
        session()->flash('success', 'Extension approved.');
    }

    public function reject(): void
    {
        app(ExtensionService::class)->reject($this->extension);
        $this->extension->refresh();
        session()->flash('success', 'Extension rejected.');
    }

    public function render(): View
    {
        return view('livewire.extensions.manage-extension');
    }
}
