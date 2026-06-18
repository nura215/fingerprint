<x-app-layout>
    <div class="space-y-6">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Pengaturan</p>
            <h1 class="mt-2 text-2xl font-extrabold text-slate-950">Audit Log</h1>
            <p class="mt-2 text-sm text-slate-600">Riwayat aksi penting: create, update, delete/nonaktif, update jadwal, dan manual unlock.</p>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">User</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Aksi</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Tabel</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">Record</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase text-slate-500">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-4 py-3 text-sm">{{ $log->created_at?->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm">{{ $log->user?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm font-bold">{{ $log->action }}</td>
                                <td class="px-4 py-3 text-sm">{{ $log->table_name }}</td>
                                <td class="px-4 py-3 text-sm">{{ $log->record_id }}</td>
                                <td class="px-4 py-3 text-sm">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada audit log.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-4 py-3">{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>

