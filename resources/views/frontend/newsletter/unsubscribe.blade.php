@extends('layouts.app')

@section('title', 'Unsubscribe from Newsletter')

@section('content')
<section class="page-header" style="background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); padding: 60px 0 40px;">
    <div class="container text-center text-white">
        <h1 class="mb-2" style="color: #fff;">Unsubscribe</h1>
        <p class="mb-0" style="color: rgba(255,255,255,0.85); font-size: 1.1rem;">
            Manage your newsletter preferences.
        </p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
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
                                <button type="submit" class="btn btn-danger btn-lg px-5">Confirm Unsubscribe</button>
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
