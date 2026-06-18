<div class="flex flex-col gap-4 px-5 py-5 md:flex-row md:items-center md:justify-between">
    <div class="text-sm font-medium text-slate-500">
        @if ($items->total() > 0)
            Menampilkan {{ $items->firstItem() }} - {{ $items->lastItem() }} dari {{ number_format($items->total(), 0, ',', '.') }} data
        @else
            Menampilkan 0 data
        @endif
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        @include('admin.partials.paginasi', ['items' => $items])

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
