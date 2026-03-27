<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Each top-level item may have a `children` array.
    | Clicking the parent navigates to the first accessible child.
    | `segments` lists every URL segment(1) value that should mark this item active.
    | `bottom` = true places the item in the sidebar footer section.
    |--------------------------------------------------------------------------
    */

  [
    "label" => "Dashboard",
    "route" => "dashboard",
    "icon" => "heroicon-o-home",
    "segments" => [],
    "permission" => null,
    "bottom" => false,
    "children" => [],
  ],

  [
    "label" => "People",
    "icon" => "heroicon-o-users",
    "segments" => [
      "users",
      "user",
      "roles",
      "role",
      "permissions",
      "user-groups",
      "user-group",
    ],
    "permission" => null,
    "bottom" => false,
    "children" => [
      [
        "label" => "Users",
        "route" => "users.index",
        "segment" => "users",
        "permission" => "user.view",
      ],
      [
        "label" => "Roles & Permissions",
        "route" => "roles.index",
        "segment" => "roles",
        "permission" => "user.view",
      ],
      [
        "label" => "User Groups",
        "route" => "user-groups.index",
        "segment" => "user-groups",
        "permission" => "user_group.view",
      ],
    ],
  ],

  [
    "label" => "Activity",
    "icon" => "heroicon-o-signal",
    "segments" => [
      "activity-overview",
      "activity-logs",
      "activity-log",
      "agent-status",
    ],
    "permission" => null,
    "bottom" => false,
    "children" => [
      [
        "label" => "Overview",
        "route" => "activity.overview",
        "segment" => "activity-overview",
        "permission" => null,
      ],
      [
        "label" => "Activity Logs",
        "route" => "activitylogs.index",
        "segment" => "activity-logs",
        "permission" => "activity_log.view",
      ],
      [
        "label" => "Agent Status",
        "route" => "agent.status.index",
        "segment" => "agent-status",
        "permission" => "agent_status.view",
      ],
    ],
  ],

  [
    "label" => "Operations",
    "icon" => "heroicon-o-megaphone",
    "segments" => ["campaigns", "campaign", "leads", "lead", "stores", "store"],
    "permission" => null,
    "bottom" => false,
    "children" => [
      [
        "label" => "Campaigns",
        "route" => "campaigns.index",
        "segment" => "campaigns",
        "permission" => "campaign.view",
      ],
      [
        "label" => "Leads",
        "route" => "leads.index",
        "segment" => "leads",
        "permission" => "lead.view",
      ],
      [
        "label" => "Stores",
        "route" => "stores.index",
        "segment" => "stores",
        "permission" => "store.view",
      ],
    ],
  ],

  [
    "label" => "Settings",
    "route" => "settings.profile",
    "icon" => "heroicon-o-cog-6-tooth",
    "segments" => ["settings"],
    "permission" => null,
    "bottom" => true,
    "children" => [],
  ],
];
