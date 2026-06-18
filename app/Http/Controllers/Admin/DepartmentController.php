<?php

namespace App\Http\Controllers\Admin;

use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends BaseCrudController
{
    protected string $modelClass = Department::class;

    protected string $routePrefix = 'admin.departments';

    protected string $title = 'Program Studi';

    protected string $description = 'Kelola data program studi dan fakultas.';

    protected ?string $filterColumn = null;

    protected ?string $badgeColumn = null;

    protected array $columns = [
        ['label' => 'Kode', 'key' => 'code'],
        ['label' => 'Nama Program Studi', 'key' => 'name'],
        ['label' => 'Fakultas', 'key' => 'faculty'],
    ];

    protected array $detailFields = [
        ['label' => 'Kode', 'key' => 'code'],
        ['label' => 'Nama Program Studi', 'key' => 'name'],
        ['label' => 'Fakultas', 'key' => 'faculty'],
    ];

    protected array $searchColumns = ['code', 'name', 'faculty'];

    public function index(Request $request): View
    {
        $perPage = $request->integer('per_page') ?: 10;
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $items = Department::query()
            ->withCount(['classes', 'subjects'])
            ->when($request->filled('search'), fn (Builder $query) => $this->applySearch($query, $request->string('search')->toString()))
            ->when($request->filled('faculty'), fn (Builder $query) => $query->where('faculty', $request->input('faculty')))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.master-data.program-studi.daftar', [
            'items' => $items,
            'routePrefix' => $this->routePrefix,
            'faculties' => Department::query()->select('faculty')->distinct()->orderBy('faculty')->pluck('faculty'),
            'stats' => [
                'total' => Department::count(),
                'faculties' => Department::query()->distinct('faculty')->count('faculty'),
                'classes' => Department::withCount('classes')->get()->sum('classes_count'),
                'subjects' => Department::withCount('subjects')->get()->sum('subjects_count'),
            ],
            'perPage' => $perPage,
        ]);
    }

    protected array $formFields = [
        ['name' => 'code', 'label' => 'Kode', 'type' => 'text', 'required' => true],
        ['name' => 'name', 'label' => 'Nama Program Studi', 'type' => 'text', 'required' => true],
        ['name' => 'faculty', 'label' => 'Fakultas', 'type' => 'text', 'required' => true],
    ];

    protected function rules(?Model $item = null): array
    {
        return [
            'code' => ['required', 'string', 'max:30', $this->uniqueRule('departments', 'code', $item)],
            'name' => ['required', 'string', 'max:255'],
            'faculty' => ['required', 'string', 'max:255'],
        ];
    }
}







