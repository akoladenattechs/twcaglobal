@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Daily Devotionals</h1>
    <div>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#devotionalModal" id="addDevotionalBtn">
            <i class="fas fa-plus"></i> Add New Devotional
        </button>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-book-open mr-2"></i> All Devotionals</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" id="devotionalsTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Scripture</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devotionals as $devotional)
                    <tr>
                        <td>{{ $devotional->devotional_date ? \Carbon\Carbon::parse($devotional->devotional_date)->format('M d, Y') : '-' }}</td>
                        <td>{{ htmlspecialchars($devotional->title) }}</td>
                        <td>{{ htmlspecialchars($devotional->scripture_reference) }}</td>
                        <td>
                            @if($devotional->status === 'published')
                            <span class="badge badge-success">Published</span>
                            @else
                            <span class="badge badge-secondary">Draft</span>
                            @endif
                        </td>
                        <td>{{ number_format($devotional->views_count) }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.devotionals') }}?action=edit&id={{ $devotional->id }}" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('devotionals.show', $devotional->slug) }}" target="_blank" class="btn btn-sm btn-info" title="Preview">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                    data-delete-action="{{ route('admin.devotionals') }}"
                                    data-delete-payload='{"action":"delete","id":{{ $devotional->id }}}'
                                    data-delete-message="Are you sure you want to delete this devotional?">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Devotional Modal -->
<div class="modal fade" id="devotionalModal" tabindex="-1" role="dialog" aria-labelledby="devotionalModalLabel" aria-hidden="true" data-route="{{ route('admin.devotionals') }}" data-edit-mode="{{ $devotionalToEdit ? 'true' : 'false' }}">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.devotionals') }}" enctype="multipart/form-data" id="devotionalForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="devotionalModalLabel">{{ $devotionalToEdit ? 'Edit Devotional' : 'Add New Devotional' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="devotional_id" id="devotionalId" value="{{ $devotionalToEdit ? $devotionalToEdit->id : '' }}">
                    <input type="hidden" name="action" id="formAction" value="{{ $devotionalToEdit ? 'update' : 'add' }}">
                    
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required value="{{ $devotionalToEdit ? htmlspecialchars($devotionalToEdit->title) : '' }}">
                    </div>
                    
                    <div class="form-group">
                        <label for="devotional_date">Date</label>
                        <input type="date" class="form-control" id="devotional_date" name="devotional_date" required value="{{ $devotionalToEdit ? $devotionalToEdit->devotional_date : '' }}">
                    </div>
                    
                    <div class="form-group">
                        <label for="scripture_reference">Scripture Reference</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="scripture_reference" name="scripture_reference" value="{{ $devotionalToEdit ? htmlspecialchars($devotionalToEdit->scripture_reference) : '' }}" placeholder="e.g., John 3:16, Psalm 23:1-3">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" id="fetchScriptureBtn">
                                    <i class="fas fa-bible"></i> Fetch
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">Type a Bible reference and click "Fetch" to automatically retrieve the verse text</small>
                    </div>
                    
                    <div class="form-group" id="scriptureTextGroup" @if(!($devotionalToEdit && $devotionalToEdit->scripture_text)) style="display:none;" @endif>
                        <label>Scripture Text</label>
                        <input type="hidden" name="scripture_text" id="scripture_text" value="{{ $devotionalToEdit ? htmlspecialchars($devotionalToEdit->scripture_text) : '' }}">
                        <div id="scriptureTextDisplay" class="p-3 bg-light rounded border scripture-text-display">
                            {{ $devotionalToEdit ? htmlspecialchars($devotionalToEdit->scripture_text) : '' }}
                        </div>
                        <small class="form-text text-muted">The verse text will be fetched automatically when you provide a reference</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Main Devotional Content</label>
                        <textarea class="form-control summernote" id="content" name="content" rows="10" required>{{ $devotionalToEdit ? $devotionalToEdit->content : '' }}</textarea>
                        <small class="form-text text-muted">Enter the main devotional content with rich text formatting</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="prayer">Confession</label>
                        <textarea class="form-control" id="prayer" name="prayer" rows="4">{{ $devotionalToEdit ? htmlspecialchars($devotionalToEdit->prayer) : '' }}</textarea>
                        <small class="form-text text-muted">Enter a confession related to this devotional</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="reflection_questions">Further Studies</label>
                        <textarea class="form-control" id="reflection_questions" name="reflection_questions" rows="3">{{ $devotionalToEdit ? htmlspecialchars($devotionalToEdit->reflection_questions) : '' }}</textarea>
                        <small class="form-text text-muted">Enter further study notes or references for readers</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="draft" {{ $devotionalToEdit && $devotionalToEdit->status === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ $devotionalToEdit && $devotionalToEdit->status === 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">{{ $devotionalToEdit ? 'Update' : 'Save' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var devotionalsTable = $('#devotionalsTable').DataTable({
        "order": [[ 0, "desc" ]],
        "pageLength": 25
    });
    
    // Initialize Summernote for rich text editing
    function initializeSummernote() {
        $('.summernote').summernote({
            height: 300,
            minHeight: null,
            maxHeight: null,
            focus: false,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'italic', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    }
    
    // Initialize Summernote ONLY when modal is shown (avoids hidden-iframe layout bug)
    $('#devotionalModal').on('shown.bs.modal', function () {
        var $summernote = $('.summernote');
        if ($summernote.next('.note-editor').length === 0) {
            initializeSummernote();
        }
    });
    
    // Clean up when modal is hidden
    $('#devotionalModal').on('hidden.bs.modal', function () {
        $('.summernote').each(function() {
            if ($(this).next('.note-editor').length > 0) {
                $(this).summernote('destroy');
            }
        });
        // Navigate back to clean URL (remove query params) after modal closes
        var cleanUrl = $(this).data('route');
        if (window.location.href.indexOf('?') > -1) {
            window.location.href = cleanUrl;
        }
    });
    
    // Fetch scripture text from Bible API
    $('#fetchScriptureBtn').on('click', function() {
        var ref = $('#scripture_reference').val().trim();
        if (!ref) {
            alert('Please enter a scripture reference first.');
            return;
        }
        
        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Fetching...').prop('disabled', true);
        
        var apiUrl = 'https://bible-api.com/' + encodeURIComponent(ref) + '?translation=kjv';
        
        fetch(apiUrl)
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.error) {
                    alert('Scripture not found. Please check the reference.');
                    return;
                }
                
                // Build the full text from verses
                var fullText = '';
                if (data.verses && data.verses.length > 0) {
                    data.verses.forEach(function(v) {
                        fullText += v.text + ' ';
                    });
                    fullText = fullText.trim();
                } else if (data.text) {
                    fullText = data.text;
                }
                
                // Populate hidden input and display
                $('#scripture_text').val(fullText);
                $('#scriptureTextDisplay').text(fullText);
                $('#scriptureTextGroup').show();
            })
            .catch(function() {
                alert('Error loading scripture. Please check your connection and try again.');
            })
            .finally(function() {
                $btn.html(originalHtml).prop('disabled', false);
            });
    });
    
    // Reset form when adding new devotional
    $('#addDevotionalBtn').click(function() {
        $('#devotionalForm')[0].reset();
        // Reset Summernote content
        var $summernote = $('.summernote');
        if ($summernote.next('.note-editor').length > 0) {
            $summernote.summernote('code', '');
        }
        $('#devotionalId').val('');
        $('#formAction').val('add');
        $('#devotionalModalLabel').text('Add New Devotional');
    });
    
    // Show modal automatically if we're in edit mode
    if ($('#devotionalModal').data('edit-mode')) {
        $('#devotionalModal').modal('show');
    }
    
    });
</script>
@endsection
