@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Users</h1>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#userModal">
        <i class="fas fa-plus"></i> Add New User
    </button>
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

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user mr-2"></i> All Users</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" id="usersTable">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ htmlspecialchars($user->username) }}</td>
                        <td>{{ htmlspecialchars(trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) ?: 'N/A' }}</td>
                        <td>{{ htmlspecialchars($user->email) }}</td>
                        <td>{{ $user->role ? htmlspecialchars($user->role->name) : 'N/A' }}</td>
                        <td>
                            @if($user->status === 'active')
                            <span class="badge badge-success">Active</span>
                            @elseif($user->status === 'inactive')
                            <span class="badge badge-warning">Inactive</span>
                            @else
                            <span class="badge badge-danger">{{ ucfirst(htmlspecialchars($user->status)) }}</span>
                            @endif
                        </td>
                        <td>{{ $user->last_login ? \Carbon\Carbon::parse($user->last_login)->format('M d, Y H:i') : 'Never' }}</td>
                        <td>
                            <a href="{{ route('admin.users') }}?action=edit&id={{ $user->id }}" class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                data-delete-action="{{ route('admin.users') }}"
                                data-delete-payload='{"action":"delete","id":{{ $user->id }}}'
                                data-delete-message="Are you sure you want to delete user {{ htmlspecialchars($user->username) }}?">
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

<!-- Add/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.users') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">{{ $userToEdit ? 'Edit User' : 'Add New User' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="{{ $userToEdit ? 'edit' : 'add' }}">
                    @if($userToEdit)
                    <input type="hidden" name="id" value="{{ $userToEdit->id }}">
                    @endif

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="{{ $userToEdit ? htmlspecialchars($userToEdit->username) : '' }}" {{ $userToEdit ? 'readonly' : 'required' }}>
                            @if($userToEdit)
                            <small class="form-text text-muted">Username cannot be changed.</small>
                            @endif
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $userToEdit ? htmlspecialchars($userToEdit->email) : '' }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="password">Password {{ $userToEdit ? '(leave blank to keep current)' : '' }}</label>
                            <input type="password" class="form-control" id="password" name="password" {{ $userToEdit ? '' : 'required' }}>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="role_id">Role</label>
                            <select class="form-control" id="role_id" name="role_id" required>
                                <option value="">Select a role...</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ $userToEdit && $userToEdit->role_id == $role->id ? 'selected' : '' }}>
                                    {{ htmlspecialchars($role->name) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="{{ $userToEdit ? htmlspecialchars($userToEdit->first_name) : '' }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="{{ $userToEdit ? htmlspecialchars($userToEdit->last_name) : '' }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="active" {{ $userToEdit && $userToEdit->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $userToEdit && $userToEdit->status !== 'active' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">{{ $userToEdit ? 'Update' : 'Save' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        "pageLength": 25,
        "order": [[ 0, "asc" ]]
    });
});
</script>

@if($userToEdit)
<script>
$(document).ready(function() {
    $('#userModal').modal('show');
});
</script>
@endif
@endsection
