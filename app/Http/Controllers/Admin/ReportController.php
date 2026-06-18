<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\DoorAccessLog;
use App\Models\Lecturer;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\ScheduleMeeting;
use App\Models\Student;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function attendanceReport(Request $request): View
    {
        $reportType = $request->input('report_type', 'class');
        $reportType = in_array($reportType, ['all', 'class', 'subject', 'lecturer'], true) ? $reportType : 'class';
        [$selectedMonth, $monthStart, $monthEnd] = $this->monthRange($request);

        $rows = collect();
        $meetingsCount = 0;
        $date = $request->input('date') ?: now()->toDateString();
        $stats = [
            'primary' => 0,
            'secondary' => 0,
            'present' => 0,
            'issue' => 0,
        ];

        if ($reportType === 'all') {
            $rows = Attendance::query()
                ->with(['schedule.class', 'schedule.subject', 'schedule.room', 'lecturer', 'student', 'device'])
                ->whereBetween('attendance_time', [$monthStart, $monthEnd])
                ->latest('attendance_time')
                ->get();

            $stats = [
                'primary' => $rows->count(),
                'secondary' => $rows->where('user_type', 'student')->count(),
                'present' => $rows->where('user_type', 'lecturer')->count(),
                'issue' => $rows->where('attendance_status', 'rejected')->count(),
            ];
        }

        if ($reportType === 'class') {
            $classId = $request->integer('class_id') ?: null;
            $subjectId = $request->integer('subject_id') ?: null;
            $startDate = $request->input('start_date') ?: ($request->filled('month') ? $monthStart->toDateString() : null);
            $endDate = $request->input('end_date') ?: ($request->filled('month') ? $monthEnd->toDateString() : null);

            if ($classId) {
                $students = Student::query()->where('class_id', $classId)->orderBy('name')->get();
                $scheduleIds = Schedule::query()
                    ->where('class_id', $classId)
                    ->when($subjectId, fn (Builder $query) => $query->where('subject_id', $subjectId))
                    ->pluck('id');

                $meetings = ScheduleMeeting::query()
                    ->whereIn('schedule_id', $scheduleIds)
                    ->when($startDate, fn (Builder $query) => $query->whereDate('meeting_date', '>=', $startDate))
                    ->when($endDate, fn (Builder $query) => $query->whereDate('meeting_date', '<=', $endDate))
                    ->orderBy('meeting_date')
                    ->get();

                $attendances = Attendance::query()
                    ->whereIn('schedule_meeting_id', $meetings->pluck('id'))
                    ->where('user_type', 'student')
                    ->get()
                    ->groupBy('user_id');

                $rows = $students->map(function (Student $student) use ($attendances, $meetings) {
                    $records = $attendances->get($student->id, collect());
                    $present = $records->whereIn('attendance_status', ['present', 'late'])->count();
                    $rejected = $records->where('attendance_status', 'rejected')->count();
                    $total = $meetings->count();

                    return [
                        'student' => $student,
                        'present' => $present,
                        'rejected' => $rejected,
                        'absent' => max($total - $present - $rejected, 0),
                        'total' => $total,
                        'status' => $total === 0 ? 'Belum ada pertemuan' : $present.'/'.$total.' hadir',
                    ];
                });

                $meetingsCount = $meetings->count();
                $stats = [
                    'primary' => $students->count(),
                    'secondary' => $meetingsCount,
                    'present' => $rows->sum('present'),
                    'issue' => $rows->sum('rejected'),
                ];
            }
        }

        if ($reportType === 'subject') {
            $academicYearId = $request->integer('academic_year_id') ?: null;
            $subjectId = $request->integer('subject_id') ?: null;
            $classId = $request->integer('class_id') ?: null;

            if ($subjectId) {
                $rows = Schedule::query()
                    ->with(['class', 'subject', 'academicYear'])
                    ->where('subject_id', $subjectId)
                    ->when($academicYearId, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
                    ->when($classId, fn (Builder $query) => $query->where('class_id', $classId))
                    ->get()
                    ->map(fn (Schedule $schedule) => $this->subjectReportRow($schedule, $selectedMonth));

                $stats = [
                    'primary' => $rows->count(),
                    'secondary' => $rows->sum('meetings'),
                    'present' => $rows->sum('present'),
                    'issue' => $rows->sum('expected'),
                ];
            }
        }

        if ($reportType === 'lecturer') {
            $lecturerId = $request->integer('lecturer_id') ?: null;

            if ($lecturerId) {
                $day = strtolower(Carbon::parse($date)->englishDayOfWeek);
                $rows = Schedule::query()
                    ->with(['class', 'subject', 'room'])
                    ->where('lecturer_id', $lecturerId)
                    ->where('day', $day)
                    ->get()
                    ->map(function (Schedule $schedule) use ($date, $lecturerId) {
                        $meeting = ScheduleMeeting::query()
                            ->where('schedule_id', $schedule->id)
                            ->whereDate('meeting_date', $date)
                            ->first();
                        $attendance = $meeting
                            ? Attendance::query()
                                ->where('schedule_meeting_id', $meeting->id)
                                ->where('user_type', 'lecturer')
                                ->where('user_id', $lecturerId)
                                ->first()
                            : null;

                        return [
                            'schedule' => $schedule,
                            'scan_time' => $attendance?->attendance_time?->format('H:i') ?? '-',
                            'status' => $attendance?->attendance_status ?? 'belum hadir',
                        ];
                    });

                $stats = [
                    'primary' => $rows->count(),
                    'secondary' => $rows->whereIn('status', ['present', 'late'])->count(),
                    'present' => $rows->where('status', 'late')->count(),
                    'issue' => $rows->where('status', 'belum hadir')->count(),
                ];
            }
        }

        return view('admin.laporan.kehadiran', [
            'reportType' => $reportType,
            'selectedMonth' => $selectedMonth,
            'date' => $date,
            'rows' => $rows,
            'meetingsCount' => $meetingsCount,
            'stats' => $stats,
            'classes' => AcademicClass::orderBy('code')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('year')->get(),
            'lecturers' => Lecturer::orderBy('name')->get(),
        ]);
    }

    public function downloadAttendanceReport(Request $request): StreamedResponse
    {
        return match ($request->input('report_type', 'class')) {
            'all' => $this->downloadAllAttendanceReport($request),
            'subject' => $this->downloadSubjectReport($request),
            'lecturer' => $this->downloadLecturerReport($request),
            default => $this->downloadClassReport($request),
        };
    }

    public function classReport(Request $request): View
    {
        $classId = $request->integer('class_id') ?: null;
        $subjectId = $request->integer('subject_id') ?: null;
        [$selectedMonth, $monthStart, $monthEnd] = $this->monthRange($request);
        $startDate = $request->input('start_date') ?: ($request->filled('month') ? $monthStart->toDateString() : null);
        $endDate = $request->input('end_date') ?: ($request->filled('month') ? $monthEnd->toDateString() : null);

        $students = collect();
        $meetings = collect();
        $rows = collect();

        if ($classId) {
            $students = Student::query()->where('class_id', $classId)->orderBy('name')->get();
            $scheduleIds = Schedule::query()
                ->where('class_id', $classId)
                ->when($subjectId, fn (Builder $query) => $query->where('subject_id', $subjectId))
                ->pluck('id');

            $meetings = ScheduleMeeting::query()
                ->whereIn('schedule_id', $scheduleIds)
                ->when($startDate, fn (Builder $query) => $query->whereDate('meeting_date', '>=', $startDate))
                ->when($endDate, fn (Builder $query) => $query->whereDate('meeting_date', '<=', $endDate))
                ->orderBy('meeting_date')
                ->get();

            $attendances = Attendance::query()
                ->whereIn('schedule_meeting_id', $meetings->pluck('id'))
                ->where('user_type', 'student')
                ->get()
                ->groupBy('user_id');

            $rows = $students->map(function (Student $student) use ($attendances, $meetings) {
                $records = $attendances->get($student->id, collect());
                $present = $records->whereIn('attendance_status', ['present', 'late'])->count();
                $rejected = $records->where('attendance_status', 'rejected')->count();
                $total = max($meetings->count(), 0);

                return [
                    'student' => $student,
                    'present' => $present,
                    'rejected' => $rejected,
                    'absent' => max($total - $present - $rejected, 0),
                    'total' => $total,
                    'status' => $total === 0 ? 'Belum ada pertemuan' : $present.'/'.$total.' hadir',
                ];
            });
        }

        return view('admin.laporan.per-kelas', [
            'classes' => AcademicClass::orderBy('code')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'rows' => $rows,
            'meetingsCount' => $meetings->count(),
            'stats' => [
                'students' => $students->count(),
                'meetings' => $meetings->count(),
                'present' => $rows->sum('present'),
                'rejected' => $rows->sum('rejected'),
            ],
            'selectedMonth' => $selectedMonth,
        ]);
    }

    public function subjectReport(Request $request): View
    {
        $academicYearId = $request->integer('academic_year_id') ?: null;
        $subjectId = $request->integer('subject_id') ?: null;
        $classId = $request->integer('class_id') ?: null;
        [$selectedMonth, $monthStart, $monthEnd] = $this->monthRange($request);
        $rows = collect();

        if ($subjectId) {
            $schedules = Schedule::query()
                ->with(['class', 'subject', 'academicYear'])
                ->where('subject_id', $subjectId)
                ->when($academicYearId, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
                ->when($classId, fn (Builder $query) => $query->where('class_id', $classId))
                ->get();

            $rows = $schedules->map(function (Schedule $schedule) {
                return $this->subjectReportRow($schedule, request()->filled('month') ? request()->input('month') : null);
            });
        }

        return view('admin.laporan.per-mata-kuliah', [
            'academicYears' => AcademicYear::orderByDesc('year')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'classes' => AcademicClass::orderBy('code')->get(),
            'rows' => $rows,
            'stats' => [
                'schedules' => $rows->count(),
                'meetings' => $rows->sum('meetings'),
                'present' => $rows->sum('present'),
                'expected' => $rows->sum('expected'),
            ],
            'selectedMonth' => $selectedMonth,
        ]);
    }

    private function subjectReportRow(Schedule $schedule, ?string $month = null): array
    {
        $meetingQuery = ScheduleMeeting::query()->where('schedule_id', $schedule->id);

        if ($month) {
            [$selectedMonth, $monthStart, $monthEnd] = $this->monthRangeValue($month);

            $meetingQuery
                ->whereDate('meeting_date', '>=', $monthStart)
                ->whereDate('meeting_date', '<=', $monthEnd);
        }

        $meetingIds = $meetingQuery->pluck('id');
                $studentCount = Student::query()->where('class_id', $schedule->class_id)->count();
                $expected = $studentCount * $meetingIds->count();
                $present = Attendance::query()
                    ->whereIn('schedule_meeting_id', $meetingIds)
                    ->where('user_type', 'student')
                    ->whereIn('attendance_status', ['present', 'late'])
                    ->count();

                return [
                    'schedule' => $schedule,
                    'meetings' => $meetingIds->count(),
                    'present' => $present,
                    'expected' => $expected,
                    'percentage' => $expected > 0 ? round(($present / $expected) * 100, 2) : 0,
                ];
    }

    public function lecturerReport(Request $request): View
    {
        $lecturerId = $request->integer('lecturer_id') ?: null;
        $date = $request->input('date') ?: now()->toDateString();
        [$selectedMonth] = $this->monthRange($request);
        $rows = collect();

        if ($lecturerId) {
            $day = strtolower(Carbon::parse($date)->englishDayOfWeek);
            $rows = Schedule::query()
                ->with(['class', 'subject', 'room'])
                ->where('lecturer_id', $lecturerId)
                ->where('day', $day)
                ->get()
                ->map(function (Schedule $schedule) use ($date, $lecturerId) {
                    $meeting = ScheduleMeeting::query()
                        ->where('schedule_id', $schedule->id)
                        ->whereDate('meeting_date', $date)
                        ->first();
                    $attendance = $meeting
                        ? Attendance::query()
                            ->where('schedule_meeting_id', $meeting->id)
                            ->where('user_type', 'lecturer')
                            ->where('user_id', $lecturerId)
                            ->first()
                        : null;

                    return [
                        'schedule' => $schedule,
                        'scan_time' => $attendance?->attendance_time?->format('H:i') ?? '-',
                        'status' => $attendance?->attendance_status ?? 'belum hadir',
                    ];
                });
        }

        return view('admin.laporan.per-dosen', [
            'lecturers' => Lecturer::orderBy('name')->get(),
            'rows' => $rows,
            'date' => $date,
            'stats' => [
                'schedules' => $rows->count(),
                'present' => $rows->whereIn('status', ['present', 'late'])->count(),
                'late' => $rows->where('status', 'late')->count(),
                'absent' => $rows->where('status', 'belum hadir')->count(),
            ],
            'selectedMonth' => $selectedMonth,
        ]);
    }

    public function deniedAccessReport(Request $request): View
    {
        $date = $request->input('date');
        $roomId = $request->integer('room_id') ?: null;
        $reason = $request->input('reason');
        [$selectedMonth, $monthStart, $monthEnd] = $this->monthRange($request);

        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $query = DoorAccessLog::query()
            ->with(['room', 'device', 'lecturer', 'student', 'user'])
            ->whereIn('access_status', ['denied', 'failed'])
            ->when($date, fn (Builder $query) => $query->whereDate('access_time', $date))
            ->when(!$date && $request->filled('month'), fn (Builder $query) => $query->whereBetween('access_time', [$monthStart, $monthEnd]))
            ->when($roomId, fn (Builder $query) => $query->where('room_id', $roomId))
            ->when($reason, fn (Builder $query) => $query->where('reason', 'like', '%'.$reason.'%'));

        $statsQuery = clone $query;

        $logs = $query
            ->latest('access_time')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.laporan.akses-ditolak', [
            'rooms' => Room::orderBy('name')->get(),
            'logs' => $logs,
            'perPage' => $perPage,
            'stats' => [
                'total' => (clone $statsQuery)->count(),
                'denied' => (clone $statsQuery)->where('access_status', 'denied')->count(),
                'failed' => (clone $statsQuery)->where('access_status', 'failed')->count(),
                'today' => DoorAccessLog::whereIn('access_status', ['denied', 'failed'])->whereDate('access_time', today())->count(),
            ],
            'selectedMonth' => $selectedMonth,
        ]);
    }

    public function downloadClassReport(Request $request): StreamedResponse
    {
        [$selectedMonth, $monthStart, $monthEnd] = $this->monthRange($request);
        $classId = $request->integer('class_id') ?: null;
        $subjectId = $request->integer('subject_id') ?: null;
        $class = $classId ? AcademicClass::find($classId) : null;
        $students = collect();
        $meetings = collect();
        $rows = collect();

        if ($classId) {
            $students = Student::query()->where('class_id', $classId)->orderBy('name')->get();
            $scheduleIds = Schedule::query()
                ->where('class_id', $classId)
                ->when($subjectId, fn (Builder $query) => $query->where('subject_id', $subjectId))
                ->pluck('id');

            $meetings = ScheduleMeeting::query()
                ->whereIn('schedule_id', $scheduleIds)
                ->whereBetween('meeting_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->orderBy('meeting_date')
                ->get();

            $attendances = Attendance::query()
                ->whereIn('schedule_meeting_id', $meetings->pluck('id'))
                ->where('user_type', 'student')
                ->get()
                ->groupBy('user_id');

            $rows = $students->map(function (Student $student) use ($attendances, $meetings) {
                $records = $attendances->get($student->id, collect());
                $present = $records->whereIn('attendance_status', ['present', 'late'])->count();
                $rejected = $records->where('attendance_status', 'rejected')->count();
                $total = $meetings->count();

                return [
                    $student->nim,
                    $student->name,
                    $total,
                    $present,
                    $rejected,
                    max($total - $present - $rejected, 0),
                    $total === 0 ? 'Belum ada pertemuan' : "{$present}/{$total} hadir",
                ];
            });
        }

        return $this->downloadCsv("laporan_per_kelas_{$selectedMonth}.csv", [
            ['Bulan', $selectedMonth],
            ['Kelas', $class ? "{$class->code} - {$class->name}" : '-'],
            [],
            ['NIM', 'Mahasiswa', 'Pertemuan', 'Hadir', 'Ditolak', 'Belum Hadir', 'Status'],
        ], $rows);
    }

    public function downloadAllAttendanceReport(Request $request): StreamedResponse
    {
        [$selectedMonth, $monthStart, $monthEnd] = $this->monthRange($request);

        $rows = Attendance::query()
            ->with(['schedule.class', 'schedule.subject', 'schedule.room', 'lecturer', 'student', 'device'])
            ->whereBetween('attendance_time', [$monthStart, $monthEnd])
            ->latest('attendance_time')
            ->get()
            ->map(function (Attendance $attendance) {
                return [
                    $attendance->attendance_time?->format('d M Y H:i'),
                    $attendance->user_type === 'lecturer' ? 'Dosen' : 'Mahasiswa',
                    $attendance->user_name,
                    $attendance->fingerprint_id,
                    $attendance->schedule?->subject?->name ?? '-',
                    $attendance->schedule?->class?->code ?? '-',
                    $attendance->schedule?->room?->name ?? '-',
                    $attendance->device?->name ?? '-',
                    match ($attendance->attendance_status) {
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        'rejected' => 'Ditolak',
                        default => $attendance->attendance_status,
                    },
                    ucwords(str_replace('_', ' ', $attendance->validation_status)),
                    $attendance->notes ?: '-',
                ];
            });

        return $this->downloadCsv("laporan_semua_kehadiran_{$selectedMonth}.csv", [
            ['Bulan', $selectedMonth],
            [],
            ['Waktu', 'Peran', 'Nama', 'Fingerprint ID', 'Mata Kuliah', 'Kelas', 'Ruangan', 'Perangkat', 'Status Absensi', 'Validasi', 'Catatan'],
        ], $rows);
    }

    public function downloadSubjectReport(Request $request): StreamedResponse
    {
        [$selectedMonth] = $this->monthRange($request);
        $academicYearId = $request->integer('academic_year_id') ?: null;
        $subjectId = $request->integer('subject_id') ?: null;
        $classId = $request->integer('class_id') ?: null;

        $rows = collect();

        if ($subjectId) {
            $rows = Schedule::query()
                ->with(['class', 'subject', 'academicYear'])
                ->where('subject_id', $subjectId)
                ->when($academicYearId, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
                ->when($classId, fn (Builder $query) => $query->where('class_id', $classId))
                ->get()
                ->map(function (Schedule $schedule) use ($selectedMonth) {
                    $row = $this->subjectReportRow($schedule, $selectedMonth);

                    return [
                        $row['schedule']->subject->name,
                        $row['schedule']->class->code,
                        $row['schedule']->academicYear->year,
                        $row['meetings'],
                        $row['present'],
                        $row['expected'],
                        str_replace('.', ',', (string) $row['percentage']).'%',
                    ];
                });
        }

        return $this->downloadCsv("laporan_per_mata_kuliah_{$selectedMonth}.csv", [
            ['Bulan', $selectedMonth],
            [],
            ['Mata Kuliah', 'Kelas', 'Tahun Akademik', 'Pertemuan', 'Hadir', 'Target', 'Persentase'],
        ], $rows);
    }

    public function downloadLecturerReport(Request $request): StreamedResponse
    {
        [$selectedMonth, $monthStart, $monthEnd] = $this->monthRange($request);
        $lecturerId = $request->integer('lecturer_id') ?: null;
        $lecturer = $lecturerId ? Lecturer::find($lecturerId) : null;
        $rows = collect();

        if ($lecturerId) {
            $meetings = ScheduleMeeting::query()
                ->with(['schedule.class', 'schedule.subject', 'schedule.room'])
                ->whereBetween('meeting_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->whereHas('schedule', fn (Builder $query) => $query->where('lecturer_id', $lecturerId))
                ->orderBy('meeting_date')
                ->get();

            $rows = $meetings->map(function (ScheduleMeeting $meeting) use ($lecturerId) {
                $attendance = Attendance::query()
                    ->where('schedule_meeting_id', $meeting->id)
                    ->where('user_type', 'lecturer')
                    ->where('user_id', $lecturerId)
                    ->first();

                return [
                    $meeting->meeting_date?->format('d M Y'),
                    $meeting->schedule->time_range,
                    $meeting->schedule->subject->name,
                    $meeting->schedule->class->code,
                    $meeting->schedule->room->name,
                    $attendance?->attendance_time?->format('H:i') ?? '-',
                    match ($attendance?->attendance_status) {
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        default => 'Belum Hadir',
                    },
                ];
            });
        }

        return $this->downloadCsv("laporan_per_dosen_{$selectedMonth}.csv", [
            ['Bulan', $selectedMonth],
            ['Dosen', $lecturer?->name ?? '-'],
            [],
            ['Tanggal', 'Jadwal', 'Mata Kuliah', 'Kelas', 'Ruangan', 'Jam Scan', 'Status'],
        ], $rows);
    }

    public function downloadDeniedAccessReport(Request $request): StreamedResponse
    {
        [$selectedMonth, $monthStart, $monthEnd] = $this->monthRange($request);
        $roomId = $request->integer('room_id') ?: null;
        $reason = $request->input('reason');

        $rows = DoorAccessLog::query()
            ->with(['room', 'device', 'lecturer', 'student', 'user'])
            ->whereIn('access_status', ['denied', 'failed'])
            ->whereBetween('access_time', [$monthStart, $monthEnd])
            ->when($roomId, fn (Builder $query) => $query->where('room_id', $roomId))
            ->when($reason, fn (Builder $query) => $query->where('reason', 'like', '%'.$reason.'%'))
            ->latest('access_time')
            ->get()
            ->map(fn (DoorAccessLog $log) => [
                $log->access_user_name,
                $log->access_fingerprint_id,
                $log->room?->name ?? '-',
                $log->access_time?->format('d M Y H:i'),
                $log->access_status === 'failed' ? 'Gagal' : 'Ditolak',
                $log->reason ?: '-',
            ]);

        return $this->downloadCsv("laporan_akses_ditolak_{$selectedMonth}.csv", [
            ['Bulan', $selectedMonth],
            [],
            ['User', 'Fingerprint ID', 'Ruangan', 'Waktu Scan', 'Status', 'Alasan'],
        ], $rows);
    }

    private function monthRange(Request $request): array
    {
        return $this->monthRangeValue($request->input('month') ?: now()->format('Y-m'));
    }

    private function monthRangeValue(string $month): array
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        return [$start->format('Y-m'), $start->copy(), $start->copy()->endOfMonth()];
    }

    private function downloadCsv(string $filename, array $headerRows, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headerRows, $rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            foreach ($headerRows as $row) {
                fputcsv($handle, $row);
            }

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}





