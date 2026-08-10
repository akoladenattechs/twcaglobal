@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Members</h1>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#memberModal">
        <i class="fas fa-plus"></i> Add New Member
    </button>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users mr-2"></i> All Members</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" id="membersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($members as $member)
                    <tr>
                        <td>{{ $member->id }}</td>
                        <td>{{ htmlspecialchars($member->first_name . ' ' . $member->last_name) }}</td>
                        <td>{{ htmlspecialchars($member->email) }}</td>
                        <td>{{ htmlspecialchars($member->phone) }}</td>
                        <td>{{ $member->created_at ? \Carbon\Carbon::parse($member->created_at)->format('M d, Y') : '-' }}</td>
                        <td>
                            @if($member->membership_status === 'active')
                            <span class="badge badge-success">Active</span>
                            @elseif($member->membership_status === 'inactive')
                            <span class="badge badge-secondary">Inactive</span>
                            @else
                            <span class="badge badge-dark">Deceased</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.members.view', $member->id) }}" class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.members') }}?action=edit&id={{ $member->id }}" class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                data-delete-action="{{ route('admin.members') }}"
                                data-delete-payload='{"action":"delete","id":{{ $member->id }}}'
                                data-delete-message="Are you sure you want to delete this member?">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="memberModal" tabindex="-1" role="dialog" aria-labelledby="memberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.members') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="memberModalLabel">{{ $memberToEdit ? 'Edit Member' : 'Add New Member' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="{{ $memberToEdit ? 'edit' : 'add' }}">
                    @if($memberToEdit)
                    <input type="hidden" name="id" value="{{ $memberToEdit->id }}">
                    @endif

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="{{ $memberToEdit ? htmlspecialchars($memberToEdit->first_name) : '' }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="other_name">Other Name</label>
                            <input type="text" class="form-control" id="other_name" name="other_name" value="{{ $memberToEdit ? htmlspecialchars($memberToEdit->other_name) : '' }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="{{ $memberToEdit ? htmlspecialchars($memberToEdit->last_name) : '' }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $memberToEdit ? htmlspecialchars($memberToEdit->email) : '' }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ $memberToEdit ? htmlspecialchars($memberToEdit->phone) : '' }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ $memberToEdit ? $memberToEdit->date_of_birth : '' }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="date_joined">Date Joined</label>
                            <input type="date" class="form-control" id="date_joined" name="date_joined" value="{{ $memberToEdit ? $memberToEdit->date_joined : '' }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="membership_status">Membership Status</label>
                            <select class="form-control" id="membership_status" name="membership_status">
                                <option value="active" {{ $memberToEdit && $memberToEdit->membership_status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $memberToEdit && $memberToEdit->membership_status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="deceased" {{ $memberToEdit && $memberToEdit->membership_status === 'deceased' ? 'selected' : '' }}>Deceased</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="marital_status">Marital Status</label>
                            <select class="form-control" id="marital_status" name="marital_status">
                                <option value="single" {{ $memberToEdit && $memberToEdit->marital_status === 'single' ? 'selected' : '' }}>Single</option>
                                <option value="married" {{ $memberToEdit && $memberToEdit->marital_status === 'married' ? 'selected' : '' }}>Married</option>
                                <option value="divorced" {{ $memberToEdit && $memberToEdit->marital_status === 'divorced' ? 'selected' : '' }}>Divorced</option>
                                <option value="widowed" {{ $memberToEdit && $memberToEdit->marital_status === 'widowed' ? 'selected' : '' }}>Widowed</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="gender">Gender</label>
                            <select class="form-control" id="gender" name="gender">
                                <option value="male" {{ $memberToEdit && $memberToEdit->gender === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $memberToEdit && $memberToEdit->gender === 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="center_id">Center</label>
                            <select class="form-control" id="center_id" name="center_id">
                                <option value="">— Select Center —</option>
                                @foreach($centers as $center)
                                <option value="{{ $center->id }}" {{ $memberToEdit && $memberToEdit->center_id == $center->id ? 'selected' : '' }}>{{ htmlspecialchars($center->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="address">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2">{{ $memberToEdit ? htmlspecialchars($memberToEdit->address) : '' }}</textarea>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="city">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="{{ $memberToEdit ? htmlspecialchars($memberToEdit->city) : '' }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="state">State</label>
                            <input type="text" class="form-control" id="state" name="state" value="{{ $memberToEdit ? htmlspecialchars($memberToEdit->state) : '' }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="country">Country</label>
                            <input type="text" class="form-control" id="country" name="country" value="{{ $memberToEdit ? htmlspecialchars($memberToEdit->country) : '' }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="nationality">Nationality</label>
                            <input type="text" class="form-control" id="nationality" name="nationality" value="{{ $memberToEdit ? htmlspecialchars($memberToEdit->nationality) : '' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="occupation">Occupation</label>
                        <input type="text" class="form-control" id="occupation" name="occupation" value="{{ $memberToEdit ? htmlspecialchars($memberToEdit->occupation) : '' }}">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="emergency_contact">Emergency Contact</label>
                            <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" value="{{ $memberToEdit ? htmlspecialchars($memberToEdit->emergency_contact) : '' }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="emergency_phone">Emergency Phone</label>
                            <input type="text" class="form-control" id="emergency_phone" name="emergency_phone" value="{{ $memberToEdit ? htmlspecialchars($memberToEdit->emergency_phone) : '' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ $memberToEdit ? htmlspecialchars($memberToEdit->notes) : '' }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">{{ $memberToEdit ? 'Update' : 'Save' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script id="members-data" type="application/json">{ "showModal": {{ $memberToEdit ? 'true' : 'false' }} }</script>
<script>
$(document).ready(function() {
    $('#membersTable').DataTable({
        "pageLength": 25,
        "order": [[ 0, "desc" ]]
    });

    // Show modal automatically if we're in edit mode
    var membersData = JSON.parse(document.getElementById('members-data').textContent);
    if (membersData.showModal) {
        $('#memberModal').modal('show');
    }
});
</script>
@endsection
