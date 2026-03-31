<?php

namespace App\Livewire\Workforce;

use App\Models\Roster;
use Livewire\Component;

class RosterCreate extends Component
{
  public string $name = "";
  public string $weekStart = "";
  public string $timezone = "America/Denver";

  public function save(): void
  {
    $this->validate([
      "weekStart" => ["required", "date"],
      "timezone" => ["required", "string", "max:100"],
    ]);

    $roster = Roster::create([
      "name" => $this->name ?: null,
      "week_start" => $this->weekStart,
      "timezone" => $this->timezone,
      "status" => "draft",
      "created_by" => auth()->id(),
    ]);

    $this->redirect(route("workforce.roster.show", $roster), navigate: true);
  }

  public function render()
  {
    return view("livewire.workforce.roster-create")->layout(
      "components.layouts.app"
    );
  }
}
