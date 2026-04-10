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
    "twilio_sid",
    "description",
    "in_group_id",
    "ivr_menu_id",
    "campaign_id",
    "is_active",
  ];

  protected $casts = [
    "is_active" => "boolean",
  ];

  public function inGroup()
  {
    return $this->belongsTo(InGroup::class);
  }

  public function ivrMenu()
  {
    return $this->belongsTo(IvrMenu::class);
  }

  public function campaign()
  {
    return $this->belongsTo(Campaign::class);
  }

  /** Campaigns using this DID in their CID rotation pool */
  public function rotationCampaigns()
  {
    return $this->belongsToMany(Campaign::class, 'campaign_cid_dids')->withPivot('sort_order');
  }
}
