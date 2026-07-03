<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CidGroup extends Model
{
    use HasFactory;

    protected $table = 'cid_groups';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** CID numbers that belong to this group */
    public function cidNumbers()
    {
        return $this->hasMany(CidNumber::class);
    }

    /** The campaign this group is currently bound to (nullable) */
    public function campaign()
    {
        return $this->hasOne(Campaign::class, 'cid_group_id');
    }
}
