<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['schedule_id', 'schedule_meeting_id', 'user_type', 'user_id', 'device_id', 'fingerprint_id', 'attendance_time', 'attendance_status', 'validation_status', 'notes'];

    protected function casts(): array { return ['attendance_time' => 'datetime']; }

    public function schedule(): BelongsTo { return $this->belongsTo(Schedule::class); }
    public function scheduleMeeting(): BelongsTo { return $this->belongsTo(ScheduleMeeting::class); }
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
}

