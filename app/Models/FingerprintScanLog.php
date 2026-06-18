<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FingerprintScanLog extends Model
{
    use HasFactory;

    protected $fillable = ['device_id', 'fingerprint_id', 'scan_time', 'raw_payload', 'process_status', 'message'];

    protected function casts(): array
    {
        return ['scan_time' => 'datetime', 'raw_payload' => 'array'];
    }

    public function device(): BelongsTo { return $this->belongsTo(Device::class); }
}

