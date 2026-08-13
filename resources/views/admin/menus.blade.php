@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Navigation Menus</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addMenuModal">
            <i class="fas fa-plus"></i> Add New Menu
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

    <div class="row">
        <!-- Left: Add Menu Item Form -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-plus-circle mr-2"></i> Add Menu Item</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.menus') }}">
                        @csrf
                        <input type="hidden" name="action" value="add_item">

                        <div class="form-group">
                            <label>Title *</label>
                            <input type="text" class="form-control" name="title" id="add_title" required>
                        </div>

                        <div class="form-group">
                            <label>URL *</label>
                            <input type="text" class="form-control" name="url" id="add_url" required>
                            <small class="form-text text-muted">Use relative URLs (e.g., /about) or full URLs for external links.</small>
                        </div>

                        <div class="form-group">
                            <label>Menu *</label>
                            <select class="form-control" name="menu_id" id="add_menu_id" required>
                                @foreach($menus as $menu)
                                    <option value="{{ $menu->id }}" {{ $loop->first ? 'selected' : '' }}>{{ htmlspecialchars($menu->name) }} {{ $menu->location === 'main_menu' ? '(Main Navigation)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Order Number</label>
                            <input type="number" class="form-control" name="order_number" value="0" min="0">
                        </div>

                        <div class="form-group">
                            <label>Target</label>
                            <select class="form-control" name="target">
                                <option value="_self">Same Window</option>
                                <option value="_blank">New Window</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Parent Item (Create Dropdown)</label>
                            <select class="form-control" name="parent_id" id="add_parent_id">
                                <option value="">None (Top Level)</option>
                                @foreach($allMenuItems as $item)
                                    @if($item->parent_id === null)
                                        <option value="{{ $item->id }}">
                                            📁 {{ htmlspecialchars($item->title) }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Select a parent to create a dropdown submenu. Leave empty for top-level items.</small>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" name="is_cta" id="add_is_cta" value="1">
                            <label class="form-check-label" for="add_is_cta">
                                <strong>CTA / Pill Button</strong> &mdash; Renders as a prominent pill button in the navbar
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">Add Menu Item</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Current Menu Structure -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-sitemap mr-2"></i> Current Menu Structure</h6>
                    <small class="text-muted">📁 = Has dropdown | 📄 = Single item</small>
                </div>
                <div class="card-body">
                    @php
                        $topLevelItems = $allMenuItems->where('parent_id', null);
                    @endphp

                    @if($topLevelItems->isEmpty())
                        <p>No menu items found. Use the form to add your first menu item.</p>
                    @else
                        <ul class="list-group">
                            @foreach($topLevelItems as $item)
                                @php
                                    $hasChildren = $allMenuItems->where('parent_id', $item->id)->isNotEmpty();
                                @endphp
                                <li class="list-group-item {{ $hasChildren ? 'list-group-item-info' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="mr-2">{{ $hasChildren ? '📁' : '📄' }}</span>
                                            <strong>{{ htmlspecialchars($item->title) }}</strong>
                                            @if($hasChildren)
                                                <span class="badge badge-primary ml-2">Dropdown Menu</span>
                                            @endif
                                            <small class="d-block text-muted">{{ htmlspecialchars($item->url) }}</small>
                                        </div>
                                        <div class="ml-2">
                                            <button type="button" class="btn btn-sm btn-primary edit-menu-item"
                                                    data-id="{{ $item->id }}"
                                                    data-title="{{ $item->title }}"
                                                    data-url="{{ $item->url }}"
                                                    data-menu_id="{{ $item->menu_id }}"
                                                    data-parent="{{ $item->parent_id }}"
                                                    data-order_number="{{ $item->order_number }}"
                                                    data-status="{{ $item->status }}"
                                                    data-is_cta="{{ $item->is_cta }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                                data-delete-action="{{ route('admin.menus') }}"
                                                data-delete-payload='{"action":"delete_item","item_id":{{ $item->id }}}'
                                                data-delete-message="Delete this menu item?">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    @if($hasChildren)
                                        <div class="mt-2">
                                            <small class="text-muted">Submenu items:</small>
                                            <ul class="list-group mt-1">
                                                @foreach($allMenuItems as $child)
                                                    @if($child->parent_id == $item->id)
                                                        <li class="list-group-item list-group-item-light">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <span class="fa fa-angle-right mr-2"></span>
                                                                    <strong>{{ htmlspecialchars($child->title) }}</strong>
                                                                    <small class="d-block text-muted ml-3">{{ htmlspecialchars($child->url) }}</small>
                                                                </div>
                                                                <div class="ml-2">
                                                                    <button type="button" class="btn btn-sm btn-outline-primary edit-menu-item"
                                                                            data-id="{{ $child->id }}"
                                                                            data-title="{{ $child->title }}"
                                                                            data-url="{{ $child->url }}"
                                                                            data-menu_id="{{ $child->menu_id }}"
                                                                            data-parent="{{ $child->parent_id }}"
                                                                            data-order_number="{{ $child->order_number }}"
                                                                            data-status="{{ $child->status }}"
                                                                            data-is_cta="{{ $child->is_cta }}">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                                        data-delete-action="{{ route('admin.menus') }}"
                                                                        data-delete-payload='{"action":"delete_item","item_id":{{ $child->id }}}'
                                                                        data-delete-message="Delete this submenu item?">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-success add-submenu-btn"
                                                        data-parent-id="{{ $item->id }}"
                                                        data-parent-title="{{ $item->title }}">
                                                    <i class="fas fa-plus"></i> Add Submenu Item
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Menu Item Modal -->
<div class="modal fade" id="editMenuModal" tabindex="-1" role="dialog" aria-labelledby="editMenuModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMenuModalLabel">Edit Menu Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{ route('admin.menus') }}" id="editMenuForm">
                @csrf
                <input type="hidden" name="action" value="edit_item">
                <input type="hidden" name="item_id" id="edit_item_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" class="form-control" name="title" id="edit_title" required>
                    </div>
                    <div class="form-group">
                        <label>URL *</label>
                        <input type="text" class="form-control" name="url" id="edit_url" required>
                    </div>
                    <div class="form-group">
                        <label>Menu *</label>
                        <select class="form-control" name="menu_id" id="edit_menu_id" required>
                            @foreach($menus as $menu)
                                <option value="{{ $menu->id }}">{{ htmlspecialchars($menu->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Order Number</label>
                        <input type="number" class="form-control" name="order_number" id="edit_order_number" min="0">
                    </div>
                    <div class="form-group">
                        <label>Target</label>
                        <select class="form-control" name="target" id="edit_target">
                            <option value="_self">Same Window</option>
                            <option value="_blank">New Window</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Parent Item (Create Dropdown)</label>
                        <select class="form-control" name="parent_id" id="edit_parent_id">
                            <option value="">None (Top Level Menu)</option>
                            @foreach($allMenuItems as $item)
                                @if($item->parent_id === null)
                                    <option value="{{ $item->id }}">
                                        📁 {{ htmlspecialchars($item->title) }} (Submenu under this)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Select a parent to create a dropdown submenu.</small>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status" id="edit_status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" name="is_cta" id="edit_is_cta" value="1">
                        <label class="form-check-label" for="edit_is_cta">
                            <strong>CTA / Pill Button</strong> &mdash; Renders as a prominent pill button in the navbar
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Menu Modal -->
<div class="modal fade" id="addMenuModal" tabindex="-1" role="dialog" aria-labelledby="addMenuModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMenuModalLabel">Add New Menu</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{ route('admin.menus') }}">
                @csrf
                <input type="hidden" name="action" value="add_menu">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Menu Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" class="form-control" name="location" placeholder="e.g., main_menu, footer_menu">
                    </div>
                    <div class="form-group">
                        <label>Display Order</label>
                        <input type="number" class="form-control" name="display_order" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Edit button: populate modal
    $(document).on('click', '.edit-menu-item', function() {
        var btn = $(this);
        $('#edit_item_id').val(btn.data('id'));
        $('#edit_title').val(btn.data('title'));
        $('#edit_url').val(btn.data('url'));
        $('#edit_menu_id').val(btn.data('menu_id'));
        $('#edit_parent_id').val(btn.data('parent') || '');
        $('#edit_order_number').val(btn.data('order_number'));
        $('#edit_status').val(btn.data('status'));
        $('#edit_is_cta').prop('checked', btn.data('is_cta') == 1);
        $('#editMenuModal').modal('show');
    });

    // Add Submenu button: pre-select parent
    $(document).on('click', '.add-submenu-btn', function() {
        var parentId = $(this).data('parent-id');
        var parentTitle = $(this).data('parent-title');
        $('#add_parent_id').val(parentId);
        $('html, body').animate({
            scrollTop: $('.card-header:contains("Add Menu Item")').offset().top - 100
        }, 500);
        alert('Parent menu "' + parentTitle + '" has been selected. Fill in the details below to add a submenu item.');
    });
});

</script>
@endsection
