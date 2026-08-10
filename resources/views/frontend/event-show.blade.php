@extends('layouts.app')

@section('content')
<section class="hero-wrap hero-wrap-2 js-fullheight" @style(['background-image: url(' . asset('admin/bg_1.jpg') . ')'])>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <p class="breadcrumbs mb-2">
                    <span class="mr-2"><a href="{{ url('/') }}">Home <i class="fa fa-chevron-right"></i></a></span>
                    <span>{{ $event->title }}</span>
                </p>
                <h1 class="mb-0 bread">{{ $event->title }}</h1>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px;">
                <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
                <i class="fa fa-exclamation-circle mr-2"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <!-- Event Image -->
            <div class="col-md-5 mb-4">
                <div class="sermon-detail-image shadow-sm rounded overflow-hidden">
                    @if(!empty($event->image))
                        <img src="{{ $event->image }}" alt="{{ $event->title }}" class="img-fluid w-100">
                    @else
                        <div class="bg-light p-5 text-center text-muted">
                            <i class="fa fa-calendar fa-5x mb-3"></i>
                            <p>No event image available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Event Details -->
            <div class="col-md-7">
                <div class="sermon-detail-content">
                    <h2 class="mb-3">{{ $event->title }}</h2>

                    <!-- Event Info -->
                    <div class="mb-4">
                        @if(!empty($event->next_date))
                        <p class="mb-1">
                            <i class="fa fa-calendar text-primary mr-2"></i>
                            {{ date('l, F j, Y', strtotime($event->next_date)) }}
                        </p>
                        <p class="mb-1">
                            <i class="fa fa-clock-o text-primary mr-2"></i>
                            {{ date('g:i A', strtotime($event->next_date)) }}
                        </p>
                        @endif
                        @if(!empty($event->location))
                        <p class="mb-1">
                            <i class="fa fa-map-marker text-primary mr-2"></i>
                            {{ $event->location }}
                        </p>
                        @endif
                    </div>

                    @if(!empty($event->description))
                    <div class="description-section mb-4">
                        <h4 class="mb-3">About {{ $event->title }}</h4>
                        <div class="description-content p-4 bg-light rounded">
                            {!! nl2br(e($event->description)) !!}
                        </div>
                    </div>
                    @endif

                    @if($event->requires_registration)
                    <div class="mt-4">
                        <a href="{{ route('events.register', $event->slug) }}" class="btn btn-primary btn-lg">
                            <i class="fa fa-user-plus mr-2"></i>Click Here to Register
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection