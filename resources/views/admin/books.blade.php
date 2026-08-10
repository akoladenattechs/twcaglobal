@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Books</h1>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#bookModal">
        <i class="fas fa-plus"></i> Add New Book
    </button>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-book mr-2"></i> All Books</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" id="booksTable">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>E-Book</th>
                        <th>Price (₦)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($books as $book)
                    <tr>
                        <td>
                            @if($book->media && $book->media->url)
                                <img src="{{ $book->media->url }}" width="50" height="50" class="img-thumbnail img-cover">
                            @else
                                <span class="text-muted">No image</span>
                            @endif
                        </td>
                        <td>{{ htmlspecialchars($book->title) }}</td>
                        <td>{{ htmlspecialchars($book->author) }}</td>
                        <td>
                            @if($book->pdf_file)
                                <a href="{{ $book->pdf_file }}" target="_blank" class="text-danger" title="Download PDF">
                                    <i class="fas fa-file-pdf fa-lg"></i>
                                </a>
                            @else
                                <span class="text-muted">&mdash;</span>
                            @endif
                        </td>
                        <td>₦{{ number_format($book->price, 2) }}</td>
                        <td>
                            @if($book->status === 'published')
                                <span class="badge badge-success">Published</span>
                            @else
                                <span class="badge badge-secondary">Draft</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.books') }}?action=edit&id={{ $book->id }}" class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                data-delete-action="{{ route('admin.books') }}"
                                data-delete-payload='{"action":"delete","id":{{ $book->id }}}'
                                data-delete-message="Are you sure you want to delete this book?">
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
<div class="modal fade" id="bookModal" tabindex="-1" role="dialog" aria-labelledby="bookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.books') }}" enctype="multipart/form-data" data-upload-url="{{ route('admin.media.upload') }}" data-media-type="book">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="bookModalLabel">{{ $bookToEdit ? 'Edit Book' : 'Add New Book' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="{{ $bookToEdit ? 'update' : 'add' }}">
                    @if($bookToEdit)
                    <input type="hidden" name="book_id" value="{{ $bookToEdit->id }}">
                    @endif

                    <!-- Basic Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle mr-2"></i> Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="title">Book Title</label>
                                    <input type="text" class="form-control" id="title" name="title" value="{{ $bookToEdit ? htmlspecialchars($bookToEdit->title) : '' }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="author">Author</label>
                                    <input type="text" class="form-control" id="author" name="author" value="{{ $bookToEdit ? htmlspecialchars($bookToEdit->author) : '' }}" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4">{{ $bookToEdit ? htmlspecialchars($bookToEdit->description) : '' }}</textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="price">Price (₦)</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{ $bookToEdit ? $bookToEdit->price : '' }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="published"{{ $bookToEdit && $bookToEdit->status === 'published' ? ' selected' : '' }}>Published</option>
                                        <option value="draft"{{ $bookToEdit && $bookToEdit->status === 'draft' ? ' selected' : '' }}>Draft</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Media & E-Book Content -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-photo-video mr-2"></i> Media &amp; E-Book Content</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="font-weight-bold">Book Cover Image &amp; E-Book PDF</label>
                                <div class="form-row">
                                    <div class="form-group col-md-6 mb-3">
                                        <label class="small text-muted mb-1">Book Cover Image</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="image_upload" name="image_file" accept="image/*">
                                            <label class="custom-file-label" for="image_upload">Choose image...</label>
                                        </div>
                                        <input type="hidden" id="uploaded_image_id" name="image_id" value="{{ $bookToEdit ? $bookToEdit->image_id : '' }}">
                                        <input type="hidden" id="uploaded_pdf_url" name="pdf_url" value="{{ $bookToEdit && $bookToEdit->pdf_file ? $bookToEdit->pdf_file : '' }}">
                                        @if($bookToEdit && $bookToEdit->media)
                                        <div class="mt-2">
                                            <img src="{{ $bookToEdit->media->url }}" alt="Current cover" class="img-thumbnail img-thumbnail-sm">
                                        </div>
                                        @endif
                                    </div>
                                    <div class="form-group col-md-6 mb-3">
                                        <label class="small text-muted mb-1">E-Book PDF File</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="pdf_upload" name="pdf_file" accept="application/pdf">
                                            <label class="custom-file-label" for="pdf_upload">Choose PDF...</label>
                                        </div>
                                        <small class="form-text text-muted">Stored in <code>books/</code> folder in R2.</small>
                                        @if($bookToEdit && $bookToEdit->pdf_file)
                                        <div class="mt-2">
                                            <a href="{{ $bookToEdit->pdf_file }}" target="_blank" class="text-danger">
                                                <i class="fas fa-file-pdf mr-1"></i> View current PDF
                                            </a>
                                            <input type="hidden" name="existing_pdf_file" value="{{ $bookToEdit->pdf_file }}">
                                        </div>
                                        @endif
                                        <div class="mt-3">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="allow_pdf_download" name="allow_pdf_download" value="1"{{ $bookToEdit && $bookToEdit->allow_pdf_download ? ' checked' : '' }}>
                                                <label class="custom-control-label" for="allow_pdf_download">Allow PDF download on frontend</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Upload progress indicator (two-step upload flow) -->
                                <x-upload-progress />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">{{ $bookToEdit ? 'Update' : 'Save' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editModalData" data-show-modal="{{ $bookToEdit ? 'true' : 'false' }}" style="display:none;"></div>

<script src="{{ asset('js/admin-upload.js') }}"></script>
<script>
$(document).ready(function() {
    $('#booksTable').DataTable({
        "pageLength": 25,
        "order": [[ 1, "asc" ]]
    });
    
    // Update file input labels
    $('#image_upload').on('change', function(e) {
        var fileName = this.files[0] ? this.files[0].name : 'Choose image...';
        $(this).next('.custom-file-label').html(fileName);
    });

    $('#pdf_upload').on('change', function(e) {
        var fileName = this.files[0] ? this.files[0].name : 'Choose PDF...';
        $(this).next('.custom-file-label').html(fileName);
    });
    
    // Reset form when modal is hidden
    $('#bookModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $('.custom-file-label').html('Choose image...');
        $('#pdf_upload').next('.custom-file-label').html('Choose PDF...');
        window.location.href = "{{ route('admin.books') }}";
    });

    // Show modal if editing
    if (document.getElementById('editModalData').getAttribute('data-show-modal') === 'true') {
        $('#bookModal').modal('show');
    }
});
</script>
@endsection
