@extends('layouts.admin')

@section('content')

{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

{{-- Page Header --}}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Newsletter</h1>
    <div class="d-flex gap-2">
        <button class="btn btn-success" data-toggle="modal" data-target="#addSubscriberModal">
            <i class="fas fa-plus mr-1"></i> Add Subscriber
        </button>
        <button class="btn btn-primary" data-toggle="modal" data-target="#sendNewsletterModal">
            <i class="fas fa-paper-plane mr-1"></i> New Campaign
        </button>
    </div>
</div>

{{-- ═══════════════════ TAB NAVIGATION ═══════════════════ --}}
<ul class="nav nav-tabs" id="newsletterTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="tab-subscribers" data-toggle="tab" href="#subscribers" role="tab" aria-controls="subscribers" aria-selected="true">
            <i class="fas fa-users text-primary"></i> Subscribers
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-history" data-toggle="tab" href="#history" role="tab" aria-controls="history" aria-selected="false">
            <i class="fas fa-history"></i> Campaign History
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-drafts" data-toggle="tab" href="#drafts" role="tab" aria-controls="drafts" aria-selected="false">
            <i class="fas fa-edit"></i> Drafts
        </a>
    </li>
</ul>

{{-- ═══════════════════ TAB CONTENT ═══════════════════ --}}
<div class="tab-content mt-3" id="newsletterTabContent">

    {{-- ---------------- TAB 1: SUBSCRIBERS ---------------- --}}
    <div class="tab-pane fade show active" id="subscribers" role="tabpanel" aria-labelledby="tab-subscribers">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-users mr-2"></i> All Subscribers
                    <span class="badge badge-secondary ml-2">{{ $subscribers->count() }}</span>
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered datatable" id="subscribersTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Subscribed Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subscribers as $subscriber)
                            <tr>
                                <td>{{ $subscriber->id }}</td>
                                <td>{{ htmlspecialchars($subscriber->email) }}</td>
                                <td>{{ $subscriber->name ? htmlspecialchars($subscriber->name) : '-' }}</td>
                                <td>
                                    @if($subscriber->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @elseif($subscriber->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($subscriber->status === 'unsubscribed')
                                        <span class="badge badge-secondary">Unsubscribed</span>
                                    @elseif($subscriber->status === 'bounced')
                                        <span class="badge badge-danger">Bounced</span>
                                    @elseif($subscriber->status === 'complaint')
                                        <span class="badge badge-danger">Complaint</span>
                                    @else
                                        <span class="badge badge-dark">{{ ucfirst($subscriber->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $subscriber->subscribed_at ? \Carbon\Carbon::parse($subscriber->subscribed_at)->format('M d, Y') : ($subscriber->created_at ? \Carbon\Carbon::parse($subscriber->created_at)->format('M d, Y') : '-') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                        data-delete-action="{{ route('admin.newsletters') }}"
                                        data-delete-payload='{"action":"delete_subscriber","id":{{ $subscriber->id }}}'
                                        data-delete-message="Are you sure you want to delete this subscriber?">
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

    {{-- ---------------- TAB 2: CAMPAIGN HISTORY ---------------- --}}
    <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="tab-history">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-check-circle mr-2"></i> Sent &amp; Scheduled Campaigns
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered datatable" id="historyTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Sent / Scheduled</th>
                                <th>Sent</th>
                                <th>Opens</th>
                                <th>Clicks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($newsletters->whereIn('status', ['sent','scheduled','sending']) as $newsletter)
                            <tr>
                                <td>{{ $newsletter->id }}</td>
                                <td>{{ htmlspecialchars($newsletter->subject) }}</td>
                                <td>
                                    @if($newsletter->status === 'sent')
                                        <span class="badge badge-success">Sent</span>
                                    @elseif($newsletter->status === 'scheduled')
                                        <span class="badge badge-info">Scheduled</span>
                                    @elseif($newsletter->status === 'sending')
                                        <span class="badge badge-warning">Sending...</span>
                                    @endif
                                </td>
                                <td>
                                    @if($newsletter->scheduled_at && !$newsletter->sent_at)
                                        {{ \Carbon\Carbon::parse($newsletter->scheduled_at)->format('M d, Y g:i A') }}
                                    @elseif($newsletter->sent_at)
                                        {{ \Carbon\Carbon::parse($newsletter->sent_at)->format('M d, Y g:i A') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $newsletter->total_sent ?? '-' }}</td>
                                <td>{{ $newsletter->opens_count ?? 0 }}</td>
                                <td>{{ $newsletter->clicks_count ?? 0 }}</td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-info view-newsletter-btn" title="View"
                                        data-subject="{{ htmlspecialchars($newsletter->subject, ENT_QUOTES) }}"
                                        data-content="{!! htmlspecialchars($newsletter->content, ENT_QUOTES) !!}"
                                        data-status="{{ $newsletter->status }}"
                                        data-sent-at="{{ $newsletter->sent_at ? \Carbon\Carbon::parse($newsletter->sent_at)->format('M d, Y g:i A') : ($newsletter->scheduled_at ? 'Scheduled: ' . \Carbon\Carbon::parse($newsletter->scheduled_at)->format('M d, Y g:i A') : 'Not sent') }}"
                                        data-total-sent="{{ $newsletter->total_sent ?? 0 }}"
                                        data-opens="{{ $newsletter->opens_count ?? 0 }}"
                                        data-clicks="{{ $newsletter->clicks_count ?? 0 }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($newsletter->status !== 'scheduled')
                                    <button type="button" class="btn btn-sm btn-warning text-white" title="Resend Campaign"
                                        data-delete-action="{{ route('admin.newsletters') }}"
                                        data-delete-payload='{"action":"resend_newsletter","id":{{ $newsletter->id }}}'
                                        data-delete-message="Are you sure you want to resend this campaign to all active subscribers?"
                                        data-title="Confirm Resend"
                                        data-btn-text="Resend Campaign"
                                        data-btn-class="btn-warning text-white"
                                        data-icon-class="fas fa-redo">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-danger" title="{{ $newsletter->status === 'scheduled' ? 'Cancel & Delete Schedule' : 'Delete Campaign' }}"
                                        data-delete-action="{{ route('admin.newsletters') }}"
                                        data-delete-payload='{"action":"delete_newsletter","id":{{ $newsletter->id }}}'
                                        data-delete-message="Are you sure you want to delete this campaign?"
                                        data-title="Confirm Deletion"
                                        data-btn-text="Delete"
                                        data-btn-class="btn-danger"
                                        data-icon-class="fas fa-trash">
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

    {{-- ---------------- TAB 3: DRAFTS ---------------- --}}
    <div class="tab-pane fade" id="drafts" role="tabpanel" aria-labelledby="tab-drafts">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-edit mr-2"></i> Draft Campaigns
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered datatable" id="draftsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($newsletters->where('status', 'draft') as $newsletter)
                            <tr>
                                <td>{{ $newsletter->id }}</td>
                                <td>{{ htmlspecialchars($newsletter->subject) }}</td>
                                <td>{{ $newsletter->updated_at ? \Carbon\Carbon::parse($newsletter->updated_at)->format('M d, Y g:i A') : '-' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info view-newsletter-btn" title="View"
                                        data-subject="{{ htmlspecialchars($newsletter->subject, ENT_QUOTES) }}"
                                        data-content="{!! htmlspecialchars($newsletter->content, ENT_QUOTES) !!}"
                                        data-status="draft"
                                        data-sent-at="Draft"
                                        data-total-sent="0"
                                        data-opens="0"
                                        data-clicks="0">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                        data-delete-action="{{ route('admin.newsletters') }}"
                                        data-delete-payload='{"action":"delete_newsletter","id":{{ $newsletter->id }}}'
                                        data-delete-message="Delete this draft?">
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

</div>{{-- /tab-content --}}

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- MODALS --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}

{{-- New Campaign Modal (Send / Schedule / Save Draft / Test Send) --}}
<div class="modal fade" id="sendNewsletterModal" tabindex="-1" role="dialog" aria-labelledby="sendNewsletterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendNewsletterModalLabel">
                    <i class="fas fa-paper-plane mr-1"></i> New Campaign
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- Form wraps the whole modal, action toggled by JS --}}
                <form method="post" action="{{ route('admin.newsletters') }}" id="campaignForm">
                    @csrf
                    <input type="hidden" name="test_email" id="testEmailInput" value="">

                    <div class="form-group">
                        <label for="campaign_status">Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="campaign_status" name="action">
                            <option value="draft"><i class="fas fa-save"></i> Draft</option>
                            <option value="send" selected>Send Now</option>
                            <option value="schedule">Schedule</option>
                            <option value="test_send">Test Send</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Enter newsletter subject" required>
                        <small class="text-muted">Available variables: &#123;&#123;name&#125;&#125;, &#123;&#123;email&#125;&#125;, &#123;&#123;unsubscribe_url&#125;&#125;</small>
                    </div>
                    <div class="form-group">
                        <label for="content">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control summernote" id="content" name="content" rows="10" required></textarea>
                        <small class="text-muted">Available variables: &#123;&#123;name&#125;&#125;, &#123;&#123;email&#125;&#125;, &#123;&#123;unsubscribe_url&#125;&#125;, &#123;&#123;tracking_pixel&#125;&#125;</small>
                    </div>

                    {{-- Schedule picker (shown for schedule action) --}}
                    <div class="form-group" id="schedulePickerGroup" style="display:none;">
                        <label for="scheduled_at">Schedule Date &amp; Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at">
                    </div>

                    {{-- Test email input (shown for test send action) --}}
                    <div class="form-group" id="testEmailGroup" style="display:none;">
                        <label for="test_email">Send to Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="test_email" name="test_email_placeholder" placeholder="Enter test email address">
                        <small class="text-muted">A test newsletter will be sent to this address.</small>
                    </div>

                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="campaignSubmitBtn">
                            <i class="fas fa-paper-plane mr-1"></i> Send Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Add Subscriber Modal --}}
<div class="modal fade" id="addSubscriberModal" tabindex="-1" role="dialog" aria-labelledby="addSubscriberModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.newsletters') }}">
                @csrf
                <input type="hidden" name="action" value="add_subscriber">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSubscriberModalLabel">
                        <i class="fas fa-user-plus mr-1"></i> Add Subscriber
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="subscriber_email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="subscriber_email" name="email" placeholder="Enter email address" required>
                    </div>
                    <div class="form-group">
                        <label for="subscriber_name">Name</label>
                        <input type="text" class="form-control" id="subscriber_name" name="name" placeholder="Enter subscriber name (optional)">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus mr-1"></i> Add
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Newsletter Modal --}}
<div class="modal fade" id="viewNewsletterModal" tabindex="-1" role="dialog" aria-labelledby="viewNewsletterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewNewsletterModalLabel">Campaign Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Subject:</strong>
                    <p id="viewNewsletterSubject" class="mb-0 lead"></p>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Status:</strong>
                        <span id="viewNewsletterStatus" class="badge"></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Date:</strong>
                        <span id="viewNewsletterSentAt"></span>
                    </div>
                    <div class="col-md-2">
                        <strong>Sent:</strong>
                        <span id="viewNewsletterTotalSent"></span>
                    </div>
                    <div class="col-md-2">
                        <strong>Opens:</strong>
                        <span id="viewNewsletterOpens"></span>
                    </div>
                    <div class="col-md-2">
                        <strong>Clicks:</strong>
                        <span id="viewNewsletterClicks"></span>
                    </div>
                </div>
                <hr>
                <strong>Content:</strong>
                <div id="viewNewsletterContent" class="newsletter-content-preview mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTables for subscribers (visible by default)
    $('#subscribersTable').DataTable({
        "pageLength": 25,
        "order": [[ 0, "desc" ]]
    });

    // Lazy init DataTables for history tab (hidden on load)
    var historyTableInitialized = false;
    $('#tab-history').on('shown.bs.tab', function() {
        if (!historyTableInitialized) {
            historyTableInitialized = true;
            setTimeout(function() {
                if (!$.fn.dataTable.isDataTable('#historyTable')) {
                    $('#historyTable').DataTable({
                        "pageLength": 25,
                        "order": [[ 0, "desc" ]],
                        "language": { "emptyTable": "No campaigns sent yet." }
                    });
                }
            }, 400);
        }
    });

    // Lazy init DataTables for drafts tab
    var draftsTableInitialized = false;
    $('#tab-drafts').on('shown.bs.tab', function() {
        if (!draftsTableInitialized) {
            draftsTableInitialized = true;
            setTimeout(function() {
                if (!$.fn.dataTable.isDataTable('#draftsTable')) {
                    $('#draftsTable').DataTable({
                        "pageLength": 25,
                        "order": [[ 0, "desc" ]],
                        "language": { "emptyTable": "No drafts." }
                    });
                }
            }, 400);
        }
    });

    // Helper to upload image files via AJAX
    function uploadNewsletterImage(file, $editor) {
        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('action', 'upload_image');
        formData.append('image', file);

        $.ajax({
            url: '{{ route("admin.newsletters") }}',
            cache: false,
            contentType: false,
            processData: false,
            data: formData,
            type: 'POST',
            success: function(response) {
                if (response.url) {
                    $editor.summernote('insertImage', response.url, function($image) {
                        $image.addClass('img-fluid my-2');
                    });
                } else if (response.error) {
                    alert('Image upload failed: ' + response.error);
                }
            },
            error: function(jqXHR) {
                var msg = 'Error uploading image.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    msg += ' ' + jqXHR.responseJSON.message;
                }
                alert(msg);
            }
        });
    }

    // Prevent Bootstrap modal focus lock from interfering with Summernote dialogs
    $(document).on('show.bs.modal', '.note-modal', function() {
        $('#sendNewsletterModal').removeAttr('tabindex');
    });
    $(document).on('hidden.bs.modal', '.note-modal', function() {
        $('#sendNewsletterModal').attr('tabindex', '-1');
    });

    // Summernote initialization for the campaign modal
    $('#sendNewsletterModal').on('shown.bs.modal', function() {
        if (!$('#content').data('summernote')) {
            $('#content').summernote({
                height: 300,
                dialogsInBody: true,
                dialogsFade: true,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        for (var i = 0; i < files.length; i++) {
                            uploadNewsletterImage(files[i], $(this));
                        }
                    }
                }
            });
        }
    });

    // Destroy Summernote when modal is closed
    $('#sendNewsletterModal').on('hidden.bs.modal', function() {
        if ($('#content').data('summernote')) {
            $('#content').summernote('destroy');
        }
    });

    // Campaign status dropdown switching
    $('#campaign_status').on('change', function() {
        var action = $(this).val();
        var btnLabel = '';

        // Reset all
        $('#schedulePickerGroup').hide();
        $('#testEmailGroup').hide();
        $('#scheduled_at').prop('required', false);
        $('#test_email').prop('required', false);

        if (action === 'send') {
            btnLabel = '<i class="fas fa-paper-plane mr-1"></i> Send Now';
        } else if (action === 'schedule') {
            btnLabel = '<i class="fas fa-clock mr-1"></i> Schedule';
            $('#schedulePickerGroup').show();
            $('#scheduled_at').prop('required', true);
        } else if (action === 'draft') {
            btnLabel = '<i class="fas fa-save mr-1"></i> Save as Draft';
        } else if (action === 'test_send') {
            btnLabel = '<i class="fas fa-vial mr-1"></i> Send Test';
            $('#testEmailGroup').show();
            $('#test_email').prop('required', true);
        }

        $('#campaignSubmitBtn').html(btnLabel);
    });

    // Before form submit: copy test_email value to hidden input if test send
    $('#campaignForm').on('submit', function() {
        var action = $('#campaign_status').val();
        if (action === 'test_send') {
            $('#testEmailInput').val($('#test_email').val());
        }
        // Confirmation for send now
        if (action === 'send') {
            return confirm('Send this newsletter to all active subscribers?');
        }
        return true;
    });

    // View Newsletter content modal
    $('.view-newsletter-btn').on('click', function() {
        var btn = $(this);
        $('#viewNewsletterSubject').text(btn.data('subject'));

        var status = btn.data('status');
        var statusBadge = $('#viewNewsletterStatus');
        var badgeClass = 'badge-secondary';
        var statusText = status;
        if (status === 'sent') { badgeClass = 'badge-success'; statusText = 'Sent'; }
        else if (status === 'scheduled') { badgeClass = 'badge-info'; statusText = 'Scheduled'; }
        else if (status === 'sending') { badgeClass = 'badge-warning'; statusText = 'Sending...'; }
        else if (status === 'draft') { badgeClass = 'badge-warning'; statusText = 'Draft'; }
        statusBadge.removeClass().addClass('badge ' + badgeClass).text(statusText);

        $('#viewNewsletterSentAt').text(btn.data('sent-at'));
        $('#viewNewsletterTotalSent').text(btn.data('total-sent'));
        $('#viewNewsletterOpens').text(btn.data('opens') || '0');
        $('#viewNewsletterClicks').text(btn.data('clicks') || '0');
        $('#viewNewsletterContent').html(btn.data('content'));
        $('#viewNewsletterModal').modal('show');
    });

    // Prevent aria-hidden focus warning when modal is closed
    $('#viewNewsletterModal').on('hidden.bs.modal', function () {
        $(document.activeElement).blur();
    });
});
</script>
@endsection
