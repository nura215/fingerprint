<x-app-layout>
    <div class="space-y-6">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Laporan</p>
            <h1 class="mt-2 text-2xl font-extrabold text-slate-950">Laporan Akses Ditolak</h1>
            <p class="mt-2 text-sm text-slate-600">Tampilkan riwayat akses ditolak atau gagal berdasarkan tanggal, ruangan, dan alasan.</p>
        </div>

        <form method="GET" class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
            <input type="date" name="date" value="{{ request('date') }}" class="rounded-lg border-slate-300 text-sm">
            <select name="room_id" class="rounded-lg border-slate-300 text-sm">
                <option value="">Semua Ruangan</option>
                @foreach ($rooms as $room)
                    <option value="{{ $room->id }}" @selected(request('room_id') == $room->id)>{{ $room->name }}</option>
                @endforeach
            </select>
            <input type="text" name="reason" value="{{ request('reason') }}" placeholder="Alasan penolakan" class="rounded-lg border-slate-300 text-sm">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Tampilkan</button>
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">User</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Fingerprint ID</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Ruangan</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Waktu Scan</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-4 py-3 text-sm font-semibold">{{ $log->access_user_name }}</td>
                                <td class="px-4 py-3 text-sm">{{ $log->access_fingerprint_id }}</td>
                                <td class="px-4 py-3 text-sm">{{ $log->room->name }}</td>
                                <td class="px-4 py-3 text-sm">{{ $log->access_time?->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $log->reason }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada data akses ditolak.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-4 py-3">{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>

