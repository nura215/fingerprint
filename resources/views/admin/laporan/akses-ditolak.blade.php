<x-app-layout>
    <div class="space-y-5">
        <div>
            <h1 class="text-2xl font-extrabold tracking-normal text-slate-950">Laporan Akses Ditolak</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Riwayat akses ditolak atau gagal berdasarkan tanggal, ruangan, dan alasan.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Total Log" :value="$stats['total']" caption="Sesuai filter aktif" color="emerald" icon="door" />
            <x-stat-card title="Ditolak" :value="$stats['denied']" caption="Akses tidak diizinkan" color="rose" icon="door" />
            <x-stat-card title="Gagal" :value="$stats['failed']" caption="Akses gagal diproses" color="amber" icon="device" />
            <x-stat-card title="Hari Ini" :value="$stats['today']" caption="Ditolak atau gagal hari ini" color="blue" icon="calendar" />
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <form method="GET" class="grid gap-3 xl:grid-cols-[160px_170px_minmax(190px,1fr)_minmax(230px,1fr)_150px_180px]">
                    <input type="month" name="month" value="{{ request('month', $selectedMonth) }}" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                    <input type="date" name="date" value="{{ request('date') }}" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                    <select name="room_id" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                        <option value="">Semua Ruangan</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" @selected(request('room_id') == $room->id)>{{ $room->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="reason" value="{{ request('reason') }}" placeholder="Cari alasan penolakan..." class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500/10">
                    <button class="inline-flex h-11 items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700">Tampilkan</button>
                    <a href="{{ route('admin.reports.denied-access.download', array_merge(request()->except('page'), ['month' => request('month', $selectedMonth)])) }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-emerald-200 px-4 text-sm font-extrabold text-emerald-700 hover:bg-emerald-50">Download Bulanan</a>
                </form>
            </div>

            <div class="overflow-x-auto px-5 pt-5">
                <table class="min-w-full overflow-hidden rounded-lg border border-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">User</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Fingerprint ID</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Ruangan</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Waktu Scan</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($logs as $log)
                            @php
                                $statusClass = $log->access_status === 'failed'
                                    ? 'bg-amber-50 text-amber-700 ring-amber-200'
                                    : 'bg-rose-50 text-rose-700 ring-rose-200';
                                $statusLabel = $log->access_status === 'failed' ? 'Gagal' : 'Ditolak';
                            @endphp
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-extrabold text-slate-800">{{ $log->access_user_name }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $log->access_fingerprint_id }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-600">{{ $log->room?->name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $log->access_time?->format('d M Y H:i') }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-extrabold ring-1 {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td class="min-w-[260px] px-5 py-4 text-sm font-medium text-slate-600">{{ $log->reason ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-sm font-medium text-slate-500">Tidak ada data akses ditolak.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('admin.partials.table-footer', ['items' => $logs, 'perPage' => $perPage])
        </div>
    </div>
</x-app-layout>
