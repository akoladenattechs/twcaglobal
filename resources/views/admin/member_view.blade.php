@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Member Details</h1>
    <a href="{{ route('admin.members') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Back to Members
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-user mr-2"></i> {{ htmlspecialchars($member->first_name . ' ' . $member->last_name) }}
        </h6>
        <span class="badge badge-{{ $member->membership_status === 'active' ? 'success' : ($member->membership_status === 'inactive' ? 'secondary' : 'warning') }} badge-lg p-2">
            {{ ucfirst($member->membership_status ?? 'Pending') }}
        </span>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Personal Information -->
            <div class="col-md-6">
                <div class="card border-left-primary shadow h-100 mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-circle mr-1"></i> Personal Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="font-weight-bold label-col">First Name:</td>
                                <td>{{ htmlspecialchars($member->first_name) }}</td>
                            </tr>
                            @if($member->other_name)
                            <tr>
                                <td class="font-weight-bold">Other Name:</td>
                                <td>{{ htmlspecialchars($member->other_name) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="font-weight-bold">Last Name:</td>
                                <td>{{ htmlspecialchars($member->last_name) }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Gender:</td>
                                <td>{{ ucfirst($member->gender) }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Marital Status:</td>
                                <td>{{ ucfirst($member->marital_status ?? 'N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Date of Birth:</td>
                                <td>{{ $member->date_of_birth ? \Carbon\Carbon::parse($member->date_of_birth)->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-md-6">
                <div class="card border-left-info shadow h-100 mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-address-card mr-1"></i> Contact Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="font-weight-bold label-col">Email:</td>
                                <td>{{ $member->email ? htmlspecialchars($member->email) : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Phone:</td>
                                <td>{{ $member->phone ? htmlspecialchars($member->phone) : 'N/A' }}</td>
                            </tr>
                            @if($member->address)
                            <tr>
                                <td class="font-weight-bold">Address:</td>
                                <td>{{ htmlspecialchars($member->address) }}</td>
                            </tr>
                            @endif
                            @if($member->city)
                            <tr>
                                <td class="font-weight-bold">City:</td>
                                <td>{{ htmlspecialchars($member->city) }}</td>
                            </tr>
                            @endif
                            @if($member->state)
                            <tr>
                                <td class="font-weight-bold">State:</td>
                                <td>{{ htmlspecialchars($member->state) }}</td>
                            </tr>
                            @endif
                            @if($member->country)
                            <tr>
                                <td class="font-weight-bold">Country:</td>
                                <td>{{ htmlspecialchars($member->country) }}</td>
                            </tr>
                            @endif
                            @if($member->nationality)
                            <tr>
                                <td class="font-weight-bold">Nationality:</td>
                                <td>{{ htmlspecialchars($member->nationality) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="font-weight-bold">Occupation:</td>
                                <td>{{ $member->occupation ? htmlspecialchars($member->occupation) : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Church Information -->
            <div class="col-md-6">
                <div class="card border-left-success shadow h-100 mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-church mr-1"></i> Church Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="font-weight-bold label-col">Center:</td>
                                <td>{{ $member->center ? htmlspecialchars($member->center->name) : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Date Joined:</td>
                                <td>{{ $member->date_joined ? \Carbon\Carbon::parse($member->date_joined)->format('M d, Y') : ($member->created_at ? \Carbon\Carbon::parse($member->created_at)->format('M d, Y') : 'N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Member Since:</td>
                                <td>{{ $member->created_at ? \Carbon\Carbon::parse($member->created_at)->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="col-md-6">
                <div class="card border-left-warning shadow h-100 mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-exclamation-triangle mr-1"></i> Emergency Contact</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="font-weight-bold label-col">Contact Name:</td>
                                <td>{{ $member->emergency_contact ? htmlspecialchars($member->emergency_contact) : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Phone:</td>
                                <td>{{ $member->emergency_phone ? htmlspecialchars($member->emergency_phone) : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if($member->notes)
        <div class="row">
            <div class="col-12">
                <div class="card border-left-secondary shadow">
                    <div class="card-header bg-light py-2">
                        <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-sticky-note mr-1"></i> Notes</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ nl2br(htmlspecialchars($member->notes)) }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="mt-4 text-right">
            <a href="{{ route('admin.members') }}?action=edit&id={{ $member->id }}" class="btn btn-primary mr-2">
                <i class="fas fa-edit mr-1"></i> Edit Member
            </a>
            <a href="{{ route('admin.members') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Members
            </a>
        </div>
    </div>
</div>
@endsection
