<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BiometricEnrollment;
use App\Models\Device;
use App\Models\DoorAccessLog;
use App\Models\FingerprintScanLog;
use App\Models\Lecturer;
use App\Models\Schedule;
use App\Models\ScheduleMeeting;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceValidationService
{
    public function validateScan(string $DeviceCode, string $fingerprintId, Carbon|string $scanTime, ?array $rawPayload = null): array
    {
        $scanAt = $scanTime instanceof Carbon ? $scanTime : Carbon::parse($scanTime);
        $Device = Device::query()->with('room')->where('device_code', $DeviceCode)->first();

        if (! $Device) {
            return $this->response('rejected', 'Device tidak ditemukan', false);
        }

        $scanLog = FingerprintScanLog::query()->create([
            'device_id' => $Device->id,
            'fingerprint_id' => $fingerprintId,
            'scan_time' => $scanAt,
            'raw_payload' => $rawPayload,
            'process_status' => 'pending',
        ]);

        return DB::transaction(function () use ($Device, $fingerprintId, $scanAt, $scanLog) {
            $identity = $this->findIdentity($fingerprintId);

            if (! $identity) {
                $scanLog->update([
                    'process_status' => 'ignored',
                    'message' => 'Fingerprint ID tidak dikenal.',
                ]);

                $this->createDoorLog($Device, null, null, null, $scanAt, false, 'denied', 'Fingerprint ID tidak dikenal.');

                return $this->response('rejected', 'Fingerprint tidak terdaftar', false);
            }

            $Schedule = $this->activeSchedule($Device, $scanAt);

            if (! $Schedule) {
                $this->createRejectedAttendance($Device, null, null, $identity, $fingerprintId, $scanAt, 'no_active_schedule', 'Tidak ada schedules aktif.');

                $scanLog->update([
                    'process_status' => 'processed',
                    'message' => 'Tidak ada schedules aktif.',
                ]);

                $this->createDoorLog($Device, null, $identity['type'], $identity['model']->id, $scanAt, false, 'denied', 'Tidak ada schedules aktif.');

                return $this->response('rejected', 'Tidak ada schedules aktif atau classes tidak sesuai', false);
            }

            if ($identity['type'] === 'lecturer') {
                return $this->handleLecturerScan($Device, $Schedule, $identity, $fingerprintId, $scanAt, $scanLog);
            }

            return $this->handleStudentScan($Device, $Schedule, $identity, $fingerprintId, $scanAt, $scanLog);
        });
    }

    private function handleLecturerScan(Device $Device, Schedule $Schedule, array $identity, string $fingerprintId, Carbon $scanAt, FingerprintScanLog $scanLog): array
    {
        if ((int) $Schedule->lecturer_id !== (int) $identity['model']->id) {
            $this->createRejectedAttendance($Device, $Schedule, null, $identity, $fingerprintId, $scanAt, 'outside_schedule', 'Lecturer tidak sesuai schedules.');

            $scanLog->update([
                'process_status' => 'processed',
                'message' => 'Lecturer tidak sesuai schedules.',
            ]);

            $this->createDoorLog($Device, $Schedule, 'lecturer', $identity['model']->id, $scanAt, false, 'denied', 'Lecturer tidak sesuai schedules.');

            return $this->response('rejected', 'Tidak ada schedules aktif atau classes tidak sesuai', false);
        }

        $meeting = $this->meetingFor($Schedule, $scanAt);
        $Attendance = $this->firstOrUpdateAttendance($meeting, $Schedule, $Device, $identity, $fingerprintId, $scanAt, 'valid', 'Lecturer valid.');

        $meeting->update([
            'lecturer_attendance_id' => $Attendance->id,
            'status' => 'ongoing',
            'opened_at' => $meeting->opened_at ?? $scanAt,
        ]);

        $scanLog->update([
            'process_status' => 'processed',
            'message' => 'Lecturer valid.',
        ]);

        $this->createDoorLog($Device, $Schedule, 'lecturer', $identity['model']->id, $scanAt, true, 'granted', 'Lecturer valid, pintu dibuka.');

        return $this->response('accepted', 'Lecturer valid, pintu dibuka', true);
    }

    private function handleStudentScan(Device $Device, Schedule $Schedule, array $identity, string $fingerprintId, Carbon $scanAt, FingerprintScanLog $scanLog): array
    {
        if ((int) $identity['model']->class_id !== (int) $Schedule->class_id) {
            $this->createRejectedAttendance($Device, $Schedule, null, $identity, $fingerprintId, $scanAt, 'wrong_class', 'Kelas students tidak sesuai schedules.');

            $scanLog->update([
                'process_status' => 'processed',
                'message' => 'Kelas students tidak sesuai schedules.',
            ]);

            $this->createDoorLog($Device, $Schedule, 'student', $identity['model']->id, $scanAt, false, 'denied', 'Kelas students tidak sesuai schedules.');

            return $this->response('rejected', 'Tidak ada schedules aktif atau classes tidak sesuai', false);
        }

        $meeting = $this->meetingFor($Schedule, $scanAt);
        $LecturerPresent = (bool) $meeting->lecturer_attendance_id;

        if (! $LecturerPresent) {
            $Attendance = $this->firstOrUpdateAttendance($meeting, $Schedule, $Device, $identity, $fingerprintId, $scanAt, 'lecturer_not_present', 'Lecturer belum hadir.');

            $scanLog->update([
                'process_status' => 'processed',
                'message' => 'Attendance students tercatat, lecturers belum hadir.',
            ]);

            $this->createDoorLog($Device, $Schedule, 'student', $identity['model']->id, $scanAt, false, 'denied', 'Lecturer belum hadir.');

            return [
                'status' => 'accepted',
                'message' => $Attendance->wasRecentlyCreated ? 'Attendance students berhasil, lecturers belum hadir' : 'Attendance students sudah tercatat, lecturers belum hadir',
                'open_door' => false,
            ];
        }

        $Attendance = $this->firstOrUpdateAttendance($meeting, $Schedule, $Device, $identity, $fingerprintId, $scanAt, 'valid', 'Student valid.');
        $openDoor = $this->settingBoolean('allow_student_open_door', false);

        $scanLog->update([
            'process_status' => 'processed',
            'message' => 'Student valid.',
        ]);

        $this->createDoorLog($Device, $Schedule, 'student', $identity['model']->id, $scanAt, $openDoor, $openDoor ? 'granted' : 'denied', $openDoor ? 'Student valid, pintu dibuka.' : 'Student valid, pintu tidak dibuka oleh pengaturan.');

        return [
            'status' => 'accepted',
            'message' => $Attendance->wasRecentlyCreated ? 'Attendance students berhasil' : 'Attendance students sudah tercatat',
            'open_door' => $openDoor,
            'unlock_duration' => $openDoor ? $this->unlockDuration() : 0,
        ];
    }

    private function activeSchedule(Device $Device, Carbon $scanAt): ?Schedule
    {
        return Schedule::query()
            ->with(['academicYear', 'lecturer', 'class', 'subject', 'room'])
            ->where('room_id', $Device->room_id)
            ->where('day', strtolower($scanAt->englishDayOfWeek))
            ->where('status', 'active')
            ->where('start_time', '<=', $scanAt->format('H:i:s'))
            ->where('end_time', '>=', $scanAt->format('H:i:s'))
            ->first();
    }

    private function findIdentity(string $fingerprintId): ?array
    {
        $enrollment = BiometricEnrollment::query()
            ->where('fingerprint_id', $fingerprintId)
            ->where('status', 'enrolled')
            ->first();

        if ($enrollment?->user_type === 'lecturer') {
            $Lecturer = Lecturer::query()->whereKey($enrollment->user_id)->where('status', 'active')->first();

            return $Lecturer ? ['type' => 'lecturer', 'model' => $Lecturer] : null;
        }

        if ($enrollment?->user_type === 'student') {
            $Student = Student::query()->whereKey($enrollment->user_id)->where('status', 'active')->first();

            return $Student ? ['type' => 'student', 'model' => $Student] : null;
        }

        $Lecturer = Lecturer::query()
            ->where('fingerprint_id', $fingerprintId)
            ->where('status', 'active')
            ->first();

        if ($Lecturer) {
            return ['type' => 'lecturer', 'model' => $Lecturer];
        }

        $Student = Student::query()
            ->where('fingerprint_id', $fingerprintId)
            ->where('status', 'active')
            ->first();

        return $Student ? ['type' => 'student', 'model' => $Student] : null;
    }

    private function meetingFor(Schedule $Schedule, Carbon $scanAt): ScheduleMeeting
    {
        $date = $scanAt->toDateString();
        $meeting = ScheduleMeeting::query()
            ->where('schedule_id', $Schedule->id)
            ->where('meeting_date', $date)
            ->first();

        if ($meeting) {
            return $meeting;
        }

        $meetingNumber = ScheduleMeeting::query()
            ->where('schedule_id', $Schedule->id)
            ->whereDate('meeting_date', '<=', $date)
            ->count() + 1;

        return ScheduleMeeting::query()->create([
            'schedule_id' => $Schedule->id,
            'meeting_date' => $date,
            'meeting_number' => $meetingNumber,
            'status' => 'ongoing',
            'opened_at' => $scanAt,
        ]);
    }

    private function firstOrUpdateAttendance(ScheduleMeeting $meeting, Schedule $Schedule, Device $Device, array $identity, string $fingerprintId, Carbon $scanAt, string $validationStatus, string $notes): Attendance
    {
        $Attendance = Attendance::query()
            ->where('schedule_meeting_id', $meeting->id)
            ->where('user_type', $identity['type'])
            ->where('user_id', $identity['model']->id)
            ->first();

        if ($Attendance) {
            if ($Attendance->validation_status !== 'valid' && $validationStatus === 'valid') {
                $Attendance->update([
                    'attendance_status' => $this->attendanceStatus($Schedule, $scanAt),
                    'validation_status' => 'valid',
                    'notes' => $notes,
                ]);
            }

            return $Attendance;
        }

        return Attendance::query()->create([
            'schedule_id' => $Schedule->id,
            'schedule_meeting_id' => $meeting->id,
            'user_type' => $identity['type'],
            'user_id' => $identity['model']->id,
            'device_id' => $Device->id,
            'fingerprint_id' => $fingerprintId,
            'attendance_time' => $scanAt,
            'attendance_status' => $validationStatus === 'valid' ? $this->attendanceStatus($Schedule, $scanAt) : 'rejected',
            'validation_status' => $validationStatus,
            'notes' => $notes,
        ]);
    }

    private function createRejectedAttendance(Device $Device, ?Schedule $Schedule, ?ScheduleMeeting $meeting, array $identity, string $fingerprintId, Carbon $scanAt, string $validationStatus, string $notes): Attendance
    {
        return Attendance::query()->create([
            'schedule_id' => $Schedule?->id,
            'schedule_meeting_id' => $meeting?->id,
            'user_type' => $identity['type'],
            'user_id' => $identity['model']->id,
            'device_id' => $Device->id,
            'fingerprint_id' => $fingerprintId,
            'attendance_time' => $scanAt,
            'attendance_status' => 'rejected',
            'validation_status' => $validationStatus,
            'notes' => $notes,
        ]);
    }

    private function createDoorLog(Device $Device, ?Schedule $Schedule, ?string $UserType, ?int $UserId, Carbon $accessAt, bool $openDoor, string $accessStatus, string $reason): DoorAccessLog
    {
        return DoorAccessLog::query()->create([
            'device_id' => $Device->id,
            'room_id' => $Device->room_id,
            'schedule_id' => $Schedule?->id,
            'user_type' => $UserType,
            'user_id' => $UserId,
            'access_time' => $accessAt,
            'access_status' => $accessStatus,
            'open_door' => $openDoor,
            'method' => 'fingerprint',
            'reason' => $reason,
        ]);
    }

    private function AttendanceStatus(Schedule $Schedule, Carbon $scanAt): string
    {
        $startAt = Carbon::parse($scanAt->toDateString().' '.$Schedule->start_time);

        return $scanAt->greaterThan($startAt) ? 'late' : 'present';
    }

    private function settingBoolean(string $key, bool $default): bool
    {
        $value = DB::table('system_settings')->where('key', $key)->value('value');

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function unlockDuration(): int
    {
        return (int) (DB::table('system_settings')->where('key', 'door_unlock_duration')->value('value') ?: 5);
    }

    private function response(string $status, string $message, bool $openDoor): array
    {
        return [
            'status' => $status,
            'message' => $message,
            'open_door' => $openDoor,
            'unlock_duration' => $openDoor ? $this->unlockDuration() : 0,
        ];
    }
}








