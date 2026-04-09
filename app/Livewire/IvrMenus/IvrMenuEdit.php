<?php

namespace App\Livewire\IvrMenus;

use Livewire\Component;
use App\Models\IvrMenu;
use App\Models\IvrMenuOption;
use App\Models\InGroup;

class IvrMenuEdit extends Component
{
  public IvrMenu $ivrMenu;

  public string $name = "";
  public string $description = "";
  public string $greeting_type = "tts";
  public string $greeting_value = "";
  public int $gather_timeout = 10;
  public int $invalid_attempts_max = 3;
  public string $invalid_action = "repeat";
  public string $invalid_destination = "";
  public bool $is_active = true;

  public string $activeTab = "settings";
  public bool $confirmingDelete = false;

  // New option form
  public string $newDigit = "";
  public string $newDescription = "";
  public string $newAction = "in_group";
  public string $newDestination = "";

  public function mount(IvrMenu $ivrMenu): void
  {
    $this->ivrMenu = $ivrMenu;
    $this->name = $ivrMenu->name;
    $this->description = $ivrMenu->description ?? "";
    $this->greeting_type = $ivrMenu->greeting_type;
    $this->greeting_value = $ivrMenu->greeting_value ?? "";
    $this->gather_timeout = $ivrMenu->gather_timeout;
    $this->invalid_attempts_max = $ivrMenu->invalid_attempts_max;
    $this->invalid_action = $ivrMenu->invalid_action;
    $this->invalid_destination = $ivrMenu->invalid_destination ?? "";
    $this->is_active = $ivrMenu->is_active;
  }

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

    $this->ivrMenu->update([
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

    session()->flash("success", "IVR Menu updated.");
  }

  public function addOption(): void
  {
    $this->validate([
      "newDigit" => ["required", "string", "max:1", 'regex:/^[0-9\*\#]$/'],
      "newDescription" => ["nullable", "string", "max:100"],
      "newAction" => [
        "required",
        "in:in_group,ivr_menu,transfer,voicemail,hangup",
      ],
      "newDestination" => ["nullable", "string", "max:255"],
    ]);

    // Enforce uniqueness — soft replace if digit already exists
    IvrMenuOption::updateOrCreate(
      ["ivr_menu_id" => $this->ivrMenu->id, "digit" => $this->newDigit],
      [
        "description" => $this->newDescription ?: null,
        "action" => $this->newAction,
        "destination" => $this->newDestination ?: null,
      ]
    );

    $this->newDigit = "";
    $this->newDescription = "";
    $this->newAction = "in_group";
    $this->newDestination = "";
    $this->ivrMenu->refresh();
  }

  public function deleteOption(int $optionId): void
  {
    IvrMenuOption::where("id", $optionId)
      ->where("ivr_menu_id", $this->ivrMenu->id)
      ->delete();
    $this->ivrMenu->refresh();
  }

  public function confirmDelete(): void
  {
    $this->confirmingDelete = true;
  }

  public function delete(): void
  {
    $this->ivrMenu->delete();
    $this->redirect(route("ivr-menus.index"), navigate: true);
  }

  public function render()
  {
    $options = $this->ivrMenu->options()->get();
    $inGroups = InGroup::where("is_active", true)->orderBy("name")->get();
    $ivrMenus = IvrMenu::where("is_active", true)
      ->where("id", "!=", $this->ivrMenu->id)
      ->orderBy("name")
      ->get();

    return view(
      "livewire.ivr-menus.ivr-menu-edit",
      compact("options", "inGroups", "ivrMenus")
    )->layout("components.layouts.app");
  }
}
