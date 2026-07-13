<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'is_active', 'lead_process'];

    protected $casts = [
        'is_active'    => 'boolean',
        'lead_process' => 'array',
    ];

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function fieldCount(): int
    {
        $steps = $this->lead_process['steps'] ?? [];
        return collect($steps)->sum(fn ($s) => count($s['fields'] ?? []));
    }

    public function stepCount(): int
    {
        return count($this->lead_process['steps'] ?? []);
    }
}
