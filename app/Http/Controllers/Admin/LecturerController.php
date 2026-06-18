<?php

namespace App\Http\Controllers\Admin;

use App\Models\BiometricEnrollment;
use App\Models\Lecturer;
use App\Services\BiometricEnrollmentSyncer;
use App\Services\FingerprintIdGenerator;
use App\Services\SpreadsheetImportReader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LecturerController extends BaseCrudController
{
    protected string $modelClass = Lecturer::class;

    protected string $routePrefix = 'admin.lecturers';

    protected string $title = 'Dosen';

    protected string $description = 'Kelola data dosen dan Fingerprint ID.';

    protected array $with = ['biometricEnrollment'];

    protected array $columns = [
        ['label' => 'NIDN', 'key' => 'nidn'],
        ['label' => 'Nama', 'key' => 'name'],
        ['label' => 'Email', 'key' => 'email'],
        ['label' => 'Fingerprint ID', 'key' => 'fingerprint_id'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $detailFields = [
        ['label' => 'NIDN', 'key' => 'nidn'],
        ['label' => 'Nama', 'key' => 'name'],
        ['label' => 'Email', 'key' => 'email'],
        ['label' => 'Telepon', 'key' => 'phone'],
        ['label' => 'Fingerprint ID', 'key' => 'fingerprint_id'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $searchColumns = ['nidn', 'name', 'email', 'fingerprint_id'];

    public function index(Request $request): View
    {
        $perPage = $request->integer('per_page') ?: 10;
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $items = $this->baseQuery()
            ->when($request->filled('search'), fn (Builder $query) => $this->applySearch($query, $request->string('search')->toString()))
            ->when($request->filled('status'), function (Builder $query) use ($request) {
                match ($request->string('status')->toString()) {
                    'active' => $query->where('status', 'active'),
                    'inactive' => $query->where('status', 'inactive'),
                    'enrolled' => $query->whereHas('biometricEnrollment', fn (Builder $nested) => $nested->where('status', 'enrolled')),
                    'not_enrolled' => $query->where('status', 'active')->where(function (Builder $nested) {
                        $nested
                            ->whereDoesntHave('biometricEnrollment')
                            ->orWhereHas('biometricEnrollment', fn (Builder $enrollment) => $enrollment->where('status', 'not_enrolled'));
                    }),
                    default => null,
                };
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $total = Lecturer::count();
        $active = Lecturer::where('status', 'active')->count();
        $enrolled = BiometricEnrollment::where('user_type', 'lecturer')->where('status', 'enrolled')->count();
        $notEnrolled = Lecturer::where('status', 'active')
            ->where(function (Builder $query) {
                $query
                    ->whereDoesntHave('biometricEnrollment')
                    ->orWhereHas('biometricEnrollment', fn (Builder $enrollment) => $enrollment->where('status', 'not_enrolled'));
            })
            ->count();

        return view('admin.master-data.dosen.daftar', [
            'items' => $items,
            'routePrefix' => $this->routePrefix,
            'stats' => [
                'total' => $total,
                'active' => $active,
                'enrolled' => $enrolled,
                'not_enrolled' => $notEnrolled,
                'active_percent' => $total > 0 ? round(($active / $total) * 100, 1) : 0,
                'enrolled_percent' => $total > 0 ? round(($enrolled / $total) * 100, 1) : 0,
                'not_enrolled_percent' => $total > 0 ? round(($notEnrolled / $total) * 100, 1) : 0,
            ],
            'perPage' => $perPage,
        ]);
    }

    public function import(): View
    {
        return view('admin.master-data.import-excel', [
            'title' => 'Import Dosen',
            'description' => 'Upload file Excel berisi data dosen. Gunakan template agar format kolom sesuai.',
            'storeRoute' => route('admin.lecturers.import.store'),
            'templateRoute' => route('admin.lecturers.import.template'),
        ]);
    }

    public function importStore(Request $request, SpreadsheetImportReader $reader): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:5120'],
        ]);

        try {
            $rows = $reader->read($request->file('file'));
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()])->withInput();
        }

        if ($rows === []) {
            return back()->withErrors(['file' => 'File tidak memiliki data untuk diimport.'])->withInput();
        }

        $errors = [];
        $prepared = [];
        $nidnsInFile = [];
        $fingerprintsInFile = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $data = [
                'nidn' => $row['nidn'] ?? null,
                'name' => $row['nama'] ?? $row['name'] ?? null,
                'email' => $row['email'] ?? null,
                'phone' => $row['telepon'] ?? $row['phone'] ?? null,
                'fingerprint_id' => filled($row['fingerprint_id'] ?? null) ? $row['fingerprint_id'] : null,
                'status' => $this->normalizeImportStatus($row['status'] ?? null),
            ];

            $validator = Validator::make($data, [
                'nidn' => ['required', 'string', 'max:50'],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30'],
                'fingerprint_id' => ['nullable', 'string', 'max:100'],
                'status' => ['required', 'in:active,inactive'],
            ]);

            if ($validator->fails()) {
                $errors[] = 'Baris '.$line.': '.$validator->errors()->first();
                continue;
            }

            if (in_array($data['nidn'], $nidnsInFile, true)) {
                $errors[] = 'Baris '.$line.': NIDN duplikat di file.';
                continue;
            }

            $nidnsInFile[] = $data['nidn'];
            $lecturer = Lecturer::where('nidn', $data['nidn'])->first();

            if (filled($data['fingerprint_id'])) {
                if (in_array($data['fingerprint_id'], $fingerprintsInFile, true)) {
                    $errors[] = 'Baris '.$line.': Fingerprint ID duplikat di file.';
                    continue;
                }

                $fingerprintsInFile[] = $data['fingerprint_id'];

                $fingerprintUsed = Lecturer::where('fingerprint_id', $data['fingerprint_id'])
                    ->when($lecturer, fn ($query) => $query->whereKeyNot($lecturer->id))
                    ->exists()
                    || BiometricEnrollment::where('fingerprint_id', $data['fingerprint_id'])
                        ->when($lecturer, fn ($query) => $query->where(function (Builder $nested) use ($lecturer) {
                            $nested->where('user_type', '!=', 'lecturer')->orWhere('user_id', '!=', $lecturer->id);
                        }))
                        ->exists();

                if ($fingerprintUsed) {
                    $errors[] = 'Baris '.$line.': Fingerprint ID sudah dipakai dosen lain.';
                    continue;
                }
            }

            $prepared[] = $data;
        }

        if ($errors !== []) {
            return back()->with('import_errors', $errors)->withInput();
        }

        DB::transaction(function () use ($prepared) {
            foreach ($prepared as $data) {
                $existingLecturer = Lecturer::where('nidn', $data['nidn'])->first();

                $lecturer = Lecturer::updateOrCreate(
                    ['nidn' => $data['nidn']],
                    [
                        'name' => $data['name'],
                        'email' => filled($data['email']) ? $data['email'] : null,
                        'phone' => filled($data['phone']) ? $data['phone'] : null,
                        'fingerprint_id' => filled($data['fingerprint_id'])
                            ? $data['fingerprint_id']
                            : ($existingLecturer?->fingerprint_id ?: app(FingerprintIdGenerator::class)->generate()),
                        'status' => $data['status'],
                    ]
                );

                app(BiometricEnrollmentSyncer::class)->syncLecturer($lecturer);
            }
        });

        return redirect()->route('admin.lecturers.index')->with('success', count($prepared).' data dosen berhasil diimport.');
    }

    public function importTemplate()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['nidn', 'nama', 'email', 'telepon']);
            fputcsv($handle, ['197812312005011002', 'Dr. Budi Santoso', 'budi@example.com', '081234567890']);
            fclose($handle);
        }, 'template_import_dosen.csv', ['Content-Type' => 'text/csv']);
    }

    protected array $formFields = [
        ['name' => 'nidn', 'label' => 'NIDN', 'type' => 'text', 'required' => true],
        ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'required' => true],
        ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
        ['name' => 'phone', 'label' => 'Telepon', 'type' => 'text'],
        ['name' => 'fingerprint_id', 'label' => 'Fingerprint ID', 'type' => 'text', 'help' => 'Kosongkan untuk dibuat otomatis. Salin ID ini ke User ID saat enroll di alat.'],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
    ];

    protected function formFieldsForContext(bool $creating): array
    {
        $fields = parent::formFieldsForContext($creating);

        if (! $creating) {
            return $fields;
        }

        return array_values(array_filter(
            $fields,
            fn (array $field) => ($field['name'] ?? '') !== 'fingerprint_id'
        ));
    }

    protected function rules(?Model $item = null): array
    {
        return [
            'nidn' => ['required', 'string', 'max:50', $this->uniqueRule('lecturers', 'nidn', $item)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'fingerprint_id' => ['nullable', 'string', 'max:100', $this->uniqueRule('lecturers', 'fingerprint_id', $item), $this->uniqueFingerprintEnrollmentRule($item)],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    protected function prepareData(array $data, bool $creating): array
    {
        $data = parent::prepareData($data, $creating);

        if (blank($data['fingerprint_id'] ?? null)) {
            $data['fingerprint_id'] = app(FingerprintIdGenerator::class)->generate();
        }

        return $data;
    }

    protected function afterStore(Model $item): void
    {
        app(BiometricEnrollmentSyncer::class)->syncLecturer($item);
    }

    protected function afterUpdate(Model $item): void
    {
        app(BiometricEnrollmentSyncer::class)->syncLecturer($item);
    }

    private function normalizeImportStatus(?string $status): string
    {
        return match (strtolower(trim((string) $status))) {
            '', 'aktif', 'active' => 'active',
            'tidak aktif', 'nonaktif', 'inactive' => 'inactive',
            default => strtolower(trim((string) $status)),
        };
    }

    private function uniqueFingerprintEnrollmentRule(?Model $item): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($item) {
            if (blank($value)) {
                return;
            }

            $exists = BiometricEnrollment::query()
                ->where('fingerprint_id', $value)
                ->when($item, fn ($query) => $query->where(function (Builder $nested) use ($item) {
                    $nested->where('user_type', '!=', 'lecturer')->orWhere('user_id', '!=', $item->getKey());
                }))
                ->exists();

            if ($exists) {
                $fail('Fingerprint ID sudah dipakai data enrollment lain.');
            }
        };
    }
}







