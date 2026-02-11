<?php

namespace App\Models;

use App\Models\User;
use App\Observers\UserMetaObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsersMeta extends Model
{
    use HasFactory;

    protected $table = 'users_meta';

    protected $fillable = ['user_id','key', 'value'];

    protected static function booted(): void
    {
        static::observe(UserMetaObserver::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
