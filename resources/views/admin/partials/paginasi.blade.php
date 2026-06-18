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
