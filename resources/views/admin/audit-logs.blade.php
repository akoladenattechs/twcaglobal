@extends('layouts.admin')

@php
    // Action → badge color mapping (classes defined in admin.css)
    $badgeMap = [
        'login'                        => 'success',
        'logout'                       => 'muted',
        'login_failed'                 => 'danger',
        '2fa_sent'                     => 'info',
        '2fa_failed'                   => 'danger',
        'password_reset_completed'     => 'success',
        'password_reset_otp_sent'      => 'info',
        'password_reset_otp_verified'  => 'success',
        'password_reset_otp_invalid'   => 'warning',
        'password_reset_otp_exhausted' => 'warning',
        'password_reset_otp_expired'   => 'warning',
        'password_reset_otp_missing'   => 'warning',
        'password_reset_lockout'       => 'danger',
        'password_reset_session_mismatch' => 'warning',
        'password_reset_email_failed'  => 'danger',
        'password_reset_notify_failed' => 'danger',
        'password_reset_user_missing'  => 'warning',
        'password_reset_blocked_inactive' => 'danger',
    ];
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Audit Logs</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.audit-logs', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-success mr-2">
            <i class="fas fa-file-csv mr-1"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-warning mr-2" data-toggle="modal" data-target="#pruneLogsModal">
            <i class="fas fa-history mr-1"></i> Retention Prune
        </button>
        <button type="button" class="btn btn-danger"
            data-delete-action="{{ route('admin.audit-logs') }}"
            data-delete-payload='{"action":"clear"}'
            data-delete-message="Are you sure you want to clear the entire audit log? This action cannot be undone."
            data-title="Clear Audit Log"
            data-btn-text="Clear Logs"
            data-btn-class="btn-danger"
            data-icon-class="fas fa-trash-alt">
            <i class="fas fa-trash-alt"></i> Clear Logs
        </button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

{{-- ── Filter Panel ── --}}
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-2"></i> Filter Logs</h6>
    </div>
    <div class="card-body">
        <form method="get" action="{{ route('admin.audit-logs') }}" class="form-row">
            <div class="form-group col-md-3 mb-md-0">
                <label for="search">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Action or description...">
            </div>
            <div class="form-group col-md-2 mb-md-0">
                <label for="action">Action</label>
                <select class="form-control" id="action" name="action">
                    <option value="">All actions</option>
                    @foreach($actions as $actionKey)
                    <option value="{{ $actionKey }}" {{ request('action') === $actionKey ? 'selected' : '' }}>{{ $actionKey }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2 mb-md-0">
                <label for="user_id">User</label>
                <select class="form-control" id="user_id" name="user_id">
                    <option value="">All users</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->username }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2 mb-md-0">
                <label for="date_from">From</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="form-group col-md-2 mb-md-0">
                <label for="date_to">To</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="form-group col-md-1 mb-md-0 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-block" title="Apply filters">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Records Table ── --}}
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-clipboard-list mr-2"></i> Activity Records
            <span class="badge badge-secondary ml-2">{{ $logs->total() }}</span>
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" id="auditLogsTable">
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td class="text-nowrap" data-order="{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y H:i:s') }}</td>
                        <td class="audit-user-cell">
                            @if($log->user)
                            <span class="audit-user">
                                <i class="fas fa-user-circle mr-1"></i>
                                {{ trim(($log->user->first_name ?? '') . ' ' . ($log->user->last_name ?? '')) ?: $log->user->username }}
                            </span>
                            @else
                            <span class="audit-guest"><i class="fas fa-user-slash mr-1"></i> Guest / System</span>
                            @endif
                        </td>
                        <td><span class="audit-badge audit-badge-{{ $badgeMap[$log->action] ?? 'muted' }}">{{ htmlspecialchars($log->action) }}</span></td>
                        <td class="audit-description">{{ htmlspecialchars($log->description) }}</td>
                        <td class="text-nowrap">{{ $log->ip_address ? htmlspecialchars($log->ip_address) : '—' }}</td>
                        <td class="audit-ua" title="{{ htmlspecialchars($log->user_agent ?? '') }}">{{ $log->user_agent ? \Illuminate\Support\Str::limit(htmlspecialchars($log->user_agent), 60) : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>

{{-- ── Prune Logs Modal ── --}}
<div class="modal fade" id="pruneLogsModal" tabindex="-1" role="dialog" aria-labelledby="pruneLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.audit-logs') }}">
                @csrf
                <input type="hidden" name="action" value="prune">
                <div class="modal-header">
                    <h5 class="modal-title" id="pruneLogsModalLabel"><i class="fas fa-history text-warning mr-2"></i> Prune Audit Logs</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Remove old activity log records to enforce your organization's data retention policy.</p>
                    <div class="form-group">
                        <label for="prune_days" class="font-weight-bold">Delete logs older than:</label>
                        <select class="form-control" id="prune_days" name="days">
                            <option value="30">30 days</option>
                            <option value="60">60 days</option>
                            <option value="90" selected>90 days (Recommended)</option>
                            <option value="180">180 days (6 months)</option>
                            <option value="365">365 days (1 year)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-trash-alt mr-1"></i> Prune Records</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#auditLogsTable').DataTable({
        "pageLength": 25,
        "order": [[ 0, "desc" ]],
        "paging": false,
        "language": {
            "emptyTable": "No activity records found."
        }
    });
});
</script>
@endsection
