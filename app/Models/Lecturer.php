<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lecturer extends Model
{
    use HasFactory;

    protected $fillable = ['nidn', 'name', 'email', 'phone', 'fingerprint_id', 'status'];

    public function schedules(): HasMany { return $this->hasMany(Schedule::class); }
    public function biometricEnrollment(): HasOne { return $this->hasOne(BiometricEnrollment::class, 'user_id')->where('user_type', 'lecturer'); }

    public function getFingerprintStatusAttribute(): string
    {
        if ($this->status === 'inactive') {
            return 'inactive';
        }

        return $this->biometricEnrollment?->status === 'enrolled' ? 'enrolled' : 'not_enrolled';
    }
}

