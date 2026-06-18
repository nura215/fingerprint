<?php

namespace App\Http\Controllers\Admin;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Department;
use Illuminate\Database\Eloquent\Model;

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







