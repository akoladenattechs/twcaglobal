@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Ministry Core</h1>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addColumnModal">
        <i class="fas fa-plus"></i> Add New Column
    </button>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-columns mr-2"></i> All Columns</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" id="columnsTable">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Icon</th>
                        <th>Title / Quote</th>
                        <th>Subtitle / Author</th>
                        <th>Display Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($columns as $column)
                    <tr>
                        <td>
                            @if($column->column_type === 'quote')
                                <span class="badge badge-info">Quote</span>
                            @else
                                <span class="badge badge-primary">Ministry</span>
                            @endif
                        </td>
                        <td>
                            @if($column->icon_class)
                                <span class="{{ $column->icon_class }} icon-lg"></span>
                            @endif
                        </td>
                        <td>{{ htmlspecialchars($column->title ?? '') }}</td>
                        <td>{{ htmlspecialchars($column->subtitle ?? $column->quote_author ?? '') }}</td>
                        <td>{{ $column->display_order }}</td>
                        <td>
                            @if($column->status === 'published')
                                <span class="badge badge-success">Published</span>
                            @else
                                <span class="badge badge-warning">Draft</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary mr-1 edit-column" 
                                    data-column='@json($column)' title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger"
                                    data-delete-action="{{ route('admin.ministry-columns') }}"
                                    data-delete-payload='{"action":"delete","id":{{ $column->id }}}'
                                    data-title="Delete Column"
                                    data-delete-message="Are you sure you want to delete the column &quot;{{ $column->title ?? 'Untitled' }}&quot;? This action cannot be undone." title="Delete">
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

<!-- Add Column Modal -->
<div class="modal fade" id="addColumnModal" tabindex="-1" role="dialog" aria-labelledby="addColumnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('admin.ministry-columns') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addColumnModalLabel">Add New Column</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="column_type">Column Type</label>
                        <select class="form-control" id="add_column_type" name="column_type" required>
                            <option value="ministry">Ministry</option>
                            <option value="quote">Quote</option>
                        </select>
                    </div>
                    <div id="add_ministry_fields">
                        <div class="form-group">
                            <label for="icon_class">Icon Class</label>
                            <input type="text" class="form-control" id="add_icon_class" name="icon_class" placeholder="e.g. flaticon-church">
                        </div>
                        <div class="form-group">
                            <label for="subtitle">Subtitle</label>
                            <input type="text" class="form-control" id="add_subtitle" name="subtitle">
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div id="add_quote_fields" style="display:none;">
                        <div class="form-group">
                            <label for="quote_author">Quote Author</label>
                            <input type="text" class="form-control" id="add_quote_author" name="quote_author">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_title">Title <small id="add_title_help">(Ministry name or Quote text)</small></label>
                        <input type="text" class="form-control" id="add_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="add_display_order">Display Order</label>
                        <input type="number" class="form-control" id="add_display_order" name="display_order" value="1" min="0">
                    </div>
                    <div class="form-group">
                        <label for="add_status">Status</label>
                        <select class="form-control" id="add_status" name="status">
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Column</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Column Modal -->
<div class="modal fade" id="editColumnModal" tabindex="-1" role="dialog" aria-labelledby="editColumnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('admin.ministry-columns') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editColumnModalLabel">Edit Column</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_column_id">
                    <div class="form-group">
                        <label for="edit_column_type">Column Type</label>
                        <select class="form-control" id="edit_column_type" name="column_type" required>
                            <option value="ministry">Ministry</option>
                            <option value="quote">Quote</option>
                        </select>
                    </div>
                    <div id="edit_ministry_fields">
                        <div class="form-group">
                            <label for="edit_icon_class">Icon Class</label>
                            <input type="text" class="form-control" id="edit_icon_class" name="icon_class" placeholder="e.g. flaticon-church">
                        </div>
                        <div class="form-group">
                            <label for="edit_subtitle">Subtitle</label>
                            <input type="text" class="form-control" id="edit_subtitle" name="subtitle">
                        </div>
                        <div class="form-group">
                            <label for="edit_description">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div id="edit_quote_fields" style="display:none;">
                        <div class="form-group">
                            <label for="edit_quote_author">Quote Author</label>
                            <input type="text" class="form-control" id="edit_quote_author" name="quote_author">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_title">Title <small id="edit_title_help">(Ministry name or Quote text)</small></label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_display_order">Display Order</label>
                        <input type="number" class="form-control" id="edit_display_order" name="display_order" min="0">
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Column</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#columnsTable').DataTable({
        "pageLength": 25,
        "order": [[ 4, "asc" ]]
    });

    // Toggle fields based on column type in Add modal
    $('#add_column_type').change(function() {
        if ($(this).val() === 'quote') {
            $('#add_ministry_fields').hide();
            $('#add_quote_fields').show();
            $('#add_title_help').text('(Quote text)');
        } else {
            $('#add_ministry_fields').show();
            $('#add_quote_fields').hide();
            $('#add_title_help').text('(Ministry name)');
        }
    });

    // Toggle fields based on column type in Edit modal
    $('#edit_column_type').change(function() {
        if ($(this).val() === 'quote') {
            $('#edit_ministry_fields').hide();
            $('#edit_quote_fields').show();
            $('#edit_title_help').text('(Quote text)');
        } else {
            $('#edit_ministry_fields').show();
            $('#edit_quote_fields').hide();
            $('#edit_title_help').text('(Ministry name)');
        }
    });

    $('.edit-column').click(function() {
        var colData = $(this).data('column');
        $('#edit_column_id').val(colData.id);
        $('#edit_column_type').val(colData.column_type);
        $('#edit_icon_class').val(colData.icon_class);
        $('#edit_title').val(colData.title);
        $('#edit_subtitle').val(colData.subtitle);
        $('#edit_description').val(colData.description);
        $('#edit_quote_author').val(colData.quote_author);
        $('#edit_display_order').val(colData.display_order);

        // Trigger type change to show/hide correct fields
        $('#edit_column_type').trigger('change');

        var statusValue = colData.status;
        if (statusValue) {
            var normalizedStatus = statusValue.toString().toLowerCase().trim();
            if (normalizedStatus === 'active' || normalizedStatus === 'published' || normalizedStatus === '1' || normalizedStatus === 'true') {
                statusValue = 'published';
            } else {
                statusValue = 'draft';
            }
        } else {
            statusValue = 'draft';
        }
        $('#edit_status').val(statusValue);

        $('#editColumnModal').modal('show');
    });

});
</script>
@endsection
