<x-app-layout>
    <div class="space-y-6">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Laporan</p>
            <h1 class="mt-2 text-2xl font-extrabold text-slate-950">Laporan Per Mata Kuliah</h1>
            <p class="mt-2 text-sm text-slate-600">Tampilkan jumlah pertemuan dan persentase kehadiran mahasiswa per jadwal mata kuliah.</p>
        </div>

        <form method="GET" class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            <select name="academic_year_id" class="rounded-lg border-slate-300 text-sm">
                <option value="">Semua Tahun Akademik</option>
                @foreach ($academicYears as $year)
                    <option value="{{ $year->id }}" @selected(request('academic_year_id') == $year->id)>{{ $year->year }} - {{ ucfirst($year->semester) }}</option>
                @endforeach
            </select>
            <select name="subject_id" class="rounded-lg border-slate-300 text-sm">
                <option value="">Pilih Mata Kuliah</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
            <select name="class_id" class="rounded-lg border-slate-300 text-sm">
                <option value="">Semua Kelas</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->code }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Tampilkan</button>
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Mata Kuliah</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Tahun</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Pertemuan</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Hadir</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="px-4 py-3 text-sm font-semibold">{{ $row['schedule']->subject->name }}</td>
                                <td class="px-4 py-3 text-sm">{{ $row['schedule']->class->code }}</td>
                                <td class="px-4 py-3 text-sm">{{ $row['schedule']->academicYear->year }}</td>
                                <td class="px-4 py-3 text-sm">{{ $row['meetings'] }}</td>
                                <td class="px-4 py-3 text-sm">{{ $row['present'] }} / {{ $row['expected'] }}</td>
                                <td class="px-4 py-3 text-sm font-bold">{{ $row['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Pilih mata kuliah untuk menampilkan laporan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

