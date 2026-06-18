<x-app-layout>
    <div class="space-y-5">
        <div>
            <h1 class="text-2xl font-extrabold tracking-normal text-slate-950">Data Mata Kuliah</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Kelola data mata kuliah, SKS, semester, dan program studi.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Total Mata Kuliah" :value="$stats['total']" caption="Seluruh mata kuliah terdaftar" color="emerald" icon="book" />
            <x-stat-card title="Mata Kuliah Aktif" :value="$stats['active']" caption="Mata kuliah berstatus aktif" color="blue" icon="check" />
            <x-stat-card title="Total SKS" :value="$stats['sks']" caption="Akumulasi SKS terdaftar" color="amber" icon="grid" />
            <x-stat-card title="Terjadwal" :value="$stats['scheduled']" caption="Mata kuliah punya jadwal" color="violet" icon="calendar" />
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <form method="GET" class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div class="grid flex-1 gap-3 md:grid-cols-[minmax(220px,1fr)_220px_160px_160px]">
                        <label class="relative">
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none"><path d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari kode atau mata kuliah..." class="h-11 w-full rounded-lg border-slate-200 pl-10 text-sm font-medium text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500/10">
                        </label>
                        <select name="department_id" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10" onchange="this.form.submit()">
                            <option value="">Semua Prodi</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <select name="semester" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10" onchange="this.form.submit()">
                            <option value="">Semua Semester</option>
                            @foreach ($semesters as $semester)
                                <option value="{{ $semester }}" @selected((string) request('semester') === (string) $semester)>Semester {{ $semester }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="active" @selected(request('status') === 'active')>Aktif</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 px-4 text-sm font-extrabold text-slate-700 hover:bg-slate-50">Filter</button>
                        <a href="{{ route($routePrefix.'.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700">+ Tambah Mata Kuliah</a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto px-5 pt-5">
                <table class="min-w-full overflow-hidden rounded-lg border border-slate-200">
                    <thead class="bg-slate-50"><tr>
                        <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Kode</th>
                        <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Mata Kuliah</th>
                        <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Program Studi</th>
                        <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">SKS</th>
                        <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Semester</th>
                        <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-5 py-4 text-center text-xs font-extrabold uppercase tracking-wide text-slate-500">Aksi</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $item)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-extrabold text-emerald-700">{{ $item->code }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-800">{{ $item->name }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $item->department?->name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-4"><span class="inline-flex rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $item->sks }}</span></td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $item->semester ? 'Semester '.$item->semester : '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-4">@include('admin.partials.lencana', ['value' => $item->status])</td>
                                <td class="whitespace-nowrap px-5 py-4">@include('admin.partials.table-actions', ['routePrefix' => $routePrefix, 'item' => $item, 'deleteLabel' => 'mata kuliah'])</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-12 text-center text-sm font-medium text-slate-500">Data mata kuliah belum tersedia.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('admin.partials.table-footer', ['items' => $items, 'perPage' => $perPage])
        </div>
    </div>
</x-app-layout>
