@extends('layouts.app')

@php
    $primary = $siteSettings['primary_color'] ?? '#ce0f3d';
    $secondary = $siteSettings['secondary_color'] ?? '#343a40';
@endphp

@section('title', $title ?? 'Newsletter')

@section('content')
<!-- Hero Section -->
<section class="hero-wrap hero-wrap-2 js-fullheight" @if(!empty($headerBgUrl)) style="background-image: url('{{ $headerBgUrl }}');" @endif>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <h1 class="mb-0 bread" style="color: #fff;">{{ $title ?? 'Newsletter' }}</h1>
            </div>
        </div>
    </div>
</section>

<!-- Content Section -->
<section class="ftco-section ftco-no-pb ftco-no-pt">
    <div class="container">
        <div class="row justify-content-center py-5">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm border-0" style="background: #fff; border-radius: 16px;">
                    <div class="card-body p-4 text-center">

                        @if($type === 'success')
                            <div class="mb-4" style="font-size: 3rem; color: {{ $primary }};">&#10003;</div>
                            <h3 class="card-title mb-3" style="color: {{ $primary }};">Success!</h3>
                        @elseif($type === 'error')
                            <div class="mb-4" style="font-size: 3rem; color: #dc3545;">&#10007;</div>
                            <h3 class="card-title mb-3 text-danger">Oops!</h3>
                        @elseif($type === 'info')
                            <div class="mb-4" style="font-size: 3rem; color: {{ $primary }};">&#8505;</div>
                            <h3 class="card-title mb-3" style="color: {{ $primary }};">Notice</h3>
                        @else
                            <h3 class="card-title mb-3" style="color: {{ $primary }};">{{ $title ?? 'Notice' }}</h3>
                        @endif

                        <p class="text-muted mb-4" style="font-size: 1.05rem;">{{ $message }}</p>

                        <a href="{{ url('/') }}" class="btn" style="background: {{ $primary }}; border-color: {{ $primary }}; color: #fff; padding: 12px 28px; border-radius: 6px; font-weight: 600;">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
