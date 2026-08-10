@extends('layouts.admin')

@section('styles')
@endsection

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Hero Section</h1>
    <div>
        <button type="button" class="btn btn-primary tab-btn" data-tab="hero-sliders" data-toggle="modal" data-target="#addSliderModal">
            <i class="fas fa-plus"></i> Add New Slide
        </button>
        <button type="button" class="btn btn-primary tab-btn ml-2 d-none" data-tab="hero-settings" id="saveSettingsBtn">
            <i class="fas fa-save"></i> Save Settings
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

{{-- ═══════════════════ TAB NAVIGATION ═══════════════════ --}}
<ul class="nav nav-tabs" id="heroTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="hero-sliders-tab" data-toggle="tab" href="#hero-sliders" role="tab" aria-controls="hero-sliders" aria-selected="true">
            <i class="fas fa-sliders-h mr-1"></i> Hero Sliders
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="hero-settings-tab" data-toggle="tab" href="#hero-settings" role="tab" aria-controls="hero-settings" aria-selected="false">
            <i class="fas fa-cog mr-1"></i> Hero Text Settings
        </a>
    </li>
</ul>

{{-- ═══════════════════ TAB CONTENT ═══════════════════ --}}
<div class="tab-content mt-3" id="heroTabContent">

    {{-- ──────────────── TAB 1: HERO SLIDERS ──────────────── --}}
    <div class="tab-pane fade show active" id="hero-sliders" role="tabpanel" aria-labelledby="hero-sliders-tab">
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
    </div>

    {{-- ──────────────── TAB 2: HERO TEXT SETTINGS ──────────────── --}}
    <div class="tab-pane fade" id="hero-settings" role="tabpanel" aria-labelledby="hero-settings-tab">
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-edit mr-2"></i> Edit Hero Section Texts</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.hero-settings') }}" id="heroSettingsForm">
                            @csrf

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-tag mr-2"></i> Badge</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="badge_text">Badge Text</label>
                                        <input type="text" class="form-control" id="badge_text" name="badge_text" value="{{ old('badge_text', $settings->badge_text) }}" placeholder="e.g. Worship With Us">
                                        <small class="form-text text-muted">Shown in the glass badge above the title. Falls back to the slider subtitle if empty.</small>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="show_badge" name="show_badge" value="1" {{ $settings->show_badge ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_badge">Show Badge</label>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-heading mr-2"></i> Title Area</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="prefix_text">Prefix Text <small class="text-muted">(before the title)</small></label>
                                        <input type="text" class="form-control" id="prefix_text" name="prefix_text" value="{{ old('prefix_text', $settings->prefix_text) }}" placeholder="e.g. Welcome to">
                                        <small class="form-text text-muted">Displayed as a gradient before the main title. Leave empty to hide.</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="title">Main Title <small class="text-muted">(the bold center title)</small></label>
                                        <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $settings->title) }}" placeholder="e.g. Welcome to Our Ministry">
                                        <small class="form-text text-muted">This is the main hero title displayed in bold.</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="suffix_text">Suffix Text <small class="text-muted">(after the title)</small></label>
                                        <input type="text" class="form-control" id="suffix_text" name="suffix_text" value="{{ old('suffix_text', $settings->suffix_text) }}" placeholder="e.g. Ministries">
                                        <small class="form-text text-muted">Displayed in italic after the main title. Leave empty to hide.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-align-left mr-2"></i> Description</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="description">Description Text</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="e.g. Join us for an inspiring time of worship...">{{ old('description', $settings->description) }}</textarea>
                                        <small class="form-text text-muted">The paragraph shown below the title.</small>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="show_description" name="show_description" value="1" {{ $settings->show_description ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_description">Show Description</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Button Card --}}
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-arrow-right mr-2"></i> Call-to-Action Button</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="button_text">Button Text</label>
                                        <input type="text" class="form-control" id="button_text" name="button_text" value="{{ old('button_text', $settings->button_text) }}" placeholder="e.g. Learn More">
                                        <small class="form-text text-muted">The label shown on the hero button.</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="button_link">Button Link</label>
                                        <input type="text" class="form-control" id="button_link" name="button_link" value="{{ old('button_link', $settings->button_link) }}" placeholder="e.g. /about or https://example.com">
                                        <small class="form-text text-muted">Full URL or relative path the button points to.</small>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="show_button" name="show_button" value="1" {{ $settings->show_button ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_button">Show Button</label>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle mr-2"></i> How It Works</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Hero Title Structure:</strong></p>
                        <div class="bg-light p-3 rounded mb-3">
                            <code class="d-block">[Prefix] <strong>[Title]</strong> <em>[Suffix]</em></code>
                            <hr class="my-2">
                            <small class="text-muted">Example: "Welcome to <strong>Our</strong> <em>Ministry</em>"</small>
                        </div>

                        <p><strong>Where texts come from:</strong></p>
                        <ul class="mb-0">
                            <li><strong>All text content</strong> — Set on this page</li>
                            <li><strong>Background</strong> — From the slider (image or video)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{--  MODALS (from Hero Sliders)                                   --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}

{{-- Add Slider Modal --}}
<div class="modal fade" id="addSliderModal" tabindex="-1" role="dialog" aria-labelledby="addSliderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('admin.hero-section') }}" enctype="multipart/form-data" data-upload-url="{{ route('admin.media.upload') }}" data-media-type="hero">
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
                        <!-- Upload progress indicator (two-step upload flow) -->
                        <x-upload-progress />
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

{{-- Edit Slider Modal --}}
<div class="modal fade" id="editSliderModal" tabindex="-1" role="dialog" aria-labelledby="editSliderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('admin.hero-section') }}" enctype="multipart/form-data" data-upload-url="{{ route('admin.media.upload') }}" data-media-type="hero">
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
                        <!-- Upload progress indicator (two-step upload flow) -->
                        <x-upload-progress />
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

@endsection

@section('scripts')
<script src="{{ asset('js/admin-upload.js') }}"></script>
<script>
$(document).ready(function() {

    // ── Toggle tab buttons based on active tab ──
    $('#heroTabs').on('shown.bs.tab', function (e) {
        var targetId = $(e.target).attr('href').replace('#', '');
        $('.tab-btn').addClass('d-none');
        $('.tab-btn[data-tab="' + targetId + '"]').removeClass('d-none');
    });

    // ── Save Settings button in title bar triggers form submission ──
    $('#saveSettingsBtn').on('click', function() {
        $('#heroSettingsForm').submit();
    });

    // ── Init DataTables ──
    $('#slidersTable').DataTable({
        "order": [[2, "asc"]]
    });

    // ── Edit Slider ──
    $('.edit-slider').click(function() {
        var sliderData = $(this).data('slider');
        var imageUrl = $(this).data('image-url');

        // Reset any leftover upload state from a previous edit session
        $('#editSliderModal form input[name="image_id"]').remove();
        $('#edit_image_file').val('');
        resetUploadProgress('#editSliderModal');

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


    // ── Reset upload state when modals close (add/edit share the page DOM) ──
    $('#addSliderModal').on('hidden.bs.modal', function() {
        $(this).find('form input[name="image_id"]').remove();
        $('#image_file').val('');
        resetUploadProgress('#addSliderModal');
    });
    $('#editSliderModal').on('hidden.bs.modal', function() {
        $(this).find('form input[name="image_id"]').remove();
        $('#edit_image_file').val('');
        resetUploadProgress('#editSliderModal');
    });

    // Hide the progress bar and re-enable the submit button for a given modal
    function resetUploadProgress(modalSelector) {
        var $wrap = $(modalSelector).find('.upload-progress-wrap');
        $wrap.addClass('d-none');
        $wrap.find('.upload-progress-bar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
        $(modalSelector).find('button[type="submit"]').prop('disabled', false);
    }
});
</script>
@endsection