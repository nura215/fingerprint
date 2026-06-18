<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoorAccessLog;
use App\Models\FingerprintScanLog;
use App\Models\ScheduleMeeting;
use App\Models\Student;
use Illuminate\View\View;

class MeetingController extends Controller
{
    public function index(): View
    {
        return view('admin.aktivitas.absensi.daftar', [
            'meetings' => ScheduleMeeting::query()
                ->with(['schedule.class', 'schedule.Subject', 'schedule.Room', 'schedule.Lecturer'])
                ->latest('meeting_date')
                ->paginate(15),
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







