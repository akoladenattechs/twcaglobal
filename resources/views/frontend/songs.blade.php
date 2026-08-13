@extends('layouts.app')

@section('content')
<section class="hero-wrap hero-wrap-2 js-fullheight" @if(!empty($headerBgUrl)) style="background-image: url('{{ $headerBgUrl }}');" @endif>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <p class="breadcrumbs mb-2"><span class="mr-2"><a href="{{ url('/') }}">Home <i class="fa fa-chevron-right"></i></a></span> <span>Songs <i class="fa fa-chevron-right"></i></span></p>
                <h1 class="mb-0 bread">Songs</h1>
            </div>
        </div>
    </div>
</section>

<!-- Search Form -->
<div class="container mt-5 mb-5">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12">
            <form action="{{ route('songs') }}" method="GET" class="content-search-form" id="songSearchForm">
                <div class="row g-2 align-items-center justify-content-center">
                    <div class="col-md-12">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control search-input rounded-pill" placeholder="Search songs by title..." value="{{ request('q') }}">
                            <div class="input-group-append">
                                <button class="btn btn-search rounded-pill" type="submit">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Songs Grid -->
<section class="ftco-section recent-sermons-uploads">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-7 text-center">
                <div class="heading-section ftco-animate">
                    <span class="subheading">Latest Psalms & Songs</span>
                    <h2 class="mb-3">Latest Songs</h2>
                </div>
            </div>
        </div>
        
        <div class="row">
            @if(!empty($songs))
                @foreach($songs as $song)
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="sermon-upload-card sermon-card">
                        <div class="sermon-image-container">
                            @if(!empty($song->media) && !empty($song->media->url))
                                <img src="{{ $song->media->url }}" alt="{{ $song->title }}" class="sermon-cover-image">
                            @else
                                <div class="sermon-placeholder">
                                    <i class="fa fa-music fa-3x text-muted"></i>
                                </div>
                            @endif
                            <div class="sermon-overlay">
                                <a href="{{ route('songs.show', $song->slug) }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-play"></i> stream/download
                                </a>
                            </div>
                        </div>
                        <div class="sermon-info">
                            <h5 class="sermon-title">{{ $song->title }}</h5>
                            @if(!empty($song->created_at))
                                <p class="sermon-date">{{ date('M d, Y', strtotime($song->created_at)) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            @else
            <div class="col-12 text-center">
                <p class="text-muted">No songs available at the moment.</p>
            </div>
            @endif
        </div>
        
        @if(method_exists($songs, 'links'))
        <div class="row mt-4">
            <div class="col-12 pagination-wrapper">
                {{ $songs->links('vendor.pagination.app-custom') }}
            </div>
        </div>
        @endif
    </div>
</section>

<!-- Filter by Date -->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h4 class="text-center mb-4">Filter by Date</h4>
            <form class="sermon-filter-form mb-4" method="GET" action="{{ route('songs') }}">
                @if(request()->filled('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif
                <div class="row g-2 justify-content-center align-items-center">
                    <div class="col-4 col-md-3">
                        <select name="year" class="form-control select-pill">
                            <option value="">Year</option>
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4 col-md-3">
                        <select name="month" class="form-control select-pill">
                            <option value="">Month</option>
                            @foreach(range(1, 12) as $m)
                                @php $monthName = date('F', mktime(0, 0, 0, $m, 1)); @endphp
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $monthName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4 col-md-2">
                        <button type="submit" class="btn btn-primary rounded-pill w-100" style="height: 40px; padding: 0.5rem 1.2rem; font-size: 0.9rem;">FILTER</button>
                    </div>
                    @if(request()->filled('year') || request()->filled('month'))
                    <div class="col-12 col-md-2 mt-2 mt-md-0">
                        <a href="{{ route('songs') }}" class="btn btn-outline-secondary rounded-pill w-100" style="height: 40px; padding: 0.5rem 1.2rem; font-size: 0.9rem;">Clear</a>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
