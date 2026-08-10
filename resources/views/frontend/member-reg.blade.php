@extends('layouts.app')

@section('content')
<section class="hero-wrap hero-wrap-2 js-fullheight" @style(['background-image: url(' . asset('admin/bg_1.jpg') . ')'])>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <p class="breadcrumbs mb-2">
                    <span class="mr-2"><a href="{{ url('/') }}">Home <i class="fa fa-chevron-right"></i></a></span>
                    <span>Member Registration <i class="fa fa-chevron-right"></i></span>
                </p>
                <h1 class="mb-0 bread">Become a Member</h1>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-8 text-center heading-section ftco-animate">
                <h2 class="mb-3">Member Registration</h2>
                <p class="lead text-muted">
                    We are delighted that you want to become a part of our church family. 
                    Please fill out the form below and we will get in touch with you.
                </p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="wrapper">
                    <div class="row no-gutters">
                        <div class="col-md-12">
                            <div class="contact-wrap w-100 p-md-5 p-4">
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fa fa-exclamation-circle mr-2"></i> {{ session('error') }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif

                                @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
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

                                <form method="POST" action="{{ route('member.register.store') }}" class="contactForm" id="memberRegForm">
                                    @csrf

                                    <h3 class="mb-4" style="color: #fff;">Member Registration</h3>

                                    @if(session('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px;">
                                            <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif

                                    <h4 class="mb-3" style="color: #fff; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 8px;"><i class="fa fa-user mr-2"></i> Personal Information</h4>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="label" for="first_name">First Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" id="first_name" placeholder="First Name" value="{{ old('first_name') }}" required>
                                                @error('first_name')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="label" for="other_name">Other Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('other_name') is-invalid @enderror" name="other_name" id="other_name" placeholder="Middle / Other Name" value="{{ old('other_name') }}" required>
                                                @error('other_name')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
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
                                                <label class="label" for="phone">Phone Number <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" placeholder="Phone Number" value="{{ old('phone') }}" required>
                                                @error('phone')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="label" for="gender">Gender <span class="text-danger">*</span></label>
                                                <select class="form-control @error('gender') is-invalid @enderror" name="gender" id="gender" required>
                                                    <option value="">— Select Gender —</option>
                                                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                                </select>
                                                @error('gender')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="label" for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" required>
                                                @error('date_of_birth')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="label" for="marital_status">Marital Status <span class="text-danger">*</span></label>
                                                <select class="form-control @error('marital_status') is-invalid @enderror" name="marital_status" id="marital_status" required>
                                                    <option value="">— Select —</option>
                                                    <option value="single" {{ old('marital_status') === 'single' ? 'selected' : '' }}>Single</option>
                                                    <option value="married" {{ old('marital_status') === 'married' ? 'selected' : '' }}>Married</option>
                                                    <option value="divorced" {{ old('marital_status') === 'divorced' ? 'selected' : '' }}>Divorced</option>
                                                    <option value="widowed" {{ old('marital_status') === 'widowed' ? 'selected' : '' }}>Widowed</option>
                                                </select>
                                                @error('marital_status')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="label" for="nationality">Nationality <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('nationality') is-invalid @enderror" name="nationality" id="nationality" placeholder="e.g. Nigerian" value="{{ old('nationality') }}" required>
                                                @error('nationality')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="label" for="occupation">Occupation <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('occupation') is-invalid @enderror" name="occupation" id="occupation" placeholder="Your Occupation" value="{{ old('occupation') }}" required>
                                                @error('occupation')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <h4 class="mb-3 mt-5" style="color: #fff; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 8px;"><i class="fa fa-map-marker mr-2"></i> Address Information</h4>

                                    <div class="form-group">
                                        <label class="label" for="address">Home Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="address" rows="2" placeholder="Your residential address" required>{{ old('address') }}</textarea>
                                        @error('address')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="label" for="city">City <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" id="city" placeholder="City" value="{{ old('city') }}" required>
                                                @error('city')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="label" for="state">State / Province <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('state') is-invalid @enderror" name="state" id="state" placeholder="State" value="{{ old('state') }}" required>
                                                @error('state')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="label" for="country">Country <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('country') is-invalid @enderror" name="country" id="country" placeholder="Country" value="{{ old('country') }}" required>
                                                @error('country')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <h4 class="mb-3 mt-5" style="color: #fff; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 8px;"><i class="fa fa-building mr-2"></i> Church Center</h4>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="label" for="center_id">Preferred Center / Location <span class="text-danger">*</span></label>
                                                <select class="form-control @error('center_id') is-invalid @enderror" name="center_id" id="center_id" required>
                                                    <option value="">— Select Center —</option>
                                                    @if(isset($centers) && $centers->isNotEmpty())
                                                        @foreach($centers as $center)
                                                            <option value="{{ $center->id }}" {{ old('center_id') == $center->id ? 'selected' : '' }}>{{ htmlspecialchars($center->name) }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                @error('center_id')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <h4 class="mb-3 mt-5" style="color: #fff; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 8px;"><i class="fa fa-phone-square mr-2"></i> Emergency Contact</h4>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="label" for="emergency_contact">Emergency Contact Person <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('emergency_contact') is-invalid @enderror" name="emergency_contact" id="emergency_contact" placeholder="Full Name" value="{{ old('emergency_contact') }}" required>
                                                @error('emergency_contact')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="label" for="emergency_phone">Emergency Phone <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('emergency_phone') is-invalid @enderror" name="emergency_phone" id="emergency_phone" placeholder="Phone Number" value="{{ old('emergency_phone') }}" required>
                                                @error('emergency_phone')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <h4 class="mb-3 mt-5" style="color: #fff; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 8px;"><i class="fa fa-comment mr-2"></i> Additional Notes</h4>

                                    <div class="form-group">
                                        <label class="label" for="notes">Any additional information</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" id="notes" rows="3" placeholder="Tell us anything else you'd like us to know...">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- ═══════════════════════════════════════════ --}}
                                    {{-- SUBMIT --}}
                                    {{-- ═══════════════════════════════════════════ --}}
                                    <div class="form-group text-center mt-5">
                                        <button type="submit" class="btn btn-primary btn-lg px-5" id="memberRegSubmitBtn">
                                            <i class="fa fa-paper-plane mr-2"></i> Submit Registration
                                        </button>
                                    </div>

                                    <p class="text-muted text-center small mb-0">
                                        <i class="fa fa-lock mr-1"></i> Your information is kept private and will only be used for church records.
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
    $('#memberRegForm').on('submit', function() {
        var btn = $('#memberRegSubmitBtn');
        btn.addClass('loading');
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Submitting...');
    });
});
</script>
@endpush
