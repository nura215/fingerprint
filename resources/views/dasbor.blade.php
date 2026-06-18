@php
    $statIcon = function (string $name): string {
        return match ($name) {
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
            'graduation' => '<path d="M22 10 12 5 2 10l10 5 10-5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M6 12v5c3.5 2.3 8.5 2.3 12 0v-5" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
            'building' => '<path d="M4 21V7l8-4 8 4v14M9 21v-7h6v7M8 9h.01M12 9h.01M16 9h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
            'calendar' => '<rect x="4" y="5" width="16" height="16" rx="2" stroke="currentColor" stroke-width="2"/><path d="M8 3v4M16 3v4M4 10h16M8 14h3M13 14h3M8 18h3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
            'clipboard' => '<path d="M9 4h6l1 3H8l1-3Z" stroke="currentColor" stroke-width="2"/><rect x="5" y="6" width="14" height="16" rx="2" stroke="currentColor" stroke-width="2"/><path d="m9 14 2 2 4-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
            'device' => '<rect x="7" y="3" width="10" height="18" rx="2" stroke="currentColor" stroke-width="2"/><path d="M10 7h4M10 17h4M12 13a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
            'shield' => '<path d="M12 3 20 6v6c0 5-3.2 8.4-8 10-4.8-1.6-8-5-8-10V6l8-3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M12 8v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 16h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>',
            default => '<circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/>',
        };
    };

    $toneClass = [
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'purple' => 'bg-purple-50 text-purple-600',
        'orange' => 'bg-orange-50 text-orange-600',
        'teal' => 'bg-teal-50 text-teal-600',
        'rose' => 'bg-rose-50 text-rose-600',
    ];

    $point = function ($index, $value, $max) use ($trend) {
        $count = max($trend->count() - 1, 1);
        $x = round(($index / $count) * 300, 2);
        $y = round(90 - (($value / max($max, 1)) * 70), 2);

        return "{$x},{$y}";
    };

    $presentPoints = $trend->values()->map(fn ($item, $index) => $point($index, $item['present'], $maxTrendValue))->implode(' ');
    $rejectedPoints = $trend->values()->map(fn ($item, $index) => $point($index, $item['rejected'], $maxTrendValue))->implode(' ');
    $percentagePoints = $trend->values()->map(function ($item, $index) use ($trend) {
        $count = max($trend->count() - 1, 1);
        $x = round(($index / $count) * 300, 2);
        $y = round(90 - (($item['percentage'] / 100) * 70), 2);

        return "{$x},{$y}";
    })->implode(' ');

    $deviceOnlinePercent = $deviceCount > 0 ? round(($deviceStatuses['online'] / $deviceCount) * 100) : 0;
    $deviceOfflinePercent = $deviceCount > 0 ? round(($deviceStatuses['offline'] / $deviceCount) * 100) : 0;
    $deviceMaintenancePercent = $deviceCount > 0 ? round(($deviceStatuses['maintenance'] / $deviceCount) * 100) : 0;
    $donutStyle = "background: conic-gradient(#10b981 0 {$deviceOnlinePercent}%, #ef4444 {$deviceOnlinePercent}% ".($deviceOnlinePercent + $deviceOfflinePercent)."%, #f59e0b ".($deviceOnlinePercent + $deviceOfflinePercent)."% 100%);";
@endphp

<x-app-layout>
    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-7">
            @foreach ($stats as $stat)
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-xl {{ $toneClass[$stat['tone']] ?? 'bg-slate-100 text-slate-600' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">{!! $statIcon($stat['icon']) !!}</svg>
                        </div>
                    </div>
                    <div class="mt-4 text-xs font-extrabold text-slate-500">{{ $stat['label'] }}</div>
                    <div class="mt-3 text-3xl font-extrabold tracking-normal text-slate-950">{{ number_format($stat['value'], 0, ',', '.') }}</div>
                    <div class="mt-4 text-xs font-semibold {{ str_contains($stat['caption'], '+') ? 'text-emerald-600' : 'text-slate-500' }}">
                        {{ $stat['caption'] }}
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.35fr_0.95fr]">
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-950">Tren Absensi</h2>
                        <p class="text-xs font-semibold text-slate-500">(7 Hari Terakhir)</p>
                    </div>
                    <select class="h-10 rounded-lg border-slate-200 text-sm font-semibold text-slate-600 focus:border-emerald-500 focus:ring-emerald-500/10">
                        <option>7 Hari Terakhir</option>
                    </select>
                </div>

                <div class="mt-4 flex items-center justify-center gap-5 text-xs font-bold">
                    <span class="inline-flex items-center gap-2 text-emerald-600"><span class="h-2 w-6 rounded-full bg-emerald-500"></span>Hadir</span>
                    <span class="inline-flex items-center gap-2 text-red-500"><span class="h-2 w-6 rounded-full bg-red-500"></span>Tidak Hadir</span>
                    <span class="inline-flex items-center gap-2 text-slate-500"><span class="h-0.5 w-6 border-t-2 border-dashed border-slate-400"></span>Persentase Kehadiran</span>
                </div>

                <div class="mt-4 overflow-hidden rounded-lg bg-gradient-to-b from-white to-emerald-50/50 p-3">
                    <svg viewBox="0 0 340 130" class="h-[260px] w-full" preserveAspectRatio="none" aria-label="Grafik tren absensi">
                        @for ($i = 0; $i <= 4; $i++)
                            <line x1="30" x2="330" y1="{{ 20 + ($i * 22) }}" y2="{{ 20 + ($i * 22) }}" stroke="#e2e8f0" stroke-width="1"/>
                        @endfor
                        @for ($i = 0; $i < $trend->count(); $i++)
                            <line x1="{{ 30 + ($i * (300 / max($trend->count() - 1, 1))) }}" x2="{{ 30 + ($i * (300 / max($trend->count() - 1, 1))) }}" y1="20" y2="108" stroke="#edf2f7" stroke-width="1"/>
                        @endfor
                        <polyline points="{{ collect(explode(' ', $presentPoints))->map(fn ($p) => ($coords = explode(',', $p)) ? (30 + (float) $coords[0]).','.$coords[1] : $p)->implode(' ') }}" fill="none" stroke="#10b981" stroke-width="3"/>
                        <polyline points="{{ collect(explode(' ', $rejectedPoints))->map(fn ($p) => ($coords = explode(',', $p)) ? (30 + (float) $coords[0]).','.$coords[1] : $p)->implode(' ') }}" fill="none" stroke="#ef4444" stroke-width="3"/>
                        <polyline points="{{ collect(explode(' ', $percentagePoints))->map(fn ($p) => ($coords = explode(',', $p)) ? (30 + (float) $coords[0]).','.$coords[1] : $p)->implode(' ') }}" fill="none" stroke="#64748b" stroke-width="2" stroke-dasharray="6 5"/>
                        @foreach ($trend->values() as $index => $item)
                            @php
                                $x = 30 + ($index * (300 / max($trend->count() - 1, 1)));
                                $presentY = 90 - (($item['present'] / max($maxTrendValue, 1)) * 70);
                                $rejectedY = 90 - (($item['rejected'] / max($maxTrendValue, 1)) * 70);
                            @endphp
                            <circle cx="{{ $x }}" cy="{{ $presentY }}" r="3.5" fill="#10b981"/>
                            <circle cx="{{ $x }}" cy="{{ $rejectedY }}" r="3.5" fill="#ef4444"/>
                            <text x="{{ $x }}" y="126" text-anchor="middle" fill="#64748b" font-size="8" font-weight="700">{{ $item['label'] }}</text>
                        @endforeach
                    </svg>
                </div>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-base font-extrabold text-slate-950">Status Perangkat</h2>
                    <a href="{{ route('admin.devices.index') }}" class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-extrabold text-emerald-700">Lihat Semua Perangkat</a>
                </div>

                <div class="mt-7 grid items-center gap-8 sm:grid-cols-[190px_1fr]">
                    <div class="mx-auto grid h-44 w-44 place-items-center rounded-full" style="{{ $donutStyle }}">
                        <div class="grid h-28 w-28 place-items-center rounded-full bg-white text-center shadow-inner">
                            <div>
                                <div class="text-4xl font-extrabold text-slate-950">{{ $deviceCount }}</div>
                                <div class="text-xs font-semibold leading-tight text-slate-500">Total<br>Perangkat</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5 text-sm font-semibold">
                        <div class="flex items-center justify-between gap-4">
                            <span class="inline-flex items-center gap-3 text-slate-700"><span class="h-3 w-3 rounded-full bg-emerald-500"></span>Online</span>
                            <span class="text-slate-500">{{ $deviceStatuses['online'] }} ({{ $deviceOnlinePercent }}%)</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="inline-flex items-center gap-3 text-slate-700"><span class="h-3 w-3 rounded-full bg-red-500"></span>Offline</span>
                            <span class="text-slate-500">{{ $deviceStatuses['offline'] }} ({{ $deviceOfflinePercent }}%)</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="inline-flex items-center gap-3 text-slate-700"><span class="h-3 w-3 rounded-full bg-amber-500"></span>Perlu Perhatian</span>
                            <span class="text-slate-500">{{ $deviceStatuses['maintenance'] }} ({{ $deviceMaintenancePercent }}%)</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 text-center text-xs font-semibold text-slate-400">
                    Terakhir diperbarui: {{ now()->translatedFormat('d M Y H:i') }}
                </div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.45fr_0.95fr]">
            <article class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4">
                    <h2 class="text-base font-extrabold text-slate-950">Log Scan Terbaru</h2>
                    <a href="{{ route('admin.meetings.index') }}" class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-extrabold text-emerald-700">Lihat Semua Log</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs font-extrabold text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Waktu</th>
                                <th class="px-5 py-3">Nama</th>
                                <th class="px-5 py-3">NIM / NIP</th>
                                <th class="px-5 py-3">Peran</th>
                                <th class="px-5 py-3">Hasil</th>
                                <th class="px-5 py-3">Perangkat</th>
                                <th class="px-5 py-3">Lokasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentAttendances as $attendance)
                                @php
                                    $person = $attendance->user_type === 'student' ? $attendance->student : $attendance->lecturer;
                                    $identifier = $attendance->user_type === 'student' ? ($person?->nim ?? '-') : ($person?->nidn ?? '-');
                                    $isSuccess = $attendance->attendance_status !== 'rejected';
                                @endphp
                                <tr class="font-semibold text-slate-700">
                                    <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-500">{{ $attendance->attendance_time?->format('H:i:s') }}</td>
                                    <td class="whitespace-nowrap px-5 py-3">{{ $person?->name ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-500">{{ $identifier }}</td>
                                    <td class="px-5 py-3">
                                        <span class="rounded-md px-2 py-1 text-xs font-extrabold {{ $attendance->user_type === 'student' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                            {{ $attendance->user_type === 'student' ? 'Mahasiswa' : 'Dosen' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-extrabold {{ $isSuccess ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                            <span class="h-2 w-2 rounded-full {{ $isSuccess ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                            {{ $isSuccess ? 'Berhasil' : 'Gagal' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-500">{{ $attendance->device?->device_code ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-500">{{ $attendance->device?->room?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-10 text-center text-sm font-semibold text-slate-400">Belum ada log scan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-center gap-2 border-t border-slate-100 px-5 py-4">
                    @foreach ([1, 2, 3] as $page)
                        <span class="grid h-8 w-8 place-items-center rounded-lg text-sm font-extrabold {{ $page === 1 ? 'bg-emerald-600 text-white' : 'border border-slate-200 text-slate-500' }}">{{ $page }}</span>
                    @endforeach
                    <span class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-sm font-extrabold text-slate-500">...</span>
                    <span class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-sm font-extrabold text-slate-500">20</span>
                </div>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4">
                    <h2 class="text-base font-extrabold text-slate-950">Aktivitas Akses Pintu Terbaru</h2>
                    <a href="{{ route('admin.reports.denied-access') }}" class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-extrabold text-emerald-700">Lihat Semua</a>
                </div>

                <div class="divide-y divide-slate-100 px-5">
                    @forelse ($recentAccessLogs as $log)
                        @php
                            $success = $log->access_status === 'granted';
                            $action = str_contains($log->reason ?? '', 'Keluar') ? 'Keluar' : 'Masuk';
                        @endphp
                        <div class="flex items-center gap-4 py-4">
                            <div class="grid h-11 w-11 place-items-center rounded-lg {{ $success ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M7 21V4h10v17M5 21h14M14 13h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-extrabold text-slate-900">{{ $log->room?->name ?? 'Pintu' }} - {{ $action }}</div>
                                <div class="truncate text-xs font-semibold text-slate-500">{{ $log->access_user_name }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-bold text-slate-500">{{ $log->access_time?->format('H:i') }}</div>
                                <div class="mt-1 rounded-md px-2 py-1 text-xs font-extrabold {{ $success ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                    {{ $success ? 'Berhasil' : 'Ditolak' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-sm font-semibold text-slate-400">Belum ada aktivitas akses pintu.</div>
                    @endforelse
                </div>

                <div class="border-t border-slate-100 px-5 py-4 text-center text-xs font-semibold text-slate-400">
                    Terakhir diperbarui: {{ now()->translatedFormat('d M Y H:i') }}
                </div>
            </article>
        </section>
    </div>
</x-app-layout>
