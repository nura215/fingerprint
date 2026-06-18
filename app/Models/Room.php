<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'location', 'capacity', 'status'];

    public function devices(): HasMany { return $this->hasMany(Device::class); }
    public function schedules(): HasMany { return $this->hasMany(Schedule::class); }
    public function doorAccessLogs(): HasMany { return $this->hasMany(DoorAccessLog::class); }
}

