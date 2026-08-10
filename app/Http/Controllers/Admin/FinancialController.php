<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChurchMember;
use App\Models\FinancialAccount;
use App\Models\FinancialCampaign;
use App\Models\FinancialFund;
use App\Models\FinancialPledge;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialController extends Controller
{
    /**
     * Main financials page – tabbed SPA entry point.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        // ── KPI Cards (always computed) ──
        $totalInflow = FinancialTransaction::where('type', 'inflow')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $totalOutflow = FinancialTransaction::where('type', 'outflow')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $netBalance = $totalInflow - $totalOutflow;

        // ── Inflows breakdown ──
        $inflowByCategory = FinancialTransaction::where('type', 'inflow')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // ── Outflows breakdown ──
        $outflowByCategory = FinancialTransaction::where('type', 'outflow')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // ── Chart data (monthly for last 12 months) ──
        $monthlyLabels = [];
        $monthlyInflow = [];
        $monthlyOutflow = [];

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
        }

        $chartData = [
            'labels' => $monthlyLabels,
            'inflow' => $monthlyInflow,
            'outflow' => $monthlyOutflow,
        ];

        // ── Lists for tabs ──
        $transactions = FinancialTransaction::with(['recordedBy', 'approvedBy', 'account', 'fund', 'member'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $inflows = $transactions->where('type', 'inflow');
        $outflows = $transactions->where('type', 'outflow');

        $pendingOutflows = FinancialTransaction::where('type', 'outflow')
            ->where('status', 'pending')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->get();

        $accounts = FinancialAccount::where('is_active', true)->orderBy('name')->get();
        $funds = FinancialFund::where('is_active', true)->orderBy('name')->get();
        $campaigns = FinancialCampaign::orderBy('start_date', 'desc')->get();
        $pledges = FinancialPledge::with(['member', 'campaign'])->orderBy('pledge_date', 'desc')->get();
        $members = ChurchMember::orderBy('last_name')->orderBy('first_name')->get();
        $users = User::orderBy('first_name')->get();

        return view('admin.financials', compact(
            'startDate', 'endDate',
            'totalInflow', 'totalOutflow', 'netBalance',
            'inflowByCategory', 'outflowByCategory',
            'chartData',
            'inflows', 'outflows', 'pendingOutflows',
            'accounts', 'funds', 'campaigns', 'pledges',
            'members', 'users', 'transactions',
        ));
    }

    // ════════════════════════════════════════════
    //  TRANSACTIONS (shared for inflow/outflow)
    // ════════════════════════════════════════════

    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:inflow,outflow',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,check,mobile_money,other',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'reference_number' => 'nullable|string|max:100',
            'member_id' => 'nullable|exists:church_members,id',
            'account_id' => 'nullable|exists:financial_accounts,id',
            'fund_id' => 'nullable|exists:financial_funds,id',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:pending,approved',
        ]);

        $validated['recorded_by'] = Auth::id();

        if (($validated['status'] ?? 'approved') === 'approved') {
            $validated['approved_by'] = Auth::id();
            $validated['approved_at'] = now();
        }

        // Default status for inflows is approved, outflows are pending (need approval)
        if (! isset($validated['status'])) {
            $validated['status'] = $validated['type'] === 'inflow' ? 'approved' : 'pending';
        }

        $transaction = FinancialTransaction::create($validated);

        // Update account balance
        if ($request->filled('account_id')) {
            $this->updateAccountBalance($request->account_id);
        }

        // Update fund current_amount
        if ($request->filled('fund_id') && $validated['type'] === 'inflow') {
            $this->updateFundBalance($request->fund_id);
        }

        // Update campaign raised_amount if it's a pledge-type inflow linked to a campaign
        if ($validated['type'] === 'inflow' && $request->filled('campaign_id')) {
            $campaign = FinancialCampaign::find($request->campaign_id);
            if ($campaign) {
                $campaign->increment('raised_amount', $validated['amount']);
            }
        }

        $msg = $validated['type'] === 'inflow'
            ? 'Income recorded successfully.'
            : 'Expense submitted for approval.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'transaction' => $transaction->load('recordedBy')]);
        }

        return redirect()->route('admin.financials')->with('success', $msg);
    }

    public function updateTransaction(Request $request, int $id)
    {
        $transaction = FinancialTransaction::findOrFail($id);

        $validated = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,check,mobile_money,other',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'reference_number' => 'nullable|string|max:100',
            'member_id' => 'nullable|exists:church_members,id',
            'account_id' => 'nullable|exists:financial_accounts,id',
            'fund_id' => 'nullable|exists:financial_funds,id',
            'notes' => 'nullable|string',
        ]);

        $oldAccountId = $transaction->account_id;
        $transaction->update($validated);

        // Recalculate balances for old and new accounts
        if ($oldAccountId) {
            $this->updateAccountBalance($oldAccountId);
        }
        if ($request->filled('account_id') && $request->account_id != $oldAccountId) {
            $this->updateAccountBalance($request->account_id);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Transaction updated.', 'transaction' => $transaction->fresh()->load('recordedBy')]);
        }

        return redirect()->route('admin.financials')->with('success', 'Transaction updated.');
    }

    public function deleteTransaction(int $id)
    {
        $transaction = FinancialTransaction::findOrFail($id);
        $accountId = $transaction->account_id;
        $transaction->delete();

        if ($accountId) {
            $this->updateAccountBalance($accountId);
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Transaction deleted.']);
        }

        return redirect()->route('admin.financials')->with('success', 'Transaction deleted.');
    }

    public function approveTransaction(int $id)
    {
        $transaction = FinancialTransaction::findOrFail($id);
        $transaction->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        if ($transaction->account_id) {
            $this->updateAccountBalance($transaction->account_id);
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Transaction approved.']);
        }

        return redirect()->route('admin.financials')->with('success', 'Transaction approved.');
    }

    public function rejectTransaction(Request $request, int $id)
    {
        $transaction = FinancialTransaction::findOrFail($id);
        $transaction->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'notes' => $request->input('notes', $transaction->notes),
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Transaction rejected.']);
        }

        return redirect()->route('admin.financials')->with('success', 'Transaction rejected.');
    }

    // ════════════════════════════════════════════
    //  ACCOUNTS
    // ════════════════════════════════════════════

    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'type' => 'required|in:bank,cash,mobile_money',
            'account_number' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:200',
            'branch' => 'nullable|string|max:200',
            'opening_balance' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $validated['current_balance'] = $validated['opening_balance'];
        $validated['is_active'] = true;

        FinancialAccount::create($validated);

        return redirect()->to(route('admin.financials').'#accounts')->with('success', 'Account created.');
    }

    public function updateAccount(Request $request, int $id)
    {
        $account = FinancialAccount::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'type' => 'required|in:bank,cash,mobile_money',
            'account_number' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:200',
            'branch' => 'nullable|string|max:200',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $account->update($validated);

        return redirect()->to(route('admin.financials').'#accounts')->with('success', 'Account updated.');
    }

    public function deleteAccount(int $id)
    {
        FinancialAccount::findOrFail($id)->delete();

        return redirect()->to(route('admin.financials').'#accounts')->with('success', 'Account deleted.');
    }

    // ════════════════════════════════════════════
    //  FUNDS
    // ════════════════════════════════════════════

    public function storeFund(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'target_amount' => 'nullable|numeric|min:0',
        ]);

        $validated['current_amount'] = 0;
        $validated['is_active'] = true;

        FinancialFund::create($validated);

        return redirect()->to(route('admin.financials').'#accounts')->with('success', 'Fund created.');
    }

    public function updateFund(Request $request, int $id)
    {
        $fund = FinancialFund::findOrFail($id);
        $fund->update($request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'target_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]));

        return redirect()->to(route('admin.financials').'#accounts')->with('success', 'Fund updated.');
    }

    public function deleteFund(int $id)
    {
        FinancialFund::findOrFail($id)->delete();

        return redirect()->to(route('admin.financials').'#accounts')->with('success', 'Fund deleted.');
    }

    // ════════════════════════════════════════════
    //  CAMPAIGNS
    // ════════════════════════════════════════════

    public function storeCampaign(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'target_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['raised_amount'] = 0;
        $validated['status'] = 'active';

        FinancialCampaign::create($validated);

        return redirect()->to(route('admin.financials').'#pledges')->with('success', 'Campaign created.');
    }

    public function updateCampaign(Request $request, int $id)
    {
        $campaign = FinancialCampaign::findOrFail($id);
        $campaign->update($request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'target_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'in:active,completed,cancelled',
        ]));

        return redirect()->to(route('admin.financials').'#pledges')->with('success', 'Campaign updated.');
    }

    public function deleteCampaign(int $id)
    {
        FinancialCampaign::findOrFail($id)->delete();

        return redirect()->to(route('admin.financials').'#pledges')->with('success', 'Campaign deleted.');
    }

    // ════════════════════════════════════════════
    //  PLEDGES
    // ════════════════════════════════════════════

    public function storePledge(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'nullable|exists:church_members,id',
            'campaign_id' => 'nullable|exists:financial_campaigns,id',
            'pledge_amount' => 'required|numeric|min:1',
            'amount_paid' => 'nullable|numeric|min:0',
            'pledge_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['amount_paid'] = $validated['amount_paid'] ?? 0;
        $validated['status'] = 'active';

        $pledge = FinancialPledge::create($validated);

        // If there's an initial payment, create a transaction
        if ($validated['amount_paid'] > 0) {
            FinancialTransaction::create([
                'type' => 'inflow',
                'category' => 'pledge',
                'amount' => $validated['amount_paid'],
                'payment_method' => 'cash',
                'transaction_date' => $validated['pledge_date'],
                'member_id' => $validated['member_id'],
                'recorded_by' => Auth::id(),
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'notes' => 'Initial payment for pledge #'.$pledge->id,
            ]);

            if ($request->filled('campaign_id')) {
                FinancialCampaign::find($request->campaign_id)?->increment('raised_amount', $validated['amount_paid']);
            }
        }

        return redirect()->to(route('admin.financials').'#pledges')->with('success', 'Pledge recorded.');
    }

    public function updatePledge(Request $request, int $id)
    {
        $pledge = FinancialPledge::findOrFail($id);
        $pledge->update($request->validate([
            'member_id' => 'nullable|exists:church_members,id',
            'campaign_id' => 'nullable|exists:financial_campaigns,id',
            'pledge_amount' => 'required|numeric|min:1',
            'status' => 'in:active,completed,cancelled',
            'notes' => 'nullable|string',
        ]));

        return redirect()->to(route('admin.financials').'#pledges')->with('success', 'Pledge updated.');
    }

    public function deletePledge(int $id)
    {
        FinancialPledge::findOrFail($id)->delete();

        return redirect()->to(route('admin.financials').'#pledges')->with('success', 'Pledge deleted.');
    }

    // ════════════════════════════════════════════
    //  HELPERS
    // ════════════════════════════════════════════

    private function updateAccountBalance(int $accountId): void
    {
        $account = FinancialAccount::find($accountId);
        if (! $account) {
            return;
        }

        $inflows = FinancialTransaction::where('account_id', $accountId)
            ->where('type', 'inflow')->where('status', 'approved')->sum('amount');
        $outflows = FinancialTransaction::where('account_id', $accountId)
            ->where('type', 'outflow')->where('status', 'approved')->sum('amount');

        $account->update([
            'current_balance' => $account->opening_balance + $inflows - $outflows,
        ]);
    }

    private function updateFundBalance(int $fundId): void
    {
        $fund = FinancialFund::find($fundId);
        if (! $fund) {
            return;
        }

        $total = FinancialTransaction::where('fund_id', $fundId)
            ->where('type', 'inflow')->where('status', 'approved')->sum('amount');

        $fund->update(['current_amount' => $total]);
    }

    /**
     * Quick-entry modal data endpoint (returns members list).
     */
    public function getMembers()
    {
        return response()->json(
            ChurchMember::orderBy('last_name')->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name', 'phone'])
        );
    }
}
