@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Events</h1>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#eventModal">
        <i class="fas fa-plus"></i> Add New Event
    </button>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-alt mr-2"></i> All Events</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" id="eventsTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Type</th>
                        <th>Reg.</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                    <tr>
                        <td>
                            @if($event->image)
                                <img src="{{ $event->image }}" alt="{{ $event->title }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ htmlspecialchars($event->title) }}</td>
                        <td>{{ htmlspecialchars($event->location) }}</td>
                        <td>{{ $event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('Y-m-d H:i') : '-' }}</td>
                        <td>{{ $event->end_date ? \Carbon\Carbon::parse($event->end_date)->format('Y-m-d H:i') : 'N/A' }}</td>
                        <td>
                            @if($event->expires)
                                <span class="badge badge-secondary">Expires</span>
                            @else
                                @php
                                    $shortDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                    $rDays = $event->recurrence_days ?? [];
                                @endphp
                                @if(count($rDays) > 0)
                                    <span class="badge badge-info">{{ implode(', ', array_map(fn($d) => $shortDays[$d], $rDays)) }}</span>
                                @else
                                    <span class="badge badge-info">Loops (Weekly)</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($event->requires_registration)
                                <span class="badge badge-success"><i class="fas fa-check"></i></span>
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-times"></i></span>
                            @endif
                        </td>
                        <td>
                            @if($event->status === 'published')
                                <span class="badge badge-success">Published</span>
                            @else
                                <span class="badge badge-warning">Draft</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.events') }}?action=edit&id={{ $event->id }}" class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                data-delete-action="{{ route('admin.events') }}"
                                data-delete-payload='{"action":"delete","id":{{ $event->id }}}'
                                data-delete-message="Are you sure you want to delete this event?">
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
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true" data-auto-show="{{ $eventToEdit ? '1' : '0' }}">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.events') }}" enctype="multipart/form-data" data-media-type="event" data-upload-url="{{ route('admin.media.upload') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">{{ $eventToEdit ? 'Edit Event' : 'Add New Event' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="{{ $eventToEdit ? 'edit' : 'add' }}">
                    <input type="hidden" name="image_id" value="">
                    @if($eventToEdit)
                    <input type="hidden" name="id" value="{{ $eventToEdit->id }}">
                    @endif

                    <!-- Basic Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle mr-2"></i> Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="title">Event Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ $eventToEdit ? htmlspecialchars($eventToEdit->title) : '' }}" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4">{{ $eventToEdit ? htmlspecialchars($eventToEdit->description) : '' }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" class="form-control" id="location" name="location" value="{{ $eventToEdit ? htmlspecialchars($eventToEdit->location) : '' }}" required>
                            </div>
                            <div class="form-group">
                                <label for="image">Event Image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="image" name="image_file" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <label class="custom-file-label" for="image">Choose image...</label>
                                </div>
                                <small class="form-text text-muted">Recommended size: 800×600px. Max 2MB. JPEG, PNG, GIF, or WebP.</small>
                                <x-upload-progress />
                                @if($eventToEdit && $eventToEdit->image)
                                    <div class="mt-2">
                                        <img src="{{ $eventToEdit->image }}" alt="Current image" style="max-height: 100px; border-radius: 4px;">
                                        <p class="small text-muted mt-1">Current image. Upload a new one to replace it.</p>
                                    </div>
                                @endif
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $eventToEdit && $eventToEdit->start_date ? \Carbon\Carbon::parse($eventToEdit->start_date)->format('Y-m-d') : '' }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="start_time">Start Time</label>
                                    <input type="time" class="form-control" id="start_time" name="start_time" value="{{ $eventToEdit && $eventToEdit->start_date ? \Carbon\Carbon::parse($eventToEdit->start_date)->format('H:i') : '' }}">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $eventToEdit && $eventToEdit->end_date ? \Carbon\Carbon::parse($eventToEdit->end_date)->format('Y-m-d') : '' }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="end_time">End Time</label>
                                    <input type="time" class="form-control" id="end_time" name="end_time" value="{{ $eventToEdit && $eventToEdit->end_date ? \Carbon\Carbon::parse($eventToEdit->end_date)->format('H:i') : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="draft"{{ $eventToEdit && $eventToEdit->status === 'draft' ? ' selected' : '' }}>Draft</option>
                                    <option value="published"{{ $eventToEdit && $eventToEdit->status === 'published' ? ' selected' : '' }}>Published</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="expires" name="expires" value="1"{{ ($eventToEdit ? $eventToEdit->expires : true) ? ' checked' : '' }}>
                                    <label class="custom-control-label" for="expires">This event expires after its end date</label>
                                </div>
                                <small class="form-text text-muted">
                                    If ticked, the event stops showing on the frontend after its date passes.
                                    If unticked, the event <strong>loops weekly</strong> on the selected day(s) until deleted.
                                </small>
                            </div>
                            <div class="form-group" id="recurrenceDaysWrap" style="{{ ($eventToEdit && !$eventToEdit->expires) ? '' : 'display:none;' }}">
                                <label><strong>Repeats on</strong></label>
                                <div class="d-flex flex-wrap">
                                    @php
                                        $days = [
                                            ['value' => 0, 'label' => 'Sun'],
                                            ['value' => 1, 'label' => 'Mon'],
                                            ['value' => 2, 'label' => 'Tue'],
                                            ['value' => 3, 'label' => 'Wed'],
                                            ['value' => 4, 'label' => 'Thu'],
                                            ['value' => 5, 'label' => 'Fri'],
                                            ['value' => 6, 'label' => 'Sat'],
                                        ];
                                        $selected = $eventToEdit && is_array($eventToEdit->recurrence_days) ? $eventToEdit->recurrence_days : [];
                                    @endphp
                                    @foreach($days as $day)
                                    <div class="custom-control custom-checkbox mr-3 mb-1">
                                        <input type="checkbox" class="custom-control-input recurrence-day" id="recDay{{ $day['value'] }}" name="recurrence_days[]" value="{{ $day['value'] }}"{{ in_array($day['value'], $selected) ? ' checked' : '' }}>
                                        <label class="custom-control-label" for="recDay{{ $day['value'] }}">{{ $day['label'] }}</label>
                                    </div>
                                    @endforeach
                                </div>
                                <small class="form-text text-muted">
                                    Select the specific days of the week this event occurs. The frontend will always show the next upcoming date for the selected day(s).
                                </small>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="requires_registration" name="requires_registration" value="1"{{ ($eventToEdit && $eventToEdit->requires_registration) ? ' checked' : '' }}>
                                    <label class="custom-control-label" for="requires_registration">Requires registration</label>
                                </div>
                                <small class="form-text text-muted">
                                    If ticked, the "Register for this Event" button will appear on the frontend event page.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Form Fields -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-clipboard-list mr-2"></i> Registration Form Fields</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addFieldBtn">
                                <i class="fas fa-plus"></i> Add Field
                            </button>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">
                                These are extra questions shown on the public registration page for this event.
                                The base fields (name, email, phone, address, city, state, country, church, first-time)
                                are always present. Use "Required" to force an answer.
                            </p>
                            <div id="fieldsContainer"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">{{ $eventToEdit ? 'Update' : 'Save' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#eventsTable').DataTable({
        "pageLength": 25,
        "order": [[ 3, "desc" ]]
    });

    // Reset form when modal is hidden
    $('#eventModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $(this).find('.upload-progress-wrap').addClass('d-none');
        $('.custom-file-label').text('Choose image...');
        window.location.href = "{{ route('admin.events') }}";
    });

    // Show selected file name in custom file input
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').text(fileName || 'Choose image...');
    });

    // ── Toggle recurrence days when expires checkbox changes ──
    function toggleRecurrenceDays() {
        if ($('#expires').is(':checked')) {
            $('#recurrenceDaysWrap').hide();
            // Uncheck all day checkboxes when hiding
            $('.recurrence-day').prop('checked', false);
        } else {
            $('#recurrenceDaysWrap').show();
        }
    }
    $('#expires').on('change', toggleRecurrenceDays);
    toggleRecurrenceDays();

    // ── Dynamic registration fields editor ──────────────────────────────
    var fieldTypes = [
        { value: 'text', label: 'Text' },
        { value: 'textarea', label: 'Textarea' },
        { value: 'email', label: 'Email' },
        { value: 'phone', label: 'Phone' },
        { value: 'number', label: 'Number' },
        { value: 'date', label: 'Date' },
        { value: 'select', label: 'Dropdown (Select)' },
        { value: 'radio', label: 'Radio buttons' },
        { value: 'checkbox', label: 'Checkbox' }
    ];

    function fieldTypeOptions(selected) {
        return fieldTypes.map(function(t) {
            return '<option value="' + t.value + '"' + (t.value === selected ? ' selected' : '') + '>' + t.label + '</option>';
        }).join('');
    }

    function fieldRowHtml(field) {
        field = field || {};
        var label = field.label || '';
        var type = field.type || 'text';
        var options = field.options || '';
        var required = field.required ? ' checked' : '';
        return '' +
            '<div class="field-row border rounded p-3 mb-3 bg-light">' +
                '<div class="form-row">' +
                    '<div class="form-group col-md-5 mb-2">' +
                        '<label class="small text-muted">Field Label</label>' +
                        '<input type="text" class="form-control form-control-sm field-label" name="fields[__IDX__][label]" value="' + $('<div>').text(label).html() + '" placeholder="e.g. Dietary preference">' +
                    '</div>' +
                    '<div class="form-group col-md-3 mb-2">' +
                        '<label class="small text-muted">Type</label>' +
                        '<select class="form-control form-control-sm field-type" name="fields[__IDX__][field_type]">' + fieldTypeOptions(type) + '</select>' +
                    '</div>' +
                    '<div class="form-group col-md-2 mb-2">' +
                        '<label class="small text-muted">Required</label>' +
                        '<div class="custom-control custom-checkbox mt-1">' +
                            '<input type="checkbox" class="custom-control-input field-required" id="fieldReq__IDX__" name="fields[__IDX__][is_required]" value="1"' + required + '>' +
                            '<label class="custom-control-label" for="fieldReq__IDX__"></label>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-md-2 mb-2 d-flex align-items-end">' +
                        '<button type="button" class="btn btn-sm btn-outline-danger remove-field-btn"><i class="fas fa-trash"></i></button>' +
                    '</div>' +
                '</div>' +
                '<div class="form-group mb-0 field-options-wrap" style="' + (['select','radio','checkbox'].indexOf(type) === -1 ? 'display:none;' : '') + '">' +
                    '<label class="small text-muted">Options (one per line)</label>' +
                    '<textarea class="form-control form-control-sm field-options" name="fields[__IDX__][options]" rows="2" placeholder="Option 1&#10;Option 2">' + $('<div>').text(options).html() + '</textarea>' +
                '</div>' +
            '</div>';
    }

    function addFieldRow(field) {
        var idx = $('#fieldsContainer .field-row').length;
        var html = fieldRowHtml(field).split('__IDX__').join(idx);
        $('#fieldsContainer').append(html);
    }

    // Add field button
    $('#addFieldBtn').on('click', function() {
        addFieldRow();
    });

    // Remove field (event delegation)
    $('#fieldsContainer').on('click', '.remove-field-btn', function() {
        $(this).closest('.field-row').remove();
        // Re-index names so the server receives a clean 0..n sequence
        $('#fieldsContainer .field-row').each(function(i) {
            $(this).find('[name^="fields["]').each(function() {
                var name = $(this).attr('name').replace(/fields\[\d+\]/, 'fields[' + i + ']');
                $(this).attr('name', name);
            });
            $(this).find('.field-required').attr('id', 'fieldReq' + i);
            $(this).find('label[for^="fieldReq"]').attr('for', 'fieldReq' + i);
        });
    });

    // Toggle options textarea based on field type
    $('#fieldsContainer').on('change', '.field-type', function() {
        var show = ['select','radio','checkbox'].indexOf($(this).val()) !== -1;
        $(this).closest('.field-row').find('.field-options-wrap').toggle(show);
    });

    // Pre-populate fields when editing an existing event
    @if($eventToEdit && $eventToEdit->registrationFields->isNotEmpty())
        @foreach($eventToEdit->registrationFields as $rf)
            addFieldRow({
                label: @json($rf->label),
                type: @json($rf->field_type),
                options: @json(is_array($rf->options) ? implode("\n", $rf->options) : ''),
                required: {{ $rf->is_required ? 'true' : 'false' }}
            });
        @endforeach
    @endif

    // Show modal automatically if we're in edit mode
    if ($('#eventModal').data('auto-show') == 1) {
        $('#eventModal').modal('show');
    }
});
</script>
<script src="{{ asset('js/admin-upload.js') }}"></script>
@endsection
