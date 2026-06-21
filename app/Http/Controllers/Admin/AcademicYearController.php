<?php

namespace App\Http\Controllers\Admin;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicYearController extends BaseCrudController
{
    protected string $modelClass = AcademicYear::class;

    protected string $routePrefix = 'admin.academic-years';

    protected string $title = 'Tahun Akademik';

    protected string $description = 'Kelola tahun akademik dan semester aktif.';

    protected ?string $filterColumn = 'is_active';

    protected array $filterOptions = [
        '1' => 'Aktif',
        '0' => 'Tidak Aktif',
    ];

    protected ?string $badgeColumn = 'is_active';

    protected string $orderBy = 'year';

    protected string $orderDirection = 'desc';

    protected array $columns = [
        ['label' => 'Tahun', 'key' => 'year'],
        ['label' => 'Semester', 'key' => 'semester'],
        ['label' => 'Tanggal Mulai', 'key' => 'start_date', 'type' => 'date'],
        ['label' => 'Tanggal Selesai', 'key' => 'end_date', 'type' => 'date'],
        ['label' => 'Status', 'key' => 'is_active', 'badge' => true],
    ];

    protected array $detailFields = [
        ['label' => 'Tahun', 'key' => 'year'],
        ['label' => 'Semester', 'key' => 'semester'],
        ['label' => 'Tanggal Mulai', 'key' => 'start_date', 'type' => 'date'],
        ['label' => 'Tanggal Selesai', 'key' => 'end_date', 'type' => 'date'],
        ['label' => 'Status', 'key' => 'is_active', 'badge' => true],
    ];

    protected array $searchColumns = ['year', 'semester'];

    public function index(Request $request): View
    {
        $perPage = $request->integer('per_page') ?: 10;
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $items = AcademicYear::query()
            ->withCount(['classes', 'schedules'])
            ->when($request->filled('search'), fn (Builder $query) => $this->applySearch($query, $request->string('search')->toString()))
            ->when($request->filled('semester'), fn (Builder $query) => $query->where('semester', $request->input('semester')))
            ->when($request->filled('is_active'), fn (Builder $query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderByDesc('year')
            ->orderBy('semester')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.master-data.tahun-akademik.daftar', [
            'items' => $items,
            'routePrefix' => $this->routePrefix,
            'stats' => [
                'total' => AcademicYear::count(),
                'active' => AcademicYear::where('is_active', true)->count(),
                'classes' => AcademicYear::withCount('classes')->get()->sum('classes_count'),
                'schedules' => AcademicYear::withCount('schedules')->get()->sum('schedules_count'),
            ],
            'perPage' => $perPage,
        ]);
    }

    protected array $formFields = [
        ['name' => 'year', 'label' => 'Tahun Akademik', 'type' => 'text', 'required' => true, 'placeholder' => '2026/2027'],
        ['name' => 'semester', 'label' => 'Semester', 'type' => 'select', 'required' => true, 'options' => ['ganjil' => 'Ganjil', 'genap' => 'Genap']],
        ['name' => 'start_date', 'label' => 'Tanggal Mulai', 'type' => 'date', 'required' => true],
        ['name' => 'end_date', 'label' => 'Tanggal Selesai', 'type' => 'date', 'required' => true],
        ['name' => 'is_active', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['1' => 'Aktif', '0' => 'Tidak Aktif']],
    ];

    protected function rules(?Model $item = null): array
    {
        return [
            'year' => ['required', 'string', 'max:20'],
            'semester' => [
                'required',
                'in:ganjil,genap',
                Rule::unique('academic_years', 'semester')
                    ->where(fn ($query) => $query->where('year', request('year')))
                    ->ignore($item?->getKey()),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}







