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
    "label" => "Campaign",
    "icon" => "heroicon-o-megaphone",
    "segments" => [
      "campaigns",
      "campaign",
    ],
    "permission" => null,
    "bottom" => false,
    "children" => [
      [
        "label" => "All Campaigns",
        "route" => "campaigns.index",
        "segment" => "campaigns",
        "permission" => "campaign.view",
      ],
    ],
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
      "shift-monitoring",
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
      [
        "label" => "Shift Monitoring",
        "route" => "shift.monitoring",
        "segment" => "shift-monitoring",
        "permission" => null,
      ],
    ],
  ],

  [
    "label" => "Operations",
    "icon" => "heroicon-o-briefcase",
    "segments" => ["leads", "lead", "stores", "store"],
    "permission" => null,
    "bottom" => false,
    "children" => [
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
    "label" => "Reports",
    "icon" => "heroicon-o-chart-bar",
    "segments" => ["reports"],
    "permission" => null,
    "role" => "Super Admin",
    "bottom" => false,
    "children" => [
      [
        "label" => "Overview",
        "route" => "reports.index",
        "segment" => "reports",
        "permission" => null,
        "role" => "Super Admin",
      ],
    ],
  ],

  [
    "label" => "Conversations",
    "icon" => "heroicon-o-chat-bubble-left-right",
    "segments" => ["conversations"],
    "permission" => null,
    "bottom" => false,
    "children" => [
      [
        "label" => "Assigned",
        "route" => "conversations.assigned",
        "permission" => null,
      ],
      [
        "label" => "All Conversations",
        "route" => "conversations.index",
        "permission" => null,
      ],
    ],
  ],

  [
    "label" => "Workforce",
    "icon" => "heroicon-o-calendar-days",
    "segments" => ["workforce"],
    "permission" => null,
    "bottom" => false,
    "children" => [
      [
        "label" => "Rosters",
        "route" => "workforce.rosters.index",
        "segment" => "workforce",
        "permission" => null,
      ],
    ],
  ],

  [
    "label" => "Inbound",
    "icon" => "heroicon-o-phone-arrow-down-left",
    "segments" => [
      "in-groups",
      "in-group",
      "dids",
      "did",
      "cid-numbers",
      "ivr-menus",
      "ivr-menu",
    ],
    "permission" => null,
    "bottom" => false,
    "children" => [
      [
        "label" => "CID Numbers",
        "route" => "cid-numbers.index",
        "segment" => "cid-numbers",
        "permission" => null,
      ],
      [
        "label" => "In-Groups",
        "route" => "in-groups.index",
        "segment" => "in-groups",
        "permission" => null,
      ],
      [
        "label" => "DIDs",
        "route" => "dids.index",
        "segment" => "dids",
        "permission" => null,
      ],
      [
        "label" => "IVR Menus",
        "route" => "ivr-menus.index",
        "segment" => "ivr-menus",
        "permission" => null,
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
