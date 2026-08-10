@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Songs</h1>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#songModal">
        <i class="fas fa-plus"></i> Add New Song
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
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-music mr-2"></i> All Songs</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" id="songsTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Audio Files</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Cover</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($songs as $song)
                    <tr>
                        <td>{{ htmlspecialchars($song->title) }}</td>
                        <td>
                            @if($song->songMedia->count() > 0)
                                @foreach($song->songMedia as $sm)
                                    @if($sm->media)
                                        <a href="{{ $sm->media->url }}" target="_blank">{{ htmlspecialchars($sm->media->title) }}</a><br>
                                    @endif
                                @endforeach
                            @else
                                No audio files
                            @endif
                        </td>
                        <td>
                            @if($song->status === 'published')
                                <span class="badge badge-success">Published</span>
                            @else
                                <span class="badge badge-warning">Draft</span>
                            @endif
                        </td>
                        <td>
                            @if($song->featured == 1)
                                <span class="badge badge-success">Featured</span>
                            @else
                                <span class="badge badge-secondary">Not Featured</span>
                            @endif
                        </td>
                        <td>
                            @if($song->media && $song->media->url)
                                <img src="{{ $song->media->url }}" width="60" class="img-thumbnail">
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.songs') }}?action=edit&id={{ $song->id }}" class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                data-delete-action="{{ route('admin.songs') }}"
                                data-delete-payload='{"action":"delete","id":{{ $song->id }}}'
                                data-delete-message="Are you sure you want to delete this song?">
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
<div class="modal fade" id="songModal" tabindex="-1" role="dialog" aria-labelledby="songModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.songs') }}" enctype="multipart/form-data" data-media-type="song" data-upload-url="{{ route('admin.media.upload') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="songModalLabel">{{ $songToEdit ? 'Edit Song' : 'Add New Song' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="{{ $songToEdit ? 'update' : 'add' }}">
                    @if($songToEdit)
                    <input type="hidden" name="song_id" value="{{ $songToEdit->id }}">
                    @endif
                    <input type="hidden" name="image_id" value="{{ $songToEdit ? $songToEdit->image_id : '' }}">

                    <!-- Basic Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle mr-2"></i> Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="title">Song Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required value="{{ $songToEdit ? htmlspecialchars($songToEdit->title) : '' }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="published"{{ $songToEdit && $songToEdit->status === 'published' ? ' selected' : '' }}>Published</option>
                                        <option value="draft"{{ $songToEdit && $songToEdit->status === 'draft' ? ' selected' : '' }}>Draft</option>
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
                                    <input type="file" class="custom-file-input" id="cover_image" name="cover_image" accept="image/*">
                                    <label class="custom-file-label" for="cover_image">Choose image...</label>
                                </div>
                                @if($songToEdit && $songToEdit->media)
                                <div class="mt-2">
                                    <img src="{{ $songToEdit->media->url }}" alt="Current cover" class="img-thumbnail img-thumbnail-sm">
                                </div>
                                @endif
                            </div>

                            <hr class="my-4">

                            <!-- Audio Files & Series/Album -->
                            <div class="form-group">
                                <label class="font-weight-bold">Audio Files & Album</label>
                                <div class="form-row">
                                    <div class="form-group col-md-6 mb-3">
                                        <label for="audio_subfolder" class="small text-muted mb-1">Subfolder / Album Name (Optional)</label>
                                        <input type="text" class="form-control" id="audio_subfolder" name="audio_subfolder" placeholder="e.g. praise-volume-1">
                                        <small class="form-text text-muted">Files will be saved in <code>songs/subfolder-name/</code> in R2.</small>
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

                                @if($songToEdit && $songToEdit->songMedia->count())
                                <div class="mt-2">
                                    <label class="small text-muted mb-1">Current Audio Files</label>
                                    <ul class="list-unstyled mb-1">
                                        @foreach($songToEdit->songMedia as $sm)
                                        <li><i class="fas fa-music mr-1"></i> {{ $sm->media ? $sm->media->title : 'Audio #'.$sm->media_id }}</li>
                                        @endforeach
                                    </ul>
                                    @foreach($songToEdit->songMedia as $sm)
                                    <input type="hidden" name="media_ids[]" value="{{ $sm->media_id }}">
                                    <input type="hidden" name="track_orders[]" value="{{ $sm->track_order }}">
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
                                    <input type="checkbox" class="custom-control-input" id="featured" name="featured"{{ $songToEdit && $songToEdit->featured == 1 ? ' checked' : '' }}>
                                    <label class="custom-control-label" for="featured">Featured Song</label>
                                </div>
                                <small class="form-text text-muted">Featured songs will be highlighted on the homepage.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">{{ $songToEdit ? 'Update' : 'Save' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#songsTable').DataTable({
        "pageLength": 25,
        "order": [[ 0, "asc" ]]
    });
    
    $('#audio_upload').on('change', function(e) {
        var fileName = Array.from(this.files).map(f => f.name).join(', ');
        $(this).next('.custom-file-label').html(fileName || 'Choose audio files...');
    });

    $('#cover_image').on('change', function(e) {
        var fileName = this.files[0] ? this.files[0].name : 'Choose image...';
        $(this).next('.custom-file-label').html(fileName);
    });

    $('#songModal').on('hidden.bs.modal', function() {
        window.location.href = "{{ route('admin.songs') }}";
    });
});
</script>

@if($songToEdit)
<script>
$(function() { $('#songModal').modal('show'); });
</script>
@endif

<script src="{{ asset('js/admin-upload.js') }}"></script>
@endsection
