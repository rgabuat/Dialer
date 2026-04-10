<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Did extends Model
{
  use HasFactory;

  protected $table = "dids";

  protected $fillable = [
    "phone_number",
    "cid_number_id",
    "in_group_id",
    "ivr_menu_id",
    "is_active",
  ];

  protected $casts = [
    "is_active" => "boolean",
  ];

  /** The CID Number (Twilio-imported) this DID handles inbound calls for */
  public function cidNumber()
  {
    return $this->belongsTo(CidNumber::class);
  }

  public function inGroup()
  {
    return $this->belongsTo(InGroup::class);
  }

  public function ivrMenu()
  {
    return $this->belongsTo(IvrMenu::class);
  }
}
