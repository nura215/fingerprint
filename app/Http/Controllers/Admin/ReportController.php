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

class ReportController extends Controller
{
    public function classReport(Request $request): View
    {
        $classId = $request->integer('class_id') ?: null;
        $subjectId = $request->integer('subject_id') ?: null;
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

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
        ]);
    }

    public function subjectReport(Request $request): View
    {
        $academicYearId = $request->integer('academic_year_id') ?: null;
        $subjectId = $request->integer('subject_id') ?: null;
        $classId = $request->integer('class_id') ?: null;
        $rows = collect();

        if ($subjectId) {
            $schedules = Schedule::query()
                ->with(['class', 'subject', 'academicYear'])
                ->where('subject_id', $subjectId)
                ->when($academicYearId, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
                ->when($classId, fn (Builder $query) => $query->where('class_id', $classId))
                ->get();

            $rows = $schedules->map(function (Schedule $schedule) {
                $meetingIds = ScheduleMeeting::query()->where('schedule_id', $schedule->id)->pluck('id');
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
            });
        }

        return view('admin.laporan.per-mata-kuliah', [
            'academicYears' => AcademicYear::orderByDesc('year')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'classes' => AcademicClass::orderBy('code')->get(),
            'rows' => $rows,
        ]);
    }

    public function lecturerReport(Request $request): View
    {
        $lecturerId = $request->integer('lecturer_id') ?: null;
        $date = $request->input('date') ?: now()->toDateString();
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
        ]);
    }

    public function deniedAccessReport(Request $request): View
    {
        $date = $request->input('date');
        $roomId = $request->integer('room_id') ?: null;
        $reason = $request->input('reason');

        $logs = DoorAccessLog::query()
            ->with(['room', 'device', 'lecturer', 'student', 'user'])
            ->whereIn('access_status', ['denied', 'failed'])
            ->when($date, fn (Builder $query) => $query->whereDate('access_time', $date))
            ->when($roomId, fn (Builder $query) => $query->where('room_id', $roomId))
            ->when($reason, fn (Builder $query) => $query->where('reason', 'like', '%'.$reason.'%'))
            ->latest('access_time')
            ->paginate(15)
            ->withQueryString();

        return view('admin.laporan.akses-ditolak', [
            'rooms' => Room::orderBy('name')->get(),
            'logs' => $logs,
        ]);
    }
}





