@extends('layouts.admin')

@php
    $fmtCurrency = function($amount) use ($currencySymbol, $currencyPosition, $decimalPlaces, $decimalSep, $thousandsSep) {
        $formatted = number_format((float)$amount, $decimalPlaces, $decimalSep, $thousandsSep);
        return $currencyPosition === 'before'
            ? $currencySymbol . $formatted
            : $formatted . ' ' . $currencySymbol;
    };

    $currencySettings = [
        'symbol' => $currencySymbol,
        'position' => $currencyPosition,
        'decimal_places' => $decimalPlaces,
        'decimal_separator' => $decimalSep,
        'thousands_separator' => $thousandsSep,
    ];
@endphp

@section('styles')
<link rel="stylesheet" href="{{ asset('admin/assets/css/reports.css') }}?v={{ filemtime(public_path('admin/assets/css/reports.css')) }}">
@endsection

@section('content')

{{-- Page Header --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2">
                Reports & Analytics
            </h1>
        </div>
        <div class="mt-2 mt-md-0 d-flex align-items-center flex-wrap">
            <small class="text-muted mr-3">
                <i class="far fa-calendar-alt mr-1"></i>
                {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} –
                {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
            </small>
            <a href="#" class="btn btn-outline-primary ml-2" data-toggle="modal" data-target="#dateRangeModal">
                <i class="fas fa-calendar-alt mr-1"></i> Change Date Range
            </a>
        </div>
    </div>



{{-- ═══════════════════════════════════════════════════════════ --}}
{{--  TAB NAVIGATION                                            --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<ul class="nav nav-tabs" id="reportTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="financial-tab" data-toggle="tab" href="#financial" role="tab">
            <i class="fas fa-coins mr-1"></i> Financial
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="attendance-tab" data-toggle="tab" href="#attendance" role="tab">
            <i class="fas fa-calendar-check mr-1"></i> Attendance
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="members-tab" data-toggle="tab" href="#members" role="tab">
            <i class="fas fa-users mr-1"></i> Members
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="accounts-tab" data-toggle="tab" href="#accounts" role="tab">
            <i class="fas fa-university mr-1"></i> Accounts & Funds
        </a>
    </li>
</ul>

{{-- ════════════════════════════════════════════════════════════ --}}
{{--  MODALS                                             --}}
{{-- ════════════════════════════════════════════════════════════ --}}

{{-- Date Range Modal --}}
<div class="modal fade" id="dateRangeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <form method="GET" action="{{ route('admin.reports') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Select Date Range</h5>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{--  TAB CONTENT                                               --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="tab-content mt-3" id="reportTabContent">

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{--  TAB 1: FINANCIAL REPORTS                              --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="financial" role="tabpanel">

        {{-- KPI Cards --}}
        <div class="reports-kpi-grid mt-3">
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(28,200,138,0.12); color: #1cc88a;">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">Total Inflow</div>
                    <div class="kpi-value">{{ $fmtCurrency($financialData['totalInflow']) }}</div>
                </div>
            </div>
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(231,74,59,0.12); color: #e74a3b;">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">Total Outflow</div>
                    <div class="kpi-value">{{ $fmtCurrency($financialData['totalOutflow']) }}</div>
                </div>
            </div>
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(54,185,204,0.12); color: #36b9cc;">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">Net Balance</div>
                    <div class="kpi-value">{{ $fmtCurrency($financialData['netBalance']) }}</div>
                </div>
            </div>
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(246,194,62,0.12); color: #f6c23e;">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">Fund Funds Raised</div>
                    <div class="kpi-value">{{ $fmtCurrency($financialData['fundBreakdown']->sum('current')) }}</div>
                </div>
            </div>
        </div>

        {{-- Inflow vs Outflow Chart + Category Breakdown --}}
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-line mr-1"></i> Inflow vs Outflow Trend
                        </h6>
                        <a href="{{ route('admin.reports.export', ['tab' => 'financial', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                           class="btn btn-sm btn-outline-success reports-export-btn">
                            <i class="fas fa-file-csv"></i> Export CSV
                        </a>
                    </div>
                    <div class="card-body">
                        @if(!empty($financialData['monthlyLabels']))
                            <div class="reports-chart-container reports-chart-container-lg">
                                <canvas id="financialTrendChart"></canvas>
                            </div>
                        @else
                            <div class="reports-no-data">
                                <i class="fas fa-chart-line d-block"></i>
                                <p>No financial data for this period.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-pie mr-1"></i> Inflow by Category
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($financialData['inflowByCategory']->isNotEmpty())
                            <div class="reports-chart-container" style="height:180px;">
                                <canvas id="inflowCategoryPie"></canvas>
                            </div>
                            <hr>
                            @foreach($financialData['inflowByCategory'] as $cat => $total)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-capitalize small">{{ str_replace('_', ' ', $cat) }}</span>
                                <span class="font-weight-bold">{{ $fmtCurrency($total) }}</span>
                            </div>
                            @endforeach
                        @else
                            <div class="reports-no-data">
                                <i class="fas fa-chart-pie d-block"></i>
                                <p>No inflow data.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Per-Fund Breakdown --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-hand-holding-usd mr-1"></i> Per-Fund Breakdown
                </h6>
                <a href="{{ route('admin.reports.export', ['tab' => 'funds', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                   class="btn btn-sm btn-outline-success reports-export-btn">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
            </div>
            <div class="card-body">
                @if($financialData['fundBreakdown']->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered datatable">
                            <thead>
                                <tr>
                                    <th>Fund Name</th>
                                    <th class="text-right">Target</th>
                                    <th class="text-right">Current</th>
                                    <th>Progress</th>
                                    <th class="text-right">Received (Period)</th>
                                    <th class="text-right">Tx Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($financialData['fundBreakdown'] as $fund)
                                <tr>
                                    <td class="font-weight-bold">{{ $fund['name'] }}</td>
                                    <td class="text-right">{{ $fmtCurrency($fund['target']) }}</td>
                                    <td class="text-right">{{ $fmtCurrency($fund['current']) }}</td>
                                    <td style="min-width:120px;">
                                        <div class="d-flex align-items-center">
                                            <div class="fund-progress-bar flex-grow-1 mr-2">
                                                <div class="progress-fill {{ $fund['progress'] >= 80 ? 'excellent' : ($fund['progress'] >= 50 ? 'good' : ($fund['progress'] >= 25 ? 'fair' : 'low')) }}"
                                                     style="width: {{ min(100, $fund['progress']) }}%"></div>
                                            </div>
                                            <span class="small font-weight-bold" style="min-width:36px;">{{ $fund['progress'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-right">{{ $fmtCurrency($fund['total_received']) }}</td>
                                    <td class="text-right">{{ $fund['tx_count'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="reports-no-data">
                        <i class="fas fa-hand-holding-usd d-block"></i>
                        <p>No active funds.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Campaign Performance --}}
        @if($financialData['campaigns']->isNotEmpty())
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bullhorn mr-1"></i> Campaign Performance
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($financialData['campaigns'] as $campaign)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="report-campaign-card h-100">
                            <div class="campaign-header d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold">{{ $campaign['title'] }}</span>
                                <span class="badge badge-{{ $campaign['status'] === 'active' ? 'success' : ($campaign['status'] === 'completed' ? 'primary' : 'secondary') }}">
                                    {{ ucfirst($campaign['status']) }}
                                </span>
                            </div>
                            <div class="campaign-body">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small text-muted">Raised</span>
                                    <span class="font-weight-bold">{{ $fmtCurrency($campaign['raised']) }} / {{ $fmtCurrency($campaign['target']) }}</span>
                                </div>
                                <div class="campaign-progress mb-2">
                                    <div class="progress-fill" style="width: {{ min(100, $campaign['progress']) }}%"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">{{ $campaign['progress'] }}% complete</small>
                                    @if($campaign['remaining_days'] !== null)
                                        <small class="text-muted">{{ $campaign['remaining_days'] }} days left</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Giving Statements --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-file-invoice-dollar mr-1"></i> Giving Statements
                </h6>
                <a href="{{ route('admin.reports.export', ['tab' => 'giving', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                   class="btn btn-sm btn-outline-success reports-export-btn">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
            </div>
            <div class="card-body">
                @if($financialData['givingStatements']->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered datatable">
                            <thead>
                                <tr>
                                    <th>Member Name</th>
                                    <th>Email</th>
                                    <th class="text-right">Transactions</th>
                                    <th>Categories</th>
                                    <th class="text-right">Total Given</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($financialData['givingStatements'] as $gs)
                                <tr>
                                    <td class="font-weight-bold">{{ $gs['name'] }}</td>
                                    <td>{{ $gs['email'] ?: '—' }}</td>
                                    <td class="text-right">{{ $gs['count'] }}</td>
                                    <td><span class="text-capitalize">{{ $gs['categories'] ?: '—' }}</span></td>
                                    <td class="text-right font-weight-bold text-success">{{ $fmtCurrency($gs['total']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="reports-no-data">
                        <i class="fas fa-file-invoice-dollar d-block"></i>
                        <p>No member giving data for this period.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Outflow by Category --}}
        @if($financialData['outflowByCategory']->isNotEmpty())
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar mr-1"></i> Outflow by Category
                </h6>
            </div>
            <div class="card-body">
                <div class="reports-chart-container" style="height:250px;">
                    <canvas id="outflowBarChart"></canvas>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{--  TAB 2: ATTENDANCE REPORTS                             --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="attendance" role="tabpanel">

        {{-- KPI Cards --}}
        <div class="reports-kpi-grid mt-3">
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(54,185,204,0.12); color: #36b9cc;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">Total Attendance</div>
                    <div class="kpi-value">{{ number_format($attendanceData['totalAttendance']) }}</div>
                </div>
            </div>
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(28,200,138,0.12); color: #1cc88a;">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">Average / Service</div>
                    <div class="kpi-value">{{ number_format($attendanceData['avgAttendance'], 1) }}</div>
                </div>
            </div>
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(78,115,223,0.12); color: #4e73df;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">Peak Attendance</div>
                    <div class="kpi-value">{{ number_format($attendanceData['peakAttendance']) }}</div>
                </div>
            </div>
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(246,194,62,0.12); color: #f6c23e;">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">First Timers</div>
                    <div class="kpi-value">{{ number_format($attendanceData['totalFirstTimers']) }}</div>
                </div>
            </div>
        </div>

        {{-- Attendance Trend + Service Type Breakdown --}}
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-area mr-1"></i> Attendance Trend
                        </h6>
                        <a href="{{ route('admin.reports.export', ['tab' => 'attendance', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                           class="btn btn-sm btn-outline-success reports-export-btn">
                            <i class="fas fa-file-csv"></i> Export CSV
                        </a>
                    </div>
                    <div class="card-body">
                        @if(!empty($attendanceData['attLabels']))
                            <div class="reports-chart-container reports-chart-container-lg">
                                <canvas id="attendanceTrendChart"></canvas>
                            </div>
                        @else
                            <div class="reports-no-data">
                                <i class="fas fa-chart-area d-block"></i>
                                <p>No attendance data for this period.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-pie mr-1"></i> By Service Type
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($attendanceData['byServiceType']->isNotEmpty())
                            <div class="reports-chart-container" style="height:180px;">
                                <canvas id="serviceTypePie"></canvas>
                            </div>
                            <hr>
                            <ul class="reports-summary-list">
                                @foreach($attendanceData['byServiceType'] as $type => $total)
                                <li>
                                    <span class="label">{{ $type ?: 'General' }}</span>
                                    <span class="value">{{ number_format($total) }}</span>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="reports-no-data">
                                <i class="fas fa-chart-pie d-block"></i>
                                <p>No service type data.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Gender Breakdown + First-Timers --}}
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-venus-mars mr-1"></i> Gender Breakdown
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($attendanceData['totalMales'] > 0 || $attendanceData['totalFemales'] > 0)
                            <div class="reports-chart-container" style="height:200px;">
                                <canvas id="genderPie"></canvas>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-around">
                                <div class="text-center">
                                    <div class="small text-muted mb-1">Males</div>
                                    <div class="font-weight-bold">{{ number_format($attendanceData['totalMales']) }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="small text-muted mb-1">Females</div>
                                    <div class="font-weight-bold">{{ number_format($attendanceData['totalFemales']) }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="small text-muted mb-1">Total</div>
                                    <div class="font-weight-bold">{{ number_format($attendanceData['totalMales'] + $attendanceData['totalFemales']) }}</div>
                                </div>
                            </div>
                        @else
                            <div class="reports-no-data">
                                <i class="fas fa-venus-mars d-block"></i>
                                <p>No gender data for this period.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user-plus mr-1"></i> First-Timers Trend
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(!empty($attendanceData['attLabels']) && array_sum($attendanceData['attFirstTimers']) > 0)
                            <div class="reports-chart-container" style="height:200px;">
                                <canvas id="firstTimersChart"></canvas>
                            </div>
                        @else
                            <div class="reports-no-data">
                                <i class="fas fa-user-plus d-block"></i>
                                <p>No first-timer data for this period.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{--  TAB 3: MEMBER ANALYTICS                               --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="members" role="tabpanel">

        {{-- KPI Cards --}}
        <div class="reports-kpi-grid mt-3">
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(78,115,223,0.12); color: #4e73df;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">Total Members</div>
                    <div class="kpi-value">{{ number_format($memberData['totalMembers']) }}</div>
                </div>
            </div>
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(28,200,138,0.12); color: #1cc88a;">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">Active Members</div>
                    <div class="kpi-value">{{ number_format($memberData['activeMembers']) }}</div>
                </div>
            </div>
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(246,194,62,0.12); color: #f6c23e;">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">New (This Period)</div>
                    <div class="kpi-value">{{ number_format($memberData['newThisPeriod']) }}</div>
                </div>
            </div>
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(54,185,204,0.12); color: #36b9cc;">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">Retention Rate</div>
                    <div class="kpi-value">{{ $memberData['retentionRate'] }}%</div>
                </div>
            </div>
        </div>

        {{-- Member Growth Chart + Status Breakdown --}}
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-line mr-1"></i> Member Growth
                        </h6>
                        <a href="{{ route('admin.reports.export', ['tab' => 'members', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                           class="btn btn-sm btn-outline-success reports-export-btn">
                            <i class="fas fa-file-csv"></i> Export CSV
                        </a>
                    </div>
                    <div class="card-body">
                        @if(!empty($memberData['memberLabels']))
                            <div class="reports-chart-container reports-chart-container-lg">
                                <canvas id="memberGrowthChart"></canvas>
                            </div>
                        @else
                            <div class="reports-no-data">
                                <i class="fas fa-chart-line d-block"></i>
                                <p>No member data for this period.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-clipboard-check mr-1"></i> Membership Status
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($memberData['statusBreakdown']->isNotEmpty())
                            <div class="reports-chart-container" style="height:180px;">
                                <canvas id="statusPie"></canvas>
                            </div>
                            <hr>
                            <ul class="reports-summary-list">
                                @php
                                    $statusColors = ['active' => '#1cc88a', 'inactive' => '#f6c23e', 'suspended' => '#e74a3b'];
                                @endphp
                                @foreach($memberData['statusBreakdown'] as $status => $count)
                                <li>
                                    <span class="label">
                                        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $statusColors[$status] ?? '#858796' }};margin-right:6px;"></span>
                                        {{ ucfirst($status ?: 'Unset') }}
                                    </span>
                                    <span class="value">{{ number_format($count) }}</span>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="reports-no-data">
                                <i class="fas fa-clipboard-check d-block"></i>
                                <p>No status data.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Gender Distribution + Center Distribution --}}
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-venus-mars mr-1"></i> Gender Distribution
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($memberData['genderDistribution']->isNotEmpty())
                            <div class="reports-chart-container" style="height:200px;">
                                <canvas id="memberGenderPie"></canvas>
                            </div>
                        @else
                            <div class="reports-no-data">
                                <i class="fas fa-venus-mars d-block"></i>
                                <p>No gender data.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-map-marker-alt mr-1"></i> Center Distribution
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($memberData['centerDistribution']->isNotEmpty())
                            @php
                                $maxCenter = $memberData['centerDistribution']->max();
                            @endphp
                            @foreach($memberData['centerDistribution'] as $center => $count)
                            <div class="reports-center-bar">
                                <span class="bar-label">{{ $center }}</span>
                                <div class="bar-track">
                                    <div class="bar-fill" style="width: {{ $maxCenter > 0 ? ($count / $maxCenter * 100) : 0 }}%">
                                        {{ $count }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="reports-no-data">
                                <i class="fas fa-map-marker-alt d-block"></i>
                                <p>No center data.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{--  TAB 4: ACCOUNTS & FUNDS                               --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="accounts" role="tabpanel">

        {{-- KPI Cards --}}
        <div class="reports-kpi-grid mt-3">
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(78,115,223,0.12); color: #4e73df;">
                    <i class="fas fa-university"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">Total Account Balance</div>
                    <div class="kpi-value">{{ $fmtCurrency($accountsData['totalAccountBalance']) }}</div>
                </div>
            </div>
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(28,200,138,0.12); color: #1cc88a;">
                    <i class="fas fa-piggy-bank"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">Total Fund Balance</div>
                    <div class="kpi-value">{{ $fmtCurrency($accountsData['totalFundBalance']) }}</div>
                </div>
            </div>
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(246,194,62,0.12); color: #f6c23e;">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">Total Fund Targets</div>
                    <div class="kpi-value">{{ $fmtCurrency($accountsData['totalFundTarget']) }}</div>
                </div>
            </div>
            <div class="reports-kpi-item">
                <div class="kpi-icon" style="background: rgba(54,185,204,0.12); color: #36b9cc;">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">Overall Fund Progress</div>
                    <div class="kpi-value">
                        {{ $accountsData['totalFundTarget'] > 0 ? round(($accountsData['totalFundBalance'] / $accountsData['totalFundTarget']) * 100, 1) : 0 }}%
                    </div>
                </div>
            </div>
        </div>

        {{-- Account Balances --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-university mr-1"></i> Account Balances
                </h6>
            </div>
            <div class="card-body">
                @if($accountsData['accounts']->isNotEmpty())
                    <div class="row">
                        @foreach($accountsData['accounts'] as $account)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="reports-account-card h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="account-name">{{ $account['name'] }}</div>
                                        <div class="account-type">{{ $account['type'] ?: 'General' }} {{ $account['bank_name'] ? '– ' . $account['bank_name'] : '' }}</div>
                                    </div>
                                </div>
                                <div class="account-balance mb-1">{{ $fmtCurrency($account['current_balance']) }}</div>
                                <div class="account-change {{ $account['change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="fas fa-{{ $account['change'] >= 0 ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                    {{ $fmtCurrency(abs($account['change'])) }} from opening
                                    @if($account['change_pct'] != 0)
                                        ({{ $account['change'] >= 0 ? '+' : '' }}{{ $account['change_pct'] }}%)
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="reports-no-data">
                        <i class="fas fa-university d-block"></i>
                        <p>No active accounts.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Fund Progress --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-flag mr-1"></i> Fund Progress
                </h6>
            </div>
            <div class="card-body">
                @if($accountsData['funds']->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered datatable">
                            <thead>
                                <tr>
                                    <th>Fund Name</th>
                                    <th class="text-right">Target</th>
                                    <th class="text-right">Current</th>
                                    <th style="min-width:180px;">Progress</th>
                                    <th class="text-right">Remaining</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accountsData['funds'] as $fund)
                                <tr>
                                    <td class="font-weight-bold">{{ $fund['name'] }}</td>
                                    <td class="text-right">{{ $fmtCurrency($fund['target']) }}</td>
                                    <td class="text-right font-weight-bold">{{ $fmtCurrency($fund['current']) }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="fund-progress-bar flex-grow-1 mr-2">
                                                <div class="progress-fill {{ $fund['progress'] >= 80 ? 'excellent' : ($fund['progress'] >= 50 ? 'good' : ($fund['progress'] >= 25 ? 'fair' : 'low')) }}"
                                                     style="width: {{ min(100, $fund['progress']) }}%"></div>
                                            </div>
                                            <span class="small font-weight-bold" style="min-width:36px;">{{ $fund['progress'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-right">{{ $fmtCurrency(max(0, $fund['target'] - $fund['current'])) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="reports-no-data">
                        <i class="fas fa-flag d-block"></i>
                        <p>No active funds.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection

{{-- ═══════════════════════════════════════════════════════════ --}}
{{--  SCRIPTS                                                   --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
$(document).ready(function() {

    // ── Currency formatter ──
    var cs = @json($currencySettings);

    function fmtCurrency(val) {
        var n = parseFloat(val) || 0;
        var parts = n.toFixed(cs.decimal_places).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, cs.thousands_separator);
        var formatted = parts.join(cs.decimal_separator);
        return cs.position === 'before' ? cs.symbol + formatted : formatted + ' ' + cs.symbol;
    }

    // ── Custom date toggle ──
    $('#customDateBtn').on('click', function() {
        var $inputs = $('#customDateInputs');
        $inputs.toggleClass('show');
    });

    $('#applyCustomDate').on('click', function() {
        var sd = $('#customStartDate').val();
        var ed = $('#customEndDate').val();
        if (sd && ed) {
            window.location.href = '{{ route("admin.reports") }}?period=custom&start_date=' + sd + '&end_date=' + ed;
        }
    });

    // ── Chart.js defaults ──
    Chart.defaults.global.defaultFontFamily = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    Chart.defaults.global.defaultFontSize = 12;
    Chart.defaults.global.responsive = true;
    Chart.defaults.global.maintainAspectRatio = false;

    var chartColors = {
        primary: '#4e73df',
        success: '#1cc88a',
        info: '#36b9cc',
        warning: '#f6c23e',
        danger: '#e74a3b',
        secondary: '#858796',
        dark: '#5a5c69',
        purple: '#7c3aed',
    };

    var pieColors = ['#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#4e73df', '#858796', '#5a5c69', '#7c3aed'];

    // ════════════════════════════════════════════
    //  TAB 1: FINANCIAL CHARTS
    // ════════════════════════════════════════════

    // Financial Trend (Inflow vs Outflow)
    var finCtx = document.getElementById('financialTrendChart');
    if (finCtx) {
        var finData = @json($financialData);
        new Chart(finCtx, {
            type: 'line',
            data: {
                labels: finData.monthlyLabels,
                datasets: [
                    {
                        label: 'Inflow',
                        data: finData.monthlyInflowData,
                        borderColor: chartColors.success,
                        backgroundColor: 'rgba(28,200,138,0.08)',
                        borderWidth: 2,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: chartColors.success,
                    },
                    {
                        label: 'Outflow',
                        data: finData.monthlyOutflowData,
                        borderColor: chartColors.danger,
                        backgroundColor: 'rgba(231,74,59,0.08)',
                        borderWidth: 2,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: chartColors.danger,
                    }
                ]
            },
            options: {
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [{
                        gridLines: { color: '#f8f9fc' },
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) { return fmtCurrency(value); }
                        }
                    }]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(item, data) {
                            return data.datasets[item.datasetIndex].label + ': ' + fmtCurrency(item.yLabel);
                        }
                    }
                },
                legend: { position: 'bottom' }
            }
        });
    }

    // Inflow Category Pie
    var catPieCtx = document.getElementById('inflowCategoryPie');
    if (catPieCtx) {
        var catData = @json($financialData['inflowByCategory']->toArray());
        var catLabels = Object.keys(catData).map(function(k) { return k.replace(/_/g, ' '); });
        var catValues = Object.values(catData);
        new Chart(catPieCtx, {
            type: 'doughnut',
            data: {
                labels: catLabels.length ? catLabels : ['No Data'],
                datasets: [{
                    data: catValues.length ? catValues : [1],
                    backgroundColor: pieColors,
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                legend: { display: false },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var total = data.datasets[0].data.reduce(function(a, b) { return a + b; }, 0);
                            var val = data.datasets[0].data[tooltipItem.index];
                            var pct = ((val / total) * 100).toFixed(1);
                            return data.labels[tooltipItem.index] + ': ' + fmtCurrency(val) + ' (' + pct + '%)';
                        }
                    }
                }
            }
        });
    }

    // Outflow Bar Chart
    var outBarCtx = document.getElementById('outflowBarChart');
    if (outBarCtx) {
        var outData = @json($financialData['outflowByCategory']->toArray());
        var outLabels = Object.keys(outData).map(function(k) { return k.replace(/_/g, ' '); });
        var outValues = Object.values(outData);
        new Chart(outBarCtx, {
            type: 'bar',
            data: {
                labels: outLabels.length ? outLabels : ['No Data'],
                datasets: [{
                    label: 'Amount',
                    data: outValues.length ? outValues : [0],
                    backgroundColor: chartColors.danger + 'cc',
                    borderColor: chartColors.danger,
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [{
                        gridLines: { color: '#f8f9fc' },
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) { return fmtCurrency(value); }
                        }
                    }]
                },
                legend: { display: false },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem) { return fmtCurrency(tooltipItem.yLabel); }
                    }
                }
            }
        });
    }

    // ════════════════════════════════════════════
    //  TAB 2: ATTENDANCE CHARTS
    // ════════════════════════════════════════════

    // Attendance Trend (stacked area)
    var attCtx = document.getElementById('attendanceTrendChart');
    if (attCtx) {
        var attData = @json($attendanceData);
        new Chart(attCtx, {
            type: 'line',
            data: {
                labels: attData.attLabels,
                datasets: [
                    {
                        label: 'Males',
                        data: attData.attMales,
                        borderColor: chartColors.primary,
                        backgroundColor: 'rgba(78,115,223,0.15)',
                        borderWidth: 2,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: chartColors.primary,
                    },
                    {
                        label: 'Females',
                        data: attData.attFemales,
                        borderColor: chartColors.info,
                        backgroundColor: 'rgba(54,185,204,0.15)',
                        borderWidth: 2,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: chartColors.info,
                    },
                    {
                        label: 'Total',
                        data: attData.attTotal,
                        borderColor: chartColors.success,
                        backgroundColor: 'rgba(28,200,138,0.08)',
                        borderWidth: 2,
                        fill: false,
                        pointRadius: 3,
                        pointBackgroundColor: chartColors.success,
                        borderDash: [5, 5],
                    }
                ]
            },
            options: {
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [{
                        gridLines: { color: '#f8f9fc' },
                        ticks: { beginAtZero: true }
                    }]
                },
                tooltips: { mode: 'index', intersect: false },
                legend: { position: 'bottom' }
            }
        });
    }

    // Service Type Pie
    var svcPieCtx = document.getElementById('serviceTypePie');
    if (svcPieCtx) {
        var svcData = @json($attendanceData['byServiceType']->toArray());
        var svcLabels = Object.keys(svcData).map(function(k) { return k || 'General'; });
        var svcValues = Object.values(svcData);
        new Chart(svcPieCtx, {
            type: 'doughnut',
            data: {
                labels: svcLabels,
                datasets: [{
                    data: svcValues,
                    backgroundColor: pieColors,
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                legend: { display: false },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var val = data.datasets[0].data[tooltipItem.index];
                            var total = data.datasets[0].data.reduce(function(a, b) { return a + b; }, 0);
                            return data.labels[tooltipItem.index] + ': ' + val + ' (' + ((val/total)*100).toFixed(1) + '%)';
                        }
                    }
                }
            }
        });
    }

    // Gender Pie
    var gPieCtx = document.getElementById('genderPie');
    if (gPieCtx) {
        new Chart(gPieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Males', 'Females'],
                datasets: [{
                    data: [attData.totalMales, attData.totalFemales],
                    backgroundColor: [chartColors.primary, chartColors.info],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                legend: { position: 'bottom', labels: { boxWidth: 12 } },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var val = data.datasets[0].data[tooltipItem.index];
                            var total = data.datasets[0].data.reduce(function(a, b) { return a + b; }, 0);
                            var pct = total > 0 ? ((val/total)*100).toFixed(1) : 0;
                            return data.labels[tooltipItem.index] + ': ' + val + ' (' + pct + '%)';
                        }
                    }
                }
            }
        });
    }

    // First-Timers Chart
    var ftCtx = document.getElementById('firstTimersChart');
    if (ftCtx) {
        new Chart(ftCtx, {
            type: 'bar',
            data: {
                labels: attData.attLabels,
                datasets: [{
                    label: 'First Timers',
                    data: attData.attFirstTimers,
                    backgroundColor: chartColors.warning + 'cc',
                    borderColor: chartColors.warning,
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [{
                        gridLines: { color: '#f8f9fc' },
                        ticks: { beginAtZero: true }
                    }]
                },
                legend: { display: false },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem) { return 'First Timers: ' + tooltipItem.yLabel; }
                    }
                }
            }
        });
    }

    // ════════════════════════════════════════════
    //  TAB 3: MEMBER CHARTS
    // ════════════════════════════════════════════

    // Member Growth
    var mgCtx = document.getElementById('memberGrowthChart');
    if (mgCtx) {
        var mgData = @json($memberData);
        new Chart(mgCtx, {
            type: 'bar',
            data: {
                labels: mgData.memberLabels,
                datasets: [
                    {
                        label: 'New Members',
                        data: mgData.newMembers,
                        backgroundColor: chartColors.primary + 'cc',
                        borderColor: chartColors.primary,
                        borderWidth: 1,
                        borderRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Cumulative Total',
                        data: mgData.cumulative,
                        type: 'line',
                        borderColor: chartColors.success,
                        backgroundColor: 'rgba(28,200,138,0.08)',
                        borderWidth: 2,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: chartColors.success,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [
                        {
                            id: 'y',
                            position: 'left',
                            gridLines: { color: '#f8f9fc' },
                            ticks: { beginAtZero: true, stepSize: 1 },
                            scaleLabel: { display: true, labelString: 'New Members' }
                        },
                        {
                            id: 'y1',
                            position: 'right',
                            gridLines: { drawOnChartArea: false },
                            ticks: { beginAtZero: true },
                            scaleLabel: { display: true, labelString: 'Total Members' }
                        }
                    ]
                },
                tooltips: { mode: 'index', intersect: false },
                legend: { position: 'bottom' }
            }
        });
    }

    // Status Pie
    var stPieCtx = document.getElementById('statusPie');
    if (stPieCtx) {
        var stData = @json($memberData['statusBreakdown']->toArray());
        var stLabels = Object.keys(stData).map(function(k) { return k ? k.charAt(0).toUpperCase() + k.slice(1) : 'Unset'; });
        var stValues = Object.values(stData);
        var stColors = Object.keys(stData).map(function(k) {
            if (k === 'active') return chartColors.success;
            if (k === 'inactive') return chartColors.warning;
            if (k === 'suspended') return chartColors.danger;
            return chartColors.secondary;
        });
        new Chart(stPieCtx, {
            type: 'doughnut',
            data: {
                labels: stLabels,
                datasets: [{
                    data: stValues,
                    backgroundColor: stColors,
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                legend: { display: false },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var val = data.datasets[0].data[tooltipItem.index];
                            var total = data.datasets[0].data.reduce(function(a, b) { return a + b; }, 0);
                            var pct = total > 0 ? ((val/total)*100).toFixed(1) : 0;
                            return data.labels[tooltipItem.index] + ': ' + val + ' (' + pct + '%)';
                        }
                    }
                }
            }
        });
    }

    // Member Gender Pie
    var mgPieCtx = document.getElementById('memberGenderPie');
    if (mgPieCtx) {
        var mgGData = @json($memberData['genderDistribution']->toArray());
        var mgGLabels = Object.keys(mgGData);
        var mgGValues = Object.values(mgGData);
        var mgGColors = mgGLabels.map(function(k) {
            if (k === 'male') return chartColors.primary;
            if (k === 'female') return chartColors.info;
            return chartColors.secondary;
        });
        new Chart(mgPieCtx, {
            type: 'doughnut',
            data: {
                labels: mgGLabels.map(function(k) { return k ? k.charAt(0).toUpperCase() + k.slice(1) : 'Unset'; }),
                datasets: [{
                    data: mgGValues,
                    backgroundColor: mgGColors,
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                legend: { position: 'bottom', labels: { boxWidth: 12 } },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var val = data.datasets[0].data[tooltipItem.index];
                            var total = data.datasets[0].data.reduce(function(a, b) { return a + b; }, 0);
                            var pct = total > 0 ? ((val/total)*100).toFixed(1) : 0;
                            return data.labels[tooltipItem.index] + ': ' + val + ' (' + pct + '%)';
                        }
                    }
                }
            }
        });
    }

});
</script>
@endsection
