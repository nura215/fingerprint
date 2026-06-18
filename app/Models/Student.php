<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['nim', 'name', 'class_id', 'fingerprint_id', 'status'];

    public function class(): BelongsTo { return $this->belongsTo(AcademicClass::class, 'class_id'); }
    public function biometricEnrollment(): HasOne { return $this->hasOne(BiometricEnrollment::class, 'user_id')->where('user_type', 'student'); }

    public function getFingerprintStatusAttribute(): string
    {
        if ($this->status === 'inactive') {
            return 'inactive';
        }

        return $this->biometricEnrollment?->status === 'enrolled' ? 'enrolled' : 'not_enrolled';
    }
}

