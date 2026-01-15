<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use Illuminate\Http\Request;

class ApiLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ApiLog::with('client')->latest();

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('endpoint')) {
            $query->where('endpoint', 'like', "%{$request->endpoint}%");
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);

        // Stats de las últimas 24 horas
        $stats = [
            'total_24h' => ApiLog::where('created_at', '>=', now()->subDay())->count(),
            'success_24h' => ApiLog::where('created_at', '>=', now()->subDay())->where('status', 'success')->count(),
            'error_24h' => ApiLog::where('created_at', '>=', now()->subDay())->where('status', 'error')->count(),
            'avg_duration' => ApiLog::where('created_at', '>=', now()->subDay())->avg('duration_ms'),
        ];

        return view('admin.api-logs.index', compact('logs', 'stats'));
    }

    public function show(ApiLog $apiLog)
    {
        return view('admin.api-logs.show', compact('apiLog'));
    }

    /**
     * Limpiar logs antiguos (más de 30 días).
     */
    public function cleanup()
    {
        $deleted = ApiLog::where('created_at', '<', now()->subDays(30))->delete();

        return back()->with('success', "Se eliminaron {$deleted} registros antiguos");
    }
}
