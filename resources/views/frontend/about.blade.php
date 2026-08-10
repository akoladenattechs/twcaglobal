@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="hero-wrap hero-wrap-2 js-fullheight" @style(['background-image: url(' . asset('admin/bg_1.jpg') . ')'])>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <p class="breadcrumbs mb-2"><span class="mr-2"><a href="{{ url('/') }}">Home <i class="fa fa-chevron-right"></i></a></span> <span>About <i class="fa fa-chevron-right"></i></span></p>
                <h1 class="mb-0 bread">About Us</h1>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- ABOUT US SECTIONS --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
@if($aboutSections->isNotEmpty())
    @php
        $regularSections = $aboutSections->where('section_type', '!=', 'quote');
        $quoteSections = $aboutSections->where('section_type', 'quote');
    @endphp

    {{-- Regular sections (mission, vision, values, custom) --}}
    @if($regularSections->isNotEmpty())
        <section class="ftco-section ftco-no-pb ftco-no-pt ministry-section">
            <div class="container">
                <div class="row no-gutters">
                    @foreach($regularSections as $section)
                        <div class="col-md-4 d-flex ftco-animate">
                            <div class="services-2">
                                <div class="text">
                                    <h4>{{ htmlspecialchars($section->title) }}</h4>
                                    @if($section->subtitle)
                                        <span class="subheading">{{ htmlspecialchars($section->subtitle) }}</span>
                                    @endif
                                    @if($section->content)
                                        <p>{!! \App\Helpers\HtmlHelper::sanitize($section->content) !!}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($loop->iteration % 3 === 0 && !$loop->last)
                </div><div class="row no-gutters">
                        @endif

                        @if($section->section_type !== 'custom' && ($loop->iteration % 3 === 0) && !$loop->last)
                </div><div class="row no-gutters">
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Quote sections --}}
    @if($quoteSections->isNotEmpty())
        <section class="ftco-section ftco-no-pb ftco-no-pt">
            <div class="container">
                <div class="row">
                    @foreach($quoteSections as $quote)
                        <div class="col-md-{{ $quoteSections->count() >= 2 ? '6' : '12' }} d-flex ftco-animate mb-4">
                            <div class="services-2 services-block p-4 w-100">
                                <div class="text">
                                    <h4 class="font-italic">"{{ htmlspecialchars($quote->title) }}"</h4>
                                    @if($quote->quote_author)
                                        <p><b>~ {{ htmlspecialchars($quote->quote_author) }}</b></p>
                                    @endif
                                    @if($quote->content)
                                        <p>{!! \App\Helpers\HtmlHelper::sanitize($quote->content) !!}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@else
    <section class="ftco-section ftco-no-pb ftco-no-pt ministry-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center py-5">
                    <p class="text-muted">Add sections in the Admin Panel → About Page to populate this area.</p>
                </div>
            </div>
        </div>
    </section>
@endif

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- OUR CENTERS LOCATIONS --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
@if($locations->isNotEmpty())
    <section class="ftco-section ftco-no-pb bg-light">
        <div class="container">
            <div class="row justify-content-center mb-2">
                <div class="col-md-7 text-center heading-section ftco-animate">
                    <span class="subheading">{{ $siteSettings['about_centers_subheading'] ?? 'Our Centers' }}</span>
                    <h2 class="mb-2">{{ $siteSettings['about_centers_heading'] ?? 'Worship With Us' }}</h2>
                </div>
            </div>

            <div class="row">
                @foreach($locations as $location)
                    <div class="col-6 col-lg-3 d-flex ftco-animate mb-2 mb-md-4 location-card">
                        <div class="services-2 w-100">
                            <div class="text p-4">
                                <h4>{{ htmlspecialchars($location->name) }}</h4>
                                @if($location->address)
                                    <p class="mb-1"><i class="fa fa-map-marker mr-2 text-primary"></i> {{ htmlspecialchars($location->address) }}</p>
                                @endif
                                @if($location->phone)
                                    <p class="mb-1"><i class="fa fa-phone mr-2 text-primary"></i> {{ htmlspecialchars($location->phone) }}</p>
                                @endif
                                @if($location->email)
                                    <p class="mb-1"><i class="fa fa-envelope mr-2 text-primary"></i> {{ htmlspecialchars($location->email) }}</p>
                                @endif
                                @if($location->service_times)
                                    <h5 class="mt-3 mb-1">Service Times</h5>
                                    <p class="mb-1">{!! \App\Helpers\HtmlHelper::sanitize($location->service_times) !!}</p>
                                @endif
                                @if($location->description)
                                    <p class="mt-2">{{ htmlspecialchars($location->description) }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@endsection
