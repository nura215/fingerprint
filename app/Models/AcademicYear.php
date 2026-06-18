<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = ['year', 'semester', 'is_active'];

    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function classes(): HasMany { return $this->hasMany(AcademicClass::class, 'academic_year_id'); }
    public function schedules(): HasMany { return $this->hasMany(Schedule::class, 'academic_year_id'); }
}

