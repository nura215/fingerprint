<?php

namespace App\Http\Controllers\Admin;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Model;

class AcademicYearController extends BaseCrudController
{
    protected string $modelClass = AcademicYear::class;

    protected string $routePrefix = 'admin.academic-years';

    protected string $title = 'Tahun Akademik';

    protected string $description = 'Kelola tahun akademik dan semester aktif.';

    protected ?string $filterColumn = 'is_active';

    protected array $filterOptions = [
        '1' => 'Active',
        '0' => 'Inactive',
    ];

    protected ?string $badgeColumn = 'is_active';

    protected string $orderBy = 'year';

    protected string $orderDirection = 'desc';

    protected array $columns = [
        ['label' => 'Tahun', 'key' => 'year'],
        ['label' => 'Semester', 'key' => 'semester'],
        ['label' => 'Status', 'key' => 'is_active', 'badge' => true],
    ];

    protected array $detailFields = [
        ['label' => 'Tahun', 'key' => 'year'],
        ['label' => 'Semester', 'key' => 'semester'],
        ['label' => 'Status', 'key' => 'is_active', 'badge' => true],
    ];

    protected array $searchColumns = ['year', 'semester'];

    protected array $formFields = [
        ['name' => 'year', 'label' => 'Tahun Akademik', 'type' => 'text', 'required' => true, 'placeholder' => '2026/2027'],
        ['name' => 'semester', 'label' => 'Semester', 'type' => 'select', 'required' => true, 'options' => ['ganjil' => 'Ganjil', 'genap' => 'Genap']],
        ['name' => 'is_active', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['1' => 'Active', '0' => 'Inactive']],
    ];

    protected function rules(?Model $item = null): array
    {
        return [
            'year' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'in:ganjil,genap'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}







