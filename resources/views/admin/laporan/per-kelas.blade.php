<x-app-layout>
    <div class="space-y-6">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Laporan</p>
            <h1 class="mt-2 text-2xl font-extrabold text-slate-950">Laporan Per Kelas</h1>
            <p class="mt-2 text-sm text-slate-600">Tampilkan daftar mahasiswa dan ringkasan status kehadiran berdasarkan kelas, mata kuliah, dan rentang tanggal.</p>
        </div>

        <form method="GET" class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="rounded-lg border-slate-300 text-sm">
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="rounded-lg border-slate-300 text-sm">
            <select name="class_id" class="rounded-lg border-slate-300 text-sm">
                <option value="">Pilih Kelas</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->code }} - {{ $class->name }}</option>
                @endforeach
            </select>
            <select name="subject_id" class="rounded-lg border-slate-300 text-sm">
                <option value="">Semua Mata Kuliah</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Tampilkan</button>
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-4 py-3 text-sm font-bold text-slate-700">Jumlah pertemuan: {{ $meetingsCount }}</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">NIM</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Mahasiswa</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Hadir</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Ditolak</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Belum Hadir</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="px-4 py-3 text-sm">{{ $row['student']->nim }}</td>
                                <td class="px-4 py-3 text-sm font-semibold">{{ $row['student']->name }}</td>
                                <td class="px-4 py-3 text-sm">{{ $row['present'] }}</td>
                                <td class="px-4 py-3 text-sm">{{ $row['rejected'] }}</td>
                                <td class="px-4 py-3 text-sm">{{ $row['absent'] }}</td>
                                <td class="px-4 py-3 text-sm">{{ $row['status'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Pilih filter untuk menampilkan laporan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

