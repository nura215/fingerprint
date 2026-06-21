<x-app-layout>
    <div class="mx-auto max-w-5xl space-y-5">
        <div>
            <h1 class="text-2xl font-extrabold tracking-normal text-slate-950">{{ $title }}</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">{{ $method === 'POST' ? 'Lengkapi data baru' : 'Perbarui data yang dipilih.' }}</p>
        </div>

        <form method="POST" action="{{ $action }}" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div class="grid gap-6 p-6 md:grid-cols-2">
                @foreach ($fields as $field)
                    @php
                        $name = $field['name'];
                        $fieldType = $field['type'] ?? 'text';
                        $rawValue = old($name, $item ? data_get($item, $name) : ($field['default'] ?? ''));
                        $value = is_bool($rawValue) ? ($rawValue ? '1' : '0') : $rawValue;
                        if ($rawValue instanceof \Illuminate\Support\Carbon && $fieldType === 'datetime-local') {
                            $value = $rawValue->format('Y-m-d\TH:i');
                        }
                        if ($rawValue instanceof \Illuminate\Support\Carbon && $fieldType === 'date') {
                            $value = $rawValue->format('Y-m-d');
                        }
                        if (is_string($value) && $fieldType === 'time') {
                            $value = substr($value, 0, 5);
                        }
                    @endphp

                    <div class="{{ ($field['wide'] ?? false) ? 'md:col-span-2' : '' }}">
                        <label for="{{ $name }}" class="block text-sm font-extrabold text-slate-800">
                            {{ $field['label'] }}
                            @if ($field['required'] ?? false)
                                <span class="text-rose-600">*</span>
                            @endif
                        </label>

                        @if ($fieldType === 'select')
                            <select
                                id="{{ $name }}"
                                name="{{ $name }}"
                                class="mt-2 block h-12 w-full rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10"
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
                                class="mt-2 block h-12 w-full rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500/10"
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

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 px-6 py-5 sm:flex-row sm:justify-end">
                <a href="{{ route($routePrefix.'.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 px-5 text-sm font-extrabold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button class="inline-flex h-11 items-center justify-center rounded-lg bg-emerald-600 px-5 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700" type="submit">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
