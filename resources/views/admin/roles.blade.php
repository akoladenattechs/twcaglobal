@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Roles &amp; Permissions</h1>
    <div>
        <button type="button" class="btn btn-primary tab-btn" data-tab="roles" data-toggle="modal" data-target="#addRoleModal">
            <i class="fas fa-plus"></i> Add New Role
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

<ul class="nav nav-tabs" id="rolesTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="roles-tab" data-toggle="tab" href="#roles" role="tab" aria-controls="roles" aria-selected="true">
            <i class="fas fa-shield-alt mr-1"></i> Roles
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="permissions-tab" data-toggle="tab" href="#permissions" role="tab" aria-controls="permissions" aria-selected="false">
            <i class="fas fa-key mr-1"></i> Permissions
        </a>
    </li>
</ul>

<div class="tab-content mt-3" id="rolesTabContent">

    {{-- ════════════════════════════════════════════ --}}
    {{-- ROLES TAB --}}
    {{-- ════════════════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="roles" role="tabpanel" aria-labelledby="roles-tab">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-shield-alt mr-2"></i> All Roles</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered datatable" id="rolesTable">
                        <thead>
                            <tr>
                                <th>Role Name</th>
                                <th>Description</th>
                                <th>Super Admin</th>
                                <th>Users</th>
                                <th>Permissions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                            <tr>
                                <td>{{ htmlspecialchars($role->name) }}</td>
                                <td>{{ htmlspecialchars($role->description) }}</td>
                                <td>
                                    @if($role->is_super_admin)
                                    <span class="badge badge-success">Yes</span>
                                    @else
                                    <span class="badge badge-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $role->users_count }}</td>
                                <td>{{ $role->permissions_count }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary edit-role"
                                            data-role-id="{{ $role->id }}"
                                            data-role-name="{{ htmlspecialchars($role->name) }}"
                                            data-role-description="{{ htmlspecialchars($role->description) }}"
                                            data-role-super-admin="{{ $role->is_super_admin }}"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                            data-delete-action="{{ route('admin.roles') }}"
                                            data-delete-payload='{"action":"delete","id":{{ $role->id }}}'
                                            data-title="Delete Role"
                                            data-delete-message="Are you sure you want to delete the role &quot;{{ htmlspecialchars($role->name) }}&quot;? This action cannot be undone." title="Delete">
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

    {{-- ════════════════════════════════════════════ --}}
    {{-- PERMISSIONS TAB --}}
    {{-- ════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-key mr-2"></i> All Permissions</h6>
            </div>
            <div class="card-body">
                @foreach($permissionsByModule as $module => $modulePermissions)
                <div class="card mb-3 border">
                    <div class="card-header py-2">
                        <h6 class="mb-0 font-weight-bold text-primary text-capitalize">{{ $module }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="col-permission">Permission</th>
                                        <th class="col-slug">Slug</th>
                                        <th class="col-roles">Assigned Roles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modulePermissions as $permission)
                                    <tr>
                                        <td>{{ htmlspecialchars($permission->description) }}</td>
                                        <td><code>{{ $permission->slug ?? $permission->name }}</code></td>
                                        <td>
                                            @php
                                                $assignedRoles = $roles->filter(function($role) use ($permission, $rolePermissionIds) {
                                                    return in_array($permission->id, $rolePermissionIds[$role->id] ?? []);
                                                });
                                            @endphp
                                            @if($assignedRoles->count() > 0)
                                                @foreach($assignedRoles as $role)
                                                    <span class="badge badge-info">{{ $role->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>{{-- /.tab-content --}}

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- ADD ROLE MODAL --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="addRoleModal" tabindex="-1" role="dialog" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.roles') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addRoleModalLabel">Add New Role</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="add_name">Role Name</label>
                        <input type="text" class="form-control" id="add_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="add_description">Description</label>
                        <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="add_is_super_admin" name="is_super_admin">
                            <label class="custom-control-label" for="add_is_super_admin">Super Admin Role</label>
                        </div>
                    </div>
                    <div id="add_permissions_section">
                        <h6 class="mb-3">Permissions</h6>
                        @foreach($permissionsByModule as $module => $modulePermissions)
                        <div class="card mb-3">
                            <div class="card-header py-2">
                                <h6 class="mb-0 font-weight-bold text-capitalize">{{ $module }}</h6>
                            </div>
                            <div class="card-body">
                                @foreach($modulePermissions as $permission)
                                <div class="custom-control custom-checkbox custom-control-inline mr-4 mb-1">
                                    <input type="checkbox" class="custom-control-input"
                                           id="add_permission_{{ $permission->id }}"
                                           name="permissions[]"
                                           value="{{ $permission->id }}">
                                    <label class="custom-control-label" for="add_permission_{{ $permission->id }}">
                                        {{ htmlspecialchars($permission->description) }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- EDIT ROLE MODAL --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="editRoleModal" tabindex="-1" role="dialog" aria-labelledby="editRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.roles') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoleModalLabel">Edit Role</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_role_id">
                    <div class="form-group">
                        <label for="edit_name">Role Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="edit_is_super_admin" name="is_super_admin">
                            <label class="custom-control-label" for="edit_is_super_admin">Super Admin Role</label>
                        </div>
                    </div>
                    <div id="edit_permissions_section">
                        <h6 class="mb-3">Permissions</h6>
                        @foreach($permissionsByModule as $module => $modulePermissions)
                        <div class="card mb-3">
                            <div class="card-header py-2">
                                <h6 class="mb-0 font-weight-bold text-capitalize">{{ $module }}</h6>
                            </div>
                            <div class="card-body">
                                @foreach($modulePermissions as $permission)
                                <div class="custom-control custom-checkbox custom-control-inline mr-4 mb-1">
                                    <input type="checkbox" class="custom-control-input edit-permission"
                                           id="edit_permission_{{ $permission->id }}"
                                           name="permissions[]"
                                           value="{{ $permission->id }}">
                                    <label class="custom-control-label" for="edit_permission_{{ $permission->id }}">
                                        {{ htmlspecialchars($permission->description) }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- DELETE ROLE MODAL --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<script type="application/json" id="rolePermissionsData">@json($rolePermissionIds)</script>

<script>
$(document).ready(function() {
    var rolePermissionIds = JSON.parse($('#rolePermissionsData').text());
    $('#rolesTable').DataTable({
        "order": [[ 0, "asc" ]]
    });

    // Handle Super Admin checkbox toggle in Add modal
    $('#add_is_super_admin').change(function() {
        if ($(this).is(':checked')) {
            $('#add_permissions_section').slideUp();
        } else {
            $('#add_permissions_section').slideDown();
        }
    });

    // Handle Super Admin checkbox toggle in Edit modal
    $('#edit_is_super_admin').change(function() {
        if ($(this).is(':checked')) {
            $('#edit_permissions_section').slideUp();
        } else {
            $('#edit_permissions_section').slideDown();
        }
    });

    // Handle Edit Role
    $('.edit-role').click(function() {
        var roleId = $(this).data('role-id');
        var roleName = $(this).data('role-name');
        var roleDescription = $(this).data('role-description');
        var isSuperAdmin = $(this).data('role-super-admin');

        $('#edit_role_id').val(roleId);
        $('#edit_name').val(roleName);
        $('#edit_description').val(roleDescription);
        $('#edit_is_super_admin').prop('checked', isSuperAdmin == 1);

        // Show/hide permissions section and check permissions
        if (isSuperAdmin == 1) {
            $('#edit_permissions_section').hide();
        } else {
            $('#edit_permissions_section').show();
            // Uncheck all first, then check the ones assigned to this role
            $('.edit-permission').prop('checked', false);
            if (rolePermissionIds[roleId]) {
                rolePermissionIds[roleId].forEach(function(permId) {
                    $('#edit_permission_' + permId).prop('checked', true);
                });
            }
        }

        $('#editRoleModal').modal('show');
    });

});
</script>
@endsection
