<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Attendance;
use App\Models\Device;
use App\Models\DoorAccessLog;
use App\Models\FingerprintScanLog;
use App\Models\Lecturer;
use App\Models\Schedule;
use App\Models\Student;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now();
        $todayDay = strtolower($today->englishDayOfWeek);
        $lastSevenDays = collect(CarbonPeriod::create($today->copy()->subDays(6), $today))
            ->map(fn (Carbon $date) => $date->copy());

        $studentCount = Student::where('status', 'active')->count();
        $lecturerCount = Lecturer::where('status', 'active')->count();
        $classCount = AcademicClass::where('status', 'active')->count();
        $scheduleTodayCount = Schedule::where('status', 'active')->where('day', $todayDay)->count();
        $attendanceTodayCount = Attendance::whereDate('attendance_time', $today)->count();
        $validAttendanceTodayCount = Attendance::whereDate('attendance_time', $today)->where('validation_status', 'valid')->count();
        $deviceCount = Device::count();
        $onlineDeviceCount = Device::where('status', 'online')->count();
        $deniedAccessTodayCount = DoorAccessLog::whereDate('access_time', $today)->where('access_status', 'denied')->count();
        $attendanceRate = $attendanceTodayCount > 0
            ? round(($validAttendanceTodayCount / $attendanceTodayCount) * 100)
            : 0;

        $trend = $lastSevenDays->map(function (Carbon $date) {
            $present = Attendance::whereDate('attendance_time', $date)
                ->where('attendance_status', '!=', 'rejected')
                ->count();
            $rejected = Attendance::whereDate('attendance_time', $date)
                ->where('attendance_status', 'rejected')
                ->count();
            $total = max($present + $rejected, 1);

            return [
                'label' => $date->translatedFormat('d M'),
                'present' => $present,
                'rejected' => $rejected,
                'percentage' => round(($present / $total) * 100),
            ];
        });

        $maxTrendValue = max($trend->max('present'), $trend->max('rejected'), 1);

        $deviceStatuses = [
            'online' => Device::where('status', 'online')->count(),
            'offline' => Device::where('status', 'offline')->count(),
            'maintenance' => Device::where('status', 'maintenance')->count(),
        ];

        $recentAttendances = Attendance::query()
            ->with(['student', 'lecturer', 'device.room'])
            ->latest('attendance_time')
            ->limit(6)
            ->get();

        $recentAccessLogs = DoorAccessLog::query()
            ->with(['student', 'lecturer', 'user', 'room'])
            ->latest('access_time')
            ->limit(5)
            ->get();

        return view('dasbor', [
            'stats' => [
                ['label' => 'Mahasiswa Aktif', 'value' => $studentCount, 'caption' => '+18 bulan ini', 'icon' => 'users', 'tone' => 'emerald'],
                ['label' => 'Dosen Aktif', 'value' => $lecturerCount, 'caption' => '+3 bulan ini', 'icon' => 'graduation', 'tone' => 'blue'],
                ['label' => 'Kelas Aktif', 'value' => $classCount, 'caption' => '+2 bulan ini', 'icon' => 'building', 'tone' => 'purple'],
                ['label' => 'Jadwal Hari Ini', 'value' => $scheduleTodayCount, 'caption' => 'Kelas terjadwal', 'icon' => 'calendar', 'tone' => 'orange'],
                ['label' => 'Absensi Hari Ini', 'value' => $attendanceTodayCount, 'caption' => $attendanceRate.'% kehadiran', 'icon' => 'clipboard', 'tone' => 'emerald'],
                ['label' => 'Perangkat Online', 'value' => $onlineDeviceCount, 'caption' => 'dari '.$deviceCount.' perangkat', 'icon' => 'device', 'tone' => 'teal'],
                ['label' => 'Akses Ditolak', 'value' => $deniedAccessTodayCount, 'caption' => 'hari ini', 'icon' => 'shield', 'tone' => 'rose'],
            ],
            'trend' => $trend,
            'maxTrendValue' => $maxTrendValue,
            'deviceCount' => $deviceCount,
            'deviceStatuses' => $deviceStatuses,
            'recentAttendances' => $recentAttendances,
            'recentAccessLogs' => $recentAccessLogs,
            'lastScanLog' => FingerprintScanLog::latest('scan_time')->first(),
        ]);
    }
}







