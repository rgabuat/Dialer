<?php

namespace App\Livewire\InGroups;

use Livewire\Component;
use App\Models\InGroup;
use App\Models\Campaign;
use App\Models\User;
use App\Models\AgentStatusType;

class InGroupEdit extends Component
{
  public InGroup $inGroup;

  public string $name = "";
  public string $description = "";
  public bool $is_active = true;
  public int $queue_priority = 1;
  public string $agent_routing = "ring_all";
  public string $max_wait_seconds = "";
  public string $queue_max_wait_seconds = "300";
  public string $drop_action = "hangup";
  public string $drop_destination = "";
  public string $web_form_url = "";
  public string $after_hours_action = "hangup";
  public string $after_hours_destination = "";
  public string $timezone = "UTC";
  public ?int $campaign_id = null;

  public array $hours = [
    "mon" => ["enabled" => true, "open" => "08:00", "close" => "17:00"],
    "tue" => ["enabled" => true, "open" => "08:00", "close" => "17:00"],
    "wed" => ["enabled" => true, "open" => "08:00", "close" => "17:00"],
    "thu" => ["enabled" => true, "open" => "08:00", "close" => "17:00"],
    "fri" => ["enabled" => true, "open" => "08:00", "close" => "17:00"],
    "sat" => ["enabled" => false, "open" => "08:00", "close" => "17:00"],
    "sun" => ["enabled" => false, "open" => "08:00", "close" => "17:00"],
  ];
  public bool $always_open = true;

  // Agent assignment
  public string $activeTab = "settings";
  public ?int $addUserId = null;
  public int $addPriority = 1;
  public bool $confirmingDelete = false;

  public function mount(InGroup $inGroup): void
  {
    $this->inGroup = $inGroup;
    $this->name = $inGroup->name;
    $this->description = $inGroup->description ?? "";
    $this->is_active = $inGroup->is_active;
    $this->queue_priority = $inGroup->queue_priority;
    $this->agent_routing = $inGroup->agent_routing;
    $this->max_wait_seconds = (string) ($inGroup->max_wait_seconds ?? "");
    $this->queue_max_wait_seconds = (string) ($inGroup->queue_max_wait_seconds ?? "300");
    $this->drop_action = $inGroup->drop_action;
    $this->drop_destination = $inGroup->drop_destination ?? "";
    $this->web_form_url = $inGroup->web_form_url ?? "";
    $this->after_hours_action = $inGroup->after_hours_action;
    $this->after_hours_destination = $inGroup->after_hours_destination ?? "";
    $this->timezone = $inGroup->timezone ?? "UTC";
    $this->campaign_id = $inGroup->campaign_id;

    if ($inGroup->hours_json) {
      $this->always_open = false;
      foreach ($inGroup->hours_json as $day => $row) {
        $this->hours[$day] = [
          "enabled" => !is_null($row),
          "open" => $row["open"] ?? "08:00",
          "close" => $row["close"] ?? "17:00",
        ];
      }
    }
  }

  public function save(): void
  {
    $this->validate([
      "name" => ["required", "string", "max:255"],
      "description" => ["nullable", "string"],
      "is_active" => ["boolean"],
      "queue_priority" => ["required", "integer", "min:1", "max:999"],
      "agent_routing" => [
        "required",
        "in:ring_all,round_robin,fewest_calls,longest_idle",
      ],
      "max_wait_seconds" => ["nullable", "integer", "min:1", "max:3600"],
      "queue_max_wait_seconds" => ["nullable", "integer", "min:0", "max:86400"],
      "drop_action" => ["required", "in:hangup,transfer,voicemail"],
      "drop_destination" => ["nullable", "string", "max:255"],
      "web_form_url" => ["nullable", "url", "max:1000"],
      "after_hours_action" => ["required", "in:hangup,transfer,voicemail"],
      "after_hours_destination" => ["nullable", "string", "max:255"],
      "timezone" => ["required", "string", "max:100"],
      "campaign_id" => ["nullable", "integer", "exists:campaigns,id"],
    ]);

    $hoursJson = null;
    if (!$this->always_open) {
      $hoursJson = [];
      foreach ($this->hours as $day => $row) {
        $hoursJson[$day] = $row["enabled"]
          ? ["open" => $row["open"], "close" => $row["close"]]
          : null;
      }
    }

    $this->inGroup->update([
      "name" => $this->name,
      "description" => $this->description ?: null,
      "is_active" => $this->is_active,
      "queue_priority" => $this->queue_priority,
      "agent_routing" => $this->agent_routing,
      "max_wait_seconds" => $this->max_wait_seconds ?: null,
      "queue_max_wait_seconds" => $this->queue_max_wait_seconds !== "" ? (int) $this->queue_max_wait_seconds : 300,
      "drop_action" => $this->drop_action,
      "drop_destination" => $this->drop_destination ?: null,
      "web_form_url" => $this->web_form_url ?: null,
      "after_hours_action" => $this->after_hours_action,
      "after_hours_destination" => $this->after_hours_destination ?: null,
      "hours_json" => $hoursJson,
      "timezone" => $this->timezone,
      "campaign_id" => $this->campaign_id,
    ]);

    session()->flash("success", "In-Group updated.");
  }

  public function addAgent(): void
  {
    $this->validate([
      "addUserId" => ["required", "integer", "exists:users,id"],
    ]);

    $this->inGroup->users()->syncWithoutDetaching([
      $this->addUserId => [
        "priority" => $this->addPriority,
        "is_active" => true,
      ],
    ]);

    $this->addUserId = null;
    $this->addPriority = 1;
    $this->inGroup->refresh();
  }

  public function removeAgent(int $userId): void
  {
    $this->inGroup->users()->detach($userId);
    $this->inGroup->refresh();
  }

  public function toggleAgent(int $userId, bool $active): void
  {
    $this->inGroup
      ->users()
      ->updateExistingPivot($userId, ["is_active" => $active]);
    $this->inGroup->refresh();
  }

  public function confirmDelete(): void
  {
    $this->confirmingDelete = true;
  }

  public function delete(): void
  {
    $this->inGroup->delete();
    $this->redirect(route("in-groups.index"), navigate: true);
  }

  public function render()
  {
    $campaigns = Campaign::where("is_active", true)->orderBy("name")->get();
    $timezones = \DateTimeZone::listIdentifiers();

    $assignedUserIds = $this->inGroup->users()->pluck("users.id");
    $availableUsers = User::whereNotIn("id", $assignedUserIds)
      ->orderBy("first_name")
      ->get();
    $assignedAgents = $this->inGroup
      ->users()
      ->withPivot(["priority", "last_call_at", "is_active"])
      ->get();
    $dids = $this->inGroup->dids()->with(['cidNumber', 'ivrMenu'])->get();

    return view(
      "livewire.in-groups.in-group-edit",
      compact(
        "campaigns",
        "timezones",
        "availableUsers",
        "assignedAgents",
        "dids"
      )
    )->layout("components.layouts.app");
  }
}
