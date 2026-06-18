<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DoorAccessLog;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ManualUnlockController extends Controller
{
    public function index(): View
    {
        return view('admin.aktivitas.akses-pintu.buka-manual', [
            'devices' => Device::query()->with('room')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'exists:devices,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $Device = Device::query()->with('room')->findOrFail($validated['device_id']);
        $log = DoorAccessLog::query()->create([
            'device_id' => $Device->id,
            'room_id' => $Device->room_id,
            'schedule_id' => null,
            'user_type' => 'admin',
            'user_id' => Auth::id(),
            'access_time' => now(),
            'access_status' => 'granted',
            'open_door' => true,
            'method' => 'manual_web',
            'reason' => $validated['reason'] ?? 'Manual unlock dari web.',
        ]);

        AuditLogger::log('manual_unlock', $log, null, $log->toArray());

        return redirect()
            ->route('admin.manual-unlock.index')
            ->with('success', 'Perintah manual unlock dicatat. Integrasi fisik pintu belum dijalankan pada tahap ini.');
    }
}








