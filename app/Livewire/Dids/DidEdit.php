<?php

namespace App\Livewire\Dids;

use Livewire\Component;
use App\Models\Did;
use App\Models\InGroup;
use App\Models\IvrMenu;
use App\Models\Campaign;

class DidEdit extends Component
{
  public Did $did;

  public string $phone_number = "";
  public string $description = "";
  public string $destination = "in_group";
  public ?int $in_group_id = null;
  public ?int $ivr_menu_id = null;
  public ?int $campaign_id = null;
  public bool $is_active = true;

  public bool $confirmingDelete = false;

  public function mount(Did $did): void
  {
    $this->did = $did;
    $this->phone_number = $did->phone_number;
    $this->description = $did->description ?? "";
    $this->in_group_id = $did->in_group_id;
    $this->ivr_menu_id = $did->ivr_menu_id;
    $this->campaign_id = $did->campaign_id;
    $this->is_active = $did->is_active;
    $this->destination = $did->ivr_menu_id ? "ivr_menu" : "in_group";
  }

  public function save(): void
  {
    $this->validate([
      "phone_number" => [
        "required",
        "string",
        "max:30",
        "unique:dids,phone_number," . $this->did->id,
      ],
      "description" => ["nullable", "string", "max:255"],
      "destination" => ["required", "in:in_group,ivr_menu"],
      "in_group_id" => [
        "required_if:destination,in_group",
        "nullable",
        "integer",
        "exists:in_groups,id",
      ],
      "ivr_menu_id" => [
        "required_if:destination,ivr_menu",
        "nullable",
        "integer",
        "exists:ivr_menus,id",
      ],
      "campaign_id" => ["nullable", "integer", "exists:campaigns,id"],
      "is_active" => ["boolean"],
    ]);

    $this->did->update([
      "phone_number" => $this->phone_number,
      "description" => $this->description ?: null,
      "in_group_id" =>
        $this->destination === "in_group" ? $this->in_group_id : null,
      "ivr_menu_id" =>
        $this->destination === "ivr_menu" ? $this->ivr_menu_id : null,
      "campaign_id" => $this->campaign_id,
      "is_active" => $this->is_active,
    ]);

    session()->flash("success", "DID updated.");
  }

  public function confirmDelete(): void
  {
    $this->confirmingDelete = true;
  }

  public function delete(): void
  {
    $this->did->delete();
    $this->redirect(route("dids.index"), navigate: true);
  }

  public function render()
  {
    $inGroups = InGroup::where("is_active", true)->orderBy("name")->get();
    $ivrMenus = IvrMenu::where("is_active", true)->orderBy("name")->get();
    $campaigns = Campaign::where("is_active", true)->orderBy("name")->get();

    return view(
      "livewire.dids.did-edit",
      compact("inGroups", "ivrMenus", "campaigns")
    )->layout("components.layouts.app");
  }
}
