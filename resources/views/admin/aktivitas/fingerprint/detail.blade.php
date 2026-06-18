<x-app-layout>
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">{{ $sectionTitle ?? 'Master Data' }}</p>
                <h1 class="mt-2 text-2xl font-extrabold text-slate-950">{{ $title }}</h1>
            </div>

            <div class="flex gap-2">
                <a href="{{ route($routePrefix.'.edit', $item) }}" class="inline-flex items-center justify-center rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-sky-700">
                    Edit
                </a>
                <a href="{{ route($routePrefix.'.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Kembali
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <dl class="divide-y divide-slate-100">
                @foreach ($fields as $field)
                    @php
                        $value = data_get($item, $field['key']);
                        if (($field['type'] ?? null) === 'datetime' && $value) {
                            $value = $value->format('d M Y H:i');
                        }
                    @endphp
                    <div class="grid gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-bold text-slate-500">{{ $field['label'] }}</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">
                            @if (($field['badge'] ?? false) === true)
                                @include('admin.partials.lencana', ['value' => $value])
                            @else
                                {{ filled($value) ? $value : '-' }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
</x-app-layout>

