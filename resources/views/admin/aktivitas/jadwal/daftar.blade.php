<x-app-layout>
    @php
        $subjectTones = [
            ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-800', 'dot' => 'bg-emerald-100'],
            ['bg' => 'bg-sky-50', 'border' => 'border-sky-200', 'text' => 'text-sky-800', 'dot' => 'bg-sky-100'],
            ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-800', 'dot' => 'bg-amber-100'],
            ['bg' => 'bg-violet-50', 'border' => 'border-violet-200', 'text' => 'text-violet-800', 'dot' => 'bg-violet-100'],
            ['bg' => 'bg-fuchsia-50', 'border' => 'border-fuchsia-200', 'text' => 'text-fuchsia-800', 'dot' => 'bg-fuchsia-100'],
            ['bg' => 'bg-teal-50', 'border' => 'border-teal-200', 'text' => 'text-teal-800', 'dot' => 'bg-teal-100'],
        ];
        $visibleStart = 7 * 60;
        $visibleEnd = 18 * 60;
        $visibleDuration = $visibleEnd - $visibleStart;
        $viewMode = request('view', 'calendar');
        $previousWeekUrl = route($routePrefix.'.index', array_merge(request()->except('week'), ['week' => $weekStart->subWeek()->toDateString()]));
        $nextWeekUrl = route($routePrefix.'.index', array_merge(request()->except('week'), ['week' => $weekStart->addWeek()->toDateString()]));
        $todayUrl = route($routePrefix.'.index', array_merge(request()->except('week'), ['week' => now()->toDateString()]));
        $tableUrl = route($routePrefix.'.index', array_merge(request()->except('view'), ['view' => 'table']));
        $calendarUrl = route($routePrefix.'.index', array_merge(request()->except('view'), ['view' => 'calendar']));
    @endphp

    <div class="space-y-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-slate-950">Jadwal Perkuliahan</h1>
                <p class="mt-1 text-sm font-medium text-slate-500">Kelola jadwal kuliah berdasarkan hari, ruangan, dosen, kelas, dan tahun akademik.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ $tableUrl }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-200 px-4 text-sm font-extrabold {{ $viewMode === 'table' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 5h16M4 12h16M4 19h16M8 5v14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Table
                </a>
                <a href="{{ $calendarUrl }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg px-4 text-sm font-extrabold {{ $viewMode === 'calendar' ? 'bg-emerald-600 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <rect x="4" y="5" width="16" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                        <path d="M8 3v4M16 3v4M4 10h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Calendar
                </a>
                <a href="{{ route($routePrefix.'.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Tambah Jadwal
                </a>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" class="grid gap-4 md:grid-cols-2 xl:grid-cols-[1fr_1fr_1fr_1fr_1fr_auto]">
                <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
                <input type="hidden" name="view" value="{{ $viewMode }}">

                <label>
                    <span class="mb-2 block text-sm font-semibold text-slate-600">Hari</span>
                    <select name="day" class="h-11 w-full rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                        <option value="">Semua Hari</option>
                        @foreach ($dayOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('day') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-slate-600">Ruangan</span>
                    <select name="room_id" class="h-11 w-full rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                        <option value="">Semua Ruangan</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" @selected((string) request('room_id') === (string) $room->id)>{{ $room->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-slate-600">Dosen</span>
                    <select name="lecturer_id" class="h-11 w-full rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                        <option value="">Semua Dosen</option>
                        @foreach ($lecturers as $lecturer)
                            <option value="{{ $lecturer->id }}" @selected((string) request('lecturer_id') === (string) $lecturer->id)>{{ $lecturer->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-slate-600">Kelas</span>
                    <select name="class_id" class="h-11 w-full rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                        <option value="">Semua Kelas</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected((string) request('class_id') === (string) $class->id)>{{ $class->code }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-slate-600">Tahun Akademik</span>
                    <select name="academic_year_id" class="h-11 w-full rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                        <option value="">Semua Tahun</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" @selected((string) request('academic_year_id') === (string) $year->id)>{{ $year->year }} {{ ucfirst($year->semester) }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700">Filter</button>
                    <a href="{{ route($routePrefix.'.index') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-200 px-4 text-sm font-extrabold text-slate-700 hover:bg-slate-50">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 4v6h6M20 20v-6h-6M5 19A8 8 0 0 0 19 8M19 5A8 8 0 0 0 5 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        @if ($viewMode === 'table')
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Hari</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Waktu</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Mata Kuliah</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Dosen</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Ruangan</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Kelas</th>
                                <th class="px-5 py-4 text-center text-xs font-extrabold uppercase tracking-wide text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($items as $item)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-700">{{ $item->day_label }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $item->time_range }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-extrabold text-slate-800">{{ $item->subject?->name ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $item->lecturer?->name ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $item->room?->name ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $item->class?->code ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route($routePrefix.'.edit', $item) }}" class="grid h-9 w-9 place-items-center rounded-lg border border-emerald-200 text-emerald-700 hover:bg-emerald-50" aria-label="Edit jadwal">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center text-sm font-medium text-slate-500">Data jadwal belum tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-100 p-5 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-2">
                        <a href="{{ $previousWeekUrl }}" class="grid h-10 w-10 place-items-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" aria-label="Minggu sebelumnya">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        <div class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 px-4 text-sm font-extrabold text-slate-800">
                            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect x="4" y="5" width="16" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                                <path d="M8 3v4M16 3v4M4 10h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            {{ $weekStart->locale('id')->translatedFormat('d') }} - {{ $weekEnd->locale('id')->translatedFormat('d M Y') }}
                        </div>
                        <a href="{{ $nextWeekUrl }}" class="grid h-10 w-10 place-items-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" aria-label="Minggu berikutnya">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>

                    <a href="{{ $todayUrl }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 px-4 text-sm font-extrabold text-slate-700 hover:bg-slate-50">Hari Ini</a>
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[1080px]">
                        <div class="grid grid-cols-[72px_repeat(7,minmax(136px,1fr))] border-b border-slate-200">
                            <div class="border-r border-slate-200 px-4 py-4 text-sm font-extrabold text-slate-600">Waktu</div>
                            @foreach ($days as $day)
                                @php
                                    $date = $weekStart->addDays($loop->index);
                                    $isToday = $date->isSameDay(now());
                                @endphp
                                <div class="border-r border-slate-200 px-4 py-4 text-center last:border-r-0 {{ $isToday ? 'bg-emerald-50/70' : '' }}">
                                    <div class="text-sm font-extrabold {{ $isToday ? 'text-emerald-700' : 'text-slate-800' }}">{{ $dayOptions[$day] }}</div>
                                    <div class="mt-1 text-xs font-semibold text-slate-500">{{ $date->locale('id')->translatedFormat('d M') }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-[72px_repeat(7,minmax(136px,1fr))]">
                            <div class="border-r border-slate-200">
                                @foreach ($timeSlots as $hour)
                                    <div class="h-20 border-b border-slate-100 px-4 pt-3 text-sm font-semibold text-slate-600">{{ str_pad((string) $hour, 2, '0', STR_PAD_LEFT) }}:00</div>
                                @endforeach
                            </div>

                            @foreach ($days as $day)
                                <div class="relative h-[880px] border-r border-slate-200 last:border-r-0">
                                    @foreach ($timeSlots as $hour)
                                        <div class="h-20 border-b border-slate-100"></div>
                                    @endforeach

                                    @foreach (($schedulesByDay[$day] ?? collect()) as $schedule)
                                        @php
                                            $tone = $subjectTones[$schedule->subject_id % count($subjectTones)];
                                            [$startHour, $startMinute] = array_map('intval', explode(':', substr((string) $schedule->start_time, 0, 5)));
                                            [$endHour, $endMinute] = array_map('intval', explode(':', substr((string) $schedule->end_time, 0, 5)));
                                            $start = ($startHour * 60) + $startMinute;
                                            $end = ($endHour * 60) + $endMinute;
                                            $top = max(0, min(100, (($start - $visibleStart) / $visibleDuration) * 100));
                                            $height = max(56, (($end - $start) / $visibleDuration) * 880);
                                        @endphp
                                        <a
                                            href="{{ route($routePrefix.'.edit', $schedule) }}"
                                            class="absolute left-2 right-2 overflow-hidden rounded-lg border p-3 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $tone['bg'] }} {{ $tone['border'] }} {{ $tone['text'] }}"
                                            style="top: {{ $top }}%; height: {{ $height }}px;"
                                        >
                                            <div class="text-[11px] font-bold opacity-80">{{ substr((string) $schedule->start_time, 0, 5) }} - {{ substr((string) $schedule->end_time, 0, 5) }}</div>
                                            <div class="mt-1 text-sm font-extrabold leading-tight">{{ $schedule->subject?->name ?? '-' }}</div>
                                            <div class="mt-1 truncate text-xs font-semibold opacity-80">{{ $schedule->lecturer?->name ?? '-' }}</div>
                                            <div class="mt-2 text-xs font-medium opacity-80">{{ $schedule->room?->name ?? '-' }}</div>
                                            <div class="text-xs font-medium opacity-80">Kelas {{ $schedule->class?->code ?? '-' }}</div>
                                        </a>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-5 border-t border-slate-100 px-5 py-4">
                    @foreach ($items->unique('subject_id')->take(8) as $schedule)
                        @php $tone = $subjectTones[$schedule->subject_id % count($subjectTones)]; @endphp
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                            <span class="h-3 w-3 rounded-full {{ $tone['dot'] }}"></span>
                            {{ $schedule->subject?->name ?? '-' }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
