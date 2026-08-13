@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="hero-wrap hero-wrap-2 js-fullheight" @if(!empty($headerBgUrl)) style="background-image: url('{{ $headerBgUrl }}');" @endif>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <p class="breadcrumbs mb-2">
                    <span class="mr-2"><a href="{{ url('/') }}">Home <i class="fa fa-chevron-right"></i></a></span> 
                    <span>Devotionals <i class="fa fa-chevron-right"></i></span>
                </p>
                <h1 class="mb-0 bread">Daily Devotionals</h1>
            </div>
        </div>
    </div>
</section>

<!-- Search Form -->
<div class="container mt-5 mb-5">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12">
            <form action="{{ route('devotionals') }}" method="GET" class="content-search-form" id="devotionalSearchForm">
                <div class="row g-2 align-items-center justify-content-center">
                    <div class="col-md-12">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control search-input rounded-pill" placeholder="Search devotionals by title or scripture..." value="{{ request('q') }}">
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

<!-- Devotionals Grid -->
<section class="ftco-section bg-light">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-7 text-center">
                <div class="heading-section ftco-animate">
                    <span class="subheading">Spiritual Growth</span>
                    <h2 class="mb-3">All Devotionals</h2>
                </div>
            </div>
        </div>
        
        <div class="row">
            @if(!empty($devotionals) && $devotionals->count() > 0)
                @foreach($devotionals as $devotional)
                <div class="col-lg-4 col-md-6 col-6 mb-4">
                    <div class="devotional-card">
                        <div class="devotional-info">
                            <h5 class="devotional-title">{{ $devotional->title }}</h5>
                            @if(!empty($devotional->devotional_date))
                                <p class="devotional-date">{{ date('M d, Y', strtotime($devotional->devotional_date)) }}</p>
                            @endif
                            <p class="devotional-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($devotional->content), 100) }}</p>
                            <div class="devotional-actions mt-3">
                                <a href="{{ route('devotionals.show', $devotional->slug) }}" class="btn btn-primary btn-block">
                                    <i class="fa fa-book-open"></i> Read More
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    <h5>No devotionals found</h5>
                    <p>Check back later for new devotionals.</p>
                </div>
            </div>
            @endif
        </div>
        
        @if(method_exists($devotionals, 'links'))
        <div class="row mt-4">
            <div class="col-12 pagination-wrapper">
                {{ $devotionals->links('vendor.pagination.app-custom') }}
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
            <form class="sermon-filter-form mb-4" method="GET" action="{{ route('devotionals') }}">
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
                        <a href="{{ route('devotionals') }}" class="btn btn-outline-secondary rounded-pill w-100" style="height: 40px; padding: 0.5rem 1.2rem; font-size: 0.9rem;">Clear</a>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
