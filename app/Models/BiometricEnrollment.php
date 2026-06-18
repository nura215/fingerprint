<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'user_id',
        'fingerprint_id',
        'device_id',
        'enrolled_at',
        'status',
        'sync_status',
        'sync_requested_at',
        'last_synced_at',
        'sync_message',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'sync_requested_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

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

    public function getUserIdentifierAttribute(): string
    {
        return match ($this->user_type) {
            'lecturer' => $this->lecturer?->nidn ?? '-',
            'student' => $this->student?->nim ?? '-',
            default => '-',
        };
    }

    public function getSyncStatusLabelAttribute(): string
    {
        return match ($this->sync_status) {
            'synced' => 'Sudah Terkirim',
            'failed' => 'Gagal Sync',
            default => 'Menunggu Sync',
        };
    }
}

