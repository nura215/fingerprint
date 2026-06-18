<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Absensi</p>
                <h1 class="mt-2 text-2xl font-extrabold text-slate-950">Detail Pertemuan #{{ $meeting->meeting_number }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ $meeting->meeting_date?->format('d M Y') }}</p>
            </div>
            <a href="{{ route('admin.meetings.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Kembali</a>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
                <h2 class="text-lg font-bold text-slate-900">Informasi Jadwal</h2>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div><dt class="text-xs font-bold uppercase text-slate-500">Mata Kuliah</dt><dd class="text-sm font-semibold">{{ $schedule->subject->name }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-slate-500">Kelas</dt><dd class="text-sm font-semibold">{{ $schedule->class->code }} - {{ $schedule->class->name }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-slate-500">Dosen</dt><dd class="text-sm font-semibold">{{ $schedule->lecturer->name }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-slate-500">Ruangan</dt><dd class="text-sm font-semibold">{{ $schedule->room->name }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-slate-500">Jam</dt><dd class="text-sm font-semibold">{{ $schedule->time_range }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-slate-500">Status Dosen</dt><dd class="text-sm font-semibold">{{ $meeting->lecturer_attendance_id ? 'Hadir' : 'Belum hadir' }}</dd></div>
                </dl>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Ringkasan</h2>
                <div class="mt-4 grid gap-3">
                    <div class="rounded-lg bg-emerald-50 p-3"><div class="text-xs font-bold text-emerald-700">Mahasiswa Hadir</div><div class="text-2xl font-extrabold">{{ $presentStudents->count() }}</div></div>
                    <div class="rounded-lg bg-slate-50 p-3"><div class="text-xs font-bold text-slate-600">Belum Hadir</div><div class="text-2xl font-extrabold">{{ $absentStudents->count() }}</div></div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Mahasiswa Hadir</h2>
                <div class="mt-4 space-y-2">
                    @forelse ($presentStudents as $student)
                        <div class="rounded-lg border border-slate-100 p-3 text-sm"><span class="font-bold">{{ $student->nim }}</span> - {{ $student->name }}</div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada mahasiswa hadir.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Mahasiswa Belum Hadir</h2>
                <div class="mt-4 space-y-2">
                    @forelse ($absentStudents as $student)
                        <div class="rounded-lg border border-slate-100 p-3 text-sm"><span class="font-bold">{{ $student->nim }}</span> - {{ $student->name }}</div>
                    @empty
                        <p class="text-sm text-slate-500">Semua mahasiswa sudah tercatat hadir.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Riwayat Log Scan</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($scanLogs as $log)
                                <tr><td class="py-2">{{ $log->scan_time_label }}</td><td class="py-2 font-semibold">{{ $log->fingerprint_id }}</td><td class="py-2">{{ $log->message }}</td></tr>
                            @empty
                                <tr><td class="py-4 text-slate-500">Belum ada scan log.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Riwayat Akses Pintu</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($doorLogs as $log)
                                <tr><td class="py-2">{{ $log->access_time?->format('H:i') }}</td><td class="py-2 font-semibold">{{ $log->access_user_name }}</td><td class="py-2">{{ ucfirst($log->access_status) }}</td><td class="py-2">{{ $log->reason }}</td></tr>
                            @empty
                                <tr><td class="py-4 text-slate-500">Belum ada akses pintu.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

