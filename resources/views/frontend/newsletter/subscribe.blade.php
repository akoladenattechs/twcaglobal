@extends('layouts.app')

@php
    $primary = $siteSettings['primary_color'] ?? '#ce0f3d';
    $secondary = $siteSettings['secondary_color'] ?? '#343a40';
@endphp

@section('title', 'Subscribe to Newsletter')

@section('content')
<!-- Hero Section -->
<section class="hero-wrap hero-wrap-2 js-fullheight" @if(!empty($headerBgUrl)) style="background-image: url('{{ $headerBgUrl }}');" @endif>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <h1 class="mb-0 bread" style="color: #fff;">Subscribe to Our Newsletter</h1>
                <p style="color: rgba(255,255,255,0.85); font-size: 1.1rem; margin-top: 10px;">
                    Stay informed with the latest news, devotionals, and updates.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Content Section -->
<section class="ftco-section ftco-no-pb ftco-no-pt">
    <div class="container">
        <div class="row justify-content-center py-5">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h3 class="card-title mb-3">Join Our Mailing List</h3>

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('newsletter.subscribe.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name (optional)</label>
                                <input type="text" id="name" name="name" class="form-control" placeholder="John Doe" value="{{ old('name') }}">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="you@example.com" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-lg w-100" style="background: {{ $primary }}; border-color: {{ $primary }}; color: #fff; font-weight: 600;">Subscribe</button>
                        </form>

                        <p class="text-muted mt-3 mb-0 small">
                            We respect your privacy. You can unsubscribe at any time.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
