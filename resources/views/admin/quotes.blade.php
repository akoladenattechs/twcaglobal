@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Quotes</h1>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addQuoteModal">
        <i class="fas fa-plus"></i> Add New Quote
    </button>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-quote-right mr-2"></i> Quotes List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" id="quotesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Quote</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotes as $quote)
                    <tr>
                        <td>{{ $quote->id }}</td>
                        <td>{{ htmlspecialchars(substr($quote->content, 0, 100)) }}{{ strlen($quote->content) > 100 ? '...' : '' }}</td>
                        <td>{{ $quote->display_order }}</td>
                        <td>
                            @if($quote->status === 'published')
                                <span class="badge badge-success">Published</span>
                            @else
                                <span class="badge badge-secondary">Draft</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary btn-action-edit mr-1" data-toggle="modal" data-target="#editQuoteModal" data-id="{{ $quote->id }}" data-content="{{ htmlspecialchars($quote->content) }}" data-author="{{ htmlspecialchars($quote->author ?? '') }}" data-title="{{ htmlspecialchars($quote->title ?? '') }}" data-image-id="{{ $quote->image_id ?? 0 }}" data-order="{{ $quote->display_order }}" data-status="{{ $quote->status }}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btn-action-delete" title="Delete"
                                data-delete-action="{{ route('admin.quotes') }}"
                                data-delete-payload='{"action":"delete","id":{{ $quote->id }}}'
                                data-delete-message="Are you sure you want to delete this quote?">
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

<!-- Add Quote Modal -->
<div class="modal fade" id="addQuoteModal" tabindex="-1" role="dialog" aria-labelledby="addQuoteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.quotes') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addQuoteModalLabel">Add New Quote</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="quote">Quote Text</label>
                        <textarea class="form-control" id="quote" name="quote" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="author">Author</label>
                        <input type="text" class="form-control" id="author" name="author">
                    </div>
                    <div class="form-group">
                        <label for="position">Position/Title</label>
                        <input type="text" class="form-control" id="position" name="position">
                    </div>
                    <div class="form-group">
                        <label for="image_id">Author Image (Optional)</label>
                        <select class="form-control" id="image_id" name="image_id">
                            <option value="">None</option>
                            @foreach($media_items as $item)
                            <option value="{{ $item->id }}">{{ htmlspecialchars($item->title) }} ({{ htmlspecialchars($item->file_name) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" class="form-control" id="display_order" name="display_order" value="{{ $max_order + 1 }}" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Quote</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Quote Modal -->
<div class="modal fade" id="editQuoteModal" tabindex="-1" role="dialog" aria-labelledby="editQuoteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.quotes') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editQuoteModalLabel">Edit Quote</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label for="edit_quote">Quote Text</label>
                        <textarea class="form-control" id="edit_quote" name="quote" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_author">Author</label>
                        <input type="text" class="form-control" id="edit_author" name="author">
                    </div>
                    <div class="form-group">
                        <label for="edit_position">Position/Title</label>
                        <input type="text" class="form-control" id="edit_position" name="position">
                    </div>
                    <div class="form-group">
                        <label for="edit_image_id">Author Image (Optional)</label>
                        <select class="form-control" id="edit_image_id" name="image_id">
                            <option value="">None</option>
                            @foreach($media_items as $item)
                            <option value="{{ $item->id }}">{{ htmlspecialchars($item->title) }} ({{ htmlspecialchars($item->file_name) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_display_order">Display Order</label>
                        <input type="number" class="form-control" id="edit_display_order" name="display_order" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    const quotesTable = $("#quotesTable").DataTable({
        order: [[2, "asc"]], // Sort by display order (column index 2)
        pageLength: 25,
        responsive: true
    });
    
    // Edit Quote Modal - Set values when edit button is clicked
    const editButtons = document.querySelectorAll(".btn-action-edit");
    editButtons.forEach(button => {
        button.addEventListener("click", function() {
            document.getElementById("edit_id").value = this.getAttribute("data-id");
            document.getElementById("edit_quote").value = this.getAttribute("data-content");
            document.getElementById("edit_author").value = this.getAttribute("data-author") || "";
            document.getElementById("edit_position").value = this.getAttribute("data-title") || "";
            document.getElementById("edit_image_id").value = this.getAttribute("data-image-id") || "";
            document.getElementById("edit_display_order").value = this.getAttribute("data-order");
            document.getElementById("edit_status").value = this.getAttribute("data-status");
        });
    });
});
</script>
@endsection
