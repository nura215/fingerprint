<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleMeeting extends Model
{
    use HasFactory;

    protected $fillable = ['schedule_id', 'meeting_date', 'meeting_number', 'lecturer_attendance_id', 'status', 'opened_at', 'closed_at'];

    protected function casts(): array
    {
        return ['meeting_date' => 'date', 'opened_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    public function schedule(): BelongsTo { return $this->belongsTo(Schedule::class); }
    public function lecturerAttendance(): BelongsTo { return $this->belongsTo(Attendance::class, 'lecturer_attendance_id'); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
}

