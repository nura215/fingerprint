<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = ['code', 'name', 'department_id', 'academic_year_id', 'status'];

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function students(): HasMany { return $this->hasMany(Student::class, 'class_id'); }
    public function schedules(): HasMany { return $this->hasMany(Schedule::class, 'class_id'); }
}

