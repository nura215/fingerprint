<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lecturer extends Model
{
    use HasFactory;

    protected $fillable = ['nidn', 'name', 'email', 'phone', 'fingerprint_id', 'status'];

    public function schedules(): HasMany { return $this->hasMany(Schedule::class); }
}

