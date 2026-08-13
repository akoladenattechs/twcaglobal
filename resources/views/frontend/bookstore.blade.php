@extends('layouts.app')

@section('content')
<section class="hero-wrap hero-wrap-2 js-fullheight" @if(!empty($headerBgUrl)) style="background-image: url('{{ $headerBgUrl }}');" @endif>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <p class="breadcrumbs mb-2"><span class="mr-2"><a href="{{ url('/') }}">Home <i class="fa fa-chevron-right"></i></a></span> <span>Books Store <i class="fa fa-chevron-right"></i></span></p>
                <h1 class="mb-0 bread">Our Books</h1>
            </div>
        </div>
    </div>
</section>

<!-- Search Form -->
<div class="container mt-5 mb-5">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12">
            <form action="{{ route('bookstore') }}" method="GET" class="content-search-form" id="bookSearchForm">
                <div class="row g-2 align-items-center justify-content-center">
                    <div class="col-md-12">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control search-input rounded-pill" placeholder="Search books by title, author, or description..." value="{{ request('q') }}">
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

<!-- Books Grid -->
<section class="ftco-section recent-sermons-uploads">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-7 text-center">
                <div class="heading-section ftco-animate">
                    <span class="subheading">Books Store</span>
                    <h2 class="mb-3">Our Recent Books</h2>
                </div>
            </div>
        </div>

        <div class="row">
            @if(!empty($books) && $books->count() > 0)
                @foreach($books as $book)
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="sermon-upload-card sermon-card">
                        <div class="sermon-image-container">
                            @if(!empty($book->media) && !empty($book->media->url))
                                <img src="{{ $book->media->url }}" alt="{{ $book->title }}" class="sermon-cover-image">
                            @else
                                <div class="sermon-placeholder">
                                    <i class="fa fa-book fa-3x text-muted"></i>
                                </div>
                            @endif
                            <div class="sermon-overlay d-flex flex-wrap align-items-center justify-content-center">
                                <a href="{{ route('bookstore.show', $book->slug) }}" class="btn btn-primary btn-sm mr-1">
                                    <i class="fa fa-book"></i> Details
                                </a>
                                @if(!empty($book->purchase_link))
                                <a href="{{ $book->purchase_link }}" target="_blank" class="btn btn-primary btn-sm mr-1">
                                    <i class="fa fa-shopping-cart"></i> Buy
                                </a>
                                @endif
                                @if(!empty($book->download_link))
                                <a href="{{ $book->download_link }}" target="_blank" class="btn btn-success btn-sm">
                                    <i class="fa fa-download"></i> Download
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="sermon-info">
                            <h5 class="sermon-title">{{ $book->title }}</h5>
                            @if(!empty($book->author))
                                <p class="sermon-preacher">by {{ $book->author }}</p>
                            @endif
                            <p class="sermon-date">
                                @if($book->price > 0)
                                    <span class="currency-value" data-currency="{{ $book->price }}">₦{{ number_format($book->price, 2) }}</span>
                                @else
                                    <span class="text-success font-weight-bold">Free</span>
                                @endif
                                @if(!empty($book->created_at))
                                    &nbsp;·&nbsp; {{ date('M d, Y', strtotime($book->created_at)) }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
            <div class="col-12 text-center">
                <p class="text-muted">No books available at the moment.</p>
            </div>
            @endif
        </div>

        @if(method_exists($books, 'links'))
        <div class="row mt-4">
            <div class="col-12 pagination-wrapper">
                {{ $books->links('vendor.pagination.app-custom') }}
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
            <form class="sermon-filter-form mb-4" method="GET" action="{{ route('bookstore') }}">
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
                        <a href="{{ route('bookstore') }}" class="btn btn-outline-secondary rounded-pill w-100" style="height: 40px; padding: 0.5rem 1.2rem; font-size: 0.9rem;">Clear</a>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
