@extends('layouts.app')

@section('content')
<section class="hero-wrap hero-wrap-2 js-fullheight" @if(!empty($headerBgUrl)) style="background-image: url('{{ $headerBgUrl }}');" @endif>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <p class="breadcrumbs mb-2">
                    <span class="mr-2"><a href="{{ url('/') }}">Home <i class="fa fa-chevron-right"></i></a></span>
                    <span><a href="{{ route('bookstore') }}">Books Store <i class="fa fa-chevron-right"></i></a></span>
                    <span>{{ $book->title }}</span>
                </p>
                <h1 class="mb-0 bread">{{ $book->title }}</h1>
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
                    @if(!empty($book->media) && !empty($book->media->url))
                        <img src="{{ $book->media->url }}" alt="{{ $book->title }}" class="img-fluid w-100">
                    @else
                        <div class="bg-light p-5 text-center text-muted">
                            <i class="fa fa-book fa-5x mb-3"></i>
                            <p>No cover image available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Book Details -->
            <div class="col-md-7">
                <div class="sermon-detail-content">
                    <div class="d-flex align-items-center mb-3 flex-wrap">
                        <span class="badge badge-primary mr-2 mb-1">{{ date('M d, Y', strtotime($book->created_at)) }}</span>
                        @if($book->price > 0)
                            <span class="badge badge-warning mr-2 mb-1">
                                <i class="fa fa-shopping-cart"></i> ₦{{ number_format($book->price, 2) }}
                            </span>
                        @else
                            <span class="badge badge-success mr-2 mb-1">
                                <i class="fa fa-cloud-download"></i> Free
                            </span>
                        @endif
                    </div>

                    <h2 class="mb-3">{{ $book->title }}</h2>

                    @if(!empty($book->author))
                        <p class="text-muted mb-4"><strong>Author:</strong> {{ $book->author }}</p>
                    @endif

                    <!-- Buy/Download Buttons -->
                    @if(!empty($book->purchase_link) || !empty($book->download_link) || (!empty($book->pdf_file) && $book->allow_pdf_download))
                    <div class="book-actions mb-4">
                        <h5 class="mb-3">Get Your Copy</h5>
                        <div class="d-flex flex-wrap gap-2">
                            @if(!empty($book->purchase_link))
                            <a href="{{ $book->purchase_link }}" target="_blank" class="btn btn-primary mr-2 mb-2">
                                <i class="fa fa-shopping-cart"></i> Buy Now
                            </a>
                            @endif
                            @if(!empty($book->download_link))
                            <a href="{{ $book->download_link }}" target="_blank" class="btn btn-success mb-2">
                                <i class="fa fa-download"></i> Download Free
                            </a>
                            @endif
                            @if(!empty($book->pdf_file) && $book->allow_pdf_download)
                            <a href="{{ $book->pdf_file }}" target="_blank" class="btn btn-danger mb-2">
                                <i class="fa fa-file-pdf-o"></i> Download PDF
                            </a>
                            @endif
                        </div>
                    </div>
                    @elseif(!empty($book->pdf_file) && !$book->allow_pdf_download)
                    <!-- PDF exists but download not enabled — show nothing extra -->
                    @else
                    <div class="alert alert-info mb-4">
                        <i class="fa fa-info-circle mr-2"></i> For purchase or download inquiries, please <a href="{{ route('contact') }}" class="alert-link">contact us</a>.
                    </div>
                    @endif

                    <!-- Description -->
                    @if(!empty($book->description))
                    <div class="description-section mt-5 mb-4">
                        <h4 class="mb-3">About This Book</h4>
                        <div class="description-content p-4 bg-light rounded">
                            {!! \App\Helpers\HtmlHelper::sanitize($book->description) !!}
                        </div>
                    </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('bookstore') }}" class="btn btn-primary">
                            <i class="fa fa-arrow-left"></i> Back to Bookstore
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Books -->
@if(!empty($relatedBooks) && $relatedBooks->count() > 0)
<section class="ftco-section bg-light py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-7 text-center heading-section ftco-animate">
                <span class="subheading">More Books</span>
                <h2 class="mb-3">You May Also Like</h2>
            </div>
        </div>
        <div class="row">
            @foreach($relatedBooks as $rb)
            <div class="col-lg-3 col-md-4 col-6 mb-4">
                <div class="sermon-upload-card mb-0 shadow-sm rounded overflow-hidden">
                    <div class="sermon-image-container mb-0 img-card-container">
                        <a href="{{ route('bookstore.show', $rb->slug) }}">
                            @if(!empty($rb->media) && !empty($rb->media->url))
                                <img src="{{ $rb->media->url }}" alt="{{ $rb->title }}" class="sermon-cover-image img-card-cover">
                            @else
                                <div class="sermon-placeholder h-100 d-flex align-items-center justify-content-center bg-secondary">
                                    <i class="fa fa-book fa-3x text-white"></i>
                                </div>
                            @endif
                            <div class="sermon-overlay">
                                <a href="{{ route('bookstore.show', $rb->slug) }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-book"></i> Details
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