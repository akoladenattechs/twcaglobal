<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    /**
     * Display the audit log with optional filters.
     *
     * Supports GET filtering by action, user, date range, and a free-text
     * search across action/description. A POST with action=clear empties
     * the log (super admin only via permission middleware).
     */
    public function index(Request $request)
    {
        if ($request->isMethod('POST')) {
            $user = Auth::user();

            if (! $user instanceof User || ! $user->isSuperAdmin()) {
                abort(403, 'Only super admins can perform audit log management actions.');
            }

            $action = $request->input('action');

            if ($action === 'clear') {
                ActivityLog::query()->delete();

                return redirect()->route('admin.audit-logs')
                    ->with('success', 'Audit log cleared successfully.');
            }

            if ($action === 'prune') {
                $days = (int) $request->input('days', 90);
                if ($days < 7) {
                    $days = 90;
                }

                $deleted = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

                return redirect()->route('admin.audit-logs')
                    ->with('success', "Pruned {$deleted} audit log entries older than {$days} days.");
            }

            return redirect()->route('admin.audit-logs');
        }

        if ($request->input('export') === 'csv') {
            $exportQuery = $this->buildQuery($request);
            $logsToExport = $exportQuery->orderBy('created_at', 'desc')->get();

            $filename = 'audit_logs_' . date('Y-m-d_H-i-s') . '.csv';

            return response()->streamDownload(function () use ($logsToExport) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID', 'Date Time', 'User', 'Email', 'Action', 'Description', 'IP Address', 'User Agent']);

                foreach ($logsToExport as $log) {
                    $userName = $log->user ? (trim(($log->user->first_name ?? '') . ' ' . ($log->user->last_name ?? '')) ?: $log->user->username) : 'Guest / System';
                    $userEmail = $log->user ? $log->user->email : 'N/A';

                    fputcsv($file, [
                        $log->id,
                        $log->created_at->format('Y-m-d H:i:s'),
                        $userName,
                        $userEmail,
                        $log->action,
                        $log->description,
                        $log->ip_address,
                        $log->user_agent,
                    ]);
                }
                fclose($file);
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $query = $this->buildQuery($request);

        $logs = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->withQueryString();

        $users = User::orderBy('first_name', 'asc')->get();

        // Distinct action keys currently in use (for the filter dropdown)
        $actions = ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action', 'asc')
            ->pluck('action');

        return view('admin.audit-logs', compact('logs', 'users', 'actions'));
    }

    private function buildQuery(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query;
    }
}
