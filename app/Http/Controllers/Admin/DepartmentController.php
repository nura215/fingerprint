<?php

namespace App\Http\Controllers\Admin;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;

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







