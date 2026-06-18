<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">{{ $sectionTitle ?? 'Master Data' }}</p>
                <h1 class="mt-2 text-2xl font-extrabold text-slate-950">{{ $title }}</h1>
                @if ($description)
                    <p class="mt-2 max-w-3xl text-sm text-slate-600">{{ $description }}</p>
                @endif
            </div>

            <a href="{{ route($routePrefix.'.create') }}" class="inline-flex items-center justify-center rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-sky-700">
                Tambah Data
            </a>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_auto_auto]">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari data..."
                    class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500"
                >

                @if ($filterColumn)
                    <select name="status" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                        <option value="">Semua Status</option>
                        @foreach ($filterOptions as $value => $label)
                            <option value="{{ $value }}" @selected((string) request('status') === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                @endif

                <div class="flex gap-2">
                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800" type="submit">Filter</button>
                    <a href="{{ route($routePrefix.'.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach ($columns as $column)
                                <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">{{ $column['label'] }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $item)
                            <tr class="hover:bg-slate-50">
                                @foreach ($columns as $column)
                                    @php
                                        $value = data_get($item, $column['key']);
                                        if (($column['type'] ?? null) === 'datetime' && $value) {
                                            $value = $value->format('d M Y H:i');
                                        }
                                    @endphp
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700">
                                        @if (($column['badge'] ?? false) === true)
                                            @include('admin.partials.lencana', ['value' => $value])
                                        @else
                                            {{ filled($value) ? $value : '-' }}
                                        @endif
                                    </td>
                                @endforeach
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route($routePrefix.'.show', $item) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 font-bold text-slate-700 hover:bg-slate-50">Detail</a>
                                        <a href="{{ route($routePrefix.'.edit', $item) }}" class="rounded-lg border border-sky-200 px-3 py-1.5 font-bold text-sky-700 hover:bg-sky-50">Edit</a>
                                        <form method="POST" action="{{ route($routePrefix.'.destroy', $item) }}" onsubmit="return confirm('Yakin ingin memproses data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg border border-rose-200 px-3 py-1.5 font-bold text-rose-700 hover:bg-rose-50" type="submit">
                                                {{ $filterColumn && $inactiveValue ? 'Nonaktifkan' : 'Hapus' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + 1 }}" class="px-4 py-10 text-center text-sm text-slate-500">
                                    Data belum tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-4 py-3">
                {{ $items->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

