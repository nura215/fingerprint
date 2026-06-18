<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log(string $action, ?Model $model = null, ?array $oldData = null, ?array $newData = null): void
    {
        AuditLog::query()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'table_name' => $model?->getTable(),
            'record_id' => $model?->getKey(),
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}







