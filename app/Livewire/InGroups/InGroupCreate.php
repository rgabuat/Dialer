<?php

namespace App\Livewire\InGroups;

use Livewire\Component;
use App\Models\InGroup;
use App\Models\Campaign;

class InGroupCreate extends Component
{
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

  // hours_json editor: one row per day
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

    InGroup::create([
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

    session()->flash("success", "In-Group created successfully.");
    $this->redirect(route("in-groups.index"), navigate: true);
  }

  public function render()
  {
    $campaigns = Campaign::where("is_active", true)->orderBy("name")->get();
    $timezones = \DateTimeZone::listIdentifiers();

    return view(
      "livewire.in-groups.in-group-create",
      compact("campaigns", "timezones")
    )->layout("components.layouts.app");
  }
}
