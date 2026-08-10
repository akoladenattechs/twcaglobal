@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Attendance</h1>
    <div class="d-flex">
        <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#serviceTypeModal">
            <i class="fas fa-cog mr-1"></i> Manage Service Types
        </button>
        <button type="button" class="btn btn-primary" id="btnAddAttendance">
            <i class="fas fa-plus mr-1"></i> Add Attendance Record
        </button>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-check mr-2"></i> Service Attendance Records</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" id="attendanceTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Center</th>
                        <th>Service Type</th>
                        <th>Males</th>
                        <th>Females</th>
                        <th>Total</th>
                        <th>First Timers</th>
                        <th>Recorded By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendance as $record)
                    <tr data-id="{{ $record->id }}"
                        data-center-id="{{ $record->center_id }}"
                        data-service-type="{{ $record->service_type }}"
                        data-service-date="{{ $record->service_date }}"
                        data-males="{{ $record->males }}"
                        data-females="{{ $record->females }}"
                        data-first-timers="{{ $record->first_timers }}"
                        data-notes="{{ $record->notes }}">
                        <td>{{ $record->id }}</td>
                        <td>{{ $record->service_date ? \Carbon\Carbon::parse($record->service_date)->format('Y-m-d') : '-' }}</td>
                        <td>{{ $record->center ? htmlspecialchars($record->center->name) : '-' }}</td>
                        <td>{{ htmlspecialchars($record->service_type) }}</td>
                        <td>{{ number_format($record->males) }}</td>
                        <td>{{ number_format($record->females) }}</td>
                        <td><strong>{{ number_format($record->total) }}</strong></td>
                        <td>{{ number_format($record->first_timers) }}</td>
                        <td>{{ htmlspecialchars($record->recorded_by) }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary btn-edit-attendance" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                data-delete-action="{{ route('admin.attendance') }}"
                                data-delete-payload='{"action":"delete","id":{{ $record->id }}}'
                                data-delete-message="Are you sure you want to delete this attendance record?">
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

<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.attendance') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="attendanceModalLabel">Add Attendance Record</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="recordId" value="">

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="center_id">Center <span class="text-danger">*</span></label>
                            <select class="form-control" id="center_id" name="center_id" required>
                                <option value="">— Select Center —</option>
                                @foreach($centers as $center)
                                <option value="{{ $center->id }}" {{ $attendanceToEdit && $attendanceToEdit->center_id == $center->id ? 'selected' : '' }}>{{ htmlspecialchars($center->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="service_type">Service Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="service_type" name="service_type" required>
                                <option value="">— Select Type —</option>
                                @foreach($serviceTypes as $type)
                                <option value="{{ htmlspecialchars($type->name) }}" {{ $attendanceToEdit && $attendanceToEdit->service_type == $type->name ? 'selected' : '' }}>{{ htmlspecialchars($type->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="service_date">Service Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="service_date" name="service_date" value="{{ $attendanceToEdit ? $attendanceToEdit->service_date : '' }}" required>
                        </div>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold text-primary"><i class="fas fa-users mr-1"></i> Attendance Count</h6>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="males">Males <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="males" name="males" min="0" value="{{ $attendanceToEdit ? $attendanceToEdit->males : 0 }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="females">Females <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="females" name="females" min="0" value="{{ $attendanceToEdit ? $attendanceToEdit->females : 0 }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="first_timers">First Timers</label>
                            <input type="number" class="form-control" id="first_timers" name="first_timers" min="0" value="{{ $attendanceToEdit ? $attendanceToEdit->first_timers : 0 }}">
                            <small class="form-text text-muted">Informational — number of first-time attendees.</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">Calculated Total</small>
                                <span class="h4 font-weight-bold text-primary" id="totalDisplay">0</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Optional remarks…">{{ $attendanceToEdit ? htmlspecialchars($attendanceToEdit->notes) : '' }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <p class="text-muted small mr-auto mb-0">
                        <i class="fas fa-user-check mr-1"></i> Recorded by: <strong>{{ auth()->check() ? (auth()->user()->name ?? auth()->user()->username) : 'System' }}</strong>
                    </p>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Service Type Modal -->
<div class="modal fade" id="serviceTypeModal" tabindex="-1" role="dialog" aria-labelledby="serviceTypeModalLabel" aria-hidden="true" data-edit-mode="{{ $serviceTypeToEdit ? 'true' : 'false' }}">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.attendance') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceTypeModalLabel">{{ $serviceTypeToEdit ? 'Edit Service Type' : 'Manage Service Types' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="service_action" value="{{ $serviceTypeToEdit ? 'edit' : 'add' }}">
                    @if($serviceTypeToEdit)
                    <input type="hidden" name="service_id" value="{{ $serviceTypeToEdit->id }}">
                    @endif

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="font-weight-bold">Add / Edit Service Type</h6>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label for="service_name">Service Name</label>
                                <input type="text" class="form-control" id="service_name" name="service_name" value="{{ $serviceTypeToEdit ? htmlspecialchars($serviceTypeToEdit->name) : '' }}" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="is_active">Active</label>
                                <div class="custom-control custom-checkbox pt-2">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" {{ $serviceTypeToEdit ? ($serviceTypeToEdit->is_active ? 'checked' : '') : 'checked' }}>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="service_description">Description</label>
                            <textarea class="form-control" id="service_description" name="service_description" rows="2">{{ $serviceTypeToEdit ? htmlspecialchars($serviceTypeToEdit->description) : '' }}</textarea>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serviceTypes as $type)
                                <tr>
                                    <td>{{ htmlspecialchars($type->name) }}</td>
                                    <td>{{ htmlspecialchars($type->description) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $type->is_active ? 'success' : 'secondary' }}">
                                            {{ $type->is_active ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.attendance') }}?service_action=edit&service_id={{ $type->id }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                            data-delete-action="{{ route('admin.attendance') }}"
                                            data-delete-payload='{"service_action":"delete","service_id":{{ $type->id }}}'
                                            data-delete-message="Are you sure you want to delete this service type?">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">{{ $serviceTypeToEdit ? 'Update' : 'Save' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#attendanceTable').DataTable({
        "pageLength": 25,
        "order": [[ 1, "desc" ]]
    });

    // Show service type modal automatically if in edit mode
    if ($('#serviceTypeModal').data('edit-mode') === 'true') {
        $('#serviceTypeModal').modal('show');
    }

    // ── Edit attendance (JS-based, no page reload) ──
    $(document).on('click', '.btn-edit-attendance', function() {
        var $row = $(this).closest('tr');

        $('#recordId').val($row.data('id'));
        $('#formAction').val('edit');
        $('#center_id').val($row.data('center-id') || '');
        $('#service_type').val($row.data('service-type') || '');
        $('#service_date').val($row.data('service-date') || '');
        $('#males').val($row.data('males') || 0);
        $('#females').val($row.data('females') || 0);
        $('#first_timers').val($row.data('first-timers') || 0);
        $('#notes').val($row.data('notes') || '');

        $('#attendanceModalLabel').text('Edit Attendance Record');
        $('#modalSubmitBtn').text('Update Record');
        $('#attendanceModal').modal('show');
        updateTotal();
    });

    // ── Reset modal when opening for add ──
    $('#btnAddAttendance').on('click', function() {
        $('#attendanceModal form')[0].reset();
        $('#formAction').val('add');
        $('#recordId').val('');
        $('#attendanceModalLabel').text('Add Attendance Record');
        $('#modalSubmitBtn').text('Save Record');
        $('#males').val(0);
        $('#females').val(0);
        $('#first_timers').val(0);
        updateTotal();
        $('#attendanceModal').modal('show');
    });

    // ── Live total calculation ──
    function updateTotal() {
        var males = parseInt($('#males').val()) || 0;
        var females = parseInt($('#females').val()) || 0;
        $('#totalDisplay').text(males + females);
    }

    $('#males, #females').on('input', updateTotal);
    updateTotal();
});
</script>
@endsection
