<?php

namespace App\Http\Controllers\Admin;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassController extends BaseCrudController
{
    protected string $modelClass = AcademicClass::class;

    protected string $routePrefix = 'admin.classes';

    protected string $title = 'Kelas';

    protected string $description = 'Kelola classes berdasarkan program studi dan tahun akademik.';

    protected array $with = ['department', 'academicYear'];

    protected array $columns = [
        ['label' => 'Kode', 'key' => 'code'],
        ['label' => 'Nama Kelas', 'key' => 'name'],
        ['label' => 'Program Studi', 'key' => 'department.name'],
        ['label' => 'Tahun Akademik', 'key' => 'academicYear.year'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $detailFields = [
        ['label' => 'Kode', 'key' => 'code'],
        ['label' => 'Nama Kelas', 'key' => 'name'],
        ['label' => 'Program Studi', 'key' => 'department.name'],
        ['label' => 'Tahun Akademik', 'key' => 'academicYear.year'],
        ['label' => 'Semester', 'key' => 'academicYear.semester'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $searchColumns = ['code', 'name'];

    public function index(Request $request): View
    {
        $perPage = $request->integer('per_page') ?: 10;
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $items = $this->baseQuery()
            ->withCount(['students', 'schedules'])
            ->when($request->filled('search'), fn (Builder $query) => $this->applySearch($query, $request->string('search')->toString()))
            ->when($request->filled('department_id'), fn (Builder $query) => $query->where('department_id', $request->integer('department_id')))
            ->when($request->filled('academic_year_id'), fn (Builder $query) => $query->where('academic_year_id', $request->integer('academic_year_id')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')))
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.master-data.kelas.daftar', [
            'items' => $items,
            'routePrefix' => $this->routePrefix,
            'departments' => Department::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('year')->get(),
            'stats' => [
                'total' => AcademicClass::count(),
                'active' => AcademicClass::where('status', 'active')->count(),
                'students' => AcademicClass::withCount('students')->get()->sum('students_count'),
                'schedules' => AcademicClass::withCount('schedules')->get()->sum('schedules_count'),
            ],
            'perPage' => $perPage,
        ]);
    }

    protected function resolvedFormFields(): array
    {
        return [
            ['name' => 'code', 'label' => 'Kode', 'type' => 'text', 'required' => true],
            ['name' => 'name', 'label' => 'Nama Kelas', 'type' => 'text', 'required' => true],
            ['name' => 'department_id', 'label' => 'Program Studi', 'type' => 'select', 'required' => true, 'options' => Department::orderBy('name')->pluck('name', 'id')->all()],
            ['name' => 'academic_year_id', 'label' => 'Tahun Akademik', 'type' => 'select', 'required' => true, 'options' => AcademicYear::orderByDesc('year')->get()->mapWithKeys(fn ($year) => [$year->id => $year->year.' - '.ucfirst($year->semester)])->all()],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
        ];
    }

    protected function rules(?Model $item = null): array
    {
        return [
            'code' => ['required', 'string', 'max:30', $this->uniqueRule('classes', 'code', $item)],
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}







