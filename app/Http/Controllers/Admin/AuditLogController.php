<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $query = AuditLog::query()
            ->with('user')
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('action', 'like', "%{$search}%")
                        ->orWhere('table_name', 'like', "%{$search}%")
                        ->orWhere('record_id', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('action'), fn (Builder $query) => $query->where('action', $request->input('action')))
            ->when($request->filled('table_name'), fn (Builder $query) => $query->where('table_name', $request->input('table_name')))
            ->when($request->filled('date'), fn (Builder $query) => $query->whereDate('created_at', $request->date('date')));

        return view('admin.sistem.audit-log.daftar', [
            'logs' => $query->latest()->paginate($perPage)->withQueryString(),
            'perPage' => $perPage,
            'actions' => AuditLog::query()->select('action')->whereNotNull('action')->distinct()->orderBy('action')->pluck('action'),
            'tables' => AuditLog::query()->select('table_name')->whereNotNull('table_name')->distinct()->orderBy('table_name')->pluck('table_name'),
            'stats' => [
                'total' => AuditLog::count(),
                'today' => AuditLog::whereDate('created_at', today())->count(),
                'manual_unlock' => AuditLog::where('action', 'manual_unlock')->count(),
                'updates' => AuditLog::where('action', 'like', '%update%')->count(),
            ],
        ]);
    }
}



