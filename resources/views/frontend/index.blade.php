@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="hero-section hero-showcase" data-sliders='{!! json_encode($sliders, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}'>
    <!-- SVG Filters for glass/gooey/glow effects (hidden) -->
    <svg class="hero-svg-filters" aria-hidden="true">
        <defs>
            <filter id="glass-effect" x="-50%" y="-50%" width="200%" height="200%">
                <feTurbulence baseFrequency="0.005" numOctaves="1" result="noise" />
                <feDisplacementMap in="SourceGraphic" in2="noise" scale="0.3" />
                <feColorMatrix type="matrix" values="1 0 0 0 0.02 0 1 0 0 0.02 0 0 1 0 0.05 0 0 0 0.9 0" result="tint" />
            </filter>
            <filter id="gooey-filter" x="-50%" y="-50%" width="200%" height="200%">
                <feGaussianBlur in="SourceGraphic" stdDeviation="4" result="blur" />
                <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 19 -9" result="gooey" />
                <feComposite in="SourceGraphic" in2="gooey" operator="atop" />
            </filter>
            <filter id="text-glow" x="-50%" y="-50%" width="200%" height="200%">
                <feGaussianBlur stdDeviation="2" result="coloredBlur" />
                <feMerge>
                    <feMergeNode in="coloredBlur" />
                    <feMergeNode in="SourceGraphic" />
                </feMerge>
            </filter>
            <linearGradient id="gradient-hero" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stopColor="#ffffff" />
                <stop offset="30%" stopColor="#06b6d4" />
                <stop offset="70%" stopColor="#f97316" />
                <stop offset="100%" stopColor="#ffffff" />
            </linearGradient>
        </defs>
    </svg>

    <!-- Owl Carousel - Background Images / Videos -->
    <div class="home-slider js-fullheight owl-carousel" id="heroSlider">
        @if(!empty($sliders))
            @foreach($sliders as $slider)
            @php
                $hasVideo = !empty($slider->video_file) || !empty($slider->video_embed_url);
                // image_file/video_file come from the Media::url accessor, which
                // returns a full URL (R2/CDN) or asset()-resolved path as-is.
                $imageUrl = !empty($slider->image_file) ? $slider->image_file : '';
            @endphp
            <div class="slider-item js-fullheight" @if(!$hasVideo && !empty($imageUrl)) style="background-image:url('{{ $imageUrl }}')" @endif>
                @if(!empty($slider->video_file))
                <video class="hero-video-bg" autoplay muted loop playsinline preload="auto"
                    src="{{ $slider->video_file }}"></video>
                @elseif(!empty($slider->video_embed_url))
                    @if(in_array($slider->video_type, ['youtube', 'vimeo']))
                    <iframe class="hero-video-bg hero-video-iframe" src="{{ $slider->video_embed_url }}"
                        frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                    @else
                    <video class="hero-video-bg" autoplay muted loop playsinline preload="auto"
                        src="{{ $slider->video_embed_url }}"></video>
                    @endif
                @endif
                <div class="overlay"></div>
            </div>
            @endforeach
        @endif
    </div>

    <!-- Animated Mesh Gradient Overlay (ShaderShowcase-style) -->
    <div class="mesh-bg" aria-hidden="true">
        <div class="mesh-gradient-anim"></div>
        <div class="mesh-wireframe"></div>
    </div>

    @if(!empty($sliders))
    @php
        $badgeText = $heroSettings->badge_text;
        $hasBadge = $heroSettings->show_badge && !empty($badgeText);
        $hasDesc = $heroSettings->show_description && !empty($heroSettings->description);

    @endphp
    <!-- Fixed Content Overlay -->
    <div class="hero-fixed-content">
        <!-- Main Content Area (aligned bottom-left) -->
        <div class="hero-body">
            <div class="hero-body-inner">
                <!-- Glass Badge -->
                <div class="hero-glass-badge" id="heroBadge" @if(!$hasBadge) style="display:none" @endif>
                    <span class="badge-dot"></span>
                    <span class="hero-dynamic hero-subtitle">{{ $badgeText }}</span>
                </div>

                <!-- Multi-line Display Title -->
                <h1 class="hero-display-title">
                    @if(!empty($heroSettings->prefix_text))
                    <span class="title-gradient hero-dynamic hero-prefix-text">{{ $heroSettings->prefix_text }}</span>
                    @endif
                    <span class="title-bold hero-dynamic hero-title-text">{{ $heroSettings->title ?? '' }}</span>
                    @if(!empty($heroSettings->suffix_text))
                    <span class="title-italic hero-dynamic hero-suffix-text">{{ $heroSettings->suffix_text }}</span>
                    @endif
                </h1>

                <!-- Description -->
                @if($hasDesc)
                <p class="hero-desc-text hero-dynamic" id="heroDesc">{{ $heroSettings->description ?? '' }}</p>
                @else
                <p class="hero-desc-text hero-dynamic d-none" id="heroDesc"></p>
                @endif

                <!-- CTA Button -->
                @php
                    $showBtn = $heroSettings->show_button ?? true;
                    $btnLink = $heroSettings->button_link ?? '';
                    // Use url() for relative paths so subdirectory installs work correctly
                    if (!empty($btnLink) && !preg_match('/^https?:\/\//', $btnLink)) {
                        $btnLink = url($btnLink);
                    }
                @endphp
                @if($showBtn && !empty($heroSettings->button_text) && !empty($heroSettings->button_link))
                <div class="hero-actions">
                    <a href="{{ $btnLink }}" class="btn hero-cta-btn">
                        {{ $heroSettings->button_text }}
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
                @endif


            </div>
        </div>

    </div>
    @endif
</section>

<!-- Four Column Ministry Section -->
<section class="ftco-section ftco-no-pb ftco-no-pt ministry-section">
    <div class="container">
        <div class="row no-gutters">
            @php
                $ministryItems = $ministryColumns->where('column_type', 'ministry');
                $quoteItems = $ministryColumns->where('column_type', 'quote');
            @endphp
            @if($ministryItems->count() > 0)
            <div class="col-md-8 d-flex">
                <div class="row no-gutters">
                    @foreach($ministryItems as $item)
                    <div class="col-md-4">
                        <div class="services-2">
                            @if(!empty($item->icon_class))
                            <div class="icon"><span class="{{ $item->icon_class }}"></span></div>
                            @endif
                            <div class="text">
                                <h4>{{ $item->title ?? '' }}</h4>
                                @if(!empty($item->subtitle))
                                <span class="subheading">{{ $item->subtitle }}</span>
                                @endif
                                <p>{{ $item->description ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($quoteItems->count() > 0)
            @foreach($quoteItems as $quote)
            <div class="col-md-4 d-flex">
                <div class="services-2 services-block">
                    <div class="text">
                        <h4>"{{ $quote->title ?? '' }}"</h4>
                        @if(!empty($quote->quote_author))
                        <p><b>{{ $quote->quote_author }}</b></p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>
</section>

<!-- Recent Teachings Section -->
<section class="recent-sermons-uploads py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center mb-4">
            <div class="col-md-7 text-center heading-section ftco-animate">
                <span class="subheading">{{ $siteSettings['home_sermons_subheading'] ?? 'Recent Uploads' }}</span>
                <h2 class="mb-3">{{ $siteSettings['home_sermons_heading'] ?? 'Recent Teachings' }}</h2>
            </div>
        </div>
        
        @if(!empty($recentSermons))
        <div class="row">
            @foreach($recentSermons as $index => $sermon)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="sermon-upload-card">
                    <div class="sermon-image-container">
                        @if(!empty($sermon['image_file']))
                            <img src="{{ $sermon['image_file'] }}" alt="{{ $sermon['title'] }}" class="sermon-cover-image">
                        @else
                            <div class="sermon-placeholder">
                                <i class="fa fa-microphone fa-3x text-muted"></i>
                            </div>
                        @endif
                        <div class="sermon-overlay">
                            <a href="{{ route('sermons.show', $sermon['slug']) }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-play"></i> stream/download
                            </a>
                        </div>
                    </div>
                    <div class="sermon-info">
                        <h5 class="sermon-title">{{ $sermon['title'] }}</h5>
                        @if(!empty($sermon['preacher']))
                            <p class="sermon-preacher">by {{ $sermon['preacher'] }}</p>
                        @endif
                        @if(!empty($sermon['sermon_date']))
                            <p class="sermon-date">{{ date('M d, Y', strtotime($sermon['sermon_date'])) }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-4">
            <a href="{{ route('sermons') }}" class="btn btn-primary">{{ $siteSettings['home_sermons_button'] ?? 'View All Teachings' }}</a>
        </div>
        @else
        <div class="text-center">
            <p class="text-muted">No recent sermon uploads available.</p>
        </div>
        @endif
    </div>
</section>

<!-- Featured Sections -->
<section class="featured-sections py-5">
    <div class="container">
        <div class="row">
            <!-- Upcoming Events Slider -->
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="featured-card h-100">
                    <div class="card-header">
                        <h3 class="mb-0">{{ $siteSettings['home_events_heading'] ?? 'Upcoming Events' }}</h3>
                    </div>
                    <div class="card-body">
                        @if($upcomingEvents->isNotEmpty())
                        <div class="events-slider owl-carousel owl-theme">
                            @foreach($upcomingEvents as $event)
                            <div class="event-slide">
                                <a href="{{ route('events.show', $event->slug) }}" class="event-item event-item-link">
                                    <div class="event-date">
                                        <span class="day">{{ date('d', strtotime($event->next_date)) }}</span>
                                        <span class="month">{{ date('M', strtotime($event->next_date)) }}</span>
                                    </div>
                                    <div class="event-details">
                                        <h4>{{ $event->title }}</h4>
                                        <p class="mb-1">
                                            <i class="fa fa-clock-o"></i>
                                            {{ date('g:i A', strtotime($event->next_date)) }}
                                        </p>
                                        <p class="mb-0">
                                            <i class="fa fa-map-marker"></i>
                                            {{ $event->location }}
                                        </p>
                                        @if(!empty($event->description))
                                        <p class="event-description mt-2">
                                            {{ substr($event->description, 0, 100) }}...
                                        </p>
                                        @endif
                                    </div>
                                </a>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-muted">No upcoming events scheduled.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Inspirational Quotes Slider -->
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="featured-card h-100">
                    <div class="card-header">
                        <h3 class="mb-0">{{ $siteSettings['home_quotes_heading'] ?? 'Nuggets' }}</h3>
                    </div>
                    <div class="card-body">
                        @if(!empty($recentQuotes))
                        <div class="quotes-slider owl-carousel owl-theme">
                            @foreach($recentQuotes as $quote)
                            <div class="quote-slide">
                                <div class="quote-item">
                                    <div class="quote-content">
                                        <blockquote class="blockquote">
                                            <p class="mb-2">
                                                <i class="fa fa-quote-left text-primary mr-2"></i>
                                                {{ $quote['content'] }}
                                                <i class="fa fa-quote-right text-primary ml-2"></i>
                                            </p>
                                            @if(!empty($quote['author']))
                                            <footer class="blockquote-footer">
                                                <cite title="Source Title">
                                                    {{ $quote['author'] }}
                                                    @if(!empty($quote['title']))
                                                        <small class="text-muted">, {{ $quote['title'] }}</small>
                                                    @endif
                                                </cite>
                                            </footer>
                                            @endif
                                        </blockquote>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-muted">No quotes available at the moment.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
