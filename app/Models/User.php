<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\UsersMeta;
use App\Models\UserGroup;
use App\Models\AgentStatus;
use App\Models\AgentStatusLog;
use App\Observers\UserObserver;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    "first_name",
    "last_name",
    "nickname",
    "name",
    "remember_token",
    "email",
    "password",
    "user_group_id",
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = ["password", "remember_token"];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    "email_verified_at" => "datetime",
    "password" => "hashed",
  ];

  protected static function booted()
  {
    static::observe(UserObserver::class);
  }

  public function meta()
  {
    return $this->hasMany(UsersMeta::class);
  }

  // 🔹 GET meta
  public function getMeta(string $key, $default = null)
  {
    return $this->meta()->where("key", $key)->value("value") ?? $default;
  }

  public function setMeta(string $key, $value): void
  {
    $meta = $this->meta()->where("key", $key)->first();

    if (!$meta) {
      $meta = $this->meta()->make([
        "key" => $key,
      ]);
    }

    // Only save if value actually changed
    if ((string) $meta->value !== (string) $value) {
      $meta->value = $value;
      $meta->save(); // ✅ safe, observer WILL fire
    }
  }

  public function userGroup()
  {
    return $this->belongsTo(UserGroup::class);
  }

  public function campaigns()
  {
    if (!$this->user_group_id || !$this->userGroup) {
      return collect();
    }
    return $this->userGroup->campaigns();
  }

  public function activeCampaign(): ?Campaign
  {
    $id = session("active_campaign_id");
    if (!$id) {
      return null;
    }
    if (!$this->user_group_id) {
      return null;
    }
    return $this->userGroup->campaigns()->find($id);
  }

  public function statusLogs()
  {
    return $this->hasMany(AgentStatusLog::class);
  }

  public function agentStatus()
  {
    return $this->hasOne(AgentStatus::class);
  }
}
