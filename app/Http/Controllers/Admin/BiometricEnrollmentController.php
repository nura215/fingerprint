<?php

namespace App\Http\Controllers\Admin;

use App\Models\BiometricEnrollment;
use App\Models\Device;
use App\Models\Lecturer;
use App\Models\Student;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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

    public function index(Request $request): View
    {
        $perPage = $request->integer('per_page') ?: 10;
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $items = $this->baseQuery()
            ->when($request->filled('search'), fn (Builder $query) => $this->applySearch($query, $request->string('search')->toString()))
            ->when($request->filled('user_type'), fn (Builder $query) => $query->where('user_type', $request->input('user_type')))
            ->when($request->filled('device_id'), fn (Builder $query) => $query->where('device_id', $request->integer('device_id')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')))
            ->when($request->filled('sync_status'), fn (Builder $query) => $query->where('sync_status', $request->input('sync_status')))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.aktivitas.fingerprint.enrollment', [
            'items' => $items,
            'routePrefix' => $this->routePrefix,
            'devices' => Device::orderBy('name')->get(),
            'stats' => [
                'total' => BiometricEnrollment::count(),
                'enrolled' => BiometricEnrollment::where('status', 'enrolled')->count(),
                'not_enrolled' => BiometricEnrollment::where('status', 'not_enrolled')->count(),
                'pending_sync' => BiometricEnrollment::where('sync_status', 'pending')->count(),
            ],
            'perPage' => $perPage,
        ]);
    }

    public function requestSync(BiometricEnrollment $biometricEnrollment): RedirectResponse
    {
        $this->markPendingSync($biometricEnrollment, 'Menunggu dikirim ulang ke perangkat.');
        AuditLogger::log('request_device_user_sync', $biometricEnrollment, null, $biometricEnrollment->toArray());

        return redirect()
            ->route($this->routePrefix.'.index')
            ->with('success', 'Data user masuk antrean sinkron ke alat.');
    }

    public function requestSyncAll(): RedirectResponse
    {
        $count = BiometricEnrollment::query()->update([
            'sync_status' => 'pending',
            'sync_requested_at' => now(),
            'sync_message' => 'Menunggu dikirim ke perangkat.',
        ]);

        AuditLogger::log('request_all_device_user_sync', null, null, ['count' => $count]);

        return redirect()
            ->route($this->routePrefix.'.index')
            ->with('success', "{$count} data user masuk antrean sinkron ke alat.");
    }

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
            ['name' => 'user_type', 'label' => 'Tipe User', 'type' => 'select', 'required' => true, 'options' => ['lecturer' => 'Dosen', 'student' => 'Mahasiswa']],
            ['name' => 'user_id', 'label' => 'User', 'type' => 'select', 'required' => true, 'options' => $this->usersOptions(), 'selected_group' => $selectedGroup, 'help' => 'Pilih user yang akan didaftarkan sidik jarinya.'],
            ['name' => 'fingerprint_id', 'label' => 'Fingerprint ID', 'type' => 'text', 'required' => true, 'help' => 'ID ini harus sama dengan User ID yang dimasukkan saat enroll di alat.'],
            ['name' => 'device_id', 'label' => 'Perangkat', 'type' => 'select', 'options' => Device::orderBy('name')->pluck('name', 'id')->all()],
            ['name' => 'enrolled_at', 'label' => 'Tanggal Enroll', 'type' => 'datetime-local'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['not_enrolled' => 'Belum Fingerprint', 'enrolled' => 'Sudah Fingerprint', 'inactive' => 'Tidak Aktif']],
        ];
    }

    protected function prepareData(array $data, bool $creating): array
    {
        $data = parent::prepareData($data, $creating);

        if (($data['status'] ?? null) === 'enrolled' && blank($data['enrolled_at'] ?? null)) {
            $data['enrolled_at'] = now();
        }

        if (($data['status'] ?? null) !== 'enrolled') {
            $data['enrolled_at'] = null;
        }

        if ($creating) {
            $data['sync_status'] = 'pending';
            $data['sync_requested_at'] = now();
            $data['sync_message'] = 'Menunggu dikirim ke perangkat.';
        }

        return $data;
    }

    protected function afterStore(Model $item): void
    {
        $this->syncUserFingerprint($item);
    }

    protected function afterUpdate(Model $item): void
    {
        $this->syncUserFingerprint($item);
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

    private function syncUserFingerprint(BiometricEnrollment $enrollment): void
    {
        $this->markPendingSync($enrollment, 'Data berubah, menunggu dikirim ke perangkat.');

        $model = match ($enrollment->user_type) {
            'lecturer' => Lecturer::find($enrollment->user_id),
            'student' => Student::find($enrollment->user_id),
            default => null,
        };

        if ($model && $model->fingerprint_id !== $enrollment->fingerprint_id) {
            $model->update(['fingerprint_id' => $enrollment->fingerprint_id]);
        }
    }

    private function markPendingSync(BiometricEnrollment $enrollment, string $message): void
    {
        $enrollment->forceFill([
            'sync_status' => 'pending',
            'sync_requested_at' => now(),
            'sync_message' => $message,
        ])->save();
    }
}






