<?php

namespace App\Services;

use App\Models\BiometricEnrollment;
use App\Models\Lecturer;
use App\Models\Student;

class FingerprintIdGenerator
{
    /**
     * @param array<int, string> $reserved
     */
    public function generate(array $reserved = []): string
    {
        for ($number = 1; $number <= 999999; $number++) {
            $candidate = str_pad((string) $number, 6, '0', STR_PAD_LEFT);

            if (! in_array($candidate, $reserved, true) && ! $this->exists($candidate)) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Fingerprint ID sudah mencapai batas maksimal.');
    }

    private function exists(string $fingerprintId): bool
    {
        return Student::where('fingerprint_id', $fingerprintId)->exists()
            || Lecturer::where('fingerprint_id', $fingerprintId)->exists()
            || BiometricEnrollment::where('fingerprint_id', $fingerprintId)->exists();
    }
}
