<x-app-layout>
    <div class="space-y-5">
        <div>
            <h1 class="text-2xl font-extrabold tracking-normal text-slate-950">Data Program Studi</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Kelola data program studi dan informasi fakultas.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="grid h-14 w-14 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 21V7l8-4 8 4v14M8 21v-7h8v7M8 9h.01M12 9h.01M16 9h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-500">Total Program Studi</div>
                        <div class="mt-1 text-3xl font-extrabold text-slate-950">{{ number_format($stats['total'], 0, ',', '.') }}</div>
                        <div class="mt-2 text-xs font-medium text-slate-500">Seluruh program studi terdaftar</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="grid h-14 w-14 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-500">Total Fakultas</div>
                        <div class="mt-1 text-3xl font-extrabold text-slate-950">{{ number_format($stats['faculties'], 0, ',', '.') }}</div>
                        <div class="mt-2 text-xs font-medium text-slate-500">Fakultas yang terhubung</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="grid h-14 w-14 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-500">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 6h16M4 12h16M4 18h16M8 6v12M16 6v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-500">Kelas Terkait</div>
                        <div class="mt-1 text-3xl font-extrabold text-slate-950">{{ number_format($stats['classes'], 0, ',', '.') }}</div>
                        <div class="mt-2 text-xs font-medium text-slate-500">Kelas dalam program studi</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="grid h-14 w-14 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-600">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 19.5V5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2M8 7h6M8 11h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-500">Mata Kuliah</div>
                        <div class="mt-1 text-3xl font-extrabold text-slate-950">{{ number_format($stats['subjects'], 0, ',', '.') }}</div>
                        <div class="mt-2 text-xs font-medium text-slate-500">Mata kuliah terhubung</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <form method="GET" class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div class="grid flex-1 gap-3 md:grid-cols-[minmax(220px,1fr)_220px]">
                        <label class="relative">
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <input
                                type="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Cari kode, program studi, atau fakultas..."
                                class="h-11 w-full rounded-lg border-slate-200 pl-10 text-sm font-medium text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500/10"
                            >
                        </label>

                        <select name="faculty" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10" onchange="this.form.submit()">
                            <option value="">Semua Fakultas</option>
                            @foreach ($faculties as $faculty)
                                <option value="{{ $faculty }}" @selected(request('faculty') === $faculty)>{{ $faculty }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 px-4 text-sm font-extrabold text-slate-700 hover:bg-slate-50">Filter</button>
                        <a href="{{ route($routePrefix.'.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Tambah Program Studi
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto px-5 pt-5">
                <table class="min-w-full overflow-hidden rounded-lg border border-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Kode</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Program Studi</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Fakultas</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Kelas</th>
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Mata Kuliah</th>
                            <th class="px-5 py-4 text-center text-xs font-extrabold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $item)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-extrabold text-emerald-700">{{ $item->code }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-800">{{ $item->name }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $item->faculty }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ number_format($item->classes_count, 0, ',', '.') }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ number_format($item->subjects_count, 0, ',', '.') }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route($routePrefix.'.edit', $item) }}" class="grid h-9 w-9 place-items-center rounded-lg border border-emerald-200 text-emerald-700 hover:bg-emerald-50" aria-label="Edit {{ $item->name }}">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route($routePrefix.'.destroy', $item) }}" onsubmit="return confirm('Yakin ingin menghapus program studi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50" aria-label="Hapus {{ $item->name }}">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7l1-3h4l1 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-sm font-medium text-slate-500">Data program studi belum tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-4 px-5 py-5 md:flex-row md:items-center md:justify-between">
                <div class="text-sm font-medium text-slate-500">
                    @if ($items->total() > 0)
                        Menampilkan {{ $items->firstItem() }} - {{ $items->lastItem() }} dari {{ number_format($items->total(), 0, ',', '.') }} data
                    @else
                        Menampilkan 0 data
                    @endif
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    @if ($items->lastPage() > 1)
                        @php
                            $currentPage = $items->currentPage();
                            $lastPage = $items->lastPage();
                            $startPage = max(1, $currentPage - 1);
                            $endPage = min($lastPage, $currentPage + 1);
                        @endphp
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ $items->previousPageUrl() ?: '#' }}" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-500 {{ $items->onFirstPage() ? 'pointer-events-none opacity-50' : 'hover:bg-slate-50' }}" aria-label="Halaman sebelumnya">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>

                            @if ($startPage > 1)
                                <a href="{{ $items->url(1) }}" class="grid h-9 min-w-9 place-items-center rounded-lg border border-slate-200 px-3 text-sm font-bold text-slate-600 hover:bg-slate-50">1</a>
                                @if ($startPage > 2)
                                    <span class="grid h-9 place-items-center px-1 text-sm font-bold text-slate-400">...</span>
                                @endif
                            @endif

                            @for ($page = $startPage; $page <= $endPage; $page++)
                                <a href="{{ $items->url($page) }}" class="grid h-9 min-w-9 place-items-center rounded-lg px-3 text-sm font-extrabold {{ $page === $currentPage ? 'bg-emerald-600 text-white shadow-sm' : 'border border-slate-200 text-slate-600 hover:bg-slate-50' }}">{{ $page }}</a>
                            @endfor

                            @if ($endPage < $lastPage)
                                @if ($endPage < $lastPage - 1)
                                    <span class="grid h-9 place-items-center px-1 text-sm font-bold text-slate-400">...</span>
                                @endif
                                <a href="{{ $items->url($lastPage) }}" class="grid h-9 min-w-9 place-items-center rounded-lg border border-slate-200 px-3 text-sm font-bold text-slate-600 hover:bg-slate-50">{{ $lastPage }}</a>
                            @endif

                            <a href="{{ $items->nextPageUrl() ?: '#' }}" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-500 {{ $items->hasMorePages() ? 'hover:bg-slate-50' : 'pointer-events-none opacity-50' }}" aria-label="Halaman berikutnya">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    @endif

                    <form method="GET">
                        @foreach (request()->except('per_page', 'page') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <select name="per_page" class="h-10 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10" onchange="this.form.submit()">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }} / halaman</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
