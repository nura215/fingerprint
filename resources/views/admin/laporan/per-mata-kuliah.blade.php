<x-app-layout>
    <div class="space-y-5">
        <div>
            <h1 class="text-2xl font-extrabold tracking-normal text-slate-950">Laporan Per Mata Kuliah</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Pantau jumlah pertemuan dan persentase kehadiran per jadwal mata kuliah.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Jadwal" :value="$stats['schedules']" caption="Jadwal sesuai filter" color="emerald" icon="calendar" />
            <x-stat-card title="Pertemuan" :value="$stats['meetings']" caption="Total pertemuan" color="blue" icon="book" />
            <x-stat-card title="Kehadiran" :value="$stats['present']" caption="Total mahasiswa hadir" color="violet" icon="check" />
            <x-stat-card title="Target Absensi" :value="$stats['expected']" caption="Mahasiswa x pertemuan" color="amber" icon="users" />
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <form method="GET" class="grid gap-3 xl:grid-cols-[160px_minmax(210px,1fr)_minmax(230px,1fr)_minmax(180px,1fr)_150px_180px]">
                    <input type="month" name="month" value="{{ request('month', $selectedMonth) }}" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                    <select name="academic_year_id" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                        <option value="">Semua Tahun Akademik</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" @selected(request('academic_year_id') == $year->id)>{{ $year->year }} - {{ ucfirst($year->semester) }}</option>
                        @endforeach
                    </select>
                    <select name="subject_id" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                        <option value="">Pilih Mata Kuliah</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    <select name="class_id" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                        <option value="">Semua Kelas</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->code }}</option>
                        @endforeach
                    </select>
                    <button class="inline-flex h-11 items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700">Tampilkan</button>
                    <a href="{{ route('admin.reports.subjects.download', array_merge(request()->except('page'), ['month' => request('month', $selectedMonth)])) }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-emerald-200 px-4 text-sm font-extrabold text-emerald-700 hover:bg-emerald-50">Download Bulanan</a>
                </form>
            </div>

            <div class="overflow-x-auto px-5 pt-5">
                <table class="min-w-full overflow-hidden rounded-lg border border-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Mata Kuliah</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Kelas</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Tahun</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Pertemuan</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Hadir</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($rows as $row)
                            @php
                                $percentageClass = $row['percentage'] >= 75
                                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                    : ($row['percentage'] >= 50 ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-rose-50 text-rose-700 ring-rose-200');
                            @endphp
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="min-w-[240px] px-5 py-4 text-sm font-extrabold text-slate-800">{{ $row['schedule']->subject->name }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-600">{{ $row['schedule']->class->code }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $row['schedule']->academicYear->year }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-700">{{ $row['meetings'] }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-emerald-700">{{ $row['present'] }} / {{ $row['expected'] }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-extrabold ring-1 {{ $percentageClass }}">{{ str_replace('.', ',', $row['percentage']) }}%</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-sm font-medium text-slate-500">Pilih mata kuliah untuk menampilkan laporan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-5 text-sm font-medium text-slate-500">
                Menampilkan {{ number_format($rows->count(), 0, ',', '.') }} jadwal.
            </div>
        </div>
    </div>
</x-app-layout>
