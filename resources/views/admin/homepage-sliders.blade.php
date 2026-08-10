@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Hero Sliders</h1>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSliderModal">
        <i class="fas fa-plus"></i> Add New Slide
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
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-sliders-h mr-2"></i> Hero Sliders</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" id="slidersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Media</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sliders as $slider)
                    <tr>
                        <td>{{ $slider->id }}</td>
                        <td>
                            @if($slider->media && $slider->media->url)
                                <img src="{{ $slider->media->url }}" width="80" class="img-thumbnail mr-1">
                            @endif
                            @if($slider->videoMedia || $slider->video_url)
                                <span class="badge badge-info"><i class="fas fa-video mr-1"></i>Video</span>
                            @endif
                            @if(!$slider->media && !$slider->videoMedia && !$slider->video_url)
                                No media
                            @endif
                        </td>
                        <td>{{ $slider->display_order }}</td>
                        <td>
                            @if($slider->status === 'published')
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary mr-1 edit-slider" 
                                    data-slider='{!! json_encode($slider, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}'
                                    data-image-url="{{ $slider->media ? $slider->media->url : '' }}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger"
                                    data-delete-action="{{ route('admin.hero-section') }}"
                                    data-delete-payload='{"action":"delete","id":{{ $slider->id }}}'
                                    data-title="Delete Slide"
                                    data-delete-message="Are you sure you want to delete this slide #{{ $slider->id }}? This action cannot be undone." title="Delete">
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

<!-- Add Slider Modal -->
<div class="modal fade" id="addSliderModal" tabindex="-1" role="dialog" aria-labelledby="addSliderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('admin.hero-section') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSliderModalLabel">Add New Slide</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="image_file">Background Image</label>
                        <input type="file" class="form-control-file" id="image_file" name="image_file" accept="image/*">
                        <small class="form-text text-muted">Upload a new image for this slide. Recommended size: 1920x1080px.</small>
                    </div>
                    <div class="form-group">
                        <label for="video_id">Background Video (overrides image)</label>
                        <select class="form-control" id="video_id" name="video_id">
                            <option value="">No Video</option>
                            @foreach($video_media_items as $media)
                            <option value="{{ $media->id }}">{{ $media->title }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Upload videos in the Media Library</small>
                    </div>
                    <div class="form-group">
                        <label for="video_url">Or External Video URL (YouTube, Vimeo, etc.)</label>
                        <input type="url" class="form-control" id="video_url" name="video_url" placeholder="https://www.youtube.com/watch?v=...">
                        <small class="form-text text-muted">If set, this takes priority over the selected video above.</small>
                    </div>
                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" class="form-control" id="display_order" name="display_order" value="1" min="1">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="published">Active</option>
                            <option value="draft">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Slider</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Slider Modal -->
<div class="modal fade" id="editSliderModal" tabindex="-1" role="dialog" aria-labelledby="editSliderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('admin.hero-section') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSliderModalLabel">Edit Slide</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_slider_id">
                    <div class="form-group">
                        <label>Current Background Image</label>
                        <div id="edit_current_image_preview" class="mb-2"></div>
                        <label for="edit_image_file">Replace Image (leave empty to keep current)</label>
                        <input type="file" class="form-control-file" id="edit_image_file" name="image_file" accept="image/*">
                        <small class="form-text text-muted">Upload a new image to replace the current one.</small>
                    </div>
                    <div class="form-group">
                        <label for="edit_video_id">Background Video (overrides image)</label>
                        <select class="form-control" id="edit_video_id" name="video_id">
                            <option value="">No Video</option>
                            @foreach($video_media_items as $media)
                            <option value="{{ $media->id }}">{{ $media->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_video_url">Or External Video URL</label>
                        <input type="url" class="form-control" id="edit_video_url" name="video_url" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <div class="form-group">
                        <label for="edit_display_order">Display Order</label>
                        <input type="number" class="form-control" id="edit_display_order" name="display_order" min="1">
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="published">Active</option>
                            <option value="draft">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Slider</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#slidersTable').DataTable({
        "order": [[2, "asc"]]
    });

    $('.edit-slider').click(function() {
        var sliderData = $(this).data('slider');
        var imageUrl = $(this).data('image-url');
        $('#edit_slider_id').val(sliderData.id);
        $('#edit_video_id').val(sliderData.video_id);
        $('#edit_video_url').val(sliderData.video_url);
        $('#edit_display_order').val(sliderData.display_order);
        
        // Show current image preview
        if (imageUrl) {
            $('#edit_current_image_preview').html('<img src="' + imageUrl + '" width="200" class="img-thumbnail">');
        } else {
            $('#edit_current_image_preview').html('<p class="text-muted mb-0">No image currently set.</p>');
        }
        
        var statusValue = sliderData.status;
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
        
        $('#editSliderModal').modal('show');
    });

});
</script>
@endsection
