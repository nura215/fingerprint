<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'faculty'];

    public function classes(): HasMany { return $this->hasMany(AcademicClass::class); }
    public function subjects(): HasMany { return $this->hasMany(Subject::class); }
}

