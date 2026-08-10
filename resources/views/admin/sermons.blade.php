@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Teachings</h1>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#sermonModal">
        <i class="fas fa-plus"></i> Add New Teaching
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

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-microphone mr-2"></i> All Teachings</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" id="sermonsTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Preacher</th>
                        <th>Date</th>
                        <th>Audio Files</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Cover</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sermons as $sermon)
                    <tr>
                        <td>{{ htmlspecialchars($sermon->title) }}</td>
                        <td>{{ htmlspecialchars($sermon->preacher) }}</td>
                        <td>{{ $sermon->sermon_date ? \Carbon\Carbon::parse($sermon->sermon_date)->format('M d, Y') : '-' }}</td>
                        <td>
                            @if($sermon->audioMedia->count() > 0)
                                @foreach($sermon->audioMedia as $audio)
                                    <a href="{{ $audio->url }}" target="_blank">{{ htmlspecialchars($audio->title) }}</a><br>
                                @endforeach
                            @else
                                No audio files
                            @endif
                        </td>
                        <td>
                            @if($sermon->status === 'published')
                                <span class="badge badge-success">Published</span>
                            @else
                                <span class="badge badge-warning">Draft</span>
                            @endif
                        </td>
                        <td>
                            @if($sermon->featured == 1)
                                <span class="badge badge-success">Featured</span>
                            @else
                                <span class="badge badge-secondary">Not Featured</span>
                            @endif
                        </td>
                        <td>
                            @if($sermon->media && $sermon->media->url)
                                <img src="{{ $sermon->media->url }}" width="60" class="img-thumbnail">
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.sermons') }}?action=edit&id={{ $sermon->id }}" class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                data-delete-action="{{ route('admin.sermons') }}"
                                data-delete-payload='{"action":"delete","id":{{ $sermon->id }}}'
                                data-delete-message="Are you sure you want to delete this teaching?">
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
<div class="modal fade" id="sermonModal" tabindex="-1" role="dialog" aria-labelledby="sermonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.sermons') }}" enctype="multipart/form-data" data-media-type="sermon" data-upload-url="{{ route('admin.media.upload') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="sermonModalLabel">{{ $sermonToEdit ? 'Edit Teaching' : 'Add New Teaching' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="{{ $sermonToEdit ? 'update' : 'add' }}">
                    @if($sermonToEdit)
                    <input type="hidden" name="sermon_id" value="{{ $sermonToEdit->id }}">
                    @endif
                    <input type="hidden" name="image_id" value="{{ $sermonToEdit ? $sermonToEdit->image_id : '' }}">

                    <!-- Basic Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle mr-2"></i> Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="title">Sermon Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required value="{{ $sermonToEdit ? htmlspecialchars($sermonToEdit->title) : '' }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="preacher">Preacher</label>
                                    <input type="text" class="form-control" id="preacher" name="preacher" value="{{ $sermonToEdit ? htmlspecialchars($sermonToEdit->preacher) : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3">{{ $sermonToEdit ? htmlspecialchars($sermonToEdit->description) : '' }}</textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="sermon_date">Sermon Date</label>
                                    <input type="date" class="form-control" id="sermon_date" name="sermon_date" value="{{ $sermonToEdit ? $sermonToEdit->sermon_date : '' }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="published"{{ $sermonToEdit && $sermonToEdit->status === 'published' ? ' selected' : '' }}>Published</option>
                                        <option value="draft"{{ $sermonToEdit && $sermonToEdit->status === 'draft' ? ' selected' : '' }}>Draft</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Media Content -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-photo-video mr-2"></i> Media Content</h6>
                        </div>
                        <div class="card-body">
                            <!-- Cover Image -->
                            <div class="form-group mb-4">
                                <label class="font-weight-bold">Cover Image</label>
                                <div class="custom-file mb-2">
                                    <input type="file" class="custom-file-input" id="image_upload" name="cover_image" accept="image/*">
                                    <label class="custom-file-label" for="image_upload">Choose image...</label>
                                </div>
                                @if($sermonToEdit && $sermonToEdit->media)
                                <div class="mt-2">
                                    <img src="{{ $sermonToEdit->media->url }}" alt="Current cover" class="img-thumbnail img-thumbnail-sm">
                                </div>
                                @endif
                            </div>

                            <hr class="my-4">

                            <!-- Audio Files & Series -->
                            <div class="form-group">
                                <label class="font-weight-bold">Audio Files & Series</label>
                                <div class="form-row">
                                    <div class="form-group col-md-6 mb-3">
                                        <label for="audio_subfolder" class="small text-muted mb-1">Subfolder / Series Name (Optional)</label>
                                        <input type="text" class="form-control" id="audio_subfolder" name="audio_subfolder" placeholder="e.g. faith-series-1">
                                        <small class="form-text text-muted">Files will be saved in <code>sermons/subfolder-name/</code> in R2.</small>
                                    </div>
                                    <div class="form-group col-md-6 mb-3">
                                        <label class="small text-muted mb-1">Upload Audio Files</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="audio_upload" name="audio_files[]" accept="audio/*" multiple>
                                            <label class="custom-file-label" for="audio_upload">Choose audio files...</label>
                                        </div>
                                        <small class="form-text text-muted">Select single or multiple audio files.</small>
                                    </div>
                                </div>

                                @if($sermonToEdit && $sermonToEdit->audioMedia->count())
                                <div class="mt-2">
                                    <label class="small text-muted mb-1">Current Audio Files</label>
                                    <ul class="list-unstyled mb-1">
                                        @foreach($sermonToEdit->audioMedia as $audio)
                                        <li><i class="fas fa-music mr-1"></i> {{ $audio->title }}</li>
                                        @endforeach
                                    </ul>
                                    @foreach($sermonToEdit->audioMedia as $audio)
                                    <input type="hidden" name="media_ids[]" value="{{ $audio->id }}">
                                    <input type="hidden" name="track_orders[]" value="{{ $audio->pivot->track_order ?? 0 }}">
                                    @endforeach
                                </div>
                                @endif

                                <!-- Upload progress indicator (two-step upload flow) -->
                                <x-upload-progress />
                            </div>
                        </div>
                    </div>

                    <!-- Additional Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-cog mr-2"></i> Additional Settings</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="featured" name="featured"{{ $sermonToEdit && $sermonToEdit->featured == 1 ? ' checked' : '' }}>
                                    <label class="custom-control-label" for="featured">Featured Sermon</label>
                                </div>
                                <small class="form-text text-muted">Featured sermons will be highlighted on the homepage.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">{{ $sermonToEdit ? 'Update' : 'Save' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<span id="sermons-data" style="display:none;">{"showModal":{{ $sermonToEdit ? 'true' : 'false' }}}</span>

<script>
$(document).ready(function() {
    $('#sermonsTable').DataTable({
        "pageLength": 25,
        "order": [[ 2, "desc" ]]
    });

    // Update file input labels
    $('#audio_upload').on('change', function(e) {
        var fileName = Array.from(this.files).map(f => f.name).join(', ');
        $(this).next('.custom-file-label').html(fileName || 'Choose audio files...');
    });

    $('#image_upload').on('change', function(e) {
        var fileName = this.value.split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Choose image...');
    });

    // Reset form when modal is hidden
    $('#sermonModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $('.custom-file-label').html('Choose files...');
        window.location.href = "{{ route('admin.sermons') }}";
    });

    // Show modal automatically if we're in edit mode
    var sermonsData = JSON.parse(document.getElementById('sermons-data').textContent);
    if (sermonsData.showModal) {
        $('#sermonModal').modal('show');
    }
});
</script>

<script src="{{ asset('js/admin-upload.js') }}"></script>
@endsection
