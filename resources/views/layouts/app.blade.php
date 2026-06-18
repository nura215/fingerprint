@php
    $sidebarSections = [
        [
            'title' => null,
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'dashboard',
                    'match' => 'dashboard',
                    'icon' => 'home',
                ],
            ],
        ],
        [
            'title' => 'Master',
            'items' => [
                [
                    'label' => 'Master Data',
                    'icon' => 'database',
                    'children' => [
                        ['label' => 'Dosen', 'route' => 'admin.lecturers.index', 'match' => 'admin.lecturers.*'],
                        ['label' => 'Mahasiswa', 'route' => 'admin.students.index', 'match' => 'admin.students.*'],
                        ['label' => 'Program Studi', 'route' => 'admin.departments.index', 'match' => 'admin.departments.*'],
                        ['label' => 'Kelas', 'route' => 'admin.classes.index', 'match' => 'admin.classes.*'],
                        ['label' => 'Mata Kuliah', 'route' => 'admin.subjects.index', 'match' => 'admin.subjects.*'],
                        ['label' => 'Ruangan', 'route' => 'admin.rooms.index', 'match' => 'admin.rooms.*'],
                        ['label' => 'Tahun Akademik', 'route' => 'admin.academic-years.index', 'match' => 'admin.academic-years.*'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Aktivitas',
            'items' => [
                [
                    'label' => 'Jadwal',
                    'route' => 'admin.schedules.index',
                    'match' => 'admin.schedules.*',
                    'icon' => 'calendar',
                ],
                [
                    'label' => 'Fingerprint',
                    'icon' => 'fingerprint',
                    'children' => [
                        ['label' => 'Device Fingerprint', 'route' => 'admin.devices.index', 'match' => 'admin.devices.*'],
                        ['label' => 'Biometric Enrollment', 'route' => 'admin.biometric-enrollments.index', 'match' => 'admin.biometric-enrollments.*'],
                    ],
                ],
                [
                    'label' => 'Absensi',
                    'icon' => 'clipboard',
                    'children' => [
                        ['label' => 'Detail Pertemuan', 'route' => 'admin.meetings.index', 'match' => 'admin.meetings.*'],
                    ],
                ],
                [
                    'label' => 'Akses Pintu',
                    'icon' => 'door',
                    'children' => [
                        ['label' => 'Manual Unlock', 'route' => 'admin.manual-unlock.index', 'match' => 'admin.manual-unlock.*'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Laporan',
            'items' => [
                [
                    'label' => 'Laporan',
                    'icon' => 'chart',
                    'children' => [
                        ['label' => 'Per Kelas', 'route' => 'admin.reports.classes', 'match' => 'admin.reports.classes'],
                        ['label' => 'Per Mata Kuliah', 'route' => 'admin.reports.subjects', 'match' => 'admin.reports.subjects'],
                        ['label' => 'Per Dosen', 'route' => 'admin.reports.lecturers', 'match' => 'admin.reports.lecturers'],
                        ['label' => 'Akses Ditolak', 'route' => 'admin.reports.denied-access', 'match' => 'admin.reports.denied-access'],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Sistem',
            'items' => [
                [
                    'label' => 'Pengaturan',
                    'icon' => 'settings',
                    'children' => [
                        ['label' => 'Audit Log', 'route' => 'admin.audit-logs.index', 'match' => 'admin.audit-logs.*'],
                    ],
                ],
            ],
        ],
    ];

    $icon = function (string $name): string {
        return match ($name) {
            'home' => '<path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
            'database' => '<path d="M4 6c0-2 16-2 16 0v12c0 2-16 2-16 0V6Z" stroke="currentColor" stroke-width="2"/><path d="M4 6c0 2 16 2 16 0M4 12c0 2 16 2 16 0" stroke="currentColor" stroke-width="2"/>',
            'calendar' => '<rect x="4" y="5" width="16" height="16" rx="2" stroke="currentColor" stroke-width="2"/><path d="M8 3v4M16 3v4M4 10h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
            'fingerprint' => '<path d="M7 12a5 5 0 0 1 10 0M9 15c0 3 1 5 3 7M12 12c0 5-.8 7-2 9M15 15c-.3 2.2-.9 4-2 6M5 16c-.7-5.7 1.9-10 7-10 4.6 0 7.4 3.3 7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
            'clipboard' => '<path d="M9 4h6l1 3H8l1-3Z" stroke="currentColor" stroke-width="2"/><rect x="5" y="6" width="14" height="16" rx="2" stroke="currentColor" stroke-width="2"/><path d="M9 13h6M9 17h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
            'door' => '<path d="M7 21V4h10v17M5 21h14M14 13h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
            'chart' => '<path d="M5 19V9M12 19V5M19 19v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 21h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
            'settings' => '<path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2 3.4-.2-.1a1.7 1.7 0 0 0-2 .2 1.7 1.7 0 0 0-.8 1.5V22H9.2v-.2a1.7 1.7 0 0 0-.8-1.5 1.7 1.7 0 0 0-2-.2l-.2.1-2-3.4.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.4-1.1H3V10h.2a1.7 1.7 0 0 0 1.4-1.1 1.7 1.7 0 0 0-.3-1.9l-.1-.1 2-3.4.2.1a1.7 1.7 0 0 0 2-.2A1.7 1.7 0 0 0 9.2 2h5.6v.2a1.7 1.7 0 0 0 .8 1.5 1.7 1.7 0 0 0 2 .2l.2-.1 2 3.4-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.4 1.1h.2v3.8h-.2a1.7 1.7 0 0 0-1.4.9Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>',
            default => '<circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/>',
        };
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
        <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div
            x-data="{ sidebarOpen: false, profileModalOpen: {{ ($errors->updateProfile->any() || $errors->updatePassword->any() || session('status') === 'profile-updated' || session('status') === 'password-updated') ? 'true' : 'false' }} }"
            class="min-h-screen bg-slate-50 text-slate-900"
        >
            <aside
                class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:translate-x-0"
                :class="{ 'translate-x-0': sidebarOpen }"
            >
                <div class="flex h-[84px] items-center gap-3 border-b border-slate-100 px-5">
                    <div class="grid h-11 w-11 place-items-center text-emerald-600">
                        <svg class="h-11 w-11" viewBox="0 0 96 96" fill="none" aria-hidden="true">
                            <path d="M17 50V38c0-17.12 13.88-31 31-31s31 13.88 31 31v12" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/>
                            <path d="M27 50V38c0-11.6 9.4-21 21-21s21 9.4 21 21v12" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/>
                            <path d="M37 50V38c0-6.08 4.92-11 11-11s11 4.92 11 11v12" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/>
                            <path d="M47.95 40.5v13" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/>
                            <rect x="30" y="50" width="36" height="32" rx="8" stroke="currentColor" stroke-width="4.5"/>
                            <path d="M48 63.5v7" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/>
                            <circle cx="48" cy="61" r="4" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-lg font-extrabold leading-tight text-emerald-700">Smart Fingerprint</div>
                    </div>
                </div>

                <nav class="flex-1 overflow-y-auto px-3 py-4">
                    @foreach ($sidebarSections as $section)
                        @if ($section['title'])
                            <div class="mb-2 mt-5 px-3 text-[11px] font-extrabold uppercase tracking-wide text-slate-400 first:mt-0">{{ $section['title'] }}</div>
                        @endif

                        <div class="space-y-1">
                            @foreach ($section['items'] as $item)
                                @php
                                    $children = $item['children'] ?? [];
                                    $active = isset($item['match'])
                                        ? request()->routeIs($item['match'])
                                        : collect($children)->contains(fn ($child) => request()->routeIs($child['match']));
                                @endphp

                                @if ($children)
                                    <div x-data="{ open: {{ $active ? 'true' : 'false' }} }">
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-3 rounded-lg px-3 py-3 text-left text-sm font-bold transition {{ $active ? 'text-emerald-700' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-700' }}"
                                            @click="open = ! open"
                                        >
                                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">{!! $icon($item['icon']) !!}</svg>
                                            <span class="min-w-0 flex-1">{{ $item['label'] }}</span>
                                            <svg class="h-4 w-4 shrink-0 transition-transform" :class="{ 'rotate-90': open }" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>

                                        <div x-show="open" class="mt-1 space-y-1 pl-11">
                                            @foreach ($children as $child)
                                                @php $childActive = request()->routeIs($child['match']); @endphp
                                                <a
                                                    href="{{ route($child['route']) }}"
                                                    class="block rounded-lg px-3 py-2 text-sm font-semibold transition {{ $childActive ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-700' }}"
                                                >
                                                    {{ $child['label'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <a
                                        href="{{ route($item['route']) }}"
                                        class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-bold transition {{ $active ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-700' }}"
                                    >
                                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">{!! $icon($item['icon']) !!}</svg>
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </nav>

            </aside>

            <div x-show="sidebarOpen" class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden" @click="sidebarOpen = false"></div>

            <div class="min-h-screen lg:pl-72">
                <header class="sticky top-0 z-20 flex h-[84px] items-center justify-between border-b border-slate-200 bg-white px-4 shadow-sm sm:px-6">
                    <div class="flex min-w-0 items-center gap-4">
                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-700 hover:bg-slate-100 lg:hidden"
                            @click="sidebarOpen = true"
                            aria-label="Open sidebar"
                        >
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                        </button>

                        <div class="min-w-0">
                            <div class="text-xl font-extrabold text-slate-950">Dashboard</div>
                            <div class="text-xs font-medium text-slate-500">Selamat datang, {{ Auth::user()->name }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 sm:gap-5">
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button
                                type="button"
                                class="flex items-center gap-3 rounded-xl px-2 py-1.5 text-left"
                                @click="open = ! open"
                                :aria-expanded="open.toString()"
                            >
                                <span class="hidden text-sm font-extrabold leading-tight text-slate-950 sm:block">{{ Auth::user()->name }}</span>
                                <span class="relative grid h-11 w-11 place-items-center rounded-full bg-emerald-600 text-white">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <span class="absolute -bottom-0.5 -right-0.5 grid h-5 w-5 place-items-center rounded-full border-2 border-white bg-emerald-800 text-white">
                                        <svg class="h-3 w-3 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="m7 10 5 5 5-5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </span>
                            </button>

                            <div
                                x-show="open"
                                x-transition
                                class="absolute right-0 z-50 mt-2 w-64 overflow-hidden rounded-xl border border-slate-200 bg-white py-2 shadow-xl"
                            >
                                <button
                                    type="button"
                                    class="flex w-full items-center gap-4 px-5 py-4 text-left text-base font-extrabold text-slate-800 hover:bg-emerald-50 hover:text-emerald-700"
                                    @click="profileModalOpen = true; open = false"
                                >
                                    <svg class="h-5 w-5 text-emerald-700" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <span class="min-w-0 flex-1">Ubah Profile</span>
                                    <svg class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="flex w-full items-center gap-4 border-t border-slate-100 px-5 py-4 text-left text-base font-extrabold text-slate-800 hover:bg-red-50 hover:text-red-600" type="submit">
                                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M10 17l5-5-5-5M15 12H3M21 3v18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span class="min-w-0 flex-1">Logout</span>
                                        <svg class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <div
                    x-show="profileModalOpen"
                    x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Ubah Profile"
                >
                    <div class="w-full max-w-4xl rounded-2xl bg-white shadow-2xl" @click.outside="profileModalOpen = false">
                        <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-7 py-6">
                            <div>
                                <h2 class="text-2xl font-extrabold tracking-normal text-slate-950">Ubah Profile</h2>
                                <p class="mt-1 text-sm font-medium text-slate-500">Ubah email akun atau perbarui password Anda.</p>
                            </div>
                            <button
                                type="button"
                                class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-lg font-extrabold text-slate-700 hover:bg-slate-50"
                                @click="profileModalOpen = false"
                                aria-label="Tutup modal"
                            >
                                x
                            </button>
                        </div>

                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('patch')

                            @if (session('status') === 'profile-updated')
                                <div class="mx-7 mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">Profile berhasil diperbarui.</div>
                            @endif

                            <div class="grid gap-6 px-7 py-6 lg:grid-cols-2">
                                <div class="space-y-5">
                                    <div>
                                        <h3 class="text-lg font-extrabold text-slate-950">Email</h3>
                                        <p class="mt-1 text-sm text-slate-500">Ubah data nama dan email yang dipakai untuk login.</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-slate-600" for="profile_name">Nama</label>
                                        <input
                                            id="profile_name"
                                            name="name"
                                            type="text"
                                            value="{{ old('name', Auth::user()->name) }}"
                                            class="mt-2 h-12 w-full rounded-xl border-slate-300 text-sm font-semibold text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10"
                                            required
                                        >
                                        <x-input-error :messages="$errors->updateProfile->get('name')" class="mt-2" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-slate-600" for="profile_email">Email</label>
                                        <input
                                            id="profile_email"
                                            name="email"
                                            type="email"
                                            value="{{ old('email', Auth::user()->email) }}"
                                            class="mt-2 h-12 w-full rounded-xl border-slate-300 text-sm font-semibold text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10"
                                            required
                                        >
                                        <x-input-error :messages="$errors->updateProfile->get('email')" class="mt-2" />
                                    </div>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <h3 class="text-lg font-extrabold text-slate-950">Ubah Password</h3>
                                        <p class="mt-1 text-sm text-slate-500">Buat password baru untuk akun Anda.</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-slate-600" for="new_password">Password Baru</label>
                                        <input
                                            id="new_password"
                                            name="password"
                                            type="password"
                                            placeholder="Minimal 8 karakter"
                                            class="mt-2 h-12 w-full rounded-xl border-slate-300 text-sm font-semibold text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10"
                                            autocomplete="new-password"
                                        >
                                        <x-input-error :messages="$errors->updateProfile->get('password')" class="mt-2" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-slate-600" for="password_confirmation">Konfirmasi Password Baru</label>
                                        <input
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type="password"
                                            placeholder="Ulangi password baru"
                                            class="mt-2 h-12 w-full rounded-xl border-slate-300 text-sm font-semibold text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10"
                                            autocomplete="new-password"
                                        >
                                        <x-input-error :messages="$errors->updateProfile->get('password_confirmation')" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 border-t border-slate-100 px-7 py-5">
                                <button type="button" class="rounded-xl border border-slate-300 px-6 py-2.5 text-sm font-extrabold text-slate-800 hover:bg-slate-50" @click="profileModalOpen = false">Batal</button>
                                <button type="submit" class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <main class="p-4 sm:p-6">
                    @if (session('success') || session('error'))
                        <div class="mb-5 rounded-lg border px-4 py-3 text-sm font-semibold {{ session('success') ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                            {{ session('success') ?? session('error') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
