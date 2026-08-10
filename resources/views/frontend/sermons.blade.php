@extends('layouts.app')

@section('content')
<section class="hero-wrap hero-wrap-2 js-fullheight" @style(['background-image: url(' . asset('admin/bg_1.jpg') . ')'])>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <p class="breadcrumbs mb-2"><span class="mr-2"><a href="{{ url('/') }}">Home <i class="fa fa-chevron-right"></i></a></span> <span>Teachings <i class="fa fa-chevron-right"></i></span></p>
                <h1 class="mb-0 bread">Teachings</h1>
            </div>
        </div>
    </div>
</section>

<!-- Search Form -->
<div class="container mt-5 mb-5">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12">
            <form action="{{ route('sermons') }}" method="GET" class="content-search-form" id="sermonSearchForm">
                <div class="row g-2 align-items-center justify-content-center">
                    <div class="col-md-12">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control search-input rounded-pill" placeholder="Search teachings by title, description, or preacher..." value="{{ request('q') }}">
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

<!-- Sermons Grid -->
<section class="ftco-section recent-sermons-uploads">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-7 text-center">
                <div class="heading-section ftco-animate">
                    <span class="subheading">Teachings that Transform Lives</span>
                    <h2 class="mb-3">Latest Teachings</h2>
                </div>
            </div>
        </div>
        
        <div class="row">
            @if(!empty($sermons))
                @foreach($sermons as $sermon)
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="sermon-upload-card sermon-card" data-sermon-date="{{ $sermon->sermon_date }}">
                        <div class="sermon-image-container">
                            @if(!empty($sermon->media) && !empty($sermon->media->url))
                                <img src="{{ $sermon->media->url }}" alt="{{ $sermon->title }}" class="sermon-cover-image">
                            @else
                                <div class="sermon-placeholder">
                                    <i class="fa fa-microphone fa-3x text-muted"></i>
                                </div>
                            @endif
                            <div class="sermon-overlay">
                                <a href="{{ route('sermons.show', $sermon->slug) }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-play"></i> stream/download
                                </a>
                            </div>
                        </div>
                        <div class="sermon-info">
                            <h5 class="sermon-title">{{ $sermon->title }}</h5>
                            @if(!empty($sermon->preacher))
                                <p class="sermon-preacher">by {{ $sermon->preacher }}</p>
                            @endif
                            @if(!empty($sermon->sermon_date))
                                <p class="sermon-date">{{ date('M d, Y', strtotime($sermon->sermon_date)) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            @else
            <div class="col-12 text-center">
                <p class="text-muted">No sermons available at the moment.</p>
            </div>
            @endif
        </div>
        
        @if(method_exists($sermons, 'links'))
        <div class="row mt-4">
            <div class="col-12 pagination-wrapper">
                {{ $sermons->links('vendor.pagination.app-custom') }}
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
            <form class="sermon-filter-form mb-4" method="GET" action="{{ route('sermons') }}">
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
                        <a href="{{ route('sermons') }}" class="btn btn-outline-secondary rounded-pill w-100" style="height: 40px; padding: 0.5rem 1.2rem; font-size: 0.9rem;">Clear</a>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
