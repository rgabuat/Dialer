<?php

/**
 * Application Theme Configuration
 *
 * Documents the design tokens used across the application.
 * Dark/light mode is toggled via the `dark` class on <html> (managed by
 * Alpine.store('theme')) and Tailwind's `dark:` variant.
 *
 * Default mode: dark
 */
return [
  "default" => "dark",

  "dark" => [
    "bg" => [
      "base" => "#0f1115", // main content area
      "sidebar" => "#0c0e12", // sidebar / subnav background
      "topbar" => "#111519", // topbar gradient end
      "surface" => "#18181b", // zinc-900 – cards, panels
      "surface2" => "#27272a", // zinc-800 – inputs, hover states
    ],
    "border" => "#27272a", // zinc-800
    "text" => [
      "primary" => "#ffffff",
      "secondary" => "#a1a1aa", // zinc-400
      "muted" => "#71717a", // zinc-500
    ],
    "accent" => "#3b82f6", // blue-500
  ],

  "light" => [
    "bg" => [
      "base" => "#f4f4f5", // zinc-100
      "sidebar" => "#ffffff",
      "topbar" => "#ffffff",
      "surface" => "#ffffff",
      "surface2" => "#f4f4f5", // zinc-100
    ],
    "border" => "#e4e4e7", // zinc-200
    "text" => [
      "primary" => "#09090b", // zinc-950
      "secondary" => "#52525b", // zinc-600
      "muted" => "#71717a", // zinc-500
    ],
    "accent" => "#3b82f6", // blue-500
  ],
];
