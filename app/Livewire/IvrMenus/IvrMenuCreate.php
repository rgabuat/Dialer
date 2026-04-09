<?php

namespace App\Livewire\IvrMenus;

use Livewire\Component;
use App\Models\IvrMenu;

class IvrMenuCreate extends Component
{
  public string $name = "";
  public string $description = "";
  public string $greeting_type = "tts";
  public string $greeting_value = "";
  public int $gather_timeout = 10;
  public int $invalid_attempts_max = 3;
  public string $invalid_action = "repeat";
  public string $invalid_destination = "";
  public bool $is_active = true;

  public function save(): void
  {
    $this->validate([
      "name" => ["required", "string", "max:255"],
      "description" => ["nullable", "string"],
      "greeting_type" => ["required", "in:tts,audio"],
      "greeting_value" => ["nullable", "string", "max:2000"],
      "gather_timeout" => ["required", "integer", "min:1", "max:30"],
      "invalid_attempts_max" => ["required", "integer", "min:1", "max:10"],
      "invalid_action" => ["required", "in:hangup,repeat,transfer"],
      "invalid_destination" => ["nullable", "string", "max:255"],
      "is_active" => ["boolean"],
    ]);

    IvrMenu::create([
      "name" => $this->name,
      "description" => $this->description ?: null,
      "greeting_type" => $this->greeting_type,
      "greeting_value" => $this->greeting_value ?: null,
      "gather_timeout" => $this->gather_timeout,
      "invalid_attempts_max" => $this->invalid_attempts_max,
      "invalid_action" => $this->invalid_action,
      "invalid_destination" => $this->invalid_destination ?: null,
      "is_active" => $this->is_active,
    ]);

    session()->flash("success", "IVR Menu created successfully.");
    $this->redirect(route("ivr-menus.index"), navigate: true);
  }

  public function render()
  {
    return view("livewire.ivr-menus.ivr-menu-create")->layout(
      "components.layouts.app"
    );
  }
}
