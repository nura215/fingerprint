<x-app-layout>
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">{{ $sectionTitle ?? 'Master Data' }}</p>
                <h1 class="mt-2 text-2xl font-extrabold text-slate-950">{{ $title }}</h1>
            </div>

            <a href="{{ route($routePrefix.'.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">
                Kembali
            </a>
        </div>

        <form method="POST" action="{{ $action }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div class="grid gap-5 md:grid-cols-2">
                @foreach ($fields as $field)
                    @php
                        $name = $field['name'];
                        $fieldType = $field['type'] ?? 'text';
                        $rawValue = old($name, $item ? data_get($item, $name) : ($field['default'] ?? ''));
                        $value = is_bool($rawValue) ? ($rawValue ? '1' : '0') : $rawValue;
                        if ($rawValue instanceof \Illuminate\Support\Carbon && $fieldType === 'datetime-local') {
                            $value = $rawValue->format('Y-m-d\TH:i');
                        }
                        if (is_string($value) && $fieldType === 'time') {
                            $value = substr($value, 0, 5);
                        }
                    @endphp

                    <div class="{{ ($field['wide'] ?? false) ? 'md:col-span-2' : '' }}">
                        <label for="{{ $name }}" class="block text-sm font-bold text-slate-800">
                            {{ $field['label'] }}
                            @if ($field['required'] ?? false)
                                <span class="text-rose-600">*</span>
                            @endif
                        </label>

                        @if ($fieldType === 'select')
                            <select
                                id="{{ $name }}"
                                name="{{ $name }}"
                                class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500"
                            >
                                <option value="">Pilih {{ $field['label'] }}</option>
                                @foreach (($field['options'] ?? []) as $optionValue => $optionLabel)
                                    @if (is_array($optionLabel))
                                        <optgroup label="{{ $optionValue }}">
                                            @foreach ($optionLabel as $groupValue => $groupLabel)
                                                @php
                                                    $groupMatches = ! isset($field['selected_group']) || (string) $field['selected_group'] === (string) $optionValue;
                                                @endphp
                                                <option value="{{ $groupValue }}" @selected($groupMatches && (string) $value === (string) $groupValue)>{{ $groupLabel }}</option>
                                            @endforeach
                                        </optgroup>
                                    @else
                                        <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                                    @endif
                                @endforeach
                            </select>
                        @else
                            <input
                                id="{{ $name }}"
                                name="{{ $name }}"
                                type="{{ $fieldType }}"
                                value="{{ $fieldType === 'password' ? '' : $value }}"
                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500"
                            >
                        @endif

                        @if ($field['help'] ?? false)
                            <p class="mt-1 text-xs text-slate-500">{{ $field['help'] }}</p>
                        @endif

                        @error($name)
                            <p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route($routePrefix.'.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button class="inline-flex items-center justify-center rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-sky-700" type="submit">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>

