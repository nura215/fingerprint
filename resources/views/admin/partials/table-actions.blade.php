<div class="flex justify-center gap-2">
    <a href="{{ route($routePrefix.'.edit', $item) }}" class="grid h-9 w-9 place-items-center rounded-lg border border-emerald-200 text-emerald-700 hover:bg-emerald-50" aria-label="Edit">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        </svg>
    </a>
    <form method="POST" action="{{ route($routePrefix.'.destroy', $item) }}" onsubmit="return confirm('Yakin ingin menghapus {{ $deleteLabel ?? 'data' }} ini?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50" aria-label="Hapus">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7l1-3h4l1 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </form>
</div>
