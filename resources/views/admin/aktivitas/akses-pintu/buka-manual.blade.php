<x-app-layout>
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Akses Pintu</p>
            <h1 class="mt-2 text-2xl font-extrabold text-slate-950">Manual Unlock</h1>
            <p class="mt-2 text-sm text-slate-600">Tombol ini baru mencatat perintah buka pintu ke log. Integrasi fisik pintu dilakukan pada tahap middleware/SDK berikutnya.</p>
        </div>

        <form method="POST" action="{{ route('admin.manual-unlock.store') }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            <div class="grid gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-800" for="device_id">Device / Ruangan</label>
                    <select id="device_id" name="device_id" class="mt-2 block w-full rounded-lg border-slate-300 text-sm">
                        <option value="">Pilih device</option>
                        @foreach ($devices as $device)
                            <option value="{{ $device->id }}">{{ $device->name }} - {{ $device->room->name }}</option>
                        @endforeach
                    </select>
                    @error('device_id')<p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-800" for="reason">Alasan</label>
                    <input id="reason" name="reason" value="{{ old('reason') }}" class="mt-2 block w-full rounded-lg border-slate-300 text-sm" placeholder="Contoh: akses darurat lab">
                    @error('reason')<p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <button class="mt-6 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-sky-700" type="submit">Buka Pintu</button>
        </form>
    </div>
</x-app-layout>

