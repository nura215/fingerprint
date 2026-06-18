<?php

namespace App\Http\Controllers\Admin;

use App\Models\Room;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends BaseCrudController
{
    protected string $modelClass = Room::class;

    protected string $routePrefix = 'admin.rooms';

    protected string $title = 'Ruangan';

    protected string $description = 'Kelola ruangan kuliah, laboratorium, dan akses pintu.';

    protected array $columns = [
        ['label' => 'Kode', 'key' => 'code'],
        ['label' => 'Nama Room', 'key' => 'name'],
        ['label' => 'Lokasi', 'key' => 'location'],
        ['label' => 'Kapasitas', 'key' => 'capacity'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $detailFields = [
        ['label' => 'Kode', 'key' => 'code'],
        ['label' => 'Nama Room', 'key' => 'name'],
        ['label' => 'Lokasi', 'key' => 'location'],
        ['label' => 'Kapasitas', 'key' => 'capacity'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $searchColumns = ['code', 'name', 'location'];

    public function index(Request $request): View
    {
        $perPage = $request->integer('per_page') ?: 10;
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $items = Room::query()
            ->withCount(['devices', 'schedules'])
            ->when($request->filled('search'), fn (Builder $query) => $this->applySearch($query, $request->string('search')->toString()))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')))
            ->when($request->filled('location'), fn (Builder $query) => $query->where('location', $request->input('location')))
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.master-data.ruangan.daftar', [
            'items' => $items,
            'routePrefix' => $this->routePrefix,
            'locations' => Room::query()->whereNotNull('location')->select('location')->distinct()->orderBy('location')->pluck('location'),
            'stats' => [
                'total' => Room::count(),
                'active' => Room::where('status', 'active')->count(),
                'capacity' => Room::sum('capacity'),
                'devices' => Room::withCount('devices')->get()->sum('devices_count'),
            ],
            'perPage' => $perPage,
        ]);
    }

    protected array $formFields = [
        ['name' => 'code', 'label' => 'Kode', 'type' => 'text', 'required' => true],
        ['name' => 'name', 'label' => 'Nama Room', 'type' => 'text', 'required' => true],
        ['name' => 'location', 'label' => 'Lokasi', 'type' => 'text'],
        ['name' => 'capacity', 'label' => 'Kapasitas', 'type' => 'number'],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
    ];

    protected function rules(?Model $item = null): array
    {
        return [
            'code' => ['required', 'string', 'max:30', $this->uniqueRule('rooms', 'code', $item)],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}







