@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Staffs</h1>
        <div>
            <a href="{{ route('admin.staff') }}?action=export" class="btn btn-success mr-2">
                <i class="fas fa-download"></i> Export CSV
            </a>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addStaffModal">
                <i class="fas fa-plus"></i> Add Staff Member
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Staff Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-tie mr-2"></i> Staff Members</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="staffTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Start Date</th>
                            <th>Status</th>
                            <th>Salary</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staff as $s)
                        <tr>
                            <td>
                                {{ optional($s->member)->first_name }} {{ optional($s->member)->last_name }}
                                @if(optional($s->member)->email)
                                    <br><small class="text-muted">{{ $s->member->email }}</small>
                                @endif
                            </td>
                            <td>{{ $s->position }}</td>
                            <td>{{ $s->department }}</td>
                            <td>{{ $s->start_date ? \Carbon\Carbon::parse($s->start_date)->format('M d, Y') : '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $s->status === 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($s->status) }}
                                </span>
                            </td>
                            <td>{{ $s->salary ? '₦' . number_format($s->salary) : 'N/A' }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info edit-staff-btn"
                                    data-id="{{ $s->id }}"
                                    data-member_id="{{ $s->member_id }}"
                                    data-position="{{ $s->position }}"
                                    data-department="{{ $s->department }}"
                                    data-start_date="{{ $s->start_date }}"
                                    data-end_date="{{ $s->end_date }}"
                                    data-status="{{ $s->status }}"
                                    data-salary="{{ $s->salary }}"
                                    data-responsibilities="{{ $s->responsibilities }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger"
                                    data-delete-action="{{ route('admin.staff') }}"
                                    data-delete-payload='{"action":"delete","id":{{ $s->id }}}'
                                    data-title="Delete Staff Member"
                                    data-delete-message="Are you sure you want to remove &quot;{{ optional($s->member)->first_name }} {{ optional($s->member)->last_name }}&quot; from staff? This action cannot be undone.">
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
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" role="dialog" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStaffModalLabel">Add Staff Member</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.staff') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Church Member *</label>
                        <select class="form-control" name="member_id" required>
                            <option value="">Select a member...</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">{{ $member->last_name }}, {{ $member->first_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Position *</label>
                        <input type="text" class="form-control" name="position" required>
                    </div>
                    <div class="form-group">
                        <label>Department *</label>
                        <input type="text" class="form-control" name="department" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Start Date *</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>End Date</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Status *</label>
                            <select class="form-control" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Salary</label>
                            <input type="number" class="form-control" name="salary" step="0.01" placeholder="e.g. 50000">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Responsibilities</label>
                        <textarea class="form-control" name="responsibilities" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Staff Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1" role="dialog" aria-labelledby="editStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStaffModalLabel">Edit Staff Member</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.staff') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Church Member *</label>
                        <select class="form-control" name="member_id" id="edit_member_id" required>
                            <option value="">Select a member...</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">{{ $member->last_name }}, {{ $member->first_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Position *</label>
                        <input type="text" class="form-control" name="position" id="edit_position" required>
                    </div>
                    <div class="form-group">
                        <label>Department *</label>
                        <input type="text" class="form-control" name="department" id="edit_department" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Start Date *</label>
                            <input type="date" class="form-control" name="start_date" id="edit_start_date" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>End Date</label>
                            <input type="date" class="form-control" name="end_date" id="edit_end_date">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Status *</label>
                            <select class="form-control" name="status" id="edit_status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Salary</label>
                            <input type="number" class="form-control" name="salary" id="edit_salary" step="0.01">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Responsibilities</label>
                        <textarea class="form-control" name="responsibilities" id="edit_responsibilities" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Staff Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function () {
    // Initialize DataTable
    $('#staffTable').DataTable({
        order: [[2, 'asc'], [1, 'asc']],
        columnDefs: [
            { orderable: false, targets: [6] }
        ],
        language: {
            emptyTable: 'No staff members found.'
        }
    });

    // Edit button
    $(document).on('click', '.edit-staff-btn', function () {
        var btn = $(this);
        $('#edit_id').val(btn.data('id'));
        $('#edit_member_id').val(btn.data('member_id'));
        $('#edit_position').val(btn.data('position'));
        $('#edit_department').val(btn.data('department'));
        $('#edit_start_date').val(btn.data('start_date'));
        $('#edit_end_date').val(btn.data('end_date') || '');
        $('#edit_status').val(btn.data('status'));
        $('#edit_salary').val(btn.data('salary') || '');
        $('#edit_responsibilities').val(btn.data('responsibilities') || '');
        $('#editStaffModal').modal('show');
    });

});
</script>
@endsection
