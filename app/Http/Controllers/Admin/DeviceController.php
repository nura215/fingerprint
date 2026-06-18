<?php

namespace App\Http\Controllers\Admin;

use App\Models\Device;
use App\Models\Room;
use Illuminate\Database\Eloquent\Model;

class DeviceController extends BaseCrudController
{
    protected string $modelClass = Device::class;

    protected string $routePrefix = 'admin.devices';

    protected string $title = 'Device Fingerprint';

    protected string $description = 'Kelola devices Biometrik Solution X606-S dan koneksi rooms.';

    protected string $viewNamespace = 'admin.aktivitas.fingerprint';

    protected string $sectionTitle = 'Aktivitas';

    protected array $with = ['room'];

    protected ?string $inactiveValue = null;

    protected array $filterOptions = [
        'online' => 'Online',
        'offline' => 'Offline',
        'maintenance' => 'Maintenance',
    ];

    protected array $columns = [
        ['label' => 'Kode Device', 'key' => 'device_code'],
        ['label' => 'Nama Device', 'key' => 'name'],
        ['label' => 'Model', 'key' => 'model'],
        ['label' => 'IP Address', 'key' => 'ip_address'],
        ['label' => 'room', 'key' => 'room.name'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $detailFields = [
        ['label' => 'Kode Device', 'key' => 'device_code'],
        ['label' => 'Nama Device', 'key' => 'name'],
        ['label' => 'Model', 'key' => 'model'],
        ['label' => 'IP Address', 'key' => 'ip_address'],
        ['label' => 'Port', 'key' => 'port'],
        ['label' => 'room', 'key' => 'room.name'],
        ['label' => 'Koneksi', 'key' => 'connection_type'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
        ['label' => 'Terakhir Online', 'key' => 'last_online_at', 'type' => 'datetime'],
    ];

    protected array $searchColumns = ['device_code', 'name', 'model', 'ip_address', 'status'];

    protected function rules(?Model $item = null): array
    {
        return [
            'device_code' => ['required', 'string', 'max:100', $this->uniqueRule('devices', 'device_code', $item)],
            'name' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'ip_address' => ['nullable', 'ip', 'max:45'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'room_id' => ['required', 'exists:rooms,id'],
            'connection_type' => ['required', 'in:tcp_ip,usb,serial'],
            'status' => ['required', 'in:online,offline,maintenance'],
            'last_online_at' => ['nullable', 'date'],
        ];
    }

    protected function resolvedFormFields(): array
    {
        return [
            ['name' => 'device_code', 'label' => 'Device Code', 'type' => 'text', 'required' => true],
            ['name' => 'name', 'label' => 'Nama Device', 'type' => 'text', 'required' => true],
            ['name' => 'model', 'label' => 'Model', 'type' => 'text', 'required' => true, 'default' => 'Solution X606-S'],
            ['name' => 'ip_address', 'label' => 'IP Address', 'type' => 'text'],
            ['name' => 'port', 'label' => 'Port', 'type' => 'number', 'default' => 4370],
            ['name' => 'room_id', 'label' => 'room', 'type' => 'select', 'required' => true, 'options' => Room::orderBy('name')->pluck('name', 'id')->all()],
            ['name' => 'connection_type', 'label' => 'Tipe Koneksi', 'type' => 'select', 'required' => true, 'options' => ['tcp_ip' => 'TCP/IP', 'usb' => 'USB', 'serial' => 'Serial']],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => $this->filterOptions],
            ['name' => 'last_online_at', 'label' => 'Terakhir Online', 'type' => 'datetime-local'],
        ];
    }
}







