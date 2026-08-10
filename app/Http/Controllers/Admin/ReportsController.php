<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CenterLocation;
use App\Models\ChurchMember;
use App\Models\FinancialAccount;
use App\Models\FinancialCampaign;
use App\Models\FinancialFund;
use App\Models\FinancialPledge;
use App\Models\FinancialTransaction;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportsController extends Controller
{
    /**
     * Display the Reports & Analytics page.
     */
    public function index(Request $request)
    {
        // Use date range directly from request (no period presets)
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        // Currency settings
        $siteSettings = SiteSetting::getAllSettings();
        $currencySymbol   = $siteSettings['currency_symbol'] ?? '';
        $currencyPosition = $siteSettings['currency_position'] ?? 'before';
        $decimalPlaces    = (int) ($siteSettings['decimal_places'] ?? 2);
        $decimalSep       = $siteSettings['decimal_separator'] ?? '.';
        $thousandsSep     = $siteSettings['thousands_separator'] ?? ',';

        // ── Financial Reports ──
        $financialData = $this->getFinancialData($startDate, $endDate);

        // ── Attendance Reports ──
        $attendanceData = $this->getAttendanceData($startDate, $endDate);

        // ── Member Analytics ──
        $memberData = $this->getMemberData($startDate, $endDate);

        // ── Accounts & Funds Overview ──
        $accountsData = $this->getAccountsData();

        return view('admin.reports', compact(
            'startDate', 'endDate',
            'currencySymbol', 'currencyPosition', 'decimalPlaces', 'decimalSep', 'thousandsSep',
            'financialData', 'attendanceData', 'memberData', 'accountsData',
        ));
    }

    /**
     * Export any tab's data as CSV.
     */
    public function exportCsv(Request $request)
    {
        $request->validate([
            'tab'       => 'required|in:financial,attendance,members,giving,funds',
            'start_date'=> 'required|date',
            'end_date'  => 'required|date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $tab = $request->input('tab');

        switch ($tab) {
            case 'financial':
                $data = $this->getFinancialTransactions($startDate, $endDate);
                $filename = "financial-report-{$startDate}-to-{$endDate}.csv";
                $headers = ['Date', 'Type', 'Category', 'Description', 'Amount', 'Payment Method', 'Status', 'Recorded By'];
                $rows = $data->map(fn($tx) => [
                    $tx->transaction_date->format('Y-m-d'),
                    ucfirst($tx->type),
                    str_replace('_', ' ', $tx->category),
                    $tx->description ?? '',
                    number_format($tx->amount, 2),
                    str_replace('_', ' ', $tx->payment_method),
                    ucfirst($tx->status),
                    $tx->recordedBy ? $tx->recordedBy->first_name . ' ' . $tx->recordedBy->last_name : '',
                ]);
                break;

            case 'giving':
                $data = $this->getGivingStatements($startDate, $endDate);
                $filename = "giving-statements-{$startDate}-to-{$endDate}.csv";
                $headers = ['Member Name', 'Email', 'Total Given', 'Transaction Count', 'Categories'];
                $rows = $data->map(fn($item) => [
                    $item['name'],
                    $item['email'],
                    number_format($item['total'], 2),
                    $item['count'],
                    $item['categories'],
                ]);
                break;

            case 'attendance':
                $data = $this->getAttendanceRecords($startDate, $endDate);
                $filename = "attendance-report-{$startDate}-to-{$endDate}.csv";
                $headers = ['Date', 'Service Type', 'Center', 'Males', 'Females', 'First Timers', 'Total'];
                $rows = $data->map(fn($a) => [
                    $a->service_date,
                    $a->service_type ?? 'General',
                    $a->center ? $a->center->name : 'Main',
                    $a->males,
                    $a->females,
                    $a->first_timers,
                    $a->total,
                ]);
                break;

            case 'members':
                $data = $this->getMemberGrowthData($startDate, $endDate);
                $filename = "member-growth-{$startDate}-to-{$endDate}.csv";
                $headers = ['Month', 'New Members', 'Total Members'];
                $rows = collect($data['labels'])->map(fn($label, $i) => [
                    $label,
                    $data['newMembers'][$i] ?? 0,
                    $data['cumulative'][$i] ?? 0,
                ]);
                break;

            case 'funds':
                $data = $this->getFundBreakdownData($startDate, $endDate);
                $filename = "fund-breakdown-{$startDate}-to-{$endDate}.csv";
                $headers = ['Fund Name', 'Target Amount', 'Current Amount', 'Progress (%)', 'Transactions', 'Total Received'];
                $rows = $data->map(fn($f) => [
                    $f['name'],
                    number_format($f['target'], 2),
                    number_format($f['current'], 2),
                    $f['progress'],
                    $f['tx_count'],
                    number_format($f['total_received'], 2),
                ]);
                break;

            default:
                return redirect()->route('admin.reports')->with('error', 'Invalid export tab.');
        }

        return $this->streamCsv($filename, $headers, $rows);
    }

    // ════════════════════════════════════════════
    //  DATE RANGE HELPERS
    // ════════════════════════════════════════════

    private function resolveDateRange(string $period, Request $request): array
    {
        return match ($period) {
            'week'   => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'month'  => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'quarter'=> [now()->startOfQuarter()->toDateString(), now()->endOfQuarter()->toDateString()],
            'year'   => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'last30' => [now()->subDays(29)->toDateString(), now()->toDateString()],
            'custom' => [
                $request->input('start_date', now()->startOfMonth()->toDateString()),
                $request->input('end_date', now()->endOfMonth()->toDateString()),
            ],
            default  => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
        };
    }

    // ════════════════════════════════════════════
    //  FINANCIAL DATA
    // ════════════════════════════════════════════

    private function getFinancialData(string $startDate, string $endDate): array
    {
        $totalInflow = FinancialTransaction::where('type', 'inflow')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $totalOutflow = FinancialTransaction::where('type', 'outflow')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $netBalance = $totalInflow - $totalOutflow;

        // Per-fund breakdown
        $fundBreakdown = $this->getFundBreakdownData($startDate, $endDate);

        // Campaign performance
        $campaigns = FinancialCampaign::withCount(['pledges', 'pledges as fulfilled_pledges_count' => function ($q) {
            $q->where('status', 'completed');
        }])->get()->map(fn($c) => [
            'title'           => $c->title,
            'target'          => (float) $c->target_amount,
            'raised'          => (float) $c->raised_amount,
            'progress'        => $c->progress,
            'remaining_days'  => $c->remaining_days,
            'status'          => $c->status,
            'pledges_count'   => $c->pledges_count,
            'fulfilled_count' => $c->fulfilled_pledges_count,
        ]);

        // Inflow by category
        $inflowByCategory = FinancialTransaction::where('type', 'inflow')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // Outflow by category
        $outflowByCategory = FinancialTransaction::where('type', 'outflow')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // Monthly chart data (for range-appropriate granularity)
        [$monthlyLabels, $monthlyInflowData, $monthlyOutflowData] = $this->getMonthlyFinancialChart($startDate, $endDate);

        // Giving statements (per-member)
        $givingStatements = $this->getGivingStatements($startDate, $endDate);

        return compact(
            'totalInflow', 'totalOutflow', 'netBalance',
            'fundBreakdown', 'campaigns',
            'inflowByCategory', 'outflowByCategory',
            'monthlyLabels', 'monthlyInflowData', 'monthlyOutflowData',
            'givingStatements',
        );
    }

    private function getFinancialTransactions(string $startDate, string $endDate): Collection
    {
        return FinancialTransaction::with(['recordedBy', 'fund', 'member'])
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    private function getGivingStatements(string $startDate, string $endDate): Collection
    {
        return FinancialTransaction::where('type', 'inflow')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->whereNotNull('member_id')
            ->selectRaw('member_id, SUM(amount) as total, COUNT(*) as count, GROUP_CONCAT(DISTINCT category) as categories')
            ->groupBy('member_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                $member = ChurchMember::find($row->member_id);
                return [
                    'name'       => $member ? $member->first_name . ' ' . $member->last_name : 'Unknown',
                    'email'      => $member?->email ?? '',
                    'total'      => (float) $row->total,
                    'count'      => (int) $row->count,
                    'categories' => str_replace('_', ' ', $row->categories ?? ''),
                ];
            });
    }

    private function getMonthlyFinancialChart(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);
        $diffMonths = $start->diffInMonths($end);

        $labels = [];
        $inflow = [];
        $outflow = [];

        if ($diffMonths <= 6) {
            // Weekly granularity
            $current = $start->copy()->startOfWeek();
            while ($current->lte($end)) {
                $weekEnd = $current->copy()->endOfWeek();
                if ($weekEnd->gt($end)) {
                    $weekEnd = $end->copy();
                }
                $labels[] = $current->format('M d') . ' – ' . $weekEnd->format('M d');
                $inflow[] = FinancialTransaction::where('type', 'inflow')
                    ->where('status', 'approved')
                    ->whereBetween('transaction_date', [$current->toDateString(), $weekEnd->toDateString()])
                    ->sum('amount');
                $outflow[] = FinancialTransaction::where('type', 'outflow')
                    ->where('status', 'approved')
                    ->whereBetween('transaction_date', [$current->toDateString(), $weekEnd->toDateString()])
                    ->sum('amount');
                $current->addWeek();
            }
        } else {
            // Monthly granularity
            $current = $start->copy()->startOfMonth();
            while ($current->lte($end)) {
                $monthEnd = $current->copy()->endOfMonth();
                if ($monthEnd->gt($end)) {
                    $monthEnd = $end->copy();
                }
                $labels[] = $current->format('M Y');
                $inflow[] = FinancialTransaction::where('type', 'inflow')
                    ->where('status', 'approved')
                    ->whereBetween('transaction_date', [$current->toDateString(), $monthEnd->toDateString()])
                    ->sum('amount');
                $outflow[] = FinancialTransaction::where('type', 'outflow')
                    ->where('status', 'approved')
                    ->whereBetween('transaction_date', [$current->toDateString(), $monthEnd->toDateString()])
                    ->sum('amount');
                $current->addMonth();
            }
        }

        return [$labels, $inflow, $outflow];
    }

    private function getFundBreakdownData(string $startDate, string $endDate): Collection
    {
        return FinancialFund::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($fund) use ($startDate, $endDate) {
                $txInPeriod = $fund->transactions()
                    ->where('type', 'inflow')
                    ->where('status', 'approved')
                    ->whereBetween('transaction_date', [$startDate, $endDate]);
                $totalReceived = (clone $txInPeriod)->sum('amount');
                $txCount       = (clone $txInPeriod)->count();

                return [
                    'name'           => $fund->name,
                    'target'         => (float) $fund->target_amount,
                    'current'        => (float) $fund->current_amount,
                    'progress'       => $fund->progress,
                    'tx_count'       => $txCount,
                    'total_received' => $totalReceived,
                ];
            });
    }

    // ════════════════════════════════════════════
    //  ATTENDANCE DATA
    // ════════════════════════════════════════════

    private function getAttendanceData(string $startDate, string $endDate): array
    {
        $totalAttendance = Attendance::whereBetween('service_date', [$startDate, $endDate])->sum('total');
        $totalMales      = Attendance::whereBetween('service_date', [$startDate, $endDate])->sum('males');
        $totalFemales    = Attendance::whereBetween('service_date', [$startDate, $endDate])->sum('females');
        $totalFirstTimers= Attendance::whereBetween('service_date', [$startDate, $endDate])->sum('first_timers');
        $serviceCount    = Attendance::whereBetween('service_date', [$startDate, $endDate])->count();
        $avgAttendance   = $serviceCount > 0 ? round($totalAttendance / $serviceCount, 1) : 0;
        $peakAttendance  = Attendance::whereBetween('service_date', [$startDate, $endDate])->max('total');

        // By service type
        $byServiceType = Attendance::whereBetween('service_date', [$startDate, $endDate])
            ->selectRaw('service_type, SUM(total) as total, SUM(males) as males, SUM(females) as females, SUM(first_timers) as first_timers, COUNT(*) as services')
            ->groupBy('service_type')
            ->pluck('total', 'service_type');

        // Gender breakdown over time
        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);
        $diffMonths = $start->diffInMonths($end);
        $attLabels = [];
        $attTotal = [];
        $attMales = [];
        $attFemales = [];
        $attFirstTimers = [];

        if ($diffMonths <= 6) {
            // Weekly
            $current = $start->copy()->startOfWeek();
            while ($current->lte($end)) {
                $weekEnd = min($current->copy()->endOfWeek(), $end->copy());
                $attLabels[] = $current->format('M d');
                $records = Attendance::whereBetween('service_date', [$current->toDateString(), $weekEnd->toDateString()])->first();
                $attTotal[]       = Attendance::whereBetween('service_date', [$current->toDateString(), $weekEnd->toDateString()])->sum('total');
                $attMales[]       = Attendance::whereBetween('service_date', [$current->toDateString(), $weekEnd->toDateString()])->sum('males');
                $attFemales[]     = Attendance::whereBetween('service_date', [$current->toDateString(), $weekEnd->toDateString()])->sum('females');
                $attFirstTimers[] = Attendance::whereBetween('service_date', [$current->toDateString(), $weekEnd->toDateString()])->sum('first_timers');
                $current->addWeek();
            }
        } else {
            // Monthly
            $current = $start->copy()->startOfMonth();
            while ($current->lte($end)) {
                $monthEnd = min($current->copy()->endOfMonth(), $end->copy());
                $attLabels[] = $current->format('M Y');
                $attTotal[]       = Attendance::whereBetween('service_date', [$current->toDateString(), $monthEnd->toDateString()])->sum('total');
                $attMales[]       = Attendance::whereBetween('service_date', [$current->toDateString(), $monthEnd->toDateString()])->sum('males');
                $attFemales[]     = Attendance::whereBetween('service_date', [$current->toDateString(), $monthEnd->toDateString()])->sum('females');
                $attFirstTimers[] = Attendance::whereBetween('service_date', [$current->toDateString(), $monthEnd->toDateString()])->sum('first_timers');
                $current->addMonth();
            }
        }

        return compact(
            'totalAttendance', 'totalMales', 'totalFemales', 'totalFirstTimers',
            'serviceCount', 'avgAttendance', 'peakAttendance',
            'byServiceType',
            'attLabels', 'attTotal', 'attMales', 'attFemales', 'attFirstTimers',
        );
    }

    private function getAttendanceRecords(string $startDate, string $endDate): Collection
    {
        return Attendance::with('center')
            ->whereBetween('service_date', [$startDate, $endDate])
            ->orderBy('service_date', 'desc')
            ->get();
    }

    // ════════════════════════════════════════════
    //  MEMBER DATA
    // ════════════════════════════════════════════

    private function getMemberData(string $startDate, string $endDate): array
    {
        $totalMembers   = ChurchMember::count();
        $activeMembers  = ChurchMember::where('membership_status', 'active')->count();
        $inactiveMembers= ChurchMember::where('membership_status', 'inactive')->count();
        $suspendedMembers = ChurchMember::where('membership_status', 'suspended')->count();
        $newThisPeriod  = ChurchMember::whereBetween('date_joined', [$startDate, $endDate])->count();
        $newThisYear    = ChurchMember::whereYear('date_joined', now()->year)->count();
        $retentionRate  = $totalMembers > 0 ? round(($activeMembers / $totalMembers) * 100, 1) : 0;

        // Gender distribution
        $genderDistribution = ChurchMember::selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->pluck('count', 'gender');

        // Center distribution
        $centerDistribution = ChurchMember::selectRaw('center_id, COUNT(*) as count')
            ->groupBy('center_id')
            ->get()
            ->mapWithKeys(function ($row) {
                $center = CenterLocation::find($row->center_id);
                return [($center?->name ?? 'Unknown') => $row->count];
            });

        // Member growth over time (monthly)
        $memberLabels = [];
        $newMembers   = [];
        $cumulative   = [];

        $start = Carbon::parse($startDate)->startOfMonth();
        $end   = Carbon::parse($endDate)->startOfMonth();
        $runningTotal = ChurchMember::where('date_joined', '<', $start->toDateString())->count();

        $current = $start->copy();
        while ($current->lte($end)) {
            $monthEnd = $current->copy()->endOfMonth();
            $memberLabels[] = $current->format('M Y');
            $newCount = ChurchMember::whereBetween('date_joined', [$current->toDateString(), $monthEnd->toDateString()])->count();
            $runningTotal += $newCount;
            $newMembers[]   = $newCount;
            $cumulative[]   = $runningTotal;
            $current->addMonth();
        }

        // Membership status breakdown
        $statusBreakdown = ChurchMember::selectRaw('membership_status, COUNT(*) as count')
            ->groupBy('membership_status')
            ->pluck('count', 'membership_status');

        return compact(
            'totalMembers', 'activeMembers', 'inactiveMembers', 'suspendedMembers',
            'newThisPeriod', 'newThisYear', 'retentionRate',
            'genderDistribution', 'centerDistribution',
            'memberLabels', 'newMembers', 'cumulative',
            'statusBreakdown',
        );
    }

    private function getMemberGrowthData(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfMonth();
        $end   = Carbon::parse($endDate)->startOfMonth();
        $runningTotal = ChurchMember::where('date_joined', '<', $start->toDateString())->count();

        $labels = [];
        $newMembers = [];
        $cumulative = [];

        $current = $start->copy();
        while ($current->lte($end)) {
            $monthEnd = $current->copy()->endOfMonth();
            $labels[] = $current->format('M Y');
            $newCount = ChurchMember::whereBetween('date_joined', [$current->toDateString(), $monthEnd->toDateString()])->count();
            $runningTotal += $newCount;
            $newMembers[] = $newCount;
            $cumulative[] = $runningTotal;
            $current->addMonth();
        }

        return compact('labels', 'newMembers', 'cumulative');
    }

    // ════════════════════════════════════════════
    //  ACCOUNTS & FUNDS DATA
    // ════════════════════════════════════════════

    private function getAccountsData(): array
    {
        $accounts = FinancialAccount::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($a) => [
                'name'            => $a->name,
                'type'            => $a->type,
                'bank_name'        => $a->bank_name ?? '',
                'opening_balance'  => (float) $a->opening_balance,
                'current_balance'  => (float) $a->current_balance,
                'change'           => (float) $a->current_balance - (float) $a->opening_balance,
                'change_pct'       => $a->opening_balance > 0
                    ? round((($a->current_balance - $a->opening_balance) / $a->opening_balance) * 100, 1)
                    : 0,
            ]);

        $funds = FinancialFund::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($f) => [
                'name'     => $f->name,
                'target'   => (float) $f->target_amount,
                'current'  => (float) $f->current_amount,
                'progress' => $f->progress,
            ]);

        $totalAccountBalance = $accounts->sum('current_balance');
        $totalFundBalance    = $funds->sum('current');
        $totalFundTarget     = $funds->sum('target');

        return compact('accounts', 'funds', 'totalAccountBalance', 'totalFundBalance', 'totalFundTarget');
    }

    // ════════════════════════════════════════════
    //  CSV STREAMING
    // ════════════════════════════════════════════

    private function streamCsv(string $filename, array $headers, Collection $rows)
    {
        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }
}
