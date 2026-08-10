@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-user-circle mr-2"></i> Profile</h1>
</div>

{{-- Success Alert --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

{{-- Error Alert --}}
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

{{-- Validation Errors --}}
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle mr-1"></i> Please fix the errors below.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div class="row">
    {{-- Profile Info Card --}}
    <div class="col-lg-4 mb-4">
        <div class="card shadow text-center h-100">
            <div class="card-body py-5">
                <div class="mb-3">
                    <div class="avatar-circle mx-auto mb-3">
                        <span class="avatar-initials">{{ strtoupper(substr($user->first_name ?? $user->username, 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}</span>
                    </div>
                    <h4 class="mb-1">{{ htmlspecialchars(trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) ?: htmlspecialchars($user->username) }}</h4>
                    <p class="text-muted mb-2">
                        <i class="fas fa-user-tag mr-1"></i> {{ $user->role ? htmlspecialchars($user->role->name) : 'N/A' }}
                    </p>
                    @if($user->status === 'active')
                        <span class="badge badge-success px-3 py-2"><i class="fas fa-check-circle mr-1"></i> Active</span>
                    @elseif($user->status === 'inactive')
                        <span class="badge badge-warning px-3 py-2"><i class="fas fa-clock mr-1"></i> Inactive</span>
                    @else
                        <span class="badge badge-danger px-3 py-2">{{ ucfirst(htmlspecialchars($user->status)) }}</span>
                    @endif
                </div>
                <hr>
                <div class="text-left small">
                    <p class="mb-2">
                        <i class="fas fa-user mr-2 text-muted"></i>
                        <strong>Username:</strong> {{ htmlspecialchars($user->username) }}
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-envelope mr-2 text-muted"></i>
                        <strong>Email:</strong> {{ htmlspecialchars($user->email) }}
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-calendar-alt mr-2 text-muted"></i>
                        <strong>Member Since:</strong> {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('M d, Y') : 'N/A' }}
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-sign-in-alt mr-2 text-muted"></i>
                        <strong>Last Login:</strong> {{ $user->last_login ? \Carbon\Carbon::parse($user->last_login)->format('M d, Y H:i') : 'Never' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Profile Form --}}
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-edit mr-1"></i> Edit Profile
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.profile.update') }}">
                    @csrf

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="first_name">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                   id="first_name" name="first_name"
                                   value="{{ old('first_name', $user->first_name) }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="last_name">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                   id="last_name" name="last_name"
                                   value="{{ old('last_name', $user->last_name) }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username"
                               value="{{ htmlspecialchars($user->username) }}" readonly disabled>
                        <small class="form-text text-muted">Username cannot be changed.</small>
                    </div>

                    <hr class="my-4">

                    <h6 class="font-weight-bold text-primary mb-3">
                        <i class="fas fa-lock mr-1"></i> Change Password
                    </h6>
                    <small class="form-text text-muted mb-3">Leave blank to keep your current password.</small>

                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                               id="current_password" name="current_password">
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="new_password">New Password</label>
                            <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                                   id="new_password" name="new_password" minlength="8">
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <input type="password" class="form-control"
                                   id="new_password_confirmation" name="new_password_confirmation">
                        </div>
                    </div>

                    <div class="text-right mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
@endsection
