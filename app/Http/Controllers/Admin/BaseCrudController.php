<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\View\View;

abstract class BaseCrudController extends Controller
{
    protected string $modelClass;

    protected string $routePrefix;

    protected string $title;

    protected string $description = '';

    protected array $columns = [];

    protected array $detailFields = [];

    protected array $formFields = [];

    protected array $searchColumns = [];

    protected array $with = [];

    protected ?string $filterColumn = 'status';

    protected array $filterOptions = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    protected ?string $badgeColumn = 'status';

    protected ?string $inactiveValue = 'inactive';

    protected string $orderBy = 'created_at';

    protected string $orderDirection = 'desc';

    protected string $viewNamespace = 'admin.master-data';

    protected string $sectionTitle = 'Master Data';

    public function index(Request $request): View
    {
        $items = $this->baseQuery()
            ->when($request->filled('search'), fn (Builder $query) => $this->applySearch($query, $request->string('search')->toString()))
            ->when($this->filterColumn && $request->filled('status'), fn (Builder $query) => $query->where($this->filterColumn, $request->input('status')))
            ->orderBy($this->orderBy, $this->orderDirection)
            ->paginate(10)
            ->withQueryString();

        return view($this->viewNamespace.'.daftar', [
            'items' => $items,
            'title' => $this->title,
            'description' => $this->description,
            'sectionTitle' => $this->sectionTitle,
            'routePrefix' => $this->routePrefix,
            'columns' => $this->columns,
            'filterColumn' => $this->filterColumn,
            'filterOptions' => $this->filterOptions,
            'badgeColumn' => $this->badgeColumn,
            'inactiveValue' => $this->inactiveValue,
        ]);
    }

    public function create(): View
    {
        return view($this->viewNamespace.'.formulir', [
            'item' => null,
            'title' => 'Tambah '.$this->title,
            'sectionTitle' => $this->sectionTitle,
            'routePrefix' => $this->routePrefix,
            'fields' => $this->resolvedFormFields(),
            'method' => 'POST',
            'action' => route($this->routePrefix.'.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $item = $this->modelClass::create($this->prepareData($validated, true));
        AuditLogger::log('create', $item, null, $item->fresh()?->toArray());

        return redirect()
            ->route($this->routePrefix.'.index')
            ->with('success', $this->title.' berhasil ditambahkan.');
    }

    public function show(string $id): View
    {
        return view($this->viewNamespace.'.detail', [
            'item' => $this->findModel($id),
            'title' => 'Detail '.$this->title,
            'sectionTitle' => $this->sectionTitle,
            'routePrefix' => $this->routePrefix,
            'fields' => $this->detailFields,
            'badgeColumn' => $this->badgeColumn,
        ]);
    }

    public function edit(string $id): View
    {
        $item = $this->findModel($id);

        return view($this->viewNamespace.'.formulir', [
            'item' => $item,
            'title' => 'Edit '.$this->title,
            'sectionTitle' => $this->sectionTitle,
            'routePrefix' => $this->routePrefix,
            'fields' => $this->resolvedFormFields(),
            'method' => 'PUT',
            'action' => route($this->routePrefix.'.update', $item),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $item = $this->findModel($id);
        $validated = $request->validate($this->rules($item));
        $oldData = $item->getOriginal();
        $item->update($this->prepareData($validated, false));
        AuditLogger::log('update', $item, $oldData, $item->fresh()?->toArray());

        return redirect()
            ->route($this->routePrefix.'.index')
            ->with('success', $this->title.' berhasil diperbarui.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $item = $this->findModel($id);
        $oldData = $item->getOriginal();

        if ($this->filterColumn === 'status' && $this->inactiveValue && in_array('status', $item->getFillable(), true)) {
            $item->update(['status' => $this->inactiveValue]);
            AuditLogger::log('deactivate', $item, $oldData, $item->fresh()?->toArray());

            return redirect()
                ->route($this->routePrefix.'.index')
                ->with('success', $this->title.' berhasil dinonaktifkan.');
        }

        if ($this->filterColumn === 'is_active' && in_array('is_active', $item->getFillable(), true)) {
            $item->update(['is_active' => false]);
            AuditLogger::log('deactivate', $item, $oldData, $item->fresh()?->toArray());

            return redirect()
                ->route($this->routePrefix.'.index')
                ->with('success', $this->title.' berhasil dinonaktifkan.');
        }

        try {
            $item->delete();
            AuditLogger::log('delete', $item, $oldData, null);
        } catch (QueryException) {
            return redirect()
                ->route($this->routePrefix.'.index')
                ->with('error', $this->title.' tidak bisa dihapus karena masih digunakan data lain.');
        }

        return redirect()
            ->route($this->routePrefix.'.index')
            ->with('success', $this->title.' berhasil dihapus.');
    }

    abstract protected function rules(?Model $item = null): array;

    protected function baseQuery(): Builder
    {
        return $this->modelClass::query()->with($this->with);
    }

    protected function applySearch(Builder $query, string $keyword): void
    {
        $query->where(function (Builder $nested) use ($keyword) {
            foreach ($this->searchColumns as $column) {
                $nested->orWhere($column, 'like', '%'.$keyword.'%');
            }
        });
    }

    protected function findModel(string $id): Model
    {
        return $this->baseQuery()->findOrFail($id);
    }

    protected function resolvedFormFields(): array
    {
        return $this->formFields;
    }

    protected function prepareData(array $data, bool $creating): array
    {
        if (array_key_exists('password', $data)) {
            if ($data['password'] === null || $data['password'] === '') {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }
        }

        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    protected function uniqueRule(string $table, string $column, ?Model $item = null): Unique
    {
        $rule = Rule::unique($table, $column);

        return $item ? $rule->ignore($item->getKey()) : $rule;
    }
}







