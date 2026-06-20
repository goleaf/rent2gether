<?php

namespace App\Livewire\Host\Rooms;

use App\Livewire\Host\Listings\BaseListingStepComponent;

class RoomTemplatePicker extends BaseListingStepComponent
{
    protected function titleKey(): string
    {
        return 'room_details.templates.title';
    }

    protected function helperKey(): string
    {
        return 'room_details.templates.helper';
    }
}
