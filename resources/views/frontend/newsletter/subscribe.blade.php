@extends('layouts.app')

@section('title', 'Subscribe to Newsletter')

@section('content')
<section class="page-header" style="background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); padding: 60px 0 40px;">
    <div class="container text-center text-white">
        <h1 class="mb-2" style="color: #fff;">Subscribe to Our Newsletter</h1>
        <p class="mb-0" style="color: rgba(255,255,255,0.85); font-size: 1.1rem;">
            Stay informed with the latest news, devotionals, and updates.
        </p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
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
                            <button type="submit" class="btn btn-danger btn-lg w-100">Subscribe</button>
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
