<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    protected $fillable = ['device_code', 'name', 'model', 'ip_address', 'port', 'comm_key', 'room_id', 'connection_type', 'status', 'last_online_at'];

    protected function casts(): array { return ['last_online_at' => 'datetime']; }

    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
    public function biometricEnrollments(): HasMany { return $this->hasMany(BiometricEnrollment::class); }
    public function fingerprintScanLogs(): HasMany { return $this->hasMany(FingerprintScanLog::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function doorAccessLogs(): HasMany { return $this->hasMany(DoorAccessLog::class); }
}

