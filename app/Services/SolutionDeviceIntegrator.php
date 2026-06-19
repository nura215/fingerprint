<?php

namespace App\Services;

use App\Models\BiometricEnrollment;
use App\Models\Device;
use App\Models\FingerprintScanLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class SolutionDeviceIntegrator
{
    public function __construct(
        private readonly SolutionX606SoapService $soap,
        private readonly AttendanceValidationService $attendanceValidation,
    ) {}

    public function syncEnrollment(BiometricEnrollment $enrollment): array
    {
        $devices = $this->targetDevices($enrollment);

        if ($devices->isEmpty()) {
            $this->markFailed($enrollment, 'Tidak ada perangkat dengan IP address.');

            return ['success' => 0, 'failed' => 1, 'message' => 'Tidak ada perangkat dengan IP address.'];
        }

        $success = 0;
        $failed = 0;
        $messages = [];

        foreach ($devices as $device) {
            try {
                $result = $this->soap->uploadUser($device, $enrollment->fingerprint_id, $enrollment->user_name);

                if ($result['success']) {
                    $success++;
                    $messages[] = "{$device->name}: ".$result['message'];
                    $device->forceFill(['status' => 'online', 'last_online_at' => now()])->save();
                } else {
                    $failed++;
                    $messages[] = "{$device->name}: ".$result['message'];
                }
            } catch (Throwable $exception) {
                $failed++;
                $messages[] = "{$device->name}: ".$exception->getMessage();
            }
        }

        $enrollment->forceFill([
            'device_id' => $enrollment->device_id ?: ($devices->count() === 1 ? $devices->first()->id : null),
            'sync_status' => $success > 0 ? 'synced' : 'failed',
            'sync_requested_at' => now(),
            'last_synced_at' => $success > 0 ? now() : $enrollment->last_synced_at,
            'sync_message' => implode(' | ', $messages),
        ])->save();

        return ['success' => $success, 'failed' => $failed, 'message' => implode(' | ', $messages)];
    }

    public function syncPending(?Device $device = null): array
    {
        $query = BiometricEnrollment::query()
            ->with(['device', 'student', 'lecturer'])
            ->where('sync_status', 'pending');

        if ($device) {
            $query->where(function ($query) use ($device) {
                $query->whereNull('device_id')->orWhere('device_id', $device->id);
            });
        }

        $summary = ['items' => 0, 'success' => 0, 'failed' => 0];

        $query->get()->each(function (BiometricEnrollment $enrollment) use (&$summary) {
            $result = $this->syncEnrollment($enrollment);
            $summary['items']++;
            $summary['success'] += $result['success'] > 0 ? 1 : 0;
            $summary['failed'] += $result['success'] > 0 ? 0 : 1;
        });

        return $summary;
    }

    public function pullLogs(?Device $onlyDevice = null): array
    {
        $devices = Device::query()
            ->when($onlyDevice, fn ($query) => $query->whereKey($onlyDevice->id))
            ->whereNotNull('ip_address')
            ->orderBy('name')
            ->get();

        $summary = ['devices' => $devices->count(), 'pulled' => 0, 'processed' => 0, 'ignored' => 0, 'failed' => 0];

        foreach ($devices as $device) {
            try {
                $rows = $this->soap->getAttendanceLogs($device);
                $summary['pulled'] += count($rows);
                $device->forceFill(['status' => 'online', 'last_online_at' => now()])->save();

                foreach ($rows as $row) {
                    $result = $this->processPulledLog($device, $row);
                    $summary[$result]++;
                }
            } catch (Throwable $exception) {
                $summary['failed']++;
                $device->forceFill(['status' => 'offline'])->save();
            }
        }

        return $summary;
    }

    private function processPulledLog(Device $device, array $row): string
    {
        $scanAt = Carbon::parse($row['scan_time']);

        $exists = FingerprintScanLog::query()
            ->where('device_id', $device->id)
            ->where('fingerprint_id', $row['fingerprint_id'])
            ->where('scan_time', $scanAt)
            ->exists();

        if ($exists) {
            return 'ignored';
        }

        try {
            DB::transaction(function () use ($device, $row, $scanAt) {
                $enrollment = BiometricEnrollment::query()
                    ->where('fingerprint_id', $row['fingerprint_id'])
                    ->first();

                if ($enrollment && $enrollment->status !== 'enrolled') {
                    $enrollment->forceFill([
                        'device_id' => $enrollment->device_id ?: $device->id,
                        'status' => 'enrolled',
                        'enrolled_at' => $scanAt,
                        'sync_status' => 'synced',
                        'last_synced_at' => now(),
                        'sync_message' => 'Sidik jari terdeteksi dari log alat.',
                    ])->save();
                }

                $this->attendanceValidation->validateScan(
                    $device->device_code,
                    $row['fingerprint_id'],
                    $scanAt,
                    [
                        'source' => 'solution_web_sdk',
                        'verified' => $row['verified'],
                        'status' => $row['status'],
                        'raw' => $row['raw'],
                    ],
                );
            });

            return 'processed';
        } catch (Throwable $exception) {
            FingerprintScanLog::query()->create([
                'device_id' => $device->id,
                'fingerprint_id' => $row['fingerprint_id'],
                'scan_time' => $scanAt,
                'raw_payload' => $row,
                'process_status' => 'failed',
                'message' => $exception->getMessage(),
            ]);

            return 'failed';
        }
    }

    private function targetDevices(BiometricEnrollment $enrollment)
    {
        if ($enrollment->device_id) {
            return Device::query()->whereKey($enrollment->device_id)->whereNotNull('ip_address')->get();
        }

        return Device::query()->whereNotNull('ip_address')->orderBy('name')->get();
    }

    private function markFailed(BiometricEnrollment $enrollment, string $message): void
    {
        $enrollment->forceFill([
            'sync_status' => 'failed',
            'sync_requested_at' => now(),
            'sync_message' => $message,
        ])->save();
    }
}
