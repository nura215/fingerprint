<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = ['academic_year_id', 'lecturer_id', 'class_id', 'subject_id', 'room_id', 'day', 'start_time', 'end_time', 'status'];

    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function lecturer(): BelongsTo { return $this->belongsTo(Lecturer::class); }
    public function class(): BelongsTo { return $this->belongsTo(AcademicClass::class, 'class_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
    public function meetings(): HasMany { return $this->hasMany(ScheduleMeeting::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function doorAccessLogs(): HasMany { return $this->hasMany(DoorAccessLog::class); }

    public function getDayLabelAttribute(): string
    {
        return [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ][$this->day] ?? $this->day;
    }

    public function getTimeRangeAttribute(): string
    {
        return substr((string) $this->start_time, 0, 5).' - '.substr((string) $this->end_time, 0, 5);
    }
}

