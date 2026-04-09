<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IvrMenu extends Model
{
  use HasFactory;

  protected $fillable = [
    "name",
    "description",
    "greeting_type",
    "greeting_value",
    "gather_timeout",
    "invalid_attempts_max",
    "invalid_action",
    "invalid_destination",
    "is_active",
  ];

  protected $casts = [
    "is_active" => "boolean",
  ];

  public function options()
  {
    return $this->hasMany(IvrMenuOption::class)->orderBy("digit");
  }

  public function dids()
  {
    return $this->hasMany(Did::class);
  }
}
