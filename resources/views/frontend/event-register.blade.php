@extends('layouts.app')

@section('content')
<section class="hero-wrap hero-wrap-2 js-fullheight" @style(['background-image: url(' . asset('admin/bg_1.jpg') . ')'])>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <p class="breadcrumbs mb-2">
                    <span class="mr-2"><a href="{{ url('/') }}">Home <i class="fa fa-chevron-right"></i></a></span>
                    <span class="mr-2"><a href="{{ route('events.show', $event->slug) }}">{{ $event->title }} <i class="fa fa-chevron-right"></i></a></span>
                    <span>Register</span>
                </p>
                <h1 class="mb-0 bread">Event Registration</h1>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-8 text-center heading-section ftco-animate">
                <h2 class="mb-3">Event Registration</h2>
                <p class="lead text-muted">
                    Please fill out the form below to secure your spot at <strong>{{ $event->title }}</strong>.
                </p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="wrapper">
                    <div class="row no-gutters">
                        <div class="col-md-12">
                            <div class="contact-wrap w-100 p-md-5 p-4">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px;">
                                        <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
                                        <i class="fa fa-exclamation-circle mr-2"></i> {{ session('error') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif

                                @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
                                        <i class="fa fa-exclamation-triangle mr-2"></i> Please correct the errors below and try again.
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <ul class="mb-0 mt-2">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('events.register.store', $event->slug) }}" class="contactForm" id="eventRegisterForm">
                                    @csrf

                                    <h3 class="mb-4" style="color: #fff;">{{ $event->title }}</h3>

                                    <div class="row mb-4">
                                        @if(!empty($event->next_date))
                                        <div class="col-md-6 mb-2">
                                            <p class="mb-1" style="color: rgba(255,255,255,0.9);">
                                                <i class="fa fa-calendar mr-2"></i>
                                                {{ date('l, F j, Y', strtotime($event->next_date)) }} at {{ date('g:i A', strtotime($event->next_date)) }}
                                            </p>
                                        </div>
                                        @endif
                                        @if(!empty($event->location))
                                        <div class="col-md-6 mb-2">
                                            <p class="mb-1" style="color: rgba(255,255,255,0.9);">
                                                <i class="fa fa-map-marker mr-2"></i>{{ $event->location }}
                                            </p>
                                        </div>
                                        @endif
                                    </div>

                                    <h4 class="mb-3" style="color: #fff; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 8px;"><i class="fa fa-user mr-2"></i> Personal Information</h4>

                                    <!-- Are you a member? -->
                                    <div class="form-group">
                                        <label class="label" for="is_member">Are you a member?</label>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input member-radio" id="member_yes" name="is_member" value="1"{{ old('is_member') === '1' ? ' checked' : '' }}>
                                            <label class="custom-control-label" for="member_yes">Yes</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input member-radio" id="member_no" name="is_member" value="0"{{ old('is_member') === '0' ? ' checked' : '' }}>
                                            <label class="custom-control-label" for="member_no">No</label>
                                        </div>
                                    </div>

                                    <!-- Member lookup (shown when "Yes") -->
                                    <div class="form-group" id="memberLookupWrap" style="display:none;">
                                        <label class="label" for="member_email_lookup">Member Email Address</label>
                                        <div class="input-group">
                                            <input type="email" class="form-control" id="member_email_lookup" placeholder="Enter your registered email">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="lookupMemberBtn">
                                                    <i class="fa fa-search mr-1"></i>Look Up
                                                </button>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted" id="memberLookupMsg" style="color: rgba(255,255,255,0.7) !important;"></small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="label" for="first_name">First Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" id="first_name" placeholder="First Name" value="{{ old('first_name') }}" required>
                                                @error('first_name')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="label" for="last_name">Last Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" id="last_name" placeholder="Last Name" value="{{ old('last_name') }}" required>
                                                @error('last_name')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="label" for="email">Email Address <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" placeholder="your@email.com" value="{{ old('email') }}" required>
                                                @error('email')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="label" for="phone">Phone Number</label>
                                                <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" placeholder="Phone Number" value="{{ old('phone') }}">
                                                @error('phone')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <h4 class="mb-3 mt-5" style="color: #fff; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 8px;"><i class="fa fa-map-marker mr-2"></i> Address Information</h4>

                                    <div class="form-group">
                                        <label class="label" for="address">Street Address</label>
                                        <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" id="address" placeholder="Your residential address" value="{{ old('address') }}">
                                        @error('address')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="label" for="city">City</label>
                                                <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" id="city" placeholder="City" value="{{ old('city') }}">
                                                @error('city')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="label" for="state">State / Province</label>
                                                <input type="text" class="form-control @error('state') is-invalid @enderror" name="state" id="state" placeholder="State" value="{{ old('state') }}">
                                                @error('state')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="label" for="country">Country</label>
                                                <select class="form-control @error('country') is-invalid @enderror" name="country" id="country">
                                                    <option value="">Select Country</option>
                                                    @foreach($countries as $country)
                                                        <option value="{{ $country }}"{{ old('country') === $country ? ' selected' : '' }}>{{ $country }}</option>
                                                    @endforeach
                                                </select>
                                                @error('country')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Church (for non-members) -->
                                    <div class="form-group" id="churchWrap" style="display:none;">
                                        <label class="label" for="church">Church</label>
                                        <input type="text" class="form-control" id="church" name="church" value="{{ old('church') }}" placeholder="Your church / ministry name">
                                    </div>

                                    <!-- First time at this event? -->
                                    <div class="form-group">
                                        <label class="label">Is this your first time at {{ $event->title }}?</label>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input" id="first_yes" name="is_first_time" value="1"{{ old('is_first_time') === '1' ? ' checked' : '' }}>
                                            <label class="custom-control-label" for="first_yes">Yes</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input" id="first_no" name="is_first_time" value="0"{{ old('is_first_time') === '0' ? ' checked' : '' }}>
                                            <label class="custom-control-label" for="first_no">No</label>
                                        </div>
                                    </div>

                                    <!-- Admin-defined dynamic fields -->
                                    @if($event->registrationFields->isNotEmpty())
                                        <h4 class="mb-3 mt-5" style="color: #fff; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 8px;"><i class="fa fa-clipboard-list mr-2"></i> Additional Information</h4>
                                        @foreach($event->registrationFields as $field)
                                            <div class="form-group">
                                                <label class="label" for="custom_{{ $field->id }}">
                                                    {{ $field->label }}
                                                    @if($field->is_required)<span class="text-danger">*</span>@endif
                                                </label>

                                                @if($field->field_type === 'textarea')
                                                    <textarea class="form-control" id="custom_{{ $field->id }}" name="custom_{{ $field->id }}" rows="3" placeholder="{{ $field->label }}"{{ $field->is_required ? ' required' : '' }}>{{ old('custom_'.$field->id) }}</textarea>
                                                @elseif($field->field_type === 'select')
                                                    <select class="form-control" id="custom_{{ $field->id }}" name="custom_{{ $field->id }}"{{ $field->is_required ? ' required' : '' }}>
                                                        <option value="">Select...</option>
                                                        @foreach($field->options ?? [] as $option)
                                                            <option value="{{ $option }}"{{ old('custom_'.$field->id) === $option ? ' selected' : '' }}>{{ $option }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif($field->field_type === 'radio')
                                                    @foreach($field->options ?? [] as $option)
                                                        <div class="custom-control custom-radio">
                                                            <input type="radio" class="custom-control-input" id="custom_{{ $field->id }}_{{ $loop->index }}" name="custom_{{ $field->id }}" value="{{ $option }}"{{ old('custom_'.$field->id) === $option ? ' checked' : '' }}{{ $field->is_required ? ' required' : '' }}>
                                                            <label class="custom-control-label" for="custom_{{ $field->id }}_{{ $loop->index }}">{{ $option }}</label>
                                                        </div>
                                                    @endforeach
                                                @elseif($field->field_type === 'checkbox')
                                                    @foreach($field->options ?? [] as $option)
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" id="custom_{{ $field->id }}_{{ $loop->index }}" name="custom_{{ $field->id }}[]" value="{{ $option }}"{{ in_array($option, (array) old('custom_'.$field->id, [])) ? ' checked' : '' }}>
                                                            <label class="custom-control-label" for="custom_{{ $field->id }}_{{ $loop->index }}">{{ $option }}</label>
                                                        </div>
                                                    @endforeach
                                                @elseif($field->field_type === 'email')
                                                    <input type="email" class="form-control" id="custom_{{ $field->id }}" name="custom_{{ $field->id }}" placeholder="{{ $field->label }}" value="{{ old('custom_'.$field->id) }}"{{ $field->is_required ? ' required' : '' }}>
                                                @elseif($field->field_type === 'phone')
                                                    <input type="tel" class="form-control" id="custom_{{ $field->id }}" name="custom_{{ $field->id }}" placeholder="{{ $field->label }}" value="{{ old('custom_'.$field->id) }}"{{ $field->is_required ? ' required' : '' }}>
                                                @elseif($field->field_type === 'number')
                                                    <input type="number" class="form-control" id="custom_{{ $field->id }}" name="custom_{{ $field->id }}" placeholder="{{ $field->label }}" value="{{ old('custom_'.$field->id) }}"{{ $field->is_required ? ' required' : '' }}>
                                                @elseif($field->field_type === 'date')
                                                    <input type="date" class="form-control" id="custom_{{ $field->id }}" name="custom_{{ $field->id }}" value="{{ old('custom_'.$field->id) }}"{{ $field->is_required ? ' required' : '' }}>
                                                @else
                                                    <input type="text" class="form-control" id="custom_{{ $field->id }}" name="custom_{{ $field->id }}" placeholder="{{ $field->label }}" value="{{ old('custom_'.$field->id) }}"{{ $field->is_required ? ' required' : '' }}>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif

                                    {{-- ═══════════════════════════════════════════ --}}
                                    {{-- SUBMIT --}}
                                    {{-- ═══════════════════════════════════════════ --}}
                                    <div class="form-group text-center mt-5">
                                        <button type="submit" class="btn btn-primary btn-lg px-5" id="eventRegSubmitBtn">
                                            <i class="fa fa-paper-plane mr-2"></i> Submit Registration
                                        </button>
                                    </div>

                                    <p class="text-muted text-center small mb-0">
                                        <i class="fa fa-lock mr-1"></i> Your information is kept private and will only be used for this event registration.
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Button loading state on submit
    $('#eventRegisterForm').on('submit', function() {
        var btn = $('#eventRegSubmitBtn');
        btn.addClass('loading');
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Submitting...');
    });

    // Toggle member lookup / church field based on "Are you a member?"
    function updateMemberUI() {
        var isMember = $('input[name="is_member"]:checked').val();
        if (isMember === '1') {
            $('#memberLookupWrap').show();
            $('#churchWrap').hide();
        } else if (isMember === '0') {
            $('#memberLookupWrap').hide();
            $('#churchWrap').show();
        } else {
            $('#memberLookupWrap').hide();
            $('#churchWrap').hide();
        }
    }

    $('input[name="is_member"]').on('change', updateMemberUI);
    updateMemberUI();

    // Member lookup by email — prefill the form if found
    $('#lookupMemberBtn').on('click', function() {
        var email = $('#member_email_lookup').val().trim();
        var msg = $('#memberLookupMsg');
        if (!email) {
            msg.removeClass('text-success').addClass('text-danger').text('Please enter your email address.');
            return;
        }

        msg.removeClass('text-danger text-success').text('Looking up...');

        $.ajax({
            url: '{{ route("events.member-lookup") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                email: email
            },
            success: function(res) {
                if (res.found) {
                    msg.removeClass('text-danger').addClass('text-success')
                       .text('Member found! Your details have been filled in. You can edit them if needed.');
                    $('#first_name').val(res.member.first_name || '');
                    $('#last_name').val(res.member.last_name || '');
                    $('#email').val(res.member.email || email);
                    $('#phone').val(res.member.phone || '');
                    $('#address').val(res.member.address || '');
                    $('#city').val(res.member.city || '');
                    $('#state').val(res.member.state || '');
                    if (res.member.country) {
                        $('#country').val(res.member.country);
                    }
                } else {
                    msg.removeClass('text-success').addClass('text-danger')
                       .text('No member found with that email. Please fill in the form manually.');
                }
            },
            error: function() {
                msg.removeClass('text-success').addClass('text-danger')
                   .text('Could not look up the member. Please fill in the form manually.');
            }
        });
    });
});
</script>
@endpush