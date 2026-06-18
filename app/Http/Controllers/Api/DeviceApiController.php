<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BiometricEnrollment;
use App\Models\Device;
use App\Models\DoorAccessLog;
use App\Services\AttendanceValidationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceApiController extends Controller
{
    public function pendingUsers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_code' => ['nullable', 'string', 'exists:devices,device_code'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $device = isset($validated['device_code'])
            ? Device::query()->where('device_code', $validated['device_code'])->first()
            : null;

        $items = BiometricEnrollment::query()
            ->with(['device', 'student.class', 'lecturer'])
            ->where('sync_status', 'pending')
            ->when($device, function ($query) use ($device) {
                $query->where(function ($query) use ($device) {
                    $query->whereNull('device_id')->orWhere('device_id', $device->id);
                });
            })
            ->oldest('sync_requested_at')
            ->limit($validated['limit'] ?? 100)
            ->get()
            ->map(fn (BiometricEnrollment $enrollment) => [
                'enrollment_id' => $enrollment->id,
                'operation' => $enrollment->status === 'inactive' ? 'disable_user' : 'upsert_user',
                'user_type' => $enrollment->user_type,
                'user_type_label' => $enrollment->user_type_label,
                'user_id' => $enrollment->user_id,
                'user_identifier' => $enrollment->user_identifier,
                'name' => $enrollment->user_name,
                'fingerprint_id' => $enrollment->fingerprint_id,
                'device_code' => $enrollment->device?->device_code,
                'device_name' => $enrollment->device?->name,
                'status' => $enrollment->status,
                'sync_requested_at' => $enrollment->sync_requested_at?->toISOString(),
            ]);

        return response()->json([
            'status' => 'accepted',
            'count' => $items->count(),
            'users' => $items,
        ]);
    }

    public function syncResult(Request $request, BiometricEnrollment $biometricEnrollment): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['success', 'failed'])],
            'message' => ['nullable', 'string', 'max:1000'],
            'device_code' => ['nullable', 'string', 'exists:devices,device_code'],
        ]);

        $device = isset($validated['device_code'])
            ? Device::query()->where('device_code', $validated['device_code'])->first()
            : null;

        $biometricEnrollment->update([
            'device_id' => $device?->id ?? $biometricEnrollment->device_id,
            'sync_status' => $validated['status'] === 'success' ? 'synced' : 'failed',
            'last_synced_at' => now(),
            'sync_message' => $validated['message'] ?? ($validated['status'] === 'success'
                ? 'Berhasil dikirim ke perangkat.'
                : 'Gagal dikirim ke perangkat.'),
        ]);

        return response()->json([
            'status' => 'accepted',
            'message' => 'Sync result stored.',
            'enrollment' => [
                'id' => $biometricEnrollment->id,
                'sync_status' => $biometricEnrollment->sync_status,
                'last_synced_at' => $biometricEnrollment->last_synced_at?->toISOString(),
            ],
        ]);
    }

    public function enrolledUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fingerprint_id' => ['required', 'string', 'max:100'],
            'device_code' => ['nullable', 'string', 'exists:devices,device_code'],
            'enrolled_at' => ['nullable', 'date'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $device = isset($validated['device_code'])
            ? Device::query()->where('device_code', $validated['device_code'])->first()
            : null;

        $enrollment = BiometricEnrollment::query()
            ->where('fingerprint_id', $validated['fingerprint_id'])
            ->first();

        if (! $enrollment) {
            return response()->json([
                'status' => 'rejected',
                'message' => 'Fingerprint ID tidak ditemukan di data enrollment.',
            ], 404);
        }

        $enrolledAt = isset($validated['enrolled_at'])
            ? Carbon::parse($validated['enrolled_at'])
            : now();

        $enrollment->update([
            'device_id' => $device?->id ?? $enrollment->device_id,
            'enrolled_at' => $enrolledAt,
            'status' => 'enrolled',
            'sync_status' => 'synced',
            'last_synced_at' => now(),
            'sync_message' => $validated['message'] ?? 'Sidik jari berhasil didaftarkan di alat.',
        ]);

        return response()->json([
            'status' => 'accepted',
            'message' => 'Enrollment status updated.',
            'enrollment' => [
                'id' => $enrollment->id,
                'fingerprint_id' => $enrollment->fingerprint_id,
                'user_type' => $enrollment->user_type,
                'user_id' => $enrollment->user_id,
                'status' => $enrollment->status,
                'sync_status' => $enrollment->sync_status,
                'enrolled_at' => $enrollment->enrolled_at?->toISOString(),
            ],
        ]);
    }

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
            'access_status' => ['nullable', Rule::in(['granted', 'denied', 'failed'])],
            'open_door' => ['nullable', 'boolean'],
            'method' => ['required', Rule::in(['fingerprint', 'manual_web', 'exit_button', 'sdk_command'])],
            'reason' => ['nullable', 'string'],
        ]);

        $Device = Device::query()->where('device_code', $validated['device_code'])->firstOrFail();
        $isExitButton = ($validated['method'] ?? null) === 'exit_button';

        $log = DoorAccessLog::query()->create([
            'device_id' => $Device->id,
            'room_id' => $Device->room_id,
            'schedule_id' => $validated['schedule_id'] ?? null,
            'user_type' => $validated['user_type'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'access_time' => isset($validated['access_time']) ? Carbon::parse($validated['access_time']) : now(),
            'access_status' => $validated['access_status'] ?? ($isExitButton ? 'granted' : 'denied'),
            'open_door' => $isExitButton ? true : (bool) ($validated['open_door'] ?? false),
            'method' => $validated['method'],
            'reason' => $validated['reason'] ?? ($isExitButton ? 'Keluar lewat exit button.' : null),
        ]);

        return response()->json([
            'status' => 'accepted',
            'message' => 'Door access log stored.',
            'log_id' => $log->id,
        ], 201);
    }
}








