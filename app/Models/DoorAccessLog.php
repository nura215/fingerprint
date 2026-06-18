<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoorAccessLog extends Model
{
    use HasFactory;

    protected $fillable = ['device_id', 'room_id', 'schedule_id', 'user_type', 'user_id', 'access_time', 'access_status', 'open_door', 'method', 'reason'];

    protected function casts(): array
    {
        return ['access_time' => 'datetime', 'open_door' => 'boolean'];
    }

    public function device(): BelongsTo { return $this->belongsTo(Device::class); }
    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
    public function schedule(): BelongsTo { return $this->belongsTo(Schedule::class); }
    public function lecturer(): BelongsTo { return $this->belongsTo(Lecturer::class, 'user_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class, 'user_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }

    public function getAccessUserNameAttribute(): string
    {
        return match ($this->user_type) {
            'lecturer' => $this->lecturer?->name ?? '-',
            'student' => $this->student?->name ?? '-',
            'admin' => $this->user?->name ?? 'Admin',
            default => '-',
        };
    }

    public function getAccessFingerprintIdAttribute(): string
    {
        return match ($this->user_type) {
            'lecturer' => $this->lecturer?->fingerprint_id ?? '-',
            'student' => $this->student?->fingerprint_id ?? '-',
            default => '-',
        };
    }
}

