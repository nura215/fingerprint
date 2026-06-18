@php
    $normalized = is_bool($value) ? ($value ? 'active' : 'inactive') : strtolower((string) $value);
    $classes = match ($normalized) {
        '1', 'active', 'online', 'enrolled' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'maintenance' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'not_enrolled' => 'bg-sky-50 text-sky-700 ring-sky-200',
        '0', 'inactive', 'offline' => 'bg-slate-100 text-slate-600 ring-slate-200',
        default => 'bg-slate-100 text-slate-600 ring-slate-200',
    };

    $label = match ($normalized) {
        '1', 'active' => 'Active',
        '0', 'inactive' => 'Inactive',
        'online' => 'Online',
        'offline' => 'Offline',
        'maintenance' => 'Maintenance',
        'enrolled' => 'Enrolled',
        'not_enrolled' => 'Not Enrolled',
        default => $value ?: '-',
    };
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $classes }}">
    {{ $label }}
</span>

