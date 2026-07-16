<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_role');
    }
}
