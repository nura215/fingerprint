<?php

namespace App\Http\Controllers\Admin;

use App\Models\Room;
use Illuminate\Database\Eloquent\Model;

class RoomController extends BaseCrudController
{
    protected string $modelClass = Room::class;

    protected string $routePrefix = 'admin.rooms';

    protected string $title = 'room';

    protected string $description = 'Kelola rooms kuliah, laboratorium, dan akses pintu.';

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







