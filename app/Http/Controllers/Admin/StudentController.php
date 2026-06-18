<?php

namespace App\Http\Controllers\Admin;

use App\Models\AcademicClass;
use App\Models\BiometricEnrollment;
use App\Models\Student;
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

class StudentController extends BaseCrudController
{
    protected string $modelClass = Student::class;

    protected string $routePrefix = 'admin.students';

    protected string $title = 'Mahasiswa';

    protected string $description = 'Kelola data mahasiswa dan Fingerprint ID.';

    protected array $with = ['class', 'biometricEnrollment'];

    protected array $columns = [
        ['label' => 'NIM', 'key' => 'nim'],
        ['label' => 'Nama', 'key' => 'name'],
        ['label' => 'Kelas', 'key' => 'class.code'],
        ['label' => 'Fingerprint ID', 'key' => 'fingerprint_id'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $detailFields = [
        ['label' => 'NIM', 'key' => 'nim'],
        ['label' => 'Nama', 'key' => 'name'],
        ['label' => 'Kelas', 'key' => 'class.name'],
        ['label' => 'Kode Kelas', 'key' => 'class.code'],
        ['label' => 'Fingerprint ID', 'key' => 'fingerprint_id'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $searchColumns = ['nim', 'name', 'fingerprint_id'];

    public function index(Request $request): View
    {
        $perPage = $request->integer('per_page') ?: 10;
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $items = $this->baseQuery()
            ->when($request->filled('search'), fn (Builder $query) => $this->applySearch($query, $request->string('search')->toString()))
            ->when($request->filled('class_id'), fn (Builder $query) => $query->where('class_id', $request->integer('class_id')))
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

        $total = Student::count();
        $active = Student::where('status', 'active')->count();
        $enrolled = BiometricEnrollment::where('user_type', 'student')->where('status', 'enrolled')->count();
        $notEnrolled = Student::where('status', 'active')
            ->where(function (Builder $query) {
                $query
                    ->whereDoesntHave('biometricEnrollment')
                    ->orWhereHas('biometricEnrollment', fn (Builder $enrollment) => $enrollment->where('status', 'not_enrolled'));
            })
            ->count();

        return view('admin.master-data.mahasiswa.daftar', [
            'items' => $items,
            'classes' => AcademicClass::orderBy('code')->get(),
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
            'title' => 'Import Mahasiswa',
            'description' => 'Upload file Excel berisi data mahasiswa. Gunakan template agar format kolom sesuai.',
            'storeRoute' => route('admin.students.import.store'),
            'templateRoute' => route('admin.students.import.template'),
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
        $nimsInFile = [];
        $fingerprintsInFile = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $data = [
                'nim' => $row['nim'] ?? null,
                'name' => $row['nama'] ?? $row['name'] ?? null,
                'class_code' => $row['kode_kelas'] ?? $row['kelas'] ?? null,
                'fingerprint_id' => filled($row['fingerprint_id'] ?? null) ? $row['fingerprint_id'] : null,
                'status' => $this->normalizeImportStatus($row['status'] ?? null),
            ];

            $validator = Validator::make($data, [
                'nim' => ['required', 'string', 'max:50'],
                'name' => ['required', 'string', 'max:255'],
                'class_code' => ['required', 'string', 'exists:classes,code'],
                'fingerprint_id' => ['nullable', 'string', 'max:100'],
                'status' => ['required', 'in:active,inactive'],
            ]);

            if ($validator->fails()) {
                $errors[] = 'Baris '.$line.': '.$validator->errors()->first();
                continue;
            }

            if (in_array($data['nim'], $nimsInFile, true)) {
                $errors[] = 'Baris '.$line.': NIM duplikat di file.';
                continue;
            }

            $nimsInFile[] = $data['nim'];
            $student = Student::where('nim', $data['nim'])->first();

            if (filled($data['fingerprint_id'])) {
                if (in_array($data['fingerprint_id'], $fingerprintsInFile, true)) {
                    $errors[] = 'Baris '.$line.': Fingerprint ID duplikat di file.';
                    continue;
                }

                $fingerprintsInFile[] = $data['fingerprint_id'];

                $fingerprintUsed = Student::where('fingerprint_id', $data['fingerprint_id'])
                    ->when($student, fn ($query) => $query->whereKeyNot($student->id))
                    ->exists()
                    || BiometricEnrollment::where('fingerprint_id', $data['fingerprint_id'])
                        ->when($student, fn ($query) => $query->where(function (Builder $nested) use ($student) {
                            $nested->where('user_type', '!=', 'student')->orWhere('user_id', '!=', $student->id);
                        }))
                        ->exists();

                if ($fingerprintUsed) {
                    $errors[] = 'Baris '.$line.': Fingerprint ID sudah dipakai mahasiswa lain.';
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
                $class = AcademicClass::where('code', $data['class_code'])->firstOrFail();
                $existingStudent = Student::where('nim', $data['nim'])->first();

                $student = Student::updateOrCreate(
                    ['nim' => $data['nim']],
                    [
                        'name' => $data['name'],
                        'class_id' => $class->id,
                        'fingerprint_id' => filled($data['fingerprint_id'])
                            ? $data['fingerprint_id']
                            : ($existingStudent?->fingerprint_id ?: app(FingerprintIdGenerator::class)->generate()),
                        'status' => $data['status'],
                    ]
                );

                app(BiometricEnrollmentSyncer::class)->syncStudent($student);
            }
        });

        return redirect()->route('admin.students.index')->with('success', count($prepared).' data mahasiswa berhasil diimport.');
    }

    public function importTemplate()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['nim', 'nama', 'kode_kelas']);
            fputcsv($handle, ['2101234567', 'Ahmad Fauzi', 'X606-5-1']);
            fclose($handle);
        }, 'template_import_mahasiswa.csv', ['Content-Type' => 'text/csv']);
    }

    protected function resolvedFormFields(): array
    {
        return [
            ['name' => 'nim', 'label' => 'NIM', 'type' => 'text', 'required' => true],
            ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'required' => true],
            ['name' => 'class_id', 'label' => 'Kelas', 'type' => 'select', 'required' => true, 'options' => AcademicClass::orderBy('code')->get()->mapWithKeys(fn ($class) => [$class->id => $class->code.' - '.$class->name])->all()],
            ['name' => 'fingerprint_id', 'label' => 'Fingerprint ID', 'type' => 'text', 'help' => 'Kosongkan untuk dibuat otomatis. Salin ID ini ke User ID saat enroll di alat.'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
        ];
    }

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
            'nim' => ['required', 'string', 'max:50', $this->uniqueRule('students', 'nim', $item)],
            'name' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'exists:classes,id'],
            'fingerprint_id' => ['nullable', 'string', 'max:100', $this->uniqueRule('students', 'fingerprint_id', $item), $this->uniqueFingerprintEnrollmentRule($item)],
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
        app(BiometricEnrollmentSyncer::class)->syncStudent($item);
    }

    protected function afterUpdate(Model $item): void
    {
        app(BiometricEnrollmentSyncer::class)->syncStudent($item);
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
                    $nested->where('user_type', '!=', 'student')->orWhere('user_id', '!=', $item->getKey());
                }))
                ->exists();

            if ($exists) {
                $fail('Fingerprint ID sudah dipakai data enrollment lain.');
            }
        };
    }
}







