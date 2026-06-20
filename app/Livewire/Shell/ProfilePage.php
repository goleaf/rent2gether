<?php

namespace App\Livewire\Shell;

class ProfilePage extends ShellPage
{
    protected string $pageKey = 'guest.profile';

    protected ?string $actionRoute = 'profile.edit';
}
