<x-app-layout>
    <div class="space-y-5">
        <div>
            <h1 class="text-2xl font-extrabold tracking-normal text-slate-950">Audit Log</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Riwayat aksi penting seperti tambah data, ubah data, nonaktifkan data, jadwal, dan manual unlock.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Total Log" :value="$stats['total']" caption="Seluruh aktivitas tercatat" color="emerald" icon="book" />
            <x-stat-card title="Hari Ini" :value="$stats['today']" caption="Aktivitas tanggal hari ini" color="blue" icon="calendar" />
            <x-stat-card title="Manual Unlock" :value="$stats['manual_unlock']" caption="Perintah buka pintu web" color="amber" icon="door" />
            <x-stat-card title="Update Data" :value="$stats['updates']" caption="Aktivitas perubahan data" color="violet" icon="check" />
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <form method="GET" class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div class="grid flex-1 gap-3 md:grid-cols-[minmax(220px,1fr)_180px_180px_170px]">
                        <label class="relative">
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <input
                                type="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Cari user, aksi, tabel, record, atau IP..."
                                class="h-11 w-full rounded-lg border-slate-200 pl-10 text-sm font-medium text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500/10"
                            >
                        </label>

                        <select name="action" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10" onchange="this.form.submit()">
                            <option value="">Semua Aksi</option>
                            @foreach ($actions as $action)
                                <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucwords(str_replace('_', ' ', $action)) }}</option>
                            @endforeach
                        </select>

                        <select name="table_name" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10" onchange="this.form.submit()">
                            <option value="">Semua Tabel</option>
                            @foreach ($tables as $table)
                                <option value="{{ $table }}" @selected(request('table_name') === $table)>{{ $table }}</option>
                            @endforeach
                        </select>

                        <input
                            type="date"
                            name="date"
                            value="{{ request('date') }}"
                            class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10"
                            onchange="this.form.submit()"
                        >
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 px-4 text-sm font-extrabold text-slate-700 hover:bg-slate-50">Filter</button>
                        @if (request()->hasAny(['search', 'action', 'table_name', 'date']))
                            <a href="{{ route('admin.audit-logs.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 px-4 text-sm font-extrabold text-slate-500 hover:bg-slate-50">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto px-5 pt-5">
                <table class="min-w-full overflow-hidden rounded-lg border border-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Waktu</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">User</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Aksi</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Tabel</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Record</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($logs as $log)
                            @php
                                $actionClass = str_contains($log->action, 'delete') || str_contains($log->action, 'inactive')
                                    ? 'bg-rose-50 text-rose-700 ring-rose-200'
                                    : (str_contains($log->action, 'update') ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-emerald-50 text-emerald-700 ring-emerald-200');
                            @endphp
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $log->created_at?->format('d M Y H:i') }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="text-sm font-extrabold text-slate-800">{{ $log->user?->name ?? '-' }}</div>
                                    <div class="mt-1 text-xs font-medium text-slate-500">{{ $log->user?->email ?? '-' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-extrabold ring-1 {{ $actionClass }}">{{ ucwords(str_replace('_', ' ', $log->action)) }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-700">{{ $log->table_name ?: '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $log->record_id ?: '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $log->ip_address ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-sm font-medium text-slate-500">Belum ada audit log.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('admin.partials.table-footer', ['items' => $logs, 'perPage' => $perPage])
        </div>
    </div>
</x-app-layout>
