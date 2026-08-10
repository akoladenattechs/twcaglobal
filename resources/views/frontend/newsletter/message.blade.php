@extends('layouts.app')

@section('title', $title ?? 'Newsletter')

@section('content')
<section class="page-header" style="background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); padding: 60px 0 40px;">
    <div class="container text-center text-white">
        <h1 class="mb-2" style="color: #fff;">{{ $title ?? 'Newsletter' }}</h1>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 text-center">

                        @if($type === 'success')
                            <div class="mb-4" style="font-size: 3rem; color: #28a745;">&#10003;</div>
                            <h3 class="card-title mb-3 text-success">Success!</h3>
                        @elseif($type === 'error')
                            <div class="mb-4" style="font-size: 3rem; color: #dc3545;">&#10007;</div>
                            <h3 class="card-title mb-3 text-danger">Oops!</h3>
                        @elseif($type === 'info')
                            <div class="mb-4" style="font-size: 3rem; color: #17a2b8;">&#8505;</div>
                            <h3 class="card-title mb-3 text-info">Notice</h3>
                        @else
                            <h3 class="card-title mb-3">{{ $title ?? 'Notice' }}</h3>
                        @endif

                        <p class="text-muted mb-4">{{ $message }}</p>

                        <a href="{{ url('/') }}" class="btn btn-danger">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
