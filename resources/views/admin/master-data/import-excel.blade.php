<x-app-layout>
    <div class="mx-auto max-w-4xl space-y-5">
        <div>
            <h1 class="text-2xl font-extrabold tracking-normal text-slate-950">{{ $title }}</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">{{ $description }}</p>
        </div>

        @if (session('import_errors'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-700">
                <div class="font-extrabold">Import gagal. Perbaiki data berikut:</div>
                <ul class="mt-3 list-disc space-y-1 pl-5">
                    @foreach (session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <div
                x-data="{ fileName: '', dragging: false }"
                class="space-y-5"
            >
                <label
                    class="flex min-h-72 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed p-8 text-center transition"
                    :class="dragging ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 bg-slate-50 hover:border-emerald-300 hover:bg-emerald-50/50'"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="
                        dragging = false;
                        if ($event.dataTransfer.files.length) {
                            $refs.file.files = $event.dataTransfer.files;
                            fileName = $event.dataTransfer.files[0].name;
                        }
                    "
                >
                    <input
                        x-ref="file"
                        type="file"
                        name="file"
                        accept=".xlsx,.csv,.txt"
                        class="hidden"
                        @change="fileName = $event.target.files.length ? $event.target.files[0].name : ''"
                        required
                    >

                    <div class="grid h-16 w-16 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M14 3v5h5M12 17V9M8.5 12.5 12 9l3.5 3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <div class="mt-5 text-lg font-extrabold text-slate-950">Drag & drop file Excel di sini</div>
                    <div class="mt-2 text-sm font-medium text-slate-500">atau klik area ini untuk memilih file dari komputer.</div>
                    <div class="mt-4 rounded-lg bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm" x-text="fileName || 'Format: .xlsx atau .csv, maksimal 5 MB'"></div>
                </label>

                <x-input-error :messages="$errors->get('file')" class="mt-2" />

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ $templateRoute }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-emerald-200 px-4 text-sm font-extrabold text-emerald-700 hover:bg-emerald-50">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M14 3v5h5M9 15l3 3 3-3M12 18V10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Download Template
                    </a>

                    <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Kirim
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
