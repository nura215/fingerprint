<x-app-layout>
    <div class="space-y-5" x-data="{ reportType: '{{ $reportType }}', downloadModalOpen: false }">
        <div>
            <h1 class="text-2xl font-extrabold tracking-normal text-slate-950">Laporan Kehadiran</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Pilih jenis laporan untuk melihat rekap per kelas, mata kuliah, atau dosen.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-stat-card
                :title="$reportType === 'all' ? 'Total Scan' : ($reportType === 'class' ? 'Mahasiswa' : ($reportType === 'subject' ? 'Jadwal' : 'Jadwal'))"
                :value="$stats['primary']"
                :caption="$reportType === 'all' ? 'Data bulan berjalan' : ($reportType === 'class' ? 'Data sesuai filter kelas' : 'Data sesuai filter')"
                color="emerald"
                icon="users"
            />
            <x-stat-card
                :title="$reportType === 'all' ? 'Mahasiswa' : ($reportType === 'class' ? 'Pertemuan' : ($reportType === 'subject' ? 'Pertemuan' : 'Hadir'))"
                :value="$stats['secondary']"
                :caption="$reportType === 'all' ? 'Scan mahasiswa' : ($reportType === 'lecturer' ? 'Scan hadir dan terlambat' : 'Total pertemuan')"
                color="blue"
                icon="calendar"
            />
            <x-stat-card
                :title="$reportType === 'all' ? 'Dosen' : ($reportType === 'lecturer' ? 'Terlambat' : 'Total Hadir')"
                :value="$stats['present']"
                :caption="$reportType === 'all' ? 'Scan dosen' : ($reportType === 'lecturer' ? 'Scan setelah jam mulai' : 'Hadir dan terlambat')"
                color="violet"
                icon="check"
            />
            <x-stat-card
                :title="$reportType === 'all' ? 'Ditolak' : ($reportType === 'class' ? 'Ditolak' : ($reportType === 'subject' ? 'Target Absensi' : 'Belum Hadir'))"
                :value="$stats['issue']"
                :caption="$reportType === 'all' ? 'Scan tidak valid' : ($reportType === 'class' ? 'Scan tidak valid' : ($reportType === 'subject' ? 'Mahasiswa x pertemuan' : 'Belum ada scan valid'))"
                color="amber"
                icon="door"
            />
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <form method="GET">
                    <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                        <div class="grid flex-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                            <select name="report_type" x-model="reportType" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10" onchange="this.form.submit()">
                                <option value="all">Semua Data</option>
                                <option value="class">Per Kelas</option>
                                <option value="subject">Per Mata Kuliah</option>
                                <option value="lecturer">Per Dosen</option>
                            </select>

                            <select x-show="reportType === 'subject'" name="academic_year_id" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                                <option value="">Semua Tahun Akademik</option>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}" @selected(request('academic_year_id') == $year->id)>{{ $year->year }} - {{ ucfirst($year->semester) }}</option>
                                @endforeach
                            </select>

                            <select x-show="reportType === 'class' || reportType === 'subject'" name="class_id" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                                <option value="">{{ $reportType === 'class' ? 'Pilih Kelas' : 'Semua Kelas' }}</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->code }} - {{ $class->name }}</option>
                                @endforeach
                            </select>

                            <select x-show="reportType === 'class' || reportType === 'subject'" name="subject_id" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                                <option value="">{{ $reportType === 'subject' ? 'Pilih Mata Kuliah' : 'Semua Mata Kuliah' }}</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->name }}</option>
                                @endforeach
                            </select>

                            <select x-show="reportType === 'lecturer'" name="lecturer_id" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                                <option value="">Pilih Dosen</option>
                                @foreach ($lecturers as $lecturer)
                                    <option value="{{ $lecturer->id }}" @selected(request('lecturer_id') == $lecturer->id)>{{ $lecturer->name }}</option>
                                @endforeach
                            </select>

                            <input x-show="reportType === 'lecturer'" type="date" name="date" value="{{ $date }}" class="h-11 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">
                        </div>

                        <div class="flex shrink-0 flex-col gap-3 sm:flex-row">
                            <button class="inline-flex h-11 min-w-40 items-center justify-center rounded-lg bg-emerald-600 px-5 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700">Tampilkan</button>
                            <button type="button" @click="downloadModalOpen = true" class="inline-flex h-11 min-w-44 items-center justify-center rounded-lg border border-emerald-200 px-5 text-sm font-extrabold text-emerald-700 hover:bg-emerald-50">Download Bulanan</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto px-5 pt-5">
                @if ($reportType === 'all')
                    <table class="min-w-full overflow-hidden rounded-lg border border-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Waktu</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Peran</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Nama</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Jadwal</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Perangkat</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($rows as $row)
                                @php
                                    $statusClass = match ($row->attendance_status) {
                                        'present' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                        'late' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                        'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200',
                                        default => 'bg-slate-100 text-slate-600 ring-slate-200',
                                    };
                                    $statusLabel = match ($row->attendance_status) {
                                        'present' => 'Hadir',
                                        'late' => 'Terlambat',
                                        'rejected' => 'Ditolak',
                                        default => ucfirst($row->attendance_status),
                                    };
                                @endphp
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $row->attendance_time?->format('d M Y H:i') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-700">{{ $row->user_type === 'lecturer' ? 'Dosen' : 'Mahasiswa' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="text-sm font-extrabold text-slate-800">{{ $row->user_name }}</div>
                                        <div class="mt-1 text-xs font-medium text-slate-500">{{ $row->fingerprint_id }}</div>
                                    </td>
                                    <td class="min-w-[260px] px-5 py-4">
                                        <div class="text-sm font-bold text-slate-800">{{ $row->schedule?->subject?->name ?? '-' }}</div>
                                        <div class="mt-1 text-xs font-medium text-slate-500">{{ $row->schedule?->class?->code ?? '-' }} - {{ $row->schedule?->room?->name ?? '-' }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $row->device?->name ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-extrabold ring-1 {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-12 text-center text-sm font-medium text-slate-500">Belum ada data kehadiran bulan ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @elseif ($reportType === 'class')
                    <table class="min-w-full overflow-hidden rounded-lg border border-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">NIM</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Mahasiswa</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Hadir</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Ditolak</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Belum Hadir</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($rows as $row)
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $row['student']->nim }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-extrabold text-slate-800">{{ $row['student']->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-emerald-700">{{ $row['present'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-rose-600">{{ $row['rejected'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-600">{{ $row['absent'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-extrabold text-emerald-700 ring-1 ring-emerald-200">{{ $row['status'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-12 text-center text-sm font-medium text-slate-500">Pilih kelas untuk menampilkan laporan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @elseif ($reportType === 'subject')
                    <table class="min-w-full overflow-hidden rounded-lg border border-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Mata Kuliah</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Kelas</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Tahun</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Pertemuan</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Hadir</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Persentase</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($rows as $row)
                                @php
                                    $percentageClass = $row['percentage'] >= 75 ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : ($row['percentage'] >= 50 ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-rose-50 text-rose-700 ring-rose-200');
                                @endphp
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="min-w-[240px] px-5 py-4 text-sm font-extrabold text-slate-800">{{ $row['schedule']->subject->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-600">{{ $row['schedule']->class->code }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $row['schedule']->academicYear->year }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-700">{{ $row['meetings'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-emerald-700">{{ $row['present'] }} / {{ $row['expected'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-extrabold ring-1 {{ $percentageClass }}">{{ str_replace('.', ',', $row['percentage']) }}%</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-12 text-center text-sm font-medium text-slate-500">Pilih mata kuliah untuk menampilkan laporan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <table class="min-w-full overflow-hidden rounded-lg border border-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Jadwal</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Mata Kuliah</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Kelas</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Ruangan</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Jam Scan</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($rows as $row)
                                @php
                                    $statusClass = match ($row['status']) {
                                        'present' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                        'late' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                        default => 'bg-slate-100 text-slate-600 ring-slate-200',
                                    };
                                    $statusLabel = match ($row['status']) {
                                        'present' => 'Hadir',
                                        'late' => 'Terlambat',
                                        default => 'Belum Hadir',
                                    };
                                @endphp
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-700">{{ $row['schedule']->time_range }}</td>
                                    <td class="min-w-[240px] px-5 py-4 text-sm font-extrabold text-slate-800">{{ $row['schedule']->subject->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-600">{{ $row['schedule']->class->code }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-600">{{ $row['schedule']->room->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-700">{{ $row['scan_time'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-extrabold ring-1 {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-12 text-center text-sm font-medium text-slate-500">Pilih dosen untuk menampilkan laporan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="px-5 py-5 text-sm font-medium text-slate-500">
                Jenis laporan: <span class="font-extrabold text-slate-800">{{ ['all' => 'Semua Data', 'class' => 'Per Kelas', 'subject' => 'Per Mata Kuliah', 'lecturer' => 'Per Dosen'][$reportType] }}</span>
            </div>
        </div>

        <div
            x-show="downloadModalOpen"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4"
            role="dialog"
            aria-modal="true"
            aria-label="Pilih bulan download"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl" @click.outside="downloadModalOpen = false">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-950">Pilih Bulan</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">Pilih bulan laporan yang ingin di-download.</p>
                    </div>
                    <button type="button" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-sm font-extrabold text-slate-600 hover:bg-slate-50" @click="downloadModalOpen = false">x</button>
                </div>

                <form method="GET" action="{{ route('admin.reports.attendance.download') }}" class="mt-5 space-y-4">
                    <input type="hidden" name="report_type" value="{{ $reportType }}">
                    @foreach (request()->except(['page', 'month', 'report_type']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <input type="month" name="month" value="{{ request('month', $selectedMonth) }}" class="h-12 w-full rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/10">

                    <div class="flex justify-end gap-3">
                        <button type="button" class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 px-4 text-sm font-extrabold text-slate-700 hover:bg-slate-50" @click="downloadModalOpen = false">Batal</button>
                        <button class="inline-flex h-11 items-center justify-center rounded-lg bg-emerald-600 px-5 text-sm font-extrabold text-white shadow-sm hover:bg-emerald-700">Download</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
