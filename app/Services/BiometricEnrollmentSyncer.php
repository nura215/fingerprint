<?php

namespace App\Services;

use App\Models\BiometricEnrollment;
use App\Models\Lecturer;
use App\Models\Student;

class BiometricEnrollmentSyncer
{
    public function syncStudent(Student $student): void
    {
        $this->sync('student', $student->id, $student->fingerprint_id);
    }

    public function syncLecturer(Lecturer $lecturer): void
    {
        $this->sync('lecturer', $lecturer->id, $lecturer->fingerprint_id);
    }

    private function sync(string $type, int $userId, ?string $fingerprintId): void
    {
        if (! filled($fingerprintId)) {
            return;
        }

        $existing = BiometricEnrollment::query()
            ->where('user_type', $type)
            ->where('user_id', $userId)
            ->first();

        $fingerprintChanged = ! $existing || $existing->fingerprint_id !== $fingerprintId;

        BiometricEnrollment::query()->updateOrCreate(
            ['user_type' => $type, 'user_id' => $userId],
            [
                'fingerprint_id' => $fingerprintId,
                'status' => $existing && ! $fingerprintChanged
                    ? $existing->status
                    : 'not_enrolled',
                'enrolled_at' => $existing && ! $fingerprintChanged
                    ? $existing->enrolled_at
                    : null,
                'device_id' => $existing?->device_id,
                'sync_status' => $fingerprintChanged ? 'pending' : ($existing->sync_status ?? 'pending'),
                'sync_requested_at' => $fingerprintChanged ? now() : ($existing->sync_requested_at ?? now()),
                'sync_message' => $fingerprintChanged ? 'Menunggu dikirim ke perangkat.' : $existing?->sync_message,
            ]
        );
    }
}
