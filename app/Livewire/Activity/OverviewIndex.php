<?php

namespace App\Livewire\Activity;

use Livewire\Component;

class OverviewIndex extends Component
{
  public function render()
  {
    return view("livewire.activity.overview-index")->layout(
      "components.layouts.app"
    );
  }
}
