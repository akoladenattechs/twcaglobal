@extends('layouts.admin')

@section('styles')
@endsection

@section('content')

{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

{{-- Page Header --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h2">
                Manage Financials
            </h1>
        </div>
        <div class="mt-2 mt-md-0 d-flex align-items-center flex-wrap">
            <small class="text-muted mr-3">
                <i class="far fa-calendar-alt mr-1"></i>
                {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} –
                {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
            </small>
            {{-- Tab-dynamic buttons --}}
            <button class="btn btn-primary tab-btn" data-tab="inflows" data-toggle="modal" data-target="#quickEntryModal">
                <i class="fas fa-plus mr-1"></i> Record Inflow
            </button>
            <button class="btn btn-primary ml-2 tab-btn d-none" data-tab="outflows" data-toggle="modal" data-target="#quickEntryModal">
                <i class="fas fa-plus mr-1"></i> Record Outflow
            </button>
            <button class="btn btn-primary ml-2 tab-btn d-none" data-tab="pledges" data-toggle="modal" data-target="#campaignModal">
                <i class="fas fa-plus mr-1"></i> New Campaign
            </button>
            <button class="btn btn-primary ml-2 tab-btn d-none" data-tab="pledges" data-toggle="modal" data-target="#pledgeModal">
                <i class="fas fa-plus mr-1"></i> New Pledge
            </button>
            <button class="btn btn-primary ml-2 tab-btn d-none" data-tab="accounts" data-toggle="modal" data-target="#accountModal">
                <i class="fas fa-plus mr-1"></i> New Account
            </button>
            <button class="btn btn-primary ml-2 tab-btn d-none" data-tab="accounts" data-toggle="modal" data-target="#fundModal">
                <i class="fas fa-plus mr-1"></i> New Fund
            </button>
            <a href="#" class="btn btn-outline-primary ml-2" data-toggle="modal" data-target="#dateRangeModal">
                <i class="fas fa-calendar-alt mr-1"></i> Change Date Range
            </a>
        </div>
    </div>

    {{-- ═══════════════════ TAB NAVIGATION ═══════════════════ --}}
    <ul class="nav nav-tabs" id="financialsTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="tab-inflows" data-toggle="tab" href="#inflows" role="tab" aria-controls="inflows" aria-selected="true">
                <i class="fas fa-arrow-down text-success"></i> Inflows
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab-outflows" data-toggle="tab" href="#outflows" role="tab" aria-controls="outflows" aria-selected="false">
                <i class="fas fa-arrow-up text-danger"></i> Outflows
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab-pledges" data-toggle="tab" href="#pledges" role="tab" aria-controls="pledges" aria-selected="false">
                <i class="fas fa-handshake"></i> Pledges & Campaigns
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab-accounts" data-toggle="tab" href="#accounts" role="tab" aria-controls="accounts" aria-selected="false">
                <i class="fas fa-landmark"></i> Accounts & Funds
            </a>
        </li>
    </ul>

    {{-- ═══════════════════ TAB CONTENT ═══════════════════ --}}
    <div class="tab-content mt-3" id="financialsTabContent">

        {{-- ──────────────── TAB 1: INFLOWS ──────────────── --}}
        <div class="tab-pane fade show active" id="inflows" role="tabpanel" aria-labelledby="tab-inflows">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-arrow-down mr-2"></i> All Inflows
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered datatable" id="inflowsTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Payment</th>
                                    <th>Member</th>
                                    <th class="text-right">Amount</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inflows as $in)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($in->transaction_date)->format('M d, Y') }}</td>
                                    <td><span class="badge badge-success text-capitalize">{{ str_replace('_', ' ', $in->category) }}</span></td>
                                    <td>{{ $in->description ?? '—' }}</td>
                                    <td class="text-capitalize">{{ str_replace('_', ' ', $in->payment_method) }}</td>
                                    <td>{{ $in->member?->first_name ?? '—' }} {{ $in->member?->last_name ?? '' }}</td>
                                    <td class="text-right text-money amount-inflow">{{ number_format($in->amount, 2) }}</td>
                                    <td>
                                        @if($in->status === 'approved')
                                            <span class="badge badge-success">Approved</span>
                                        @elseif($in->status === 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @else
                                            <span class="badge badge-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary edit-tx" data-id="{{ $in->id }}"
                                            data-type="{{ $in->type }}" data-category="{{ $in->category }}"
                                            data-amount="{{ $in->amount }}" data-payment_method="{{ $in->payment_method }}"
                                            data-transaction_date="{{ $in->transaction_date }}"
                                            data-description="{{ $in->description }}"
                                            data-reference_number="{{ $in->reference_number }}"
                                            data-member_id="{{ $in->member_id }}"
                                            data-account_id="{{ $in->account_id }}"
                                            data-fund_id="{{ $in->fund_id }}"
                                            data-notes="{{ $in->notes }}"
                                            title="Edit"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger"
                                                data-delete-action="{{ route('admin.financials.transaction.delete', $in->id) }}"
                                                data-title="Delete Transaction"
                                                data-delete-message="Are you sure you want to delete this transaction? This action cannot be undone." title="Delete"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ──────────────── TAB 3: OUTFLOWS ──────────────── --}}
        <div class="tab-pane fade" id="outflows" role="tabpanel" aria-labelledby="tab-outflows">
            {{-- Approval Queue --}}
            @if($pendingOutflows->count() > 0)
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-clock mr-1"></i> Pending Approval ({{ $pendingOutflows->count() }})
                    </h6>
                </div>
                <div class="card-body p-3">
                    @foreach($pendingOutflows as $po)
                    <div class="approval-item d-flex flex-wrap justify-content-between align-items-center">
                        <div class="mb-2 mb-md-0">
                            <strong>{{ number_format($po->amount, 2) }}</strong>
                            <span class="text-capitalize ml-2">{{ str_replace('_', ' ', $po->category) }}</span>
                            <br><small class="text-muted">{{ \Carbon\Carbon::parse($po->transaction_date)->format('M d, Y') }}
                            @if($po->description) – {{ $po->description }} @endif
                            </small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-success approve-tx" data-id="{{ $po->id }}" title="Approve">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="btn btn-sm btn-danger reject-tx" data-id="{{ $po->id }}" title="Reject">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Outflows Table --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-arrow-up mr-2"></i> All Outflows
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered datatable" id="outflowsTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Payment</th>
                                    <th class="text-right">Amount</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($outflows as $out)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($out->transaction_date)->format('M d, Y') }}</td>
                                    <td><span class="badge badge-danger text-capitalize">{{ str_replace('_', ' ', $out->category) }}</span></td>
                                    <td>{{ $out->description ?? '—' }}</td>
                                    <td class="text-capitalize">{{ str_replace('_', ' ', $out->payment_method) }}</td>
                                    <td class="text-right text-money amount-outflow">{{ number_format($out->amount, 2) }}</td>
                                    <td>
                                        @if($out->status === 'approved')
                                            <span class="badge badge-success">Approved</span>
                                        @elseif($out->status === 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @else
                                            <span class="badge badge-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary edit-tx" data-id="{{ $out->id }}"
                                            data-type="{{ $out->type }}" data-category="{{ $out->category }}"
                                            data-amount="{{ $out->amount }}" data-payment_method="{{ $out->payment_method }}"
                                            data-transaction_date="{{ $out->transaction_date }}"
                                            data-description="{{ $out->description }}"
                                            data-reference_number="{{ $out->reference_number }}"
                                            data-account_id="{{ $out->account_id }}"
                                            data-notes="{{ $out->notes }}"
                                            title="Edit"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger"
                                                data-delete-action="{{ route('admin.financials.transaction.delete', $out->id) }}"
                                                data-title="Delete Transaction"
                                                data-delete-message="Are you sure you want to delete this transaction? This action cannot be undone." title="Delete"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ──────────────── TAB 4: PLEDGES & CAMPAIGNS ──────────────── --}}
        <div class="tab-pane fade" id="pledges" role="tabpanel" aria-labelledby="tab-pledges">
            {{-- Campaigns Section --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bullhorn mr-2"></i> Campaigns
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($campaigns as $campaign)
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="campaign-card shadow-sm h-100">
                                <div class="campaign-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 font-weight-bold">{{ $campaign->title }}</h6>
                                    @if($campaign->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @elseif($campaign->status === 'completed')
                                        <span class="badge badge-info">Completed</span>
                                    @else
                                        <span class="badge badge-secondary">Cancelled</span>
                                    @endif
                                </div>
                                <div class="campaign-body">
                                    <p class="small text-muted mb-2">{{ Str::limit($campaign->description, 100) }}</p>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="font-weight-bold text-primary">{{ number_format($campaign->raised_amount, 2) }}</span>
                                        <span class="text-muted small">of {{ number_format($campaign->target_amount, 2) }}</span>
                                    </div>
                                    <div class="progress pledge-progress mb-2">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            data-progress="{{ $campaign->progress }}"
                                            aria-valuenow="{{ $campaign->progress }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>{{ $campaign->progress }}%</span>
                                        @if($campaign->remaining_days !== null)
                                            <span>{{ $campaign->remaining_days }} days left</span>
                                        @endif
                                    </div>
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-primary edit-campaign" data-id="{{ $campaign->id }}"
                                            data-title="{{ $campaign->title }}" data-description="{{ $campaign->description }}"
                                            data-target_amount="{{ $campaign->target_amount }}"
                                            data-start_date="{{ $campaign->start_date }}" data-end_date="{{ $campaign->end_date }}"
                                            data-status="{{ $campaign->status }}"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger"
                                                data-delete-action="{{ route('admin.financials.campaign.delete', $campaign->id) }}"
                                                data-title="Delete Campaign"
                                                data-delete-message="Are you sure you want to delete this campaign? This action cannot be undone."><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-body text-center text-muted py-5">
                                    <i class="fas fa-bullhorn fa-3x mb-3 text-muted"></i>
                                    <p>No campaigns yet. Create your first fundraising campaign!</p>
                                </div>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Pledges Section --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-handshake mr-2"></i> All Pledges
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered datatable" id="pledgesTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Member</th>
                                    <th>Campaign</th>
                                    <th class="text-right">Pledged</th>
                                    <th class="text-right">Paid</th>
                                    <th class="text-right">Balance</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pledges as $pledge)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($pledge->pledge_date)->format('M d, Y') }}</td>
                                    <td>{{ $pledge->member?->first_name ?? '—' }} {{ $pledge->member?->last_name ?? '' }}</td>
                                    <td>{{ $pledge->campaign?->title ?? '—' }}</td>
                                    <td class="text-right text-money">{{ number_format($pledge->pledge_amount, 2) }}</td>
                                    <td class="text-right text-money amount-inflow">{{ number_format($pledge->amount_paid, 2) }}</td>
                                    <td class="text-right text-money amount-outflow">{{ number_format($pledge->balance, 2) }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress pledge-progress pledge-progress-wrap flex-grow-1 mr-2">
                                                <div class="progress-bar bg-info" role="progressbar"
                                                    data-progress="{{ $pledge->progress }}"></div>
                                            </div>
                                            <small>{{ $pledge->progress }}%</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($pledge->status === 'active')
                                            <span class="badge badge-warning">Active</span>
                                        @elseif($pledge->status === 'completed')
                                            <span class="badge badge-success">Completed</span>
                                        @else
                                            <span class="badge badge-secondary">Cancelled</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary edit-pledge" data-id="{{ $pledge->id }}"
                                            data-member_id="{{ $pledge->member_id }}" data-campaign_id="{{ $pledge->campaign_id }}"
                                            data-pledge_amount="{{ $pledge->pledge_amount }}" data-status="{{ $pledge->status }}"
                                            data-notes="{{ $pledge->notes }}"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger"
                                                data-delete-action="{{ route('admin.financials.pledge.delete', $pledge->id) }}"
                                                data-title="Delete Pledge"
                                                data-delete-message="Are you sure you want to delete this pledge? This action cannot be undone."><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ──────────────── TAB 5: ACCOUNTS & FUNDS ──────────────── --}}
        <div class="tab-pane fade" id="accounts" role="tabpanel" aria-labelledby="tab-accounts">
            {{-- Accounts Section --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-landmark mr-2"></i> Accounts
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($accounts as $account)
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="account-card shadow-sm p-3 position-relative h-100">
                                <span class="account-type-badge badge badge-{{ $account->type === 'bank' ? 'primary' : ($account->type === 'cash' ? 'success' : 'info') }}">
                                    {{ ucwords(str_replace('_', ' ', $account->type)) }}
                                </span>
                                <h6 class="font-weight-bold mb-1">{{ $account->name }}</h6>
                                @if($account->account_number)
                                    <small class="text-muted">{{ $account->bank_name ?? '' }} • {{ $account->account_number }}</small>
                                @endif
                                <div class="account-balance mt-2 amount-inflow">{{ number_format($account->current_balance, 2) }}</div>
                                <small class="text-muted">Current Balance</small>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-primary edit-account" data-id="{{ $account->id }}"
                                        data-name="{{ $account->name }}" data-type="{{ $account->type }}"
                                        data-account_number="{{ $account->account_number }}" data-bank_name="{{ $account->bank_name }}"
                                        data-branch="{{ $account->branch }}" data-notes="{{ $account->notes }}"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger"
                                            data-delete-action="{{ route('admin.financials.account.delete', $account->id) }}"
                                            data-title="Delete Account"
                                            data-delete-message="Are you sure you want to delete this account? This action cannot be undone."><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-body text-center text-muted py-5">
                                    <i class="fas fa-landmark fa-3x mb-3 text-muted"></i>
                                    <p>No accounts set up yet.</p>
                                </div>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Funds Section --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-piggy-bank mr-2"></i> Designated Funds
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($funds as $fund)
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="account-card shadow-sm p-3 h-100">
                                <h6 class="font-weight-bold mb-1">{{ $fund->name }}</h6>
                                <p class="small text-muted mb-2">{{ $fund->description ?? 'No description' }}</p>
                                @if($fund->target_amount && $fund->target_amount > 0)
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="font-weight-bold text-primary">{{ number_format($fund->current_amount, 2) }}</span>
                                        <span class="text-muted small">of {{ number_format($fund->target_amount, 2) }}</span>
                                    </div>
                                    <div class="progress fund-progress-xs mb-2">
                                        <div class="progress-bar bg-info" role="progressbar"
                                            data-progress="{{ $fund->progress }}"
                                            aria-valuenow="{{ $fund->progress }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $fund->progress }}% funded</small>
                                @else
                                    <div class="account-balance amount-inflow">{{ number_format($fund->current_amount, 2) }}</div>
                                    <small class="text-muted">Current balance (no target set)</small>
                                @endif
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-primary edit-fund" data-id="{{ $fund->id }}"
                                        data-name="{{ $fund->name }}" data-description="{{ $fund->description }}"
                                        data-target_amount="{{ $fund->target_amount }}"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger"
                                            data-delete-action="{{ route('admin.financials.fund.delete', $fund->id) }}"
                                            data-title="Delete Fund"
                                            data-delete-message="Are you sure you want to delete this fund? This action cannot be undone."><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-body text-center text-muted py-5">
                                    <i class="fas fa-piggy-bank fa-3x mb-3 text-muted"></i>
                                    <p>No funds created yet.</p>
                                </div>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /tab-content --}}

{{-- ════════════════════════════════════════════════════════════ --}}
{{--  MODALS                                             --}}
{{-- ════════════════════════════════════════════════════════════ --}}

{{-- Date Range Modal --}}
<div class="modal fade" id="dateRangeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <form method="GET" action="{{ route('admin.financials') }}">
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

{{-- Quick Entry Modal (Rapid Keyboard-Only Entry) --}}
<div class="modal fade" id="quickEntryModal" tabindex="-1" role="dialog" aria-labelledby="quickEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickEntryModalLabel">
                    <i class="fas fa-bolt mr-1"></i> Quick Entry
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="quickEntryForm" method="POST" action="{{ route('admin.financials.transaction.store') }}">
                @csrf
                <input type="hidden" name="type" id="qe_type" value="inflow">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Type</label>
                                <select class="form-control" id="qe_type_select" required>
                                    <option value="inflow">💰 Inflow (Income)</option>
                                    <option value="outflow">💸 Outflow (Expense)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" class="form-control" name="transaction_date" value="{{ now()->toDateString() }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Category</label>
                                <select class="form-control" name="category" id="qe_category" required>
                                    <optgroup label="Inflow Categories">
                                        <option value="tithe">Tithe</option>
                                        <option value="offering" selected>Offering</option>
                                        <option value="special_offering">Special Offering</option>
                                        <option value="building_fund">Building Fund</option>
                                        <option value="pledge">Pledge Payment</option>
                                        <option value="other_income">Other Income</option>
                                    </optgroup>
                                    <optgroup label="Outflow Categories" id="qe_outflow_categories" style="display:none;">
                                        <option value="ministry_expense">Ministry Expense</option>
                                        <option value="administrative">Administrative</option>
                                        <option value="utilities">Utilities</option>
                                        <option value="salary">Salary / Stipend</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="missions">Missions / Outreach</option>
                                        <option value="other_expense">Other Expense</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Amount</label>
                                <input type="number" step="0.01" class="form-control" name="amount" placeholder="0.00" required autofocus>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select class="form-control" name="payment_method" required>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Account</label>
                                <select class="form-control" name="account_id">
                                    <option value="">— Select Account —</option>
                                    @foreach($accounts as $acct)
                                        <option value="{{ $acct->id }}">{{ $acct->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" class="form-control" name="description" placeholder="Brief description..." maxlength="500">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Member (optional)</label>
                                <select class="form-control select2-member" name="member_id">
                                    <option value="">— None —</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Fund (optional)</label>
                                <select class="form-control" name="fund_id" id="qe_fund">
                                    <option value="">— None —</option>
                                    @foreach($funds as $fund)
                                        <option value="{{ $fund->id }}">{{ $fund->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-keyboard mr-1"></i>
                        <span class="kbd-hint">Tab</span> to navigate fields,
                        <span class="kbd-hint">Ctrl+Enter</span> to save
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Transaction Modal --}}
<div class="modal fade" id="editTxModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Transaction</h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="editTxForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Category</label>
                        <select class="form-control" name="category" id="edit_category" required>
                            <option value="tithe">Tithe</option>
                            <option value="offering">Offering</option>
                            <option value="special_offering">Special Offering</option>
                            <option value="building_fund">Building Fund</option>
                            <option value="pledge">Pledge Payment</option>
                            <option value="other_income">Other Income</option>
                            <option value="ministry_expense">Ministry Expense</option>
                            <option value="administrative">Administrative</option>
                            <option value="utilities">Utilities</option>
                            <option value="salary">Salary / Stipend</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="missions">Missions / Outreach</option>
                            <option value="other_expense">Other Expense</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Amount</label>
                                <input type="number" step="0.01" class="form-control" name="amount" id="edit_amount" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select class="form-control" name="payment_method" id="edit_payment_method" required>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" class="form-control" name="transaction_date" id="edit_transaction_date" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Account</label>
                                <select class="form-control" name="account_id" id="edit_account_id">
                                    <option value="">— None —</option>
                                    @foreach($accounts as $acct)
                                        <option value="{{ $acct->id }}">{{ $acct->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" class="form-control" name="description" id="edit_description" maxlength="500">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" id="edit_notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Account Modal --}}
<div class="modal fade" id="accountModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accountModalLabel"><i class="fas fa-landmark mr-1"></i> New Account</h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="accountForm" method="POST" action="{{ route('admin.financials.account.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-8">
                            <div class="form-group">
                                <label>Account Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label>Type *</label>
                                <select class="form-control" name="type" required>
                                    <option value="bank">Bank</option>
                                    <option value="cash">Cash Till</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Account Number</label>
                        <input type="text" class="form-control" name="account_number">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Bank Name</label>
                                <input type="text" class="form-control" name="bank_name">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Designate</label>
                                <input type="text" class="form-control" name="branch">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Opening Balance</label>
                        <input type="number" step="0.01" class="form-control" name="opening_balance" value="0">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Fund Modal --}}
<div class="modal fade" id="fundModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fundModalLabel"><i class="fas fa-piggy-bank mr-1"></i> New Fund</h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <form method="POST" action="{{ route('admin.financials.fund.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Fund Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Target Amount (optional)</label>
                        <input type="number" step="0.01" class="form-control" name="target_amount" placeholder="Leave empty for no target">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Fund</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Campaign Modal --}}
<div class="modal fade" id="campaignModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="campaignModalLabel"><i class="fas fa-bullhorn mr-1"></i> New Campaign</h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <form method="POST" action="{{ route('admin.financials.campaign.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Campaign Title *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Target Amount *</label>
                        <input type="number" step="0.01" class="form-control" name="target_amount" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Start Date *</label>
                                <input type="date" class="form-control" name="start_date" value="{{ now()->toDateString() }}" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" class="form-control" name="end_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Pledge Modal --}}
<div class="modal fade" id="pledgeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pledgeModalLabel"><i class="fas fa-handshake mr-1"></i> New Pledge</h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <form method="POST" action="{{ route('admin.financials.pledge.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Member</label>
                        <select class="form-control select2-member" name="member_id">
                            <option value="">— None —</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Campaign</label>
                        <select class="form-control" name="campaign_id">
                            <option value="">— None —</option>
                            @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}">{{ $campaign->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Pledge Amount *</label>
                                <input type="number" step="0.01" class="form-control" name="pledge_amount" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Initial Payment</label>
                                <input type="number" step="0.01" class="form-control" name="amount_paid" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Pledge Date *</label>
                        <input type="date" class="form-control" name="pledge_date" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Pledge</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Campaign Modal --}}
<div class="modal fade" id="editCampaignModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Campaign</h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="editCampaignForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" class="form-control" name="title" id="ec_title" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" id="ec_description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Target Amount *</label>
                        <input type="number" step="0.01" class="form-control" name="target_amount" id="ec_target_amount" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Start Date *</label>
                                <input type="date" class="form-control" name="start_date" id="ec_start_date" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" class="form-control" name="end_date" id="ec_end_date">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status" id="ec_status">
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Pledge Modal --}}
<div class="modal fade" id="editPledgeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Pledge</h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="editPledgeForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Member</label>
                        <select class="form-control select2-member" name="member_id" id="ep_member_id">
                            <option value="">— None —</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Campaign</label>
                        <select class="form-control" name="campaign_id" id="ep_campaign_id">
                            <option value="">— None —</option>
                            @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}">{{ $campaign->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Pledge Amount *</label>
                        <input type="number" step="0.01" class="form-control" name="pledge_amount" id="ep_pledge_amount" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status" id="ep_status">
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" id="ep_notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Pledge</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Account Modal --}}
<div class="modal fade" id="editAccountModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Account</h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="editAccountForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-8">
                            <div class="form-group">
                                <label>Account Name *</label>
                                <input type="text" class="form-control" name="name" id="ea_name" required>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label>Type *</label>
                                <select class="form-control" name="type" id="ea_type" required>
                                    <option value="bank">Bank</option>
                                    <option value="cash">Cash Till</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Account Number</label>
                        <input type="text" class="form-control" name="account_number" id="ea_account_number">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Bank Name</label>
                                <input type="text" class="form-control" name="bank_name" id="ea_bank_name">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Designate</label>
                                <input type="text" class="form-control" name="branch" id="ea_branch">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" id="ea_notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Fund Modal --}}
<div class="modal fade" id="editFundModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit mr-1"></i> Edit Fund</h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="editFundForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Fund Name *</label>
                        <input type="text" class="form-control" name="name" id="ef_name" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" id="ef_description" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Target Amount</label>
                        <input type="number" step="0.01" class="form-control" name="target_amount" id="ef_target_amount">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Fund</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {

    // ── Toggle tab buttons based on active tab ──
    $('#financialsTab').on('shown.bs.tab', function (e) {
        var targetId = $(e.target).attr('href').replace('#', '');
        $('.tab-btn').addClass('d-none');
        $('.tab-btn[data-tab="' + targetId + '"]').removeClass('d-none');
    });

    // Apply progress bar widths from data-progress attributes
    $('.progress-bar[data-progress]').each(function() {
        $(this).css('width', $(this).data('progress') + '%');
    });

    // Base URL for dynamic AJAX / form routes (no Blade route() with missing params)
    var financialsBase = "{{ url('admin/financials') }}";

    // ════════════════════════════════════════════
    //  DATATABLES – Table sorting & search
    //  (init on tab shown to avoid hidden-table issues)
    // ════════════════════════════════════════════
    if ($.fn.DataTable) {
        var dtOpts = {
            pageLength: 25,
            order: [[0, "desc"]],
            language: { emptyTable: "No data available" },
            destroy: true
        };

        function initDt(id) {
            // destroy: true ensures a clean init every time, even if
            // DataTables was partially initialized while the tab was hidden
            // or if an @@empty colspan row confused the parser.
            $('#' + id).DataTable(dtOpts);
        }

        // Register tab handler BEFORE deep linking so hash-based activation is caught
        $('#financialsTab').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr('href');
            if (target === '#inflows')  initDt('inflowsTable');
            if (target === '#outflows') initDt('outflowsTable');
            if (target === '#pledges')  initDt('pledgesTable');
        });

        // Inflows tab is visible by default — init now
        initDt('inflowsTable');
    }

    // ════════════════════════════════════════════
    //  DEEP LINKING – Activate tab from URL hash or ?tab= query param
    //  (runs after DataTable handler is registered above)
    // ════════════════════════════════════════════
    function activateTabFromHash() {
        var hash = window.location.hash;
        if (!hash) {
            // Check for ?tab= query param (redirect support)
            var params = new URLSearchParams(window.location.search);
            var tabParam = params.get('tab');
            if (tabParam) {
                // Map legacy tab names to actual hash anchors
                var tabMap = { funds: 'accounts', transactions: 'inflows', campaigns: 'pledges' };
                hash = '#' + (tabMap[tabParam] || tabParam);
            }
        }
        if (hash) {
            var tab = document.querySelector('#financialsTab .nav-link[href="' + hash + '"]');
            if (tab) {
                $(tab).tab('show');
            }
        }
    }
    activateTabFromHash();

    // Update URL hash when tab changes
    $('#financialsTab .nav-link').on('shown.bs.tab', function(e) {
        history.replaceState(null, null, $(this).attr('href'));
    });

    // ════════════════════════════════════════════
    //  QUICK ENTRY – Type toggle
    // ════════════════════════════════════════════
    $('#qe_type_select').on('change', function() {
        var val = $(this).val();
        $('#qe_type').val(val);
        if (val === 'outflow') {
            $('#qe_outflow_categories').show();
            $('#qe_category optgroup[label="Inflow Categories"] option').hide();
            $('#qe_category optgroup[label="Outflow Categories"] option').show();
            $('#qe_category').val('ministry_expense');
        } else {
            $('#qe_outflow_categories').hide();
            $('#qe_category optgroup[label="Inflow Categories"] option').show();
            $('#qe_category optgroup[label="Outflow Categories"] option').hide();
            $('#qe_category').val('offering');
        }
    }).trigger('change');

    // ════════════════════════════════════════════
    //  KEYBOARD SHORTCUTS
    // ════════════════════════════════════════════
    $(document).on('keydown', function(e) {
        // Ctrl+Shift+N → Open Quick Entry
        if (e.ctrlKey && e.shiftKey && (e.which === 78 || e.key === 'N')) {
            e.preventDefault();
            $('#quickEntryModal').modal('show');
            setTimeout(function() { $('#quickEntryModal input[name="amount"]').focus(); }, 500);
        }
        // Ctrl+Enter → Submit quick entry
        if (e.ctrlKey && (e.which === 13 || e.key === 'Enter')) {
            var modal = $('#quickEntryModal');
            if (modal.is(':visible')) {
                e.preventDefault();
                $('#quickEntryForm').submit();
            }
        }
        // Escape → close modals
        if (e.which === 27) {
            $('.modal.show').modal('hide');
        }
    });

    // ════════════════════════════════════════════
    //  SELECT2 INIT
    // ════════════════════════════════════════════
    $('.select2-member').select2({
        placeholder: 'Search member...',
        allowClear: true,
        width: '100%',
        dropdownParent: function() { return $(this).closest('.modal'); }
    });

    // ════════════════════════════════════════════
    //  TRANSACTION ACTIONS (Edit / Delete)
    // ════════════════════════════════════════════
    $(document).on('click', '.edit-tx', function() {
        var btn = $(this);
        $('#edit_category').val(btn.data('category'));
        $('#edit_amount').val(btn.data('amount'));
        $('#edit_payment_method').val(btn.data('payment_method'));
        $('#edit_transaction_date').val(btn.data('transaction_date'));
        $('#edit_account_id').val(btn.data('account_id') || '');
        $('#edit_description').val(btn.data('description') || '');
        $('#edit_notes').val(btn.data('notes') || '');
        $('#editTxForm').attr('action', financialsBase + '/transaction/' + btn.data('id'));
        $('#editTxModal').modal('show');
    });

    // ════════════════════════════════════════════
    //  APPROVAL ACTIONS
    // ════════════════════════════════════════════
    $(document).on('click', '.approve-tx', function() {
        var btn = $(this);
        var id = btn.data('id');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.post(financialsBase + '/transaction/' + id + '/approve', {
            _token: '{{ csrf_token() }}'
        }).done(function(res) {
            if (res.success) location.reload();
        }).fail(function() {
            btn.prop('disabled', false).html('<i class="fas fa-check"></i> Approve');
            alert('Error approving transaction.');
        });
    });

    $(document).on('click', '.reject-tx', function() {
        var id = $(this).data('id');
        var reason = prompt('Enter rejection reason (optional):');
        $.post(financialsBase + '/transaction/' + id + '/reject', {
            _token: '{{ csrf_token() }}',
            notes: reason || ''
        }).done(function(res) {
            if (res.success) location.reload();
        }).fail(function() {
            alert('Error rejecting transaction.');
        });
    });

    // ════════════════════════════════════════════
    //  CAMPAIGN EDIT
    // ════════════════════════════════════════════
    $(document).on('click', '.edit-campaign', function() {
        var btn = $(this);
        $('#ec_title').val(btn.data('title'));
        $('#ec_description').val(btn.data('description'));
        $('#ec_target_amount').val(btn.data('target_amount'));
        $('#ec_start_date').val(btn.data('start_date'));
        $('#ec_end_date').val(btn.data('end_date') || '');
        $('#ec_status').val(btn.data('status'));
        $('#editCampaignForm').attr('action', financialsBase + '/campaign/' + btn.data('id'));
        $('#editCampaignModal').modal('show');
    });

    // ════════════════════════════════════════════
    //  PLEDGE EDIT
    // ════════════════════════════════════════════
    $(document).on('click', '.edit-pledge', function() {
        var btn = $(this);
        $('#ep_member_id').val(btn.data('member_id') || '').trigger('change');
        $('#ep_campaign_id').val(btn.data('campaign_id') || '');
        $('#ep_pledge_amount').val(btn.data('pledge_amount'));
        $('#ep_status').val(btn.data('status'));
        $('#ep_notes').val(btn.data('notes') || '');
        $('#editPledgeForm').attr('action', financialsBase + '/pledge/' + btn.data('id'));
        $('#editPledgeModal').modal('show');
    });

    // ════════════════════════════════════════════
    //  ACCOUNT EDIT
    // ════════════════════════════════════════════
    $(document).on('click', '.edit-account', function() {
        var btn = $(this);
        $('#ea_name').val(btn.data('name'));
        $('#ea_type').val(btn.data('type'));
        $('#ea_account_number').val(btn.data('account_number') || '');
        $('#ea_bank_name').val(btn.data('bank_name') || '');
        $('#ea_branch').val(btn.data('branch') || '');
        $('#ea_notes').val(btn.data('notes') || '');
        $('#editAccountForm').attr('action', financialsBase + '/account/' + btn.data('id'));
        $('#editAccountModal').modal('show');
    });

    // ════════════════════════════════════════════
    //  FUND EDIT
    // ════════════════════════════════════════════
    $(document).on('click', '.edit-fund', function() {
        var btn = $(this);
        $('#ef_name').val(btn.data('name'));
        $('#ef_description').val(btn.data('description') || '');
        $('#ef_target_amount').val(btn.data('target_amount') || '');
        $('#editFundForm').attr('action', financialsBase + '/fund/' + btn.data('id'));
        $('#editFundModal').modal('show');
    });

});
</script>
@endsection
