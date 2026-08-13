@extends('layouts.app')

@section('content')
<section class="hero-wrap hero-wrap-2 js-fullheight" @if(!empty($headerBgUrl)) style="background-image: url('{{ $headerBgUrl }}');" @endif>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <p class="breadcrumbs mb-2">
                    <span class="mr-2"><a href="{{ url('/') }}">Home <i class="fa fa-chevron-right"></i></a></span>
                    <span><a href="{{ route('sermons') }}">Teachings <i class="fa fa-chevron-right"></i></a></span>
                    <span>{{ $sermon->title }}</span>
                </p>
                <h1 class="mb-0 bread">{{ $sermon->title }}</h1>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <div class="row">
            <!-- Cover Image -->
            <div class="col-md-5 mb-4">
                <div class="sermon-detail-image shadow-sm rounded overflow-hidden">
                    @if(!empty($sermon->media) && !empty($sermon->media->url))
                        <img src="{{ $sermon->media->url }}" alt="{{ $sermon->title }}" class="img-fluid w-100">
                    @else
                        <div class="bg-light p-5 text-center text-muted">
                            <i class="fa fa-microphone fa-5x mb-3"></i>
                            <p>No cover image available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sermon Details -->
            <div class="col-md-7">
                <div class="sermon-detail-content">
                    <div class="d-flex align-items-center mb-3">
                        @if(!empty($sermon->sermon_date))
                            <span class="badge badge-primary mr-2">{{ date('M d, Y', strtotime($sermon->sermon_date)) }}</span>
                        @endif
                        @if(!empty($sermon->featured))
                            <span class="badge badge-success">Featured</span>
                        @endif
                    </div>

                    <h2 class="mb-3">{{ $sermon->title }}</h2>

                    @if(!empty($sermon->preacher))
                        <p class="text-muted mb-4"><strong>Preacher:</strong> {{ $sermon->preacher }}</p>
                    @endif

                    <!-- Audio Files -->
                    <div class="audio-files mb-4">
                        <h5 class="mb-3">Stream & Download</h5>
                        @if(!empty($audioFiles) && $audioFiles->count() > 0)
                            @foreach($audioFiles as $pivot)
                                @php $audio = $pivot->media; @endphp
                                <div class="mb-4 pb-3 border-bottom">
                                    <h6 class="mb-2 text-dark font-weight-bold">
                                        <i class="fa fa-microphone mr-2 text-muted"></i>
                                        @if(!empty($pivot->track_order) && $pivot->track_order > 0)
                                            <span class="badge badge-secondary mr-2">Track {{ $pivot->track_order }}</span>
                                        @endif
                                        {{ $audio->title }}
                                    </h6>
                                    <div class="d-flex align-items-center">
                                        <audio controls class="flex-grow-1 mr-3 audio-control-full" preload="metadata">
                                            <source src="{{ $audio->url }}" type="{{ $audio->file_type }}">
                                            Your browser does not support the audio element.
                                        </audio>
                                        <a href="{{ $audio->url }}" class="btn btn-sm btn-light border" title="Download Audio" download>
                                            <i class="fa fa-download text-secondary"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle mr-2"></i> No audio recordings available for this teaching.
                            </div>
                        @endif
                    </div>

                    <!-- Description -->
                    @if(!empty($sermon->description))
                    <div class="description-section mt-5 mb-4">
                        <h4 class="mb-3">About This Teaching</h4>
                        <div class="description-content p-4 bg-light rounded">
                            {!! \App\Helpers\HtmlHelper::sanitize($sermon->description) !!}
                        </div>
                    </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('sermons') }}" class="btn btn-primary">
                            <i class="fa fa-arrow-left"></i> Back to Teachings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Sermons -->
@if(!empty($relatedSermons) && $relatedSermons->count() > 0)
<section class="ftco-section bg-light py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-7 text-center heading-section ftco-animate">
                <span class="subheading">More Teachings</span>
                <h2 class="mb-3">You May Also Like</h2>
            </div>
        </div>
        <div class="row">
            @foreach($relatedSermons as $rs)
            <div class="col-lg-3 col-md-4 col-6 mb-4">
                <div class="sermon-upload-card mb-0 shadow-sm rounded overflow-hidden">
                    <div class="sermon-image-container mb-0 img-card-container">
                        <a href="{{ route('sermons.show', $rs->slug) }}">
                            @if(!empty($rs->media) && !empty($rs->media->url))
                                <img src="{{ $rs->media->url }}" alt="{{ $rs->title }}" class="sermon-cover-image img-card-cover">
                            @else
                                <div class="sermon-placeholder h-100 d-flex align-items-center justify-content-center bg-secondary">
                                    <i class="fa fa-microphone fa-3x text-white"></i>
                                </div>
                            @endif
                            <div class="sermon-overlay">
                                <a href="{{ route('sermons.show', $rs->slug) }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-play"></i> stream/download
                                </a>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection