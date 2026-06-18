<x-app-layout>
    <div class="space-y-6">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Absensi</p>
            <h1 class="mt-2 text-2xl font-extrabold text-slate-950">Detail Pertemuan</h1>
            <p class="mt-2 text-sm text-slate-600">Pilih pertemuan untuk melihat detail dosen, mahasiswa, scan log, dan akses pintu.</p>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Pertemuan</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Jadwal</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-extrabold uppercase text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($meetings as $meeting)
                            <tr>
                                <td class="px-4 py-3 text-sm">{{ $meeting->meeting_date?->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $meeting->meeting_number }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-semibold">{{ $meeting->schedule->subject->name }} - {{ $meeting->schedule->class->code }}</div>
                                    <div class="text-xs text-slate-500">{{ $meeting->schedule->room->name }} | {{ $meeting->schedule->lecturer->name }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ ucfirst($meeting->status) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.meetings.show', $meeting) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada pertemuan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-4 py-3">{{ $meetings->links() }}</div>
        </div>
    </div>
</x-app-layout>

