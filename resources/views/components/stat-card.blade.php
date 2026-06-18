@props(['title', 'value', 'caption', 'color' => 'emerald', 'icon' => 'grid'])

@php
    $tones = [
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'amber' => 'bg-amber-50 text-amber-500',
        'violet' => 'bg-violet-50 text-violet-600',
        'rose' => 'bg-rose-50 text-rose-500',
    ];

    $iconPath = match ($icon) {
        'check' => '<path d="m5 12 4 4 10-10" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>',
        'users' => '<path d="M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 20c.8-3.4 3.8-5 8-5s7.2 1.6 8 5M5 10.5a2.5 2.5 0 1 0 0-5M19 10.5a2.5 2.5 0 1 0 0-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
        'calendar' => '<rect x="4" y="5" width="16" height="16" rx="2" stroke="currentColor" stroke-width="2"/><path d="M8 3v4M16 3v4M4 10h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'book' => '<path d="M4 19.5V5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2M8 7h6M8 11h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'door' => '<path d="M7 21V4h10v17M5 21h14M14 13h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'device' => '<rect x="7" y="3" width="10" height="18" rx="2" stroke="currentColor" stroke-width="2"/><path d="M11 17h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        default => '<path d="M4 6h16M4 12h16M4 18h16M8 6v12M16 6v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    };
@endphp

<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start gap-4">
        <div class="grid h-14 w-14 shrink-0 place-items-center rounded-xl {{ $tones[$color] ?? $tones['emerald'] }}">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">{!! $iconPath !!}</svg>
        </div>
        <div>
            <div class="text-sm font-semibold text-slate-500">{{ $title }}</div>
            <div class="mt-1 text-3xl font-extrabold text-slate-950">{{ number_format((int) $value, 0, ',', '.') }}</div>
            <div class="mt-2 text-xs font-medium text-slate-500">{{ $caption }}</div>
        </div>
    </div>
</div>
