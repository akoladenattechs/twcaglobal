@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Contact Messages</h1>
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

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-inbox mr-2"></i> Inbox</h6>
        <span class="badge badge-primary badge-pill">{{ $messages->count() }} Messages</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" id="messagesTable">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $msg)
                    <tr class="{{ $msg->status === 'unread' ? 'table-info' : '' }}">
                        <td>
                            @if($msg->status === 'unread')
                            <span class="badge badge-info">Unread</span>
                            @elseif($msg->status === 'replied')
                            <span class="badge badge-success">Replied</span>
                            @else
                            <span class="badge badge-secondary">Read</span>
                            @endif
                        </td>
                        <td>{{ htmlspecialchars($msg->name) }}</td>
                        <td><a href="mailto:{{ htmlspecialchars($msg->email) }}">{{ htmlspecialchars($msg->email) }}</a></td>
                        <td>{{ htmlspecialchars($msg->subject) }}</td>
                        <td>{{ $msg->created_at ? \Carbon\Carbon::parse($msg->created_at)->format('Y-m-d H:i') : '-' }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary view-message-btn" title="{{ $msg->status === 'unread' ? 'Mark Read' : ($msg->status === 'replied' ? 'View Message' : 'Mark Unread') }}"
                                data-id="{{ $msg->id }}"
                                data-name="{{ htmlspecialchars($msg->name, ENT_QUOTES) }}"
                                data-email="{{ htmlspecialchars($msg->email, ENT_QUOTES) }}"
                                data-subject="{{ htmlspecialchars($msg->subject, ENT_QUOTES) }}"
                                data-message="{{ htmlspecialchars($msg->message, ENT_QUOTES) }}"
                                data-date="{{ $msg->created_at ? \Carbon\Carbon::parse($msg->created_at)->format('Y-m-d H:i') : '-' }}"
                                data-status="{{ $msg->status }}"
                                data-reply-subject="Re: {{ htmlspecialchars($msg->subject, ENT_QUOTES) }}"
                                data-replies='{!! json_encode($msg->replies->map(function ($r) { return ["subject" => $r->reply_subject, "message" => $r->reply_message, "sent_at" => $r->sent_at ? \Carbon\Carbon::parse($r->sent_at)->format("Y-m-d H:i") : ""]; })->values()->toArray(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}'>
                                <i class="fas fa-{{ $msg->status === 'unread' ? 'envelope-open' : 'envelope' }}"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                data-delete-action="{{ route('admin.inbox') }}"
                                data-delete-payload='{"action":"delete","id":{{ $msg->id }}}'
                                data-delete-message="Are you sure you want to delete this message?">
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

<script>
$(document).ready(function() {
    $('#messagesTable').DataTable({
        "pageLength": 25,
        "order": [[ 4, "desc" ]]
    });

    // View Message modal - click toggles read/unread, opens modal, and marks replied as read
    $('.view-message-btn').on('click', function() {
        var btn = $(this);
        var msgId = btn.data('id');
        var currentStatus = btn.data('status');
        var row = btn.closest('tr');
        var csrfToken = $('input[name="_token"]').first().val();

        $('#viewMessageModalLabel').text(btn.data('subject'));
        $('#viewMessageFrom').text(btn.data('name') + ' (' + btn.data('email') + ')');
        $('#viewMessageDate').text(btn.data('date'));
        $('#viewMessageBody').text(btn.data('message'));

        // Render reply history
        var repliesContainer = $('#repliesContainer');
        var repliesHeading = $('#repliesHeading');
        repliesContainer.empty();
        var replies = JSON.parse(btn.attr('data-replies') || '[]');
        if (replies && replies.length > 0) {
            repliesHeading.show();
            repliesContainer.show();
            $.each(replies, function(idx, reply) {
                var replyHtml = '<div class="card mb-2 border-left-primary">' +
                    '<div class="card-header py-2 d-flex justify-content-between align-items-center">' +
                    '<span class="font-weight-bold text-primary"><i class="fas fa-reply"></i> ' + $('<span>').text(reply.subject).html() + '</span>' +
                    '<small class="text-muted">' + $('<span>').text(reply.sent_at).html() + '</small>' +
                    '</div>' +
                    '<div class="card-body py-2">' +
                    '<p class="mb-0 message-body-text">' + $('<span>').text(reply.message).html() + '</p>' +
                    '</div>' +
                    '</div>';
                repliesContainer.append(replyHtml);
            });
        } else {
            repliesHeading.hide();
            repliesContainer.hide();
        }
        // Store data for reply modal
        $('#replyMessageId').val(msgId);
        $('#replyToName').text(btn.data('name'));
        $('#replyToEmail').text(btn.data('email'));
        $('#replySubject').val(btn.data('reply-subject'));
        $('#replyMessage').val('');

        if (currentStatus === 'unread') {
            // Mark as read via AJAX
            $.ajax({
                url: '{{ route("admin.inbox.mark-read") }}',
                method: 'POST',
                data: {
                    id: msgId,
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        updateMessageStatus(btn, row, 'read');
                    }
                }
            });
        } else if (currentStatus === 'read') {
            // Mark as unread via AJAX
            $.ajax({
                url: '{{ route("admin.inbox.mark-read") }}',
                method: 'POST',
                data: {
                    id: msgId,
                    action: 'mark_unread',
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        updateMessageStatus(btn, row, 'unread');
                    }
                }
            });
        }

        // Show current status in modal (will be updated by AJAX callback if needed)
        if (currentStatus === 'unread') {
            $('#viewMessageStatus').text('Unread');
        } else if (currentStatus === 'replied') {
            $('#viewMessageStatus').text('Replied');
        } else {
            $('#viewMessageStatus').text('Read');
        }

        $('#viewMessageModal').modal('show');
    });

    // Helper to update UI after status change
    function updateMessageStatus(btn, row, newStatus) {
        var iconEl = btn.find('i');
        var badge = row.find('.badge');

        btn.attr('data-status', newStatus);
        btn.data('status', newStatus);

        if (newStatus === 'read') {
            iconEl.removeClass('fa-envelope-open').addClass('fa-envelope');
            badge.removeClass('badge-info').addClass('badge-secondary').text('Read');
            row.removeClass('table-info');
            btn.attr('title', 'Mark Unread');
        } else {
            iconEl.removeClass('fa-envelope').addClass('fa-envelope-open');
            badge.removeClass('badge-secondary').addClass('badge-info').text('Unread');
            row.addClass('table-info');
            btn.attr('title', 'Mark Read');
        }
        // Update modal status if visible
        var modalStatus = $('#viewMessageStatus');
        if (modalStatus.length && $('#viewMessageModal').hasClass('show')) {
            modalStatus.text(newStatus === 'unread' ? 'Unread' : 'Read');
        }
    }

    // Open reply modal from view modal
    $('#openReplyModal').on('click', function() {
        $('#viewMessageModal').modal('hide');
        $('#composeReplyModal').modal('show');
    });

    // When reply modal closes, go back to view modal if needed
    $('#composeReplyModal').on('hidden.bs.modal', function() {
        // Just clean up on hide
    });
});
</script>

<!-- View Message Modal -->
<div class="modal fade" id="viewMessageModal" tabindex="-1" role="dialog" aria-labelledby="viewMessageModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewMessageModalLabel">Message</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-2">From:</dt>
                    <dd class="col-sm-10" id="viewMessageFrom"></dd>
                    <dt class="col-sm-2">Date:</dt>
                    <dd class="col-sm-10" id="viewMessageDate"></dd>
                    <dt class="col-sm-2">Status:</dt>
                    <dd class="col-sm-10" id="viewMessageStatus"></dd>
                    <dt class="col-sm-12">Message:</dt>
                    <dd class="col-sm-12">
                        <div class="p-3 bg-light rounded message-body-text" id="viewMessageBody"></div>
                    </dd>
                    <dt class="col-sm-12" id="repliesHeading" style="display:none;">
                        <hr>
                        <h6><i class="fas fa-reply-all"></i> Reply History</h6>
                    </dt>
                    <dd class="col-sm-12" id="repliesContainer" style="display:none;"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="openReplyModal">
                    <i class="fas fa-reply"></i> Reply
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Compose Reply Modal -->
<div class="modal fade" id="composeReplyModal" tabindex="-1" role="dialog" aria-labelledby="composeReplyModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <form method="post" action="{{ route('admin.inbox') }}">
            @csrf
            <input type="hidden" name="id" id="replyMessageId" value="">
            <input type="hidden" name="action" value="reply">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="composeReplyModalLabel">Compose Reply</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>To:</label>
                        <p class="form-control-static font-weight-bold" id="replyToName"></p>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <p class="form-control-static" id="replyToEmail"></p>
                    </div>
                    <div class="form-group">
                        <label for="replySubject">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="reply_subject" id="replySubject" required>
                    </div>
                    <div class="form-group">
                        <label for="replyMessage">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reply_message" id="replyMessage" rows="8" required placeholder="Type your reply here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
