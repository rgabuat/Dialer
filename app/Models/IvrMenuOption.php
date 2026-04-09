<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IvrMenuOption extends Model
{
  protected $fillable = [
    "ivr_menu_id",
    "digit",
    "description",
    "action",
    "destination",
  ];

  public function ivrMenu()
  {
    return $this->belongsTo(IvrMenu::class);
  }
}
