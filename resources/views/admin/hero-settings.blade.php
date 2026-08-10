@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Hero Text Settings</h1>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-edit mr-2"></i> Edit Hero Section Texts</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.hero-settings') }}">
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







                    <div class="form-group text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-save mr-2"></i> Save Hero Settings
                        </button>
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
@endsection
