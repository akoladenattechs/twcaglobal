@extends('layouts.admin')

@php
    // Currency formatting helper for this view
    // Variables come from the controller via compact()
    $fmtCurrency = function($amount) use ($currencySymbol, $currencyPosition, $decimalPlaces, $decimalSep, $thousandsSep) {
        $formatted = number_format($amount, $decimalPlaces, $decimalSep, $thousandsSep);
        return $currencyPosition === 'before'
            ? $currencySymbol . $formatted
            : $formatted . ' ' . $currencySymbol;
    };

    // Currency settings for JSON in the scripts section
    $currencySettings = [
        'symbol' => $currencySymbol,
        'position' => $currencyPosition,
        'decimal_places' => (int)$decimalPlaces,
        'decimal_separator' => $decimalSep,
        'thousands_separator' => $thousandsSep,
    ];
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="d-flex align-items-center">
        <a href="{{ url('/') }}" class="btn btn-outline-primary mr-3" target="_blank">
            <i class="fas fa-globe mr-1"></i> View Website
        </a>
        <div class="dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-user-circle fa-2x text-dark"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="{{ route('admin.profile') }}">Profile</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Logout
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-link mr-2"></i> Quick Links
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.sermons') }}" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-microphone mr-1"></i> Teachings
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.devotionals') }}" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-pray mr-1"></i> Devotionals
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.financials') }}" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-coins mr-1"></i> Financials
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.books') }}" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-book mr-1"></i> Books
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Management Overview -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-cogs mr-2"></i> Content Management Overview
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Teachings</h6>
                                        <h2 class="mb-0">{{ $stats['sermons'] }}</h2>
                                    </div>
                                    <i class="fas fa-microphone fa-3x opacity-50"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between small">
                                <a href="{{ route('admin.sermons') }}" class="text-white">View Details</a>
                                <i class="fas fa-angle-right text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Songs</h6>
                                        <h2 class="mb-0">{{ $stats['songs'] }}</h2>
                                    </div>
                                    <i class="fas fa-music fa-3x opacity-50"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between small">
                                <a href="{{ route('admin.songs') }}" class="text-white">View Details</a>
                                <i class="fas fa-angle-right text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Books</h6>
                                        <h2 class="mb-0">{{ $stats['books'] }}</h2>
                                    </div>
                                    <i class="fas fa-book fa-3x opacity-50"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between small">
                                <a href="{{ route('admin.books') }}" class="text-white">View Details</a>
                                <i class="fas fa-angle-right text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-danger text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Events</h6>
                                        <h2 class="mb-0">{{ $stats['events'] }}</h2>
                                    </div>
                                    <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between small">
                                <a href="{{ route('admin.events') }}" class="text-white">View Details</a>
                                <i class="fas fa-angle-right text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Overview -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-coins mr-2"></i> Financial Overview
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Net Balance</h6>
                                        <h2 class="mb-0">{{ $fmtCurrency($netBalance) }}</h2>
                                    </div>
                                    <i class="fas fa-coins fa-3x opacity-50"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between small">
                                <a href="{{ route('admin.financials') }}" class="text-white">View Details</a>
                                <i class="fas fa-angle-right text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Monthly Inflow</h6>
                                        <h2 class="mb-0">{{ $fmtCurrency($totalInflow) }}</h2>
                                    </div>
                                    <i class="fas fa-arrow-down fa-3x opacity-50"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between small">
                                <a href="{{ route('admin.financials') }}" class="text-white">View Details</a>
                                <i class="fas fa-angle-right text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-danger text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Monthly Outflow</h6>
                                        <h2 class="mb-0">{{ $fmtCurrency($totalOutflow) }}</h2>
                                    </div>
                                    <i class="fas fa-arrow-up fa-3x opacity-50"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between small">
                                <a href="{{ route('admin.financials') }}" class="text-white">View Details</a>
                                <i class="fas fa-angle-right text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Designated Funds</h6>
                                        <h2 class="mb-0">{{ $fmtCurrency($designatedFunds) }}</h2>
                                    </div>
                                    <i class="fas fa-hand-holding-heart fa-3x opacity-50"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between small">
                                <a href="{{ route('admin.financials') }}" class="text-white">View Details</a>
                                <i class="fas fa-angle-right text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mb-4">
    <div class="col-lg-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-pie mr-1"></i> Inflow Breakdown
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container chart-container-sm">
                    <canvas id="inflowPieChart"></canvas>
                </div>
                <hr>
                @foreach($inflowByCategory as $cat => $total)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-capitalize small">{{ str_replace('_', ' ', $cat) }}</span>
                    <span class="font-weight-bold text-money">{{ $fmtCurrency($total) }}</span>
                </div>
                @endforeach
                @if($inflowByCategory->isEmpty())
                    <p class="text-muted text-center mb-0">No inflow data</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-line mr-1"></i> Monthly Trend (12 Months)
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar mr-1"></i> Attendance Trend (12 Months)
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="attendanceTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list mr-1"></i> Recent Transactions
                </h6>
                <small class="text-muted">Last 10 entries</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" id="recentTransactionsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th class="text-right">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $tx)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('M d') }}</td>
                                <td>
                                    @if($tx->type === 'inflow')
                                        <span class="badge badge-success">Inflow</span>
                                    @else
                                        <span class="badge badge-danger">Outflow</span>
                                    @endif
                                </td>
                                <td class="text-capitalize">{{ str_replace('_', ' ', $tx->category) }}</td>
                                <td>{{ $tx->description ?? '—' }}</td>
                                <td class="text-right text-money {{ $tx->type === 'inflow' ? 'amount-inflow' : 'amount-outflow' }}">
                                    {{ $fmtCurrency($tx->amount) }}
                                </td>
                                <td>
                                    @if($tx->status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($tx->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @else
                                        <span class="badge badge-danger">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No transactions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script id="chart-data-json" type="application/json">@json($chartData)</script>
<script id="inflow-data-json" type="application/json">@json($inflowByCategory)</script>
<script id="currency-settings" type="application/json">@json($currencySettings)</script>
<script>
$(document).ready(function() {

    // ════════════════════════════════════════════
    //  CHART.JS – Monthly Trend
    // ════════════════════════════════════════════
    var chartData = JSON.parse(document.getElementById('chart-data-json').textContent);
    var currency = JSON.parse(document.getElementById('currency-settings').textContent);

    function fmtCurrencyJs(val) {
        var fixed = Number(val).toFixed(currency.decimal_places);
        var parts = fixed.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, currency.thousands_separator);
        var formatted = parts.join(currency.decimal_separator);
        return currency.position === 'before'
            ? currency.symbol + formatted
            : formatted + ' ' + currency.symbol;
    }

    var ctx = document.getElementById('monthlyTrendChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Inflows',
                    data: chartData.inflow,
                    backgroundColor: 'rgba(28, 200, 138, 0.7)',
                    borderColor: '#1cc88a',
                    borderWidth: 2,
                }, {
                    label: 'Outflows',
                    data: chartData.outflow,
                    backgroundColor: 'rgba(231, 74, 59, 0.7)',
                    borderColor: '#e74a3b',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return data.datasets[tooltipItem.datasetIndex].label + ': ' +
                                fmtCurrencyJs(parseFloat(tooltipItem.value));
                        }
                    }
                },
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [{
                        gridLines: { color: '#f8f9fc' },
                        ticks: {
                            callback: function(value) { return fmtCurrencyJs(value); }
                        }
                    }]
                }
            }
        });
    }

    // ════════════════════════════════════════════
    //  CHART.JS – Inflow Pie Chart
    // ════════════════════════════════════════════
    var pieCtx = document.getElementById('inflowPieChart');
    if (pieCtx) {
        var inflowData = JSON.parse(document.getElementById('inflow-data-json').textContent);
        var labels = Object.keys(inflowData).map(function(k) { return k.replace(/_/g, ' '); });
        var values = Object.values(inflowData);
        var colors = ['#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#4e73df'];

        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: labels.length ? labels : ['No Data'],
                datasets: [{
                    data: values.length ? values : [1],
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, fontStyle: 'italic' } },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var total = data.datasets[0].data.reduce(function(a, b) { return a + b; }, 0);
                            var val = data.datasets[0].data[tooltipItem.index];
                            var pct = ((val / total) * 100).toFixed(1);
                            return data.labels[tooltipItem.index] + ': ' +
                                fmtCurrencyJs(parseFloat(val)) +
                                ' (' + pct + '%)';
                        }
                    }
                }
            }
        });
    }

    // ════════════════════════════════════════════
    //  CHART.JS – Attendance Trend
    // ════════════════════════════════════════════
    var attCtx = document.getElementById('attendanceTrendChart');
    if (attCtx) {
        new Chart(attCtx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Attendance',
                    data: chartData.attendance,
                    backgroundColor: 'rgba(54, 185, 204, 0.7)',
                    borderColor: '#36b9cc',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return 'Attendance: ' + tooltipItem.value;
                        }
                    }
                },
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [{
                        gridLines: { color: '#f8f9fc' },
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) { return value.toLocaleString(); }
                        }
                    }]
                }
            }
        });
    }

    // ════════════════════════════════════════════
    //  DATATABLES – Recent Transactions
    // ════════════════════════════════════════════
    if ($.fn.DataTable) {
        $('#recentTransactionsTable').DataTable({
            "pageLength": 10,
            "order": [[0, "desc"]],
            "paging": false,
            "info": false,
            "searching": false
        });
    }

});
</script>
@endsection
