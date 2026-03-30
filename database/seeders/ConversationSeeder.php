<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Conversation;
use App\Models\Campaign;
use App\Models\User;
use Carbon\Carbon;

class ConversationSeeder extends Seeder
{
  public function run(): void
  {
    $campaigns = Campaign::pluck("id")->toArray();
    $users = User::pluck("id")->toArray();

    if (empty($users)) {
      $this->command->warn("No users found — skipping ConversationSeeder.");
      return;
    }

    $channels = ["voice", "voice", "voice", "sms", "email", "chat"];
    $directions = ["inbound", "outbound"];
    $statuses = [
      "in_progress",
      "completed",
      "completed",
      "completed",
      "queued",
      "abandoned",
    ];
    $queues = ["Sales", "Support", "Billing", "General", "VIP"];

    $contactNames = [
      "James Carter",
      "Sofia Hernandez",
      "Liam Thompson",
      "Aisha Patel",
      "Marcus Johnson",
      "Emily Chen",
      "Daniel Okafor",
      "Isabella Rossi",
      "Noah Williams",
      "Fatima Al-Rashid",
      "Ethan Brooks",
      "Mia Nguyen",
      "Oliver Davis",
      "Amara Osei",
      "Lucas Martinez",
      "Hannah Kim",
      "Benjamin Scott",
      "Priya Sharma",
      "Alexander Brown",
      "Zoe Wilson",
      "Samuel Lee",
      "Camila Reyes",
      "Henry Taylor",
      "Nadia Kovac",
      "Jackson Moore",
      "Mei Zhang",
      "Sebastian Clark",
      "Aaliya Hassan",
      "Owen Lewis",
      "Elena Petrov",
    ];

    $detailPreviews = [
      "voice" => [
        "Called regarding account renewal",
        "Follow-up on previous support ticket",
        "Inquiry about pricing and plans",
        "Requested callback — missed first attempt",
        "Technical issue with login",
        "Billing dispute — charge not recognised",
        "Interested in upgrading plan",
        "General product enquiry",
      ],
      "sms" => [
        "Replied YES to opt-in campaign",
        "Requested callback via SMS",
        "Confirmed appointment via text",
        "Asked about promotional offer",
      ],
      "email" => [
        "Re: Your recent support request #4821",
        "Question about invoice INV-2026-0312",
        "Onboarding follow-up — Day 7",
        "Cancellation request submitted",
      ],
      "chat" => [
        "How do I reset my password?",
        "What are your business hours?",
        "Can I speak to a manager?",
        "Status of my order #78901",
      ],
    ];

    $now = Carbon::now();
    $rows = [];

    for ($i = 0; $i < 120; $i++) {
      $channel = $channels[array_rand($channels)];
      $status = $statuses[array_rand($statuses)];
      $startedAt = $now->copy()->subMinutes(rand(0, 60 * 72)); // within last 3 days

      $assignedTo = $users[array_rand($users)];
      $completedBy =
        $status === "completed" ? $users[array_rand($users)] : null;
      $duration = in_array($status, ["completed", "abandoned"])
        ? rand(30, 1800)
        : ($status === "in_progress"
          ? rand(10, 600)
          : null);

      $previews = $detailPreviews[$channel];

      $rows[] = [
        "channel" => $channel,
        "direction" => $directions[array_rand($directions)],
        "status" => $status,
        "contact_name" => $contactNames[array_rand($contactNames)],
        "contact_phone" => "+1" . rand(2000000000, 9999999999),
        "queue" => $queues[array_rand($queues)],
        "detail_preview" => $previews[array_rand($previews)],
        "duration_seconds" => $duration,
        "campaign_id" => !empty($campaigns)
          ? $campaigns[array_rand($campaigns)]
          : null,
        "assigned_to" => $assignedTo,
        "completed_by" => $completedBy,
        "started_at" => $startedAt,
        "created_at" => $startedAt,
        "updated_at" => $startedAt,
      ];
    }

    // Insert in chunks for efficiency
    foreach (array_chunk($rows, 50) as $chunk) {
      Conversation::insert($chunk);
    }

    $this->command->info("Seeded " . count($rows) . " conversations.");
  }
}
