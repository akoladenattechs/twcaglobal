@extends('layouts.app')

@php
    $primary = $siteSettings['primary_color'] ?? '#ce0f3d';
    $secondary = $siteSettings['secondary_color'] ?? '#343a40';
@endphp

@section('title', 'Unsubscribe from Newsletter')

@section('content')
<!-- Hero Section -->
<section class="hero-wrap hero-wrap-2 js-fullheight" @if(!empty($headerBgUrl)) style="background-image: url('{{ $headerBgUrl }}');" @endif>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <h1 class="mb-0 bread" style="color: #fff;">Unsubscribe</h1>
                <p style="color: rgba(255,255,255,0.85); font-size: 1.1rem; margin-top: 10px;">
                    Manage your newsletter preferences.
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
                    <div class="card-body p-4 text-center">

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

                        @if($subscriber->hasVerifiedEmail() && !$subscriber->isUnsubscribed())
                            <h3 class="card-title mb-3">Sorry to see you go</h3>
                            <p class="text-muted mb-4">
                                You are subscribed as <strong>{{ $subscriber->email }}</strong>.
                                Would you like to unsubscribe from our mailing list?
                            </p>
                            <form method="POST" action="{{ route('newsletter.unsubscribe.process', $subscriber->unsubscribe_token) }}">
                                @csrf
                                <button type="submit" class="btn btn-lg px-5" style="background: {{ $primary }}; border-color: {{ $primary }}; color: #fff; font-weight: 600;">Confirm Unsubscribe</button>
                            </form>
                            <p class="text-muted mt-3 small">
                                Changed your mind? You can simply close this page.
                            </p>
                        @elseif($subscriber->isUnsubscribed())
                            <h3 class="card-title mb-3">Already Unsubscribed</h3>
                            <p class="text-muted mb-0">
                                You have already been unsubscribed from our mailing list.
                            </p>
                        @elseif(!$subscriber->hasVerifiedEmail())
                            <h3 class="card-title mb-3">Not Yet Verified</h3>
                            <p class="text-muted mb-0">
                                Your subscription is pending email verification. No further action is needed.
                            </p>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
