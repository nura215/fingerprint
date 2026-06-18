<?php

namespace App\Http\Controllers\Admin;

use App\Models\Department;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

    public function index(Request $request): View
    {
        $perPage = $request->integer('per_page') ?: 10;
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $items = $this->baseQuery()
            ->withCount('schedules')
            ->when($request->filled('search'), fn (Builder $query) => $this->applySearch($query, $request->string('search')->toString()))
            ->when($request->filled('department_id'), fn (Builder $query) => $query->where('department_id', $request->integer('department_id')))
            ->when($request->filled('semester'), fn (Builder $query) => $query->where('semester', $request->integer('semester')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.master-data.mata-kuliah.daftar', [
            'items' => $items,
            'routePrefix' => $this->routePrefix,
            'departments' => Department::orderBy('name')->get(),
            'semesters' => Subject::whereNotNull('semester')->distinct()->orderBy('semester')->pluck('semester'),
            'stats' => [
                'total' => Subject::count(),
                'active' => Subject::where('status', 'active')->count(),
                'sks' => Subject::sum('sks'),
                'scheduled' => Subject::has('schedules')->count(),
            ],
            'perPage' => $perPage,
        ]);
    }

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







