<x-app-layout>
    <div class="space-y-6">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Laporan</p>
            <h1 class="mt-2 text-2xl font-extrabold text-slate-950">Laporan Per Dosen</h1>
            <p class="mt-2 text-sm text-slate-600">Tampilkan jadwal dosen, jam scan, dan status hadir pada tanggal tertentu.</p>
        </div>

        <form method="GET" class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-3">
            <select name="lecturer_id" class="rounded-lg border-slate-300 text-sm">
                <option value="">Pilih Dosen</option>
                @foreach ($lecturers as $lecturer)
                    <option value="{{ $lecturer->id }}" @selected(request('lecturer_id') == $lecturer->id)>{{ $lecturer->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ $date }}" class="rounded-lg border-slate-300 text-sm">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Tampilkan</button>
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Jadwal</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Mata Kuliah</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Ruangan</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Jam Scan</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="px-4 py-3 text-sm">{{ $row['schedule']->time_range }}</td>
                                <td class="px-4 py-3 text-sm font-semibold">{{ $row['schedule']->subject->name }}</td>
                                <td class="px-4 py-3 text-sm">{{ $row['schedule']->class->code }}</td>
                                <td class="px-4 py-3 text-sm">{{ $row['schedule']->room->name }}</td>
                                <td class="px-4 py-3 text-sm">{{ $row['scan_time'] }}</td>
                                <td class="px-4 py-3 text-sm">{{ ucfirst(str_replace('_', ' ', $row['status'])) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Pilih dosen untuk menampilkan laporan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

