<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Masuk - {{ config('app.name', 'Smart Fingerprint') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
        <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/css/login.css', 'resources/js/app.js', 'resources/js/login.js'])
    </head>
    <body class="min-h-screen overflow-y-auto bg-white font-sans text-slate-950 antialiased lg:h-screen lg:overflow-hidden">
        <main class="grid min-h-screen bg-white lg:h-screen lg:grid-cols-2">
            <section class="login-brand-bg relative flex min-h-[42vh] items-center justify-center overflow-hidden px-6 py-10 lg:h-screen lg:min-h-0 lg:px-10 lg:py-8 2xl:px-14" aria-label="Smart Fingerprint">
                <div class="relative z-10 flex w-full max-w-[520px] flex-col items-center justify-center text-center">
                    <svg class="mb-3 h-20 w-20 text-emerald-600 lg:h-[92px] lg:w-[92px]" viewBox="0 0 96 96" fill="none" aria-hidden="true">
                        <path d="M17 50V38c0-17.12 13.88-31 31-31s31 13.88 31 31v12" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/>
                        <path d="M27 50V38c0-11.6 9.4-21 21-21s21 9.4 21 21v12" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/>
                        <path d="M37 50V38c0-6.08 4.92-11 11-11s11 4.92 11 11v12" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/>
                        <path d="M47.95 40.5v13" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/>
                        <rect x="30" y="50" width="36" height="32" rx="8" stroke="currentColor" stroke-width="4.5"/>
                        <path d="M48 63.5v7" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/>
                        <circle cx="48" cy="61" r="4" fill="currentColor"/>
                    </svg>

                    <h1 class="text-3xl font-extrabold leading-tight tracking-normal text-slate-950 sm:text-4xl lg:text-[38px]">
                        <span class="text-emerald-600">Smart</span> Fingerprint
                    </h1>
                    <p class="mt-2 text-sm font-bold text-slate-900 sm:text-base">Attendance & Door Access System</p>
                    <div class="mt-3 h-0.5 w-12 rounded-full bg-emerald-600"></div>
                    <p class="mt-4 max-w-[410px] text-sm leading-6 text-slate-500">
                        Sistem keamanan dan kehadiran berbasis fingerprint untuk kampus dan ruang kelas modern.
                    </p>

                    <div class="door-scene relative mt-7 hidden h-[282px] w-full max-w-[420px] sm:block" aria-hidden="true">
                        <div class="floor-shadow"></div>
                        <div class="door"><span class="door-handle"></span></div>
                        <div class="reader">
                            <svg width="40" height="40" viewBox="0 0 48 48" fill="none">
                                <path d="M14 23c0-5.52 4.48-10 10-10s10 4.48 10 10" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
                                <path d="M19 25c0-2.76 2.24-5 5-5s5 2.24 5 5c0 7-3 9.5-3 13" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
                                <path d="M14 30c0 5.5 2 8 4.5 10" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
                                <path d="M24 25c0 8-2 10.5-2 16" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
                                <path d="M34 29c-.4 4-1.4 6.9-3.5 10.5" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="shield-card">
                            <svg width="34" height="34" viewBox="0 0 48 48" fill="none">
                                <path d="M24 6 38 11v10c0 10.2-5.7 17.7-14 21-8.3-3.3-14-10.8-14-21V11l14-5Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                                <path d="m18 24 4 4 8-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <svg class="plant" viewBox="0 0 130 210" fill="none">
                            <path d="M66 205V82" stroke="#2f8e58" stroke-width="4" stroke-linecap="round"/>
                            <path d="M66 171c-26-2-44-17-54-42 28 1 48 14 54 42Z" fill="#58b678"/>
                            <path d="M67 143c25-4 39-20 45-43-27 3-43 18-45 43Z" fill="#68bf84"/>
                            <path d="M65 117c-22-5-36-20-42-43 25 4 40 19 42 43Z" fill="#63b87f"/>
                            <path d="M67 92c18-7 28-22 31-42-21 7-32 22-31 42Z" fill="#78c991"/>
                            <path d="M65 77C47 66 40 50 42 31c19 10 27 26 23 46Z" fill="#58b678"/>
                        </svg>
                    </div>
                </div>
            </section>

            <section class="flex min-h-[58vh] items-center justify-center px-6 py-10 lg:h-screen lg:min-h-0 lg:px-10 lg:py-8 2xl:px-14">
                <div class="w-full max-w-[500px] rounded-xl border border-slate-200 bg-white px-7 py-8 shadow-[0_24px_70px_rgba(15,23,42,0.10)] sm:px-10 sm:py-10">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                        <svg width="40" height="40" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                            <path d="M19 14V9h20v30H19v-5" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="m27 17 7 7-7 7" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 24h24" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/>
                        </svg>
                    </div>

                    <h2 class="mt-5 text-center text-2xl font-extrabold tracking-normal text-slate-950 sm:text-[28px]">Selamat Datang Kembali</h2>
                    <p class="mt-2 text-center text-sm text-slate-500">Silakan masuk untuk melanjutkan ke dashboard</p>

                    @if (session('status'))
                        <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
                    @endif

                    <form class="mt-7" method="POST" action="{{ route('login') }}">
                        @csrf

                        <div>
                            <label class="block text-sm font-extrabold text-slate-950" for="email">Email</label>
                            <div class="relative mt-2">
                                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <input id="email" class="h-[52px] w-full rounded-lg border border-slate-300 bg-white pl-12 pr-4 text-sm font-semibold text-slate-950 outline-none transition placeholder:font-medium placeholder:text-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10" type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email Anda" required autofocus autocomplete="username">
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-5">
                            <label class="block text-sm font-extrabold text-slate-950" for="password">Password</label>
                            <div class="relative mt-2" data-password-field>
                                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-emerald-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M12 14v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <input id="password" class="h-[52px] w-full rounded-lg border border-slate-300 bg-white pl-12 pr-12 text-sm font-semibold text-slate-950 outline-none transition placeholder:font-medium placeholder:text-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10" type="password" name="password" placeholder="Masukkan password Anda" required autocomplete="current-password" data-login-password>
                                <button class="absolute right-3 top-1/2 z-20 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-lg text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-600 focus:bg-emerald-50 focus:text-emerald-600 focus:outline-none" type="button" aria-label="Tampilkan password" aria-pressed="false" data-login-password-toggle>
                                    <svg data-password-visible-icon class="hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                    <svg data-password-hidden-icon width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M3 3l18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a17.6 17.6 0 0 1-2.8 3.4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M6.3 6.9C3.9 8.6 2.5 12 2.5 12s3.5 6 9.5 6c1.4 0 2.7-.3 3.8-.8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-5 flex items-center justify-between gap-4">
                            <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-600" for="remember_me">
                                <input id="remember_me" class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-4 focus:ring-emerald-600/15" type="checkbox" name="remember">
                                <span>Ingat saya</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm font-bold text-emerald-700 no-underline hover:underline focus:outline-none" href="{{ route('password.request') }}">Lupa password?</a>
                            @endif
                        </div>

                        <button class="mt-5 inline-flex h-[52px] w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 text-base font-extrabold text-white shadow-[0_14px_24px_rgba(5,150,105,0.24)] transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-[0_18px_30px_rgba(5,150,105,0.28)] focus:outline-none focus:ring-4 focus:ring-emerald-600/20" type="submit">
                            <svg width="22" height="22" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                                <path d="M19 14V9h20v30H19v-5" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="m27 17 7 7-7 7" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 24h24" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/>
                            </svg>
                            <span>Masuk</span>
                        </button>
                    </form>
                </div>
            </section>
        </main>
    </body>
</html>
