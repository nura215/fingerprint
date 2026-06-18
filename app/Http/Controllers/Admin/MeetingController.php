<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoorAccessLog;
use App\Models\FingerprintScanLog;
use App\Models\ScheduleMeeting;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetingController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $query = ScheduleMeeting::query()
            ->with(['schedule.class', 'schedule.subject', 'schedule.room', 'schedule.lecturer'])
            ->withCount([
                'attendances as student_attendances_count' => fn (Builder $query) => $query->where('user_type', 'student'),
                'attendances as valid_attendances_count' => fn (Builder $query) => $query->where('validation_status', 'valid'),
            ])
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('meeting_number', 'like', "%{$search}%")
                        ->orWhereHas('schedule.subject', fn (Builder $nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('schedule.class', fn (Builder $nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('schedule.lecturer', fn (Builder $nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('nidn', 'like', "%{$search}%"))
                        ->orWhereHas('schedule.room', fn (Builder $nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')))
            ->when($request->filled('date'), fn (Builder $query) => $query->whereDate('meeting_date', $request->date('date')));

        $stats = [
            'total' => ScheduleMeeting::count(),
            'today' => ScheduleMeeting::whereDate('meeting_date', today())->count(),
            'ongoing' => ScheduleMeeting::where('status', 'ongoing')->count(),
            'finished' => ScheduleMeeting::where('status', 'finished')->count(),
        ];

        return view('admin.aktivitas.absensi.daftar', [
            'meetings' => $query
                ->latest('meeting_date')
                ->latest('id')
                ->paginate($perPage)
                ->withQueryString(),
            'perPage' => $perPage,
            'stats' => $stats,
        ]);
    }

    public function show(ScheduleMeeting $meeting): View
    {
        $meeting->load(['schedule.class', 'schedule.Subject', 'schedule.Room', 'schedule.Lecturer', 'LecturerAttendance']);
        $Schedule = $meeting->schedule;
        $Attendances = $meeting->attendances()->with(['student', 'lecturer', 'device'])->get();
        $StudentAttendances = $Attendances->where('user_type', 'student')->keyBy('user_id');
        $Students = Student::query()->where('class_id', $Schedule->class_id)->orderBy('name')->get();

        $presentStudents = $Students->filter(fn (Student $Student) => $StudentAttendances->has($Student->id));
        $absentStudents = $Students->reject(fn (Student $Student) => $StudentAttendances->has($Student->id));

        $scanLogs = FingerprintScanLog::query()
            ->with('device')
            ->whereDate('scan_time', $meeting->meeting_date)
            ->whereHas('device', fn ($query) => $query->where('room_id', $Schedule->room_id))
            ->latest('scan_time')
            ->get();

        $doorLogs = DoorAccessLog::query()
            ->with(['device', 'room', 'lecturer', 'student', 'user'])
            ->where('schedule_id', $Schedule->id)
            ->whereDate('access_time', $meeting->meeting_date)
            ->latest('access_time')
            ->get();

        return view('admin.aktivitas.absensi.detail', compact('meeting', 'schedule', 'Attendances', 'presentStudents', 'absentStudents', 'scanLogs', 'doorLogs'));
    }
}







