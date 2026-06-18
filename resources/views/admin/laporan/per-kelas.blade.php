<x-app-layout>
    <div class="space-y-5">
        <div>
            <h1 class="text-2xl font-extrabold tracking-normal text-slate-950">Laporan Per Kelas</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Ringkasan kehadiran mahasiswa berdasarkan kelas, mata kuliah, dan rentang tanggal.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Mahasiswa" :value="$stats['students']" caption="Data sesuai filter kelas" color="emerald" icon="users" />
            <x-stat-card title="Pertemuan" :value="$stats['meetings']" caption="Total pertemuan ditemukan" color="blue" icon="calendar" />
            <x-stat-card title="Total Hadir" :value="$stats['present']" caption="Hadir dan terlambat" color="violet" icon="check" />
            <x-stat-card title="Ditolak" :value="$stats['rejected']" caption="Scan tidak valid" color="rose" icon="door" />
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <form method="GET" class="grid gap-3 xl:grid-cols-[160px_160px_160px_minmax(210px,1fr)_minmax(210px,1fr)_150px_180px]">
                    <input type="month" name="month" value="{{ request('month', $selectedMonth) }}" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                    <select name="class_id" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                        <option value="">Pilih Kelas</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->code }} - {{ $class->name }}</option>
                        @endforeach
                    </select>
                    <select name="subject_id" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                        <option value="">Semua Mata Kuliah</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    <button class="inline-flex h-11 items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700">Tampilkan</button>
                    <a href="{{ route('admin.reports.classes.download', array_merge(request()->except('page'), ['month' => request('month', $selectedMonth)])) }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-emerald-200 px-4 text-sm font-extrabold text-emerald-700 hover:bg-emerald-50">Download Bulanan</a>
                </form>
            </div>

            <div class="overflow-x-auto px-5 pt-5">
                <table class="min-w-full overflow-hidden rounded-lg border border-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">NIM</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Mahasiswa</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Hadir</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Ditolak</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Belum Hadir</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($rows as $row)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $row['student']->nim }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-extrabold text-slate-800">{{ $row['student']->name }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-emerald-700">{{ $row['present'] }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-rose-600">{{ $row['rejected'] }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-600">{{ $row['absent'] }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-extrabold text-emerald-700 ring-1 ring-emerald-200">{{ $row['status'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-sm font-medium text-slate-500">Pilih filter untuk menampilkan laporan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-5 text-sm font-medium text-slate-500">
                Jumlah pertemuan: <span class="font-extrabold text-slate-800">{{ number_format($meetingsCount, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</x-app-layout>
