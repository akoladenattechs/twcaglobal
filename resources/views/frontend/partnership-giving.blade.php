@extends('layouts.app')

@section('content')
{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- HERO SECTION                                                    --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<section class="hero-wrap hero-wrap-2 js-fullheight" @if(!empty($headerBgUrl)) style="background-image: url('{{ $headerBgUrl }}');" @endif>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <p class="breadcrumbs mb-2">
                    <span class="mr-2"><a href="{{ url('/') }}">Home <i class="fa fa-chevron-right"></i></a></span>
                    <span>Partnership &amp; Giving <i class="fa fa-chevron-right"></i></span>
                </p>
                <h1 class="mb-0 bread">Partnership &amp; Giving</h1>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- INTRO SECTION                                                   --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center mb-4">
            <div class="col-md-8 text-center heading-section ftco-animate">
                <h2 class="mb-3">Give &amp; Partner With Us</h2>
                <p class="lead text-muted">
                    Your generous partnership helps us reach more lives with the gospel,
                    support community initiatives, and expand our ministry impact.
                    Every gift — no matter the size — makes an eternal difference.
                </p>
            </div>
        </div>

        <div class="row mt-5">
            {{-- ── Bank Transfer Card (flip) ── --}}
            <div class="col-md-4 mb-4 ftco-animate">
                <div class="flip-card" id="bankFlipCard">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <div class="giving-option-card">
                                <div class="giving-option-icon">
                                    <i class="fa fa-university"></i>
                                </div>
                                <h4 class="giving-option-title">Bank Transfer</h4>
                                <p class="giving-option-text">Transfer directly to our church bank account. <a href="#" class="bank-details-link" onclick="event.preventDefault();">Click here</a> to see our banking details.</p>
                            </div>
                        </div>
                        <div class="flip-card-back">
                            <div class="flip-card-back-content">
                                @if($bankAccounts->isNotEmpty())
                                    @foreach($bankAccounts as $account)
                                        <div class="bank-detail-item">
                                            <div class="bank-detail-icon">
                                                <i class="fa fa-bank"></i>
                                            </div>
                                            <h4 class="bank-detail-title">{{ $account->bank_name ?? $account->name }}</h4>
                                            @if($account->name && $account->name !== $account->bank_name)
                                                <div class="bank-detail-line">
                                                    <span class="bank-detail-label">Account Name:</span>
                                                    <span class="bank-detail-value">{{ $account->name }}</span>
                                                </div>
                                            @endif
                                            @if($account->account_number)
                                                <div class="bank-detail-line bank-detail-line-number">
                                                    <span class="bank-detail-label">Account No:</span>
                                                    <span class="bank-detail-value">
                                                        <span class="account-number-text">{{ $account->account_number }}</span>
                                                        <button type="button" class="btn-copy-account" data-account="{{ $account->account_number }}" title="Copy account number">
                                                            <i class="fa fa-copy"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            @endif
                                            @if($account->branch)
                                                <div class="bank-detail-line">
                                                    <span class="bank-detail-label">Designate:</span>
                                                    <span class="bank-detail-value">{{ $account->branch }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-4">
                                        <i class="fa fa-info-circle fa-2x text-muted mb-3"></i>
                                        <p class="text-muted">No banking details are currently available. Please contact the church office for assistance.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Online Giving Card (plain, matching In-Person) ── --}}
            <div class="col-md-4 mb-4 ftco-animate">
                <div class="giving-option-card">
                    <div class="giving-option-icon">
                        <i class="fa fa-credit-card"></i>
                    </div>
                    <h4 class="giving-option-title">Online Giving</h4>
                    <p class="giving-option-text">Give securely online via Card, Bank Transfer, or USSD. <a href="#" class="open-giving-modal bank-details-link">Click here</a> to give online now.</p>
                </div>
            </div>

            {{-- ── In-Person Giving Card (plain) ── --}}
            <div class="col-md-4 mb-4 ftco-animate">
                <div class="giving-option-card">
                    <div class="giving-option-icon">
                        <i class="fa fa-envelope"></i>
                    </div>
                    <h4 class="giving-option-title">In-Person Giving</h4>
                    <p class="giving-option-text">You can give during our weekly services. Visit our <a href="{{ url('/contact') }}">Contact page</a> for service schedules.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- DESIGNATED FUNDS SECTION                                        --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
@if($funds->isNotEmpty())
<section class="ftco-section bg-light ftco-no-pt">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-7 text-center heading-section ftco-animate">
                <span class="subheading">Designated Funds</span>
                <h2 class="mb-3">Where Your Gift Goes</h2>
                <p class="text-muted">Select a specific fund to support, or give generally below.</p>
            </div>
        </div>

        <div class="row">
            @foreach($funds as $fund)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="fund-card">
                        <div class="fund-card-body">
                            <div class="fund-icon-wrap">
                                <i class="fa fa-hand-holding-heart"></i>
                            </div>
                            <h5 class="fund-card-title">{{ $fund->name }}</h5>
                            @if($fund->description)
                                <p class="fund-card-description">{{ $fund->description }}</p>
                            @endif
                            <div class="mt-3">
                                <button class="btn btn-give-now select-fund" data-fund-id="{{ $fund->id }}" data-fund-name="{{ $fund->name }}">
                                    <i class="fa fa-gift mr-1"></i> Give to this Fund
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- ONLINE GIVING MODAL                                             --}}
{{-- Rendered via @push('modals') — lives as direct <body> child    --}}
{{-- so no CSS stacking-context parent can interfere with z-index.  --}}
{{-- Styles → public/css/style.css                                  --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
@push('modals')
<div class="modal fade" id="onlineGivingModal" tabindex="-1" role="dialog"
     aria-labelledby="onlineGivingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0" id="onlineGivingModalLabel">
                    <i class="fa fa-heart mr-2"></i> Online Giving
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="onlineGivingForm">
                @csrf
                <div class="modal-body">
                    {{-- Quick Amount Pills --}}
                    <label>Select Amount</label>
                    <div class="giving-pill-group">
                        <button type="button" class="giving-pill amount-pill" data-amount="1000">{{ $siteSettings['currency_symbol'] ?? '₦' }}1,000</button>
                        <button type="button" class="giving-pill amount-pill" data-amount="5000">{{ $siteSettings['currency_symbol'] ?? '₦' }}5,000</button>
                        <button type="button" class="giving-pill amount-pill active" data-amount="10000">{{ $siteSettings['currency_symbol'] ?? '₦' }}10,000</button>
                        <button type="button" class="giving-pill amount-pill" data-amount="50000">{{ $siteSettings['currency_symbol'] ?? '₦' }}50,000</button>
                    </div>

                    <div class="form-group mb-3">
                        <label for="giving_amount">Custom Amount ({{ $siteSettings['currency_symbol'] ?? '₦' }})</label>
                        <input type="number" class="form-control form-control-lg font-weight-bold text-dark" id="giving_amount" name="amount" value="10000" min="1" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="giving_category">Giving Category / Fund</label>
                        <select class="form-control" id="giving_category" name="category" required>
                            <option value="offering">General Offering</option>
                            <option value="tithe">Tithe</option>
                            <option value="thanksgiving">Thanksgiving</option>
                            <option value="partnership">Partnership Contribution</option>
                            @foreach($funds as $fund)
                                <option value="designated_fund" data-fund-id="{{ $fund->id }}">{{ $fund->name }} (Designated Fund)</option>
                            @endforeach
                        </select>
                        <input type="hidden" id="giving_fund_id" name="fund_id" value="">
                    </div>

                    <div class="form-group mb-3">
                        <label for="giving_name">Full Name</label>
                        <input type="text" class="form-control" id="giving_name" name="name" placeholder="John Doe" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="giving_email">Email Address</label>
                        <input type="email" class="form-control" id="giving_email" name="email" placeholder="john@example.com" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="giving_phone">Phone Number <span class="text-muted font-weight-normal">(Optional)</span></label>
                        <input type="text" class="form-control" id="giving_phone" name="phone" placeholder="+234...">
                    </div>

                    <div id="givingAlert" class="alert alert-danger d-none" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel-giving" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-submit-giving" id="btnSubmitGiving">
                        <i class="fa fa-lock mr-1"></i> Proceed to Pay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
{{-- Payment Gateway SDKs — allowed by CSP in SecurityHeaders middleware --}}
<script src="https://js.paystack.co/v1/inline.js"></script>
<script src="https://checkout.flutterwave.com/v3.js"></script>
<script src="https://js.stripe.com/v3/"></script>
<script>
$(function() {
    // ── Flip card: Bank Transfer ──
    $('#bankFlipCard').on('click', function(e) {
        if ($(e.target).closest('.btn-copy-account').length) return;
        $(this).find('.flip-card-inner').toggleClass('flipped');
    });

    // ── Copy account number ──
    $(document).on('click', '.btn-copy-account', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn    = $(this);
        var account = $btn.data('account');
        function fallbackCopy() {
            var $inp = $('<input>').val(account).appendTo('body').select();
            try { document.execCommand('copy'); } catch(ex) {}
            $inp.remove();
        }
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(account)
                .then(function() { $btn.addClass('copied'); setTimeout(function(){ $btn.removeClass('copied'); }, 2000); })
                .catch(function() { fallbackCopy(); $btn.addClass('copied'); setTimeout(function(){ $btn.removeClass('copied'); }, 2000); });
        } else {
            fallbackCopy();
            $btn.addClass('copied');
            setTimeout(function(){ $btn.removeClass('copied'); }, 2000);
        }
    });

    // ── Open Giving Modal ──
    // Use JS options (backdrop:'static', keyboard:false) — not data-* attributes —
    // to avoid conflicts with Bootstrap's internal modal initialisation.
    $(document).on('click', '.open-giving-modal', function(e) {
        e.preventDefault();
        $('#onlineGivingModal').modal({ backdrop: 'static', keyboard: false, show: true });
    });

    // ── Give to specific Fund (from fund cards below) ──
    $(document).on('click', '.select-fund', function(e) {
        e.preventDefault();
        var fundId   = $(this).data('fund-id');
        $('#giving_category').val('designated_fund');
        $('#giving_fund_id').val(fundId);
        $('#onlineGivingModal').modal({ backdrop: 'static', keyboard: false, show: true });
    });

    // ── Sync fund_id when dropdown changes ──
    $(document).on('change', '#giving_category', function() {
        var $opt = $(this).find(':selected');
        $('#giving_fund_id').val($opt.val() === 'designated_fund' ? ($opt.data('fund-id') || '') : '');
    });

    // ── Amount pills ──
    $(document).on('click', '.amount-pill', function() {
        $('.amount-pill').removeClass('active');
        $(this).addClass('active');
        $('#giving_amount').val($(this).data('amount'));
    });

    // ── Payment Submission ──
    $(document).on('submit', '#onlineGivingForm', function(e) {
        e.preventDefault();
        var $btn   = $('#btnSubmitGiving');
        var $alert = $('#givingAlert');
        $alert.addClass('d-none').text('');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Preparing...');

        var formData = {
            _token:   '{{ csrf_token() }}',
            amount:   $('#giving_amount').val(),
            category: $('#giving_category').val(),
            fund_id:  $('#giving_fund_id').val(),
            name:     $('#giving_name').val(),
            email:    $('#giving_email').val(),
            phone:    $('#giving_phone').val()
        };

        $.ajax({
            url:  '{{ route("payment.initialize") }}',
            type: 'POST',
            data: formData,
            success: function(res) {
                if (!res.success) {
                    $alert.removeClass('d-none').text(res.message || 'Payment initialization failed.');
                    $btn.prop('disabled', false).html('<i class="fa fa-lock mr-1"></i> Proceed to Pay');
                    return;
                }
                var data     = res.data;
                var provider = data.provider;

                // Paystack
                if (provider === 'paystack') {
                    if (typeof PaystackPop === 'undefined' || !data.key) {
                        $alert.removeClass('d-none').text('Online giving is temporarily unavailable. Please try again later or use Bank Transfer.');
                        $btn.prop('disabled', false).html('<i class="fa fa-lock mr-1"></i> Proceed to Pay');
                        return;
                    }
                    PaystackPop.setup({
                        key:      data.key,
                        email:    data.email,
                        amount:   data.amount_kobo,
                        currency: data.currency,
                        ref:      data.reference,
                        callback: function(response) {
                            verifyGiving(data, response.reference || data.reference, 'paystack');
                        },
                        onClose: function() {
                            $btn.prop('disabled', false).html('<i class="fa fa-lock mr-1"></i> Proceed to Pay');
                        }
                    }).openIframe();
                }
                // Flutterwave
                else if (provider === 'flutterwave') {
                    if (typeof FlutterwaveCheckout === 'undefined' || !data.key) {
                        $alert.removeClass('d-none').text('Online giving is temporarily unavailable. Please try again later or use Bank Transfer.');
                        $btn.prop('disabled', false).html('<i class="fa fa-lock mr-1"></i> Proceed to Pay');
                        return;
                    }
                    FlutterwaveCheckout({
                        public_key: data.key,
                        tx_ref:     data.reference,
                        amount:     data.amount,
                        currency:   data.currency,
                        customer:   { email: data.email, name: data.name, phone_number: data.phone || '' },
                        callback:   function(response) { verifyGiving(data, data.reference, 'flutterwave', response.transaction_id); },
                        onclose:    function() { $btn.prop('disabled', false).html('<i class="fa fa-lock mr-1"></i> Proceed to Pay'); }
                    });
                }
                // Stripe
                else if (provider === 'stripe') {
                    if (typeof Stripe === 'undefined' || !data.key || !data.client_secret) {
                        $alert.removeClass('d-none').text('Online giving is temporarily unavailable. Please try again later or use Bank Transfer.');
                        $btn.prop('disabled', false).html('<i class="fa fa-lock mr-1"></i> Proceed to Pay');
                        return;
                    }
                    var stripe = Stripe(data.key);
                    stripe.confirmCardPayment(data.client_secret, {
                        payment_method: {
                            billing_details: { name: data.name, email: data.email }
                        }
                    }).then(function(result) {
                        if (result.error) {
                            $alert.removeClass('d-none').text(result.error.message);
                            $btn.prop('disabled', false).html('<i class="fa fa-lock mr-1"></i> Proceed to Pay');
                        } else if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                            verifyGiving(data, data.reference, 'stripe', result.paymentIntent.id);
                        }
                    });
                }
                // Unknown provider
                else {
                    $alert.removeClass('d-none').text('Online giving is temporarily unavailable. Please try again later or use Bank Transfer.');
                    $btn.prop('disabled', false).html('<i class="fa fa-lock mr-1"></i> Proceed to Pay');
                }
            },
            error: function(xhr) {
                var err = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred. Please try again.';
                $alert.removeClass('d-none').text(err);
                $btn.prop('disabled', false).html('<i class="fa fa-lock mr-1"></i> Proceed to Pay');
            }
        });
    });

    function verifyGiving(initData, ref, provider, txId) {
        var $btn   = $('#btnSubmitGiving');
        var $alert = $('#givingAlert');
        $btn.html('<i class="fa fa-spinner fa-spin mr-1"></i> Verifying...');
        $.ajax({
            url:  '{{ route("payment.verify") }}',
            type: 'POST',
            data: {
                _token:         '{{ csrf_token() }}',
                reference:      ref,
                provider:       provider,
                amount:         initData.amount,
                email:          initData.email,
                name:           initData.name,
                category:       initData.category,
                fund_id:        initData.fund_id,
                transaction_id: txId || ''
            },
            success: function(res) {
                $('#onlineGivingModal').modal('hide');
                $btn.prop('disabled', false).html('<i class="fa fa-lock mr-1"></i> Proceed to Pay');
                alert(res.message || 'Thank you for your generous gift!');
                location.reload();
            },
            error: function(xhr) {
                var err = xhr.responseJSON ? xhr.responseJSON.message : 'Verification failed. Please contact support.';
                $alert.removeClass('d-none').text(err);
                $btn.prop('disabled', false).html('<i class="fa fa-lock mr-1"></i> Proceed to Pay');
            }
        });
    }
});
</script>
@endpush
