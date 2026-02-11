<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class Layout extends Component
{
    public function render()
    {
        return view('livewire.settings.layout')->layout('components.layouts.app');
    }
}
