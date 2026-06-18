<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['nim', 'name', 'class_id', 'fingerprint_id', 'status'];

    public function class(): BelongsTo { return $this->belongsTo(AcademicClass::class, 'class_id'); }
}

