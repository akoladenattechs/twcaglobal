@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage About Us</h1>
    <div>
        <button type="button" class="btn btn-primary tab-btn" data-tab="about-sections" data-toggle="modal" data-target="#addAboutModal">
            <i class="fas fa-plus"></i> Add Section
        </button>
        <button type="button" class="btn btn-primary ml-2 tab-btn d-none" data-tab="locations" data-toggle="modal" data-target="#addLocationModal">
            <i class="fas fa-plus"></i> Add Location
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

<ul class="nav nav-tabs" id="aboutTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="about-sections-tab" data-toggle="tab" href="#about-sections" role="tab">
            <i class="fas fa-info-circle mr-1"></i> About Us Sections
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="locations-tab" data-toggle="tab" href="#locations" role="tab">
            <i class="fas fa-map-marker-alt mr-1"></i> Our Centers Locations
        </a>
    </li>
</ul>

<div class="tab-content mt-3" id="aboutTabContent">

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- ABOUT US SECTIONS --}}
    {{-- ════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="about-sections" role="tabpanel">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle mr-2"></i> All About Us Sections</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered datatable" id="aboutTable">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Subtitle</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($aboutSections as $section)
                            <tr>
                                <td>
                                    @php
                                        $typeLabels = [
                                            'mission' => 'Mission',
                                            'vision' => 'Vision',
                                            'values' => 'Values',
                                            'quote' => 'Quote',
                                            'custom' => 'Custom',
                                        ];
                                        $typeBadges = [
                                            'mission' => 'badge-primary',
                                            'vision' => 'badge-success',
                                            'values' => 'badge-info',
                                            'quote' => 'badge-warning',
                                            'custom' => 'badge-secondary',
                                        ];
                                        $type = $section->section_type ?? 'custom';
                                    @endphp
                                    <span class="badge {{ $typeBadges[$type] ?? 'badge-secondary' }}">
                                        {{ $typeLabels[$type] ?? ucfirst($type) }}
                                    </span>
                                </td>
                                <td>{{ htmlspecialchars($section->title ?? '') }}</td>
                                <td>{{ htmlspecialchars($section->subtitle ?? '') }}</td>
                                <td>{{ $section->display_order }}</td>
                                <td>
                                    @if($section->status === 'published')
                                        <span class="badge badge-success">Published</span>
                                    @else
                                        <span class="badge badge-warning">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary mr-1 edit-about"
                                            data-target="#editAboutModal"
                                            data-section='@json($section)' title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                            data-delete-action="{{ route('admin.about') }}"
                                            data-delete-payload='{"action":"delete_about","id":{{ $section->id }}}'
                                            data-title="Delete Section"
                                            data-delete-message="Are you sure you want to delete the section &quot;{{ $section->title ?? 'Untitled' }}&quot;? This action cannot be undone." title="Delete">
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

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- CENTER LOCATIONS --}}
    {{-- ════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="locations" role="tabpanel">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-map-marker-alt mr-2"></i> All Center Locations</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered datatable" id="locationsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Phone</th>
                                <th>Service Times</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($locations as $location)
                            <tr>
                                <td>{{ htmlspecialchars($location->name ?? '') }}</td>
                                <td>{{ htmlspecialchars($location->address ?? '') }}</td>
                                <td>{{ htmlspecialchars($location->phone ?? '') }}</td>
                                <td>{{ htmlspecialchars($location->service_times ?? '') }}</td>
                                <td>{{ $location->display_order }}</td>
                                <td>
                                    @if($location->status === 'published')
                                        <span class="badge badge-success">Published</span>
                                    @else
                                        <span class="badge badge-warning">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary mr-1 edit-location"
                                            data-target="#editLocationModal"
                                            data-location='@json($location)' title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                            data-delete-action="{{ route('admin.about') }}"
                                            data-delete-payload='{"action":"delete_location","id":{{ $location->id }}}'
                                            data-title="Delete Location"
                                            data-delete-message="Are you sure you want to delete the location &quot;{{ $location->name ?? 'Untitled' }}&quot;? This action cannot be undone." title="Delete">
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

</div>{{-- /.tab-content --}}

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- ADD ABOUT US SECTION MODAL --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="addAboutModal" tabindex="-1" role="dialog" aria-labelledby="addAboutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('admin.about') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="action" value="add_about">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAboutModalLabel">Add About Us Section</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_section_type">Section Type</label>
                                <select class="form-control" id="add_section_type" name="section_type">
                                    <option value="custom">Custom</option>
                                    <option value="mission">Mission</option>
                                    <option value="vision">Vision</option>
                                    <option value="values">Values</option>
                                    <option value="quote">Quote</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="add_title">Title <small class="text-muted">(Required)</small></label>
                                <input type="text" class="form-control" id="add_title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="add_subtitle">Subtitle</label>
                                <input type="text" class="form-control" id="add_subtitle" name="subtitle">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" id="add_quote_author_group">
                                <label for="add_quote_author">Quote Author</label>
                                <input type="text" class="form-control" id="add_quote_author" name="quote_author" placeholder="e.g. ~ Author's Name">
                            </div>
                            <div class="form-group">
                                <label for="add_display_order">Display Order</label>
                                <input type="number" class="form-control" id="add_display_order" name="display_order" value="0" min="0">
                            </div>
                            <div class="form-group">
                                <label for="add_status">Status</label>
                                <select class="form-control" id="add_status" name="status">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_content">Content <small class="text-muted">(Description / Quote text)</small></label>
                        <textarea class="form-control summernote" id="add_content" name="content" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Section</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- EDIT ABOUT US SECTION MODAL --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="editAboutModal" tabindex="-1" role="dialog" aria-labelledby="editAboutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('admin.about') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="action" value="edit_about">
            <input type="hidden" name="id" id="edit_about_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAboutModalLabel">Edit About Us Section</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Section Type</label>
                                <select class="form-control" id="edit_section_type" name="section_type">
                                    <option value="custom">Custom</option>
                                    <option value="mission">Mission</option>
                                    <option value="vision">Vision</option>
                                    <option value="values">Values</option>
                                    <option value="quote">Quote</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_title">Title <small class="text-muted">(Required)</small></label>
                                <input type="text" class="form-control" id="edit_title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_subtitle">Subtitle</label>
                                <input type="text" class="form-control" id="edit_subtitle" name="subtitle">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" id="edit_quote_author_group">
                                <label for="edit_quote_author">Quote Author</label>
                                <input type="text" class="form-control" id="edit_quote_author" name="quote_author" placeholder="e.g. ~ Author's Name">
                            </div>
                            <div class="form-group">
                                <label for="edit_display_order">Display Order</label>
                                <input type="number" class="form-control" id="edit_display_order" name="display_order" min="0">
                            </div>
                            <div class="form-group">
                                <label for="edit_status">Status</label>
                                <select class="form-control" id="edit_status" name="status">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_content">Content</label>
                        <textarea class="form-control summernote" id="edit_content" name="content" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Section</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- ADD LOCATION MODAL --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="addLocationModal" tabindex="-1" role="dialog" aria-labelledby="addLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('admin.about') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="action" value="add_location">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addLocationModalLabel">Add Center Location</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_location_name">Name <small class="text-muted">(Required)</small></label>
                                <input type="text" class="form-control" id="add_location_name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="add_location_address">Address</label>
                                <textarea class="form-control" id="add_location_address" name="address" rows="2"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="add_location_phone">Phone</label>
                                <input type="text" class="form-control" id="add_location_phone" name="phone">
                            </div>
                            <div class="form-group">
                                <label for="add_location_email">Email</label>
                                <input type="email" class="form-control" id="add_location_email" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add_location_service_times">Service Times</label>
                                <textarea class="form-control" id="add_location_service_times" name="service_times" rows="2" placeholder="e.g. Sunday: 9:00 AM Worship&#10;Wednesday: 6:30 PM Bible Study"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="add_location_description">Description</label>
                                <textarea class="form-control" id="add_location_description" name="description" rows="2"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="add_location_display_order">Display Order</label>
                                <input type="number" class="form-control" id="add_location_display_order" name="display_order" value="0" min="0">
                            </div>
                            <div class="form-group">
                                <label for="add_location_status">Status</label>
                                <select class="form-control" id="add_location_status" name="status">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Location</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- EDIT LOCATION MODAL --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="editLocationModal" tabindex="-1" role="dialog" aria-labelledby="editLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('admin.about') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="action" value="edit_location">
            <input type="hidden" name="id" id="edit_location_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLocationModalLabel">Edit Center Location</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_location_name">Name</label>
                                <input type="text" class="form-control" id="edit_location_name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_location_address">Address</label>
                                <textarea class="form-control" id="edit_location_address" name="address" rows="2"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="edit_location_phone">Phone</label>
                                <input type="text" class="form-control" id="edit_location_phone" name="phone">
                            </div>
                            <div class="form-group">
                                <label for="edit_location_email">Email</label>
                                <input type="email" class="form-control" id="edit_location_email" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_location_service_times">Service Times</label>
                                <textarea class="form-control" id="edit_location_service_times" name="service_times" rows="2"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="edit_location_description">Description</label>
                                <textarea class="form-control" id="edit_location_description" name="description" rows="2"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="edit_location_display_order">Display Order</label>
                                <input type="number" class="form-control" id="edit_location_display_order" name="display_order" min="0">
                            </div>
                            <div class="form-group">
                                <label for="edit_location_status">Status</label>
                                <select class="form-control" id="edit_location_status" name="status">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Location</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // ── Toggle tab buttons based on active tab ──
    $('#aboutTabs').on('shown.bs.tab', function (e) {
        var targetId = $(e.target).attr('href').replace('#', '');
        $('.tab-btn').addClass('d-none');
        $('.tab-btn[data-tab="' + targetId + '"]').removeClass('d-none');
    });

    // Init DataTables
    $('#aboutTable').DataTable({
        pageLength: 25,
        order: [[3, 'asc']],
        language: { emptyTable: 'No about sections yet. Click "Add Section" to create one.' }
    });
    $('#locationsTable').DataTable({
        pageLength: 25,
        order: [[4, 'asc']],
        language: { emptyTable: 'No locations yet. Click "Add Location" to create one.' }
    });

    // Init Summernote
    $('.summernote').summernote({
        height: 200,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['codeview']]
        ]
    });

    // Custom file input label
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });

    // ── ABOUT US: Show/hide quote author field ──
    function toggleQuoteAuthor(addOrEdit) {
        var type = $('#' + addOrEdit + '_section_type').val();
        if (type === 'quote') {
            $('#' + addOrEdit + '_quote_author_group').show();
        } else {
            $('#' + addOrEdit + '_quote_author_group').hide();
        }
    }
    $('#add_section_type').change(function() { toggleQuoteAuthor('add'); });
    $('#edit_section_type').change(function() { toggleQuoteAuthor('edit'); });
    toggleQuoteAuthor('add');

    // ── ABOUT US: Edit button ──
    $('.edit-about').click(function() {
        var data = $(this).data('section');
        $('#edit_about_id').val(data.id);
        $('#edit_section_type').val(data.section_type || 'custom');
        $('#edit_title').val(data.title || '');
        $('#edit_subtitle').val(data.subtitle || '');
        $('#edit_quote_author').val(data.quote_author || '');
        $('#edit_display_order').val(data.display_order || 0);
        $('#edit_status').val(data.status || 'draft');
        $('#edit_section_type').trigger('change');

        // Set Summernote content
        if ($('#edit_content').summernote()) {
            $('#edit_content').summernote('code', data.content || '');
        }

        $('#editAboutModal').modal('show');
    });

    // ── LOCATION: Edit button ──
    $('.edit-location').click(function() {
        var data = $(this).data('location');
        $('#edit_location_id').val(data.id);
        $('#edit_location_name').val(data.name || '');
        $('#edit_location_address').val(data.address || '');
        $('#edit_location_phone').val(data.phone || '');
        $('#edit_location_email').val(data.email || '');
        $('#edit_location_service_times').val(data.service_times || '');
        $('#edit_location_description').val(data.description || '');
        $('#edit_location_display_order').val(data.display_order || 0);
        $('#edit_location_status').val(data.status || 'draft');

        $('#editLocationModal').modal('show');
    });

});
</script>
@endsection
