<?php

namespace Database\Seeders;

use App\Models\Did;
use App\Models\Campaign;
use App\Models\InGroup;
use App\Models\IvrMenu;
use App\Models\IvrMenuOption;
use Illuminate\Database\Seeder;

class InboundSeeder extends Seeder
{
  /**
   * Seed default inbound routing records.
   * Uses firstOrCreate so it is safe to run multiple times.
   */
  public function run(): void
  {
    // ── Default Campaign ───────────────────────────────────────────────
    $campaign = Campaign::where("name", "Admin Campaign")->first();

    // ── 1. Default In-Group ────────────────────────────────────────────
    $inGroup = InGroup::firstOrCreate(
      ["name" => "General Support"],
      [
        "description" =>
          "Default inbound queue. Routes calls to all available agents.",
        "is_active" => true,
        "queue_priority" => 1,
        "agent_routing" => "ring_all",
        "max_wait_seconds" => 30,
        "drop_action" => "voicemail",
        "drop_destination" => null,
        "web_form_url" => null,
        "hold_music_url" => null,
        "after_hours_action" => "voicemail",
        "after_hours_destination" => null,
        "hours_json" => null, // null = always open
        "timezone" => "UTC",
        "campaign_id" => $campaign?->id,
      ]
    );

    // ── 2. Default IVR Menu ────────────────────────────────────────────
    $ivrMenu = IvrMenu::firstOrCreate(
      ["name" => "Main Menu"],
      [
        "description" => "Default IVR menu presented to inbound callers.",
        "greeting_type" => "tts",
        "greeting_value" =>
          "Thank you for calling. Press 1 for General Support. Press 0 to repeat this menu.",
        "gather_timeout" => 10,
        "invalid_attempts_max" => 3,
        "invalid_action" => "repeat",
        "invalid_destination" => null,
        "is_active" => true,
      ]
    );

    // ── 2a. IVR Menu options ───────────────────────────────────────────
    IvrMenuOption::firstOrCreate(
      ["ivr_menu_id" => $ivrMenu->id, "digit" => "1"],
      [
        "description" => "General Support",
        "action" => "in_group",
        "destination" => (string) $inGroup->id,
      ]
    );

    IvrMenuOption::firstOrCreate(
      ["ivr_menu_id" => $ivrMenu->id, "digit" => "0"],
      [
        "description" => "Repeat menu",
        "action" => "ivr_menu",
        "destination" => (string) $ivrMenu->id,
      ]
    );

    // ── 3. Default DID ─────────────────────────────────────────────────
    $phoneNumber = env("TWILIO_PHONE_NUMBER", "+10000000000");

    Did::firstOrCreate(
      ["phone_number" => $phoneNumber],
      [
        "description" =>
          "Default inbound number — routes through Main IVR Menu.",
        "ivr_menu_id" => $ivrMenu->id,
        "in_group_id" => null,
        "campaign_id" => $campaign?->id,
        "is_active" => true,
      ]
    );
  }
}
