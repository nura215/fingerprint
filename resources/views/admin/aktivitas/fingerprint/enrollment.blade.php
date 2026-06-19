<x-app-layout>
    <div class="space-y-5">
        <div>
            <h1 class="text-2xl font-extrabold tracking-normal text-slate-950">Biometric Enrollment</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Kelola ID fingerprint yang dipakai sebagai User ID saat daftar sidik jari di alat.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Total Enrollment" :value="$stats['total']" caption="Data fingerprint terdaftar" color="emerald" icon="users" />
            <x-stat-card title="Sudah Fingerprint" :value="$stats['enrolled']" caption="Sudah selesai enroll di alat" color="blue" icon="check" />
            <x-stat-card title="Belum Fingerprint" :value="$stats['not_enrolled']" caption="Menunggu enroll di alat" color="amber" icon="device" />
            <x-stat-card title="Menunggu Sync" :value="$stats['pending_sync']" caption="Belum dikirim ke alat" color="violet" icon="device" />
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <form method="GET" class="grid gap-2 lg:grid-cols-2 xl:grid-cols-[minmax(170px,1fr)_122px_150px_138px_132px_84px_104px_96px] xl:items-center">
                    <label class="relative">
                        <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none"><path d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari user / fingerprint" class="h-10 w-full rounded-lg border-slate-200 pl-10 text-xs font-semibold text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500/10">
                    </label>
                    <select name="user_type" class="h-10 rounded-lg border-slate-200 text-xs font-extrabold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10" onchange="this.form.submit()">
                            <option value="">Semua User</option>
                            <option value="student" @selected(request('user_type') === 'student')>Mahasiswa</option>
                            <option value="lecturer" @selected(request('user_type') === 'lecturer')>Dosen</option>
                    </select>
                    <select name="device_id" class="h-10 rounded-lg border-slate-200 text-xs font-extrabold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10" onchange="this.form.submit()">
                            <option value="">Semua Perangkat</option>
                            @foreach ($devices as $device)
                                <option value="{{ $device->id }}" @selected((string) request('device_id') === (string) $device->id)>{{ $device->name }}</option>
                            @endforeach
                    </select>
                    <select name="status" class="h-10 rounded-lg border-slate-200 text-xs font-extrabold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="enrolled" @selected(request('status') === 'enrolled')>Sudah Fingerprint</option>
                            <option value="not_enrolled" @selected(request('status') === 'not_enrolled')>Belum Fingerprint</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Tidak Aktif</option>
                    </select>
                    <select name="sync_status" class="h-10 rounded-lg border-slate-200 text-xs font-extrabold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10" onchange="this.form.submit()">
                            <option value="">Semua Sync</option>
                            <option value="pending" @selected(request('sync_status') === 'pending')>Menunggu Sync</option>
                            <option value="synced" @selected(request('sync_status') === 'synced')>Sudah Terkirim</option>
                            <option value="failed" @selected(request('sync_status') === 'failed')>Gagal Sync</option>
                    </select>
                    <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-slate-200 px-3 text-xs font-extrabold text-slate-700 hover:bg-slate-50">Filter</button>
                    <button type="submit" form="sync-all-form" class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-emerald-200 px-3 text-xs font-extrabold text-emerald-700 hover:bg-emerald-50">Sync Semua</button>
                    <button type="submit" form="pull-logs-form" class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-emerald-600 px-3 text-xs font-extrabold text-white shadow-sm hover:bg-emerald-700">Tarik Log</button>
                </form>
                <form id="sync-all-form" method="POST" action="{{ route($routePrefix.'.sync-all') }}" onsubmit="return confirm('Kirim semua data enrollment ke antrean sinkron?')">
                    @csrf
                </form>
                <form id="pull-logs-form" method="POST" action="{{ route($routePrefix.'.pull-logs') }}" onsubmit="return confirm('Tarik log scan dari semua perangkat?')">
                    @csrf
                </form>
            </div>

            <div class="overflow-x-auto px-5 pt-5">
                <table class="min-w-full overflow-hidden rounded-lg border border-slate-200">
                    <thead class="bg-slate-50"><tr>
                        <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Tipe</th>
                        <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Nama User</th>
                        <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Fingerprint ID</th>
                        <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Perangkat</th>
                        <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Tanggal Enroll</th>
                        <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Sync Alat</th>
                        <th class="px-5 py-4 text-center text-xs font-extrabold uppercase tracking-wide text-slate-500">Aksi</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $item)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-600">{{ $item->user_type_label }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-800">{{ $item->user_name }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-extrabold text-emerald-700">{{ $item->fingerprint_id }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $item->device?->name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $item->enrolled_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-4">@include('admin.partials.lencana', ['value' => $item->status])</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="space-y-1">
                                        @include('admin.partials.lencana', ['value' => $item->sync_status])
                                        @if ($item->sync_message)
                                            <div class="max-w-48 truncate text-xs font-medium text-slate-500" title="{{ $item->sync_message }}">{{ $item->sync_message }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="flex justify-center gap-2">
                                        <form method="POST" action="{{ route($routePrefix.'.sync', $item) }}">
                                            @csrf
                                            <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg border border-blue-200 text-blue-700 hover:bg-blue-50" aria-label="Kirim ke alat">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M4 12h12M12 6l6 6-6 6M4 5h4M4 19h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </form>
                                        @include('admin.partials.table-actions', ['routePrefix' => $routePrefix, 'item' => $item, 'deleteLabel' => 'enrollment'])
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-12 text-center text-sm font-medium text-slate-500">Data enrollment belum tersedia.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('admin.partials.table-footer', ['items' => $items, 'perPage' => $perPage])
        </div>
    </div>
</x-app-layout>
