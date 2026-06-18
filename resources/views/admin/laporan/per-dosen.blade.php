<x-app-layout>
    <div class="space-y-5">
        <div>
            <h1 class="text-2xl font-extrabold tracking-normal text-slate-950">Laporan Per Dosen</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Tampilkan jadwal dosen, jam scan, dan status kehadiran pada tanggal tertentu.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Jadwal" :value="$stats['schedules']" caption="Jadwal pada tanggal dipilih" color="emerald" icon="calendar" />
            <x-stat-card title="Hadir" :value="$stats['present']" caption="Scan hadir dan terlambat" color="blue" icon="check" />
            <x-stat-card title="Terlambat" :value="$stats['late']" caption="Scan setelah jam mulai" color="amber" icon="device" />
            <x-stat-card title="Belum Hadir" :value="$stats['absent']" caption="Belum ada scan valid" color="rose" icon="users" />
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <form method="GET" class="grid gap-3 xl:grid-cols-[minmax(260px,1fr)_170px_170px_150px_180px]">
                    <select name="lecturer_id" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                        <option value="">Pilih Dosen</option>
                        @foreach ($lecturers as $lecturer)
                            <option value="{{ $lecturer->id }}" @selected(request('lecturer_id') == $lecturer->id)>{{ $lecturer->name }}</option>
                        @endforeach
                    </select>
                    <input type="month" name="month" value="{{ request('month', $selectedMonth) }}" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                    <input type="date" name="date" value="{{ $date }}" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                    <button class="inline-flex h-11 items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700">Tampilkan</button>
                    <a href="{{ route('admin.reports.lecturers.download', array_merge(request()->except('page'), ['month' => request('month', $selectedMonth)])) }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-emerald-200 px-4 text-sm font-extrabold text-emerald-700 hover:bg-emerald-50">Download Bulanan</a>
                </form>
            </div>

            <div class="overflow-x-auto px-5 pt-5">
                <table class="min-w-full overflow-hidden rounded-lg border border-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Jadwal</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Mata Kuliah</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Kelas</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Ruangan</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Jam Scan</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($rows as $row)
                            @php
                                $statusClass = match ($row['status']) {
                                    'present' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                    'late' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                    default => 'bg-slate-100 text-slate-600 ring-slate-200',
                                };
                                $statusLabel = match ($row['status']) {
                                    'present' => 'Hadir',
                                    'late' => 'Terlambat',
                                    default => 'Belum Hadir',
                                };
                            @endphp
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-700">{{ $row['schedule']->time_range }}</td>
                                <td class="min-w-[240px] px-5 py-4 text-sm font-extrabold text-slate-800">{{ $row['schedule']->subject->name }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-600">{{ $row['schedule']->class->code }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $row['schedule']->room->name }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-700">{{ $row['scan_time'] }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-extrabold ring-1 {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-sm font-medium text-slate-500">Pilih dosen untuk menampilkan laporan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-5 text-sm font-medium text-slate-500">
                Tanggal laporan: <span class="font-extrabold text-slate-800">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
            </div>
        </div>
    </div>
</x-app-layout>
