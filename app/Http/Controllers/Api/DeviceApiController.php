<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DoorAccessLog;
use App\Services\AttendanceValidationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceApiController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_code' => ['required', 'string', 'exists:devices,device_code'],
            'status' => ['required', 'in:online,offline,maintenance'],
            'ip_address' => ['nullable', 'ip'],
            'last_online_at' => ['nullable', 'date'],
        ]);

        $Device = Device::query()->where('device_code', $validated['device_code'])->firstOrFail();
        $lastOnlineAt = $validated['last_online_at'] ?? ($validated['status'] === 'online' ? now() : $Device->last_online_at);

        $Device->update([
            'status' => $validated['status'],
            'ip_address' => $validated['ip_address'] ?? $Device->ip_address,
            'last_online_at' => $lastOnlineAt,
        ]);

        return response()->json([
            'status' => 'accepted',
            'message' => 'Device status updated.',
            'device' => [
                'device_code' => $Device->device_code,
                'status' => $Device->status,
                'last_online_at' => $Device->last_online_at?->toISOString(),
            ],
        ]);
    }

    public function scan(Request $request, AttendanceValidationService $service): JsonResponse
    {
        $validated = $request->validate([
            'device_code' => ['required', 'string'],
            'fingerprint_id' => ['required', 'string', 'max:100'],
            'scan_time' => ['nullable', 'date'],
            'raw_payload' => ['nullable', 'array'],
        ]);

        $scanTime = isset($validated['scan_time'])
            ? Carbon::parse($validated['scan_time'])
            : now();

        $result = $service->validateScan(
            $validated['device_code'],
            $validated['fingerprint_id'],
            $scanTime,
            $validated['raw_payload'] ?? $request->all(),
        );

        return response()->json($result, $result['status'] === 'rejected' ? 422 : 200);
    }

    public function doorLog(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_code' => ['required', 'string', 'exists:devices,device_code'],
            'schedule_id' => ['nullable', 'exists:schedules,id'],
            'user_type' => ['nullable', Rule::in(['lecturer', 'student', 'admin'])],
            'user_id' => ['nullable', 'integer'],
            'access_time' => ['nullable', 'date'],
            'access_status' => ['required', Rule::in(['granted', 'denied', 'failed'])],
            'open_door' => ['nullable', 'boolean'],
            'method' => ['required', Rule::in(['fingerprint', 'manual_web', 'exit_button', 'sdk_command'])],
            'reason' => ['nullable', 'string'],
        ]);

        $Device = Device::query()->where('device_code', $validated['device_code'])->firstOrFail();

        $log = DoorAccessLog::query()->create([
            'device_id' => $Device->id,
            'room_id' => $Device->room_id,
            'schedule_id' => $validated['schedule_id'] ?? null,
            'user_type' => $validated['user_type'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'access_time' => isset($validated['access_time']) ? Carbon::parse($validated['access_time']) : now(),
            'access_status' => $validated['access_status'],
            'open_door' => (bool) ($validated['open_door'] ?? false),
            'method' => $validated['method'],
            'reason' => $validated['reason'] ?? null,
        ]);

        return response()->json([
            'status' => 'accepted',
            'message' => 'Door access log stored.',
            'log_id' => $log->id,
        ], 201);
    }
}








