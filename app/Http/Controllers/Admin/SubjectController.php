<?php

namespace App\Http\Controllers\Admin;

use App\Models\Department;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Model;

class SubjectController extends BaseCrudController
{
    protected string $modelClass = Subject::class;

    protected string $routePrefix = 'admin.subjects';

    protected string $title = 'Mata Kuliah';

    protected string $description = 'Kelola mata kuliah dan SKS.';

    protected array $with = ['department'];

    protected array $columns = [
        ['label' => 'Kode', 'key' => 'code'],
        ['label' => 'Nama Mata Kuliah', 'key' => 'name'],
        ['label' => 'Program Studi', 'key' => 'department.name'],
        ['label' => 'SKS', 'key' => 'sks'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $detailFields = [
        ['label' => 'Kode', 'key' => 'code'],
        ['label' => 'Nama Mata Kuliah', 'key' => 'name'],
        ['label' => 'Program Studi', 'key' => 'department.name'],
        ['label' => 'SKS', 'key' => 'sks'],
        ['label' => 'Semester', 'key' => 'semester'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $searchColumns = ['code', 'name'];

    protected function resolvedFormFields(): array
    {
        return [
            ['name' => 'code', 'label' => 'Kode', 'type' => 'text', 'required' => true],
            ['name' => 'name', 'label' => 'Nama Mata Kuliah', 'type' => 'text', 'required' => true],
            ['name' => 'sks', 'label' => 'SKS', 'type' => 'number', 'required' => true],
            ['name' => 'semester', 'label' => 'Semester', 'type' => 'number'],
            ['name' => 'department_id', 'label' => 'Program Studi', 'type' => 'select', 'required' => true, 'options' => Department::orderBy('name')->pluck('name', 'id')->all()],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
        ];
    }

    protected function rules(?Model $item = null): array
    {
        return [
            'code' => ['required', 'string', 'max:30', $this->uniqueRule('subjects', 'code', $item)],
            'name' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'min:1', 'max:12'],
            'semester' => ['nullable', 'integer', 'min:1', 'max:14'],
            'department_id' => ['required', 'exists:departments,id'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}







