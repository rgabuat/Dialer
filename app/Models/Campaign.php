<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
  use HasFactory;

  // ── Enum options (single source of truth) ─────────────────────────────────
  const TYPES           = ['OUTBOUND', 'INBOUND', 'BLENDED'];
  const DIAL_MODES      = ['MANUAL', 'PREVIEW', 'PROGRESSIVE', 'PREDICTIVE'];
  const TTS_VOICES      = ['alice', 'man', 'woman'];
  const REC_CHANNELS    = ['both', 'inbound', 'outbound'];

  // ── Available modules (key => default enabled) ───────────────────────────
  const MODULES = [
      'call_lists'    => ['label' => 'Call Lists',           'desc' => 'Upload and manage outbound call lists.',          'default' => true],
      'dispositions'  => ['label' => 'Dispositions',         'desc' => 'Track call outcomes with disposition codes.',      'default' => true],
      'callbacks'     => ['label' => 'Callback Scheduling',  'desc' => 'Allow agents to schedule follow-up callbacks.',    'default' => true],
      'lead_capture'  => ['label' => 'Lead Capture Form',    'desc' => 'Show lead data-entry form to agents on calls.',    'default' => true],
      'inbound_queue' => ['label' => 'Inbound Queue',        'desc' => 'Route inbound calls to this campaign.',            'default' => true],
      'reports'       => ['label' => 'Reports & Analytics',  'desc' => 'Access campaign performance reports.',             'default' => true],
      'dnc_check'     => ['label' => 'DNC Check',            'desc' => 'Auto-check Do Not Call registry before dialing.', 'default' => false],
      'sms'           => ['label' => 'SMS Messaging',        'desc' => 'Send and receive SMS from agents.',                'default' => false],
  ];

  protected $fillable = [
    "name", "description", "is_active",
    "type", "dial_mode", "dial_level", "caller_id", "cid_rotation", "cid_group_id",
    "script", "acw_seconds", "hopper_level", "max_calls", "script_enabled",
    // Voice & recording
    "tts_voice", "tts_language",
    "tts_completed", "tts_busy", "tts_no_answer", "tts_failed", "tts_canceled",
    "greeting_message", "hold_music_url",
    "recording_enabled",
    "recording_channels",
    "lead_process",
    "modules",
    "lead_template_id",
  ];

  protected $casts = [
    "is_active"          => "boolean",
    "script_enabled"     => "boolean",
    "cid_rotation"       => "boolean",
    "cid_group_id"       => "integer",
    "dial_level"         => "decimal:2",
    "acw_seconds"        => "integer",
    "hopper_level"       => "integer",
    "max_calls"          => "integer",
    "recording_enabled"  => "boolean",
    "lead_process"       => "array",
    "modules"            => "array",
    "lead_template_id"   => "integer",
  ];

  public function userGroups()
  {
    return $this->belongsToMany(UserGroup::class);
  }

  /** Check if a module is enabled for this campaign (falls back to the module's default). */
  public function moduleEnabled(string $key): bool
  {
      $modules = $this->modules ?? [];
      $def     = self::MODULES[$key]['default'] ?? true;
      return (bool) ($modules[$key] ?? $def);
  }

  public function users()
  {
    return $this->belongsToMany(User::class);
  }

  public function callLists()
  {
    return $this->hasMany(\App\Models\CallList::class);
  }

  public function dispositions()
  {
    return $this->hasMany(\App\Models\Disposition::class);
  }

  public function callbackSchedules()
  {
    return $this->hasMany(\App\Models\CallbackSchedule::class);
  }

  public function inGroups()
  {
    return $this->hasMany(\App\Models\InGroup::class);
  }

  public function cidGroup()
  {
    return $this->belongsTo(\App\Models\CidGroup::class);
  }

  public function leadTemplate()
  {
    return $this->belongsTo(\App\Models\LeadTemplate::class);
  }

  /**
   * Pick the next CID model for this campaign using round-robin from the global CID rotation pool.
   * Returns a CidNumber model, or null if rotation is disabled / pool is empty.
   */
  public function nextCidModel(): ?\App\Models\CidNumber
  {
    if (!$this->cid_rotation || !$this->cid_group_id) {
        return null;
    }

    $pool = \App\Models\CidNumber::where('cid_group_id', $this->cid_group_id)
        ->where('is_active', true)
        ->where('in_rotation', true)
        ->orderBy('phone_number')
        ->get();

    if ($pool->isEmpty()) {
        return null;
    }

    $cacheKey = "cid_rotation:{$this->id}";
    $count    = $pool->count();
    $index    = (int) \Illuminate\Support\Facades\Cache::get($cacheKey, 0);
    $model    = $pool[$index % $count];

    \Illuminate\Support\Facades\Cache::put($cacheKey, ($index + 1) % $count, now()->addDay());

    return $model;
  }

  /**
   * Pick the next CID for this campaign using round-robin from the global CID rotation pool.
   * The pool is all CidNumbers where in_rotation=true and is_active=true.
   * Returns a phone_number string, or the fixed caller_id if rotation is disabled / pool is empty.
   */
  public function nextCid(): ?string
  {
    return $this->nextCidModel()?->phone_number ?? ($this->caller_id ?: null);
  }
}
