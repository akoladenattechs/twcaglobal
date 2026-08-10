<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Book;
use App\Models\ChurchMember;
use App\Models\ChurchStaff;
use App\Models\Devotional;
use App\Models\Event;
use App\Models\FinancialFund;
use App\Models\FinancialTransaction;
use App\Models\HomepageSlider;
use App\Models\Media;
use App\Models\Menu;
use App\Models\Sermon;
use App\Models\SiteSetting;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    private function getStats()
    {
        return [
            'media' => Media::count(),
            'books' => Book::count(),
            'events' => Event::count(),
            'members' => ChurchMember::count(),
            'staff' => ChurchStaff::count(),
            'offerings' => FinancialTransaction::where('type', 'inflow')->where('status', 'approved')->count(),
            'attendance' => Attendance::count(),
            'sermons' => Sermon::count(),
            'songs' => Song::count(),
            'devotionals' => Devotional::count(),
            'sliders' => HomepageSlider::count(),
            'menus' => Menu::count(),
        ];
    }

    public function index()
    {
        $stats = $this->getStats();

        // ── Financial Overview Data ──
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->endOfMonth()->toDateString();

        $designatedFunds = FinancialFund::where('is_active', true)->sum('current_amount');

        $totalInflow = FinancialTransaction::where('type', 'inflow')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $totalOutflow = FinancialTransaction::where('type', 'outflow')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $netBalance = $totalInflow - $totalOutflow;

        $inflowByCategory = FinancialTransaction::where('type', 'inflow')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // Monthly chart data (last 12 months)
        $monthlyLabels = [];
        $monthlyInflow = [];
        $monthlyOutflow = [];
        $monthlyAttendance = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            $start = $month->copy()->startOfMonth()->toDateString();
            $end = $month->copy()->endOfMonth()->toDateString();

            $monthlyInflow[] = FinancialTransaction::where('type', 'inflow')
                ->where('status', 'approved')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $monthlyOutflow[] = FinancialTransaction::where('type', 'outflow')
                ->where('status', 'approved')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $monthlyAttendance[] = Attendance::whereBetween('service_date', [$start, $end])
                ->sum('total');
        }

        $chartData = [
            'labels' => $monthlyLabels,
            'inflow' => $monthlyInflow,
            'outflow' => $monthlyOutflow,
            'attendance' => $monthlyAttendance,
        ];

        // Recent transactions
        $transactions = FinancialTransaction::with(['recordedBy', 'approvedBy', 'account', 'fund', 'member'])
            ->where('status', 'approved')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // ── Currency Settings ──
        $siteSettingsFromDb = SiteSetting::getAllSettings();
        $currencySymbol = $siteSettingsFromDb['currency_symbol'] ?? '';
        $currencyPosition = $siteSettingsFromDb['currency_position'] ?? '';
        $decimalPlaces = (int) ($siteSettingsFromDb['decimal_places'] ?? 2);
        $decimalSep = $siteSettingsFromDb['decimal_separator'] ?? '.';
        $thousandsSep = $siteSettingsFromDb['thousands_separator'] ?? ',';

        return view('admin.dashboard', compact(
            'stats',
            'netBalance', 'totalInflow', 'totalOutflow',
            'designatedFunds',
            'inflowByCategory', 'chartData', 'transactions',
            'startDate', 'endDate',
            'currencySymbol', 'currencyPosition', 'decimalPlaces', 'decimalSep', 'thousandsSep',
        ));
    }

    public function profile()
    {
        /** @var User $user */
        $user = Auth::user();

        return view('admin.profile', compact('user'));
    }

    public function profileUpdate(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Validation
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
        ];

        // Password change validation
        if ($request->filled('new_password')) {
            $rules['current_password'] = 'required|string';
            $rules['new_password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        // Verify current password if trying to change password
        if ($request->filled('new_password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
        }

        // Update user
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;

        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
            $user->setRememberToken(\Illuminate\Support\Str::random(60));
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }
}
