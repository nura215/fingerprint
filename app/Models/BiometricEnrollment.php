<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricEnrollment extends Model
{
    use HasFactory;

    protected $fillable = ['user_type', 'user_id', 'fingerprint_id', 'device_id', 'enrolled_at', 'status'];

    protected function casts(): array { return ['enrolled_at' => 'datetime']; }

    public function device(): BelongsTo { return $this->belongsTo(Device::class); }
    public function lecturer(): BelongsTo { return $this->belongsTo(Lecturer::class, 'user_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class, 'user_id'); }

    public function getUserNameAttribute(): string
    {
        return match ($this->user_type) {
            'lecturer' => $this->lecturer?->name ?? '-',
            'student' => $this->student?->name ?? '-',
            default => '-',
        };
    }

    public function getUserTypeLabelAttribute(): string
    {
        return match ($this->user_type) {
            'lecturer' => 'Dosen',
            'student' => 'Mahasiswa',
            default => '-',
        };
    }
}

