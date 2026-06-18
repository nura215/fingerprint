<x-app-layout>
    <div class="space-y-5">
        <div>
            <h1 class="text-2xl font-extrabold tracking-normal text-slate-950">Detail Pertemuan</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Pantau pertemuan, kehadiran, scan log, dan akses pintu berdasarkan jadwal.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Total Pertemuan" :value="$stats['total']" caption="Seluruh pertemuan tercatat" color="emerald" icon="calendar" />
            <x-stat-card title="Pertemuan Hari Ini" :value="$stats['today']" caption="Sesuai tanggal hari ini" color="blue" icon="calendar" />
            <x-stat-card title="Sedang Berjalan" :value="$stats['ongoing']" caption="Status pertemuan aktif" color="amber" icon="device" />
            <x-stat-card title="Selesai" :value="$stats['finished']" caption="Pertemuan sudah ditutup" color="violet" icon="check" />
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <form method="GET" class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div class="grid flex-1 gap-3 md:grid-cols-[minmax(220px,1fr)_170px_170px]">
                        <label class="relative">
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <input
                                type="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Cari mata kuliah, kelas, dosen, atau ruangan..."
                                class="h-11 w-full rounded-lg border-slate-200 pl-10 text-sm font-medium text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500/10"
                            >
                        </label>

                        <select name="status" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="scheduled" @selected(request('status') === 'scheduled')>Terjadwal</option>
                            <option value="ongoing" @selected(request('status') === 'ongoing')>Berjalan</option>
                            <option value="finished" @selected(request('status') === 'finished')>Selesai</option>
                            <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
                        </select>

                        <input
                            type="date"
                            name="date"
                            value="{{ request('date') }}"
                            class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10"
                            onchange="this.form.submit()"
                        >
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 px-4 text-sm font-extrabold text-slate-700 hover:bg-slate-50">Filter</button>
                        @if (request()->hasAny(['search', 'status', 'date']))
                            <a href="{{ route('admin.meetings.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 px-4 text-sm font-extrabold text-slate-500 hover:bg-slate-50">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto px-5 pt-5">
                <table class="min-w-full overflow-hidden rounded-lg border border-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Tanggal</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Pertemuan</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Jadwal</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Kehadiran</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-5 py-4 text-center text-xs font-extrabold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($meetings as $meeting)
                            @php
                                $schedule = $meeting->schedule;
                                $statusClasses = match ($meeting->status) {
                                    'ongoing' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                    'finished' => 'bg-blue-50 text-blue-700 ring-blue-200',
                                    'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
                                    default => 'bg-amber-50 text-amber-700 ring-amber-200',
                                };
                                $statusLabel = match ($meeting->status) {
                                    'ongoing' => 'Berjalan',
                                    'finished' => 'Selesai',
                                    'cancelled' => 'Dibatalkan',
                                    default => 'Terjadwal',
                                };
                            @endphp
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="text-sm font-extrabold text-slate-800">{{ $meeting->meeting_date?->format('d M Y') }}</div>
                                    <div class="mt-1 text-xs font-medium text-slate-500">{{ $schedule?->day_label ?? '-' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-emerald-50 px-3 text-sm font-extrabold text-emerald-700">
                                        {{ $meeting->meeting_number }}
                                    </div>
                                </td>
                                <td class="min-w-[300px] px-5 py-4">
                                    <div class="text-sm font-extrabold text-slate-800">{{ $schedule?->subject?->name ?? '-' }}</div>
                                    <div class="mt-1 text-xs font-semibold text-slate-500">
                                        {{ $schedule?->class?->code ?? '-' }} · {{ $schedule?->room?->name ?? '-' }} · {{ $schedule?->time_range ?? '-' }}
                                    </div>
                                    <div class="mt-1 text-xs font-medium text-slate-500">{{ $schedule?->lecturer?->name ?? '-' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="text-sm font-extrabold text-slate-800">{{ number_format($meeting->student_attendances_count, 0, ',', '.') }} mahasiswa</div>
                                    <div class="mt-1 text-xs font-medium text-slate-500">{{ number_format($meeting->valid_attendances_count, 0, ',', '.') }} scan valid</div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-md px-2.5 py-1 text-xs font-extrabold ring-1 {{ $statusClasses }}">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="flex justify-center">
                                        <a href="{{ route('admin.meetings.show', $meeting) }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-emerald-200 px-3 text-sm font-extrabold text-emerald-700 hover:bg-emerald-50">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                            Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-sm font-medium text-slate-500">Belum ada pertemuan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('admin.partials.table-footer', ['items' => $meetings, 'perPage' => $perPage])
        </div>
    </div>
</x-app-layout>
