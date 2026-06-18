<?php

namespace App\Http\Controllers\Admin;

use App\Models\BiometricEnrollment;
use App\Models\Device;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BiometricEnrollmentController extends BaseCrudController
{
    protected string $modelClass = BiometricEnrollment::class;

    protected string $routePrefix = 'admin.biometric-enrollments';

    protected string $title = 'Biometric Enrollment';

    protected string $description = 'Kelola Fingerprint ID lecturers dan students. Sistem tidak menyimpan gambar sidik jari.';

    protected string $viewNamespace = 'admin.aktivitas.fingerprint';

    protected string $sectionTitle = 'Aktivitas';

    protected array $with = ['device', 'lecturer', 'student'];

    protected array $filterOptions = [
        'enrolled' => 'Enrolled',
        'not_enrolled' => 'Not Enrolled',
        'inactive' => 'Inactive',
    ];

    protected array $columns = [
        ['label' => 'Tipe User', 'key' => 'user_type_label'],
        ['label' => 'Nama User', 'key' => 'User_name'],
        ['label' => 'Fingerprint ID', 'key' => 'fingerprint_id'],
        ['label' => 'device', 'key' => 'device.name'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $detailFields = [
        ['label' => 'Tipe User', 'key' => 'user_type_label'],
        ['label' => 'Nama User', 'key' => 'User_name'],
        ['label' => 'Fingerprint ID', 'key' => 'fingerprint_id'],
        ['label' => 'device', 'key' => 'device.name'],
        ['label' => 'Tanggal Enroll', 'key' => 'enrolled_at', 'type' => 'datetime'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $searchColumns = ['user_type', 'fingerprint_id', 'status'];

    protected function rules(?Model $item = null): array
    {
        return [
            'user_type' => ['required', 'in:lecturer,student'],
            'user_id' => ['required', 'integer', function ($attribute, $value, $fail) {
                $table = request('user_type') === 'lecturer' ? 'lecturers' : 'students';

                if (! DB::table($table)->where('id', $value)->exists()) {
                    $fail('User yang dipilih tidak valid untuk tipe users tersebut.');
                }
            }],
            'fingerprint_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('biometric_enrollments', 'fingerprint_id')->ignore($item?->getKey()),
            ],
            'device_id' => ['nullable', 'exists:devices,id'],
            'enrolled_at' => ['nullable', 'date'],
            'status' => ['required', 'in:enrolled,not_enrolled,inactive'],
        ];
    }

    protected function resolvedFormFields(): array
    {
        $selectedType = old('user_type', $this->currentEnrollment()?->user_type);
        $selectedGroup = match ($selectedType) {
            'lecturer' => 'lecturer',
            'student' => 'student',
            default => null,
        };

        return [
            ['name' => 'user_type', 'label' => 'Tipe User', 'type' => 'select', 'required' => true, 'options' => ['lecturer' => 'lecturer', 'student' => 'student']],
            ['name' => 'user_id', 'label' => 'user', 'type' => 'select', 'required' => true, 'options' => $this->usersOptions(), 'selected_group' => $selectedGroup, 'help' => 'Pilih users sesuai tipe. Grup Lecturer/Student membantu membedakan data.'],
            ['name' => 'fingerprint_id', 'label' => 'Fingerprint ID', 'type' => 'text', 'required' => true],
            ['name' => 'device_id', 'label' => 'device', 'type' => 'select', 'options' => Device::orderBy('name')->pluck('name', 'id')->all()],
            ['name' => 'enrolled_at', 'label' => 'Tanggal Enroll', 'type' => 'datetime-local'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => $this->filterOptions],
        ];
    }

    private function usersOptions(): array
    {
        return [
            'lecturer' => Lecturer::orderBy('name')->pluck('name', 'id')->all(),
            'student' => Student::orderBy('name')->get()->mapWithKeys(fn ($students) => [
                $students->id => $students->name.' ('.$students->nim.')',
            ])->all(),
        ];
    }

    private function currentEnrollment(): ?BiometricEnrollment
    {
        $id = request()->route('biometric_enrollment');

        return $id ? BiometricEnrollment::find($id) : null;
    }
}






