<x-app-layout>
    <div class="space-y-5">
        <div>
            <h1 class="text-2xl font-extrabold tracking-normal text-slate-950">Manual Unlock</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Kirim perintah buka pintu darurat dan pantau riwayat akses manual dari web.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Total Perangkat" :value="$stats['devices']" caption="Perangkat akses pintu" color="emerald" icon="device" />
            <x-stat-card title="Perangkat Online" :value="$stats['online']" caption="Siap menerima command" color="blue" icon="check" />
            <x-stat-card title="Unlock Hari Ini" :value="$stats['manual_today']" caption="Perintah manual web" color="amber" icon="door" />
            <x-stat-card title="Akses Diterima" :value="$stats['granted_today']" caption="Seluruh akses hari ini" color="violet" icon="check" />
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
            <form method="POST" action="{{ route('admin.manual-unlock.store') }}" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                @csrf
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-lg font-extrabold text-slate-950">Buka Pintu Manual</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Gunakan saat ada akses darurat atau kebutuhan operasional dari admin.</p>
                </div>

                <div class="grid gap-5 p-5">
                    <div>
                        <label class="block text-sm font-extrabold text-slate-800" for="device_id">Perangkat / Ruangan</label>
                        <select id="device_id" name="device_id" class="mt-2 h-12 w-full rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                            <option value="">Pilih perangkat</option>
                            @foreach ($devices as $device)
                                <option value="{{ $device->id }}" @selected(old('device_id') == $device->id)>
                                    {{ $device->name }} - {{ $device->room?->name ?? 'Tanpa ruangan' }} ({{ strtoupper(str_replace('_', ' ', $device->connection_type)) }})
                                </option>
                            @endforeach
                        </select>
                        @error('device_id')
                            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-extrabold text-slate-800" for="reason">Alasan</label>
                        <textarea
                            id="reason"
                            name="reason"
                            rows="4"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm font-medium text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500/10"
                            placeholder="Contoh: akses darurat laboratorium"
                        >{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-800">
                        Perintah ini dicatat sebagai log manual web. Saat bridge SDK sudah aktif, command ini dapat diteruskan ke perangkat untuk membuka relay pintu.
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-100 px-5 py-4">
                    <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 21V4h10v17M5 21h14M14 13h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Buka Pintu
                    </button>
                </div>
            </form>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-lg font-extrabold text-slate-950">Riwayat Terbaru</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Log manual unlock dari admin.</p>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($recentLogs as $log)
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-sm font-extrabold text-slate-800">{{ $log->device?->name ?? 'Perangkat tidak tersedia' }}</div>
                                    <div class="mt-1 text-xs font-semibold text-slate-500">{{ $log->room?->name ?? '-' }} - {{ $log->access_time?->format('d M Y H:i') }}</div>
                                </div>
                                <span class="inline-flex rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-extrabold text-emerald-700 ring-1 ring-emerald-200">Dibuka</span>
                            </div>
                            <p class="mt-3 text-sm font-medium text-slate-600">{{ $log->reason ?: 'Manual unlock dari web.' }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center text-sm font-medium text-slate-500">Belum ada riwayat manual unlock.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
