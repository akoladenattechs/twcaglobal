<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = $siteSettings['site_title'] ?? config('app.name');
        $metaDesc = $siteSettings['meta_description'] ?? ($siteSettings['site_description'] ?? '');
        $metaKeywords = $siteSettings['meta_keywords'] ?? '';
        $ogImage = !empty($siteSettings['og_image']) ? \App\Helpers\HtmlHelper::assetUrl($siteSettings['og_image']) : (!empty($siteSettings['logo']) ? \App\Helpers\HtmlHelper::assetUrl($siteSettings['logo']) : ($headerBgUrl ?? ''));
        $gaId = $siteSettings['google_analytics_id'] ?? '';
        $googleVerify = $siteSettings['google_site_verification'] ?? '';
        $bingVerify = $siteSettings['bing_site_verification'] ?? '';
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="{{ $metaDesc }}">
    @if($metaKeywords)<meta name="keywords" content="{{ $metaKeywords }}">@endif
    <meta name="robots" content="index, follow, max-image-preview:large">
    <link rel="canonical" href="{{ url()->current() }}">

    @if($googleVerify)
        @if(str_contains($googleVerify, '<meta'))
            {!! $googleVerify !!}
        @else
            <meta name="google-site-verification" content="{{ $googleVerify }}">
        @endif
    @endif
    @if($bingVerify)
        <meta name="msvalidate.01" content="{{ $bingVerify }}">
    @endif

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $siteSettings['site_title'] ?? config('app.name') }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:image" content="{{ $ogImage }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <!-- Schema.org JSON-LD Structured Data for Google -->
    @php
        $socials = array_values(array_filter([
            $siteSettings['facebook'] ?? null,
            $siteSettings['twitter'] ?? null,
            $siteSettings['instagram'] ?? null,
            $siteSettings['youtube'] ?? null,
            $siteSettings['telegram'] ?? null,
        ]));
        $jsonLd = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Church',
            'name'        => $siteSettings['site_title'] ?? config('app.name'),
            'url'         => url('/'),
            'logo'        => \App\Helpers\HtmlHelper::assetUrl($siteSettings['logo'] ?? ''),
            'description' => $metaDesc,
            'sameAs'      => $socials,
        ];
        if (!empty($siteSettings['address'])) {
            $jsonLd['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $siteSettings['address']];
        }
        if (!empty($siteSettings['phone'])) {
            $jsonLd['telephone'] = $siteSettings['phone'];
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

    @if($gaId)
        <!-- Google Analytics (GA4) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', '{{ $gaId }}');
        </script>
    @endif

    <!-- CSS Files -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery.timepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    <link rel="icon" type="image/png" href="{{ \App\Helpers\HtmlHelper::assetUrl($siteSettings['favicon'] ?? '') }}">
    
    @php
        $pc = $siteSettings['primary_color'] ?? '#ce0f3d';
        $sc = $siteSettings['secondary_color'] ?? '#343a40';
        $pcLight = $pc . '18';
        $pcLight50 = $pc . '80';
    @endphp
    <style>
        :root {
            --primary-color: {{ $pc }};
            --secondary-color: {{ $sc }};
            --primary-color-light: {{ $pcLight }};
            --primary-color-light-50: {{ $pcLight50 }};
        }
    </style>
    
    <!-- Base URL for JS API calls (supports subdirectory installs) -->
    <script>window.BASE_URL = "{{ url('/') }}";</script>
    
    <!-- jQuery must be loaded before any scripts that use it -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}"><img src="{{ \App\Helpers\HtmlHelper::assetUrl($siteSettings['logo'] ?? '') }}" alt="Site Logo" /></a>
        <button class="navbar-toggler" type="button" id="sideDrawerToggle" aria-label="Toggle navigation">
            <span class="hamburger-lines">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </button>

        <!-- Mobile Nav Overlay -->
        <div class="nav-overlay" id="navOverlay"></div>

        <!-- Mobile Side Drawer -->
        <div class="side-drawer navbar-collapse" id="ftco-nav">
            <div class="side-drawer-header">
                <button class="drawer-close" id="drawerClose">&times;</button>
            </div>
            <ul class="navbar-nav ml-auto">
                @if(empty($menuItems) || empty($menuItems['']))
                    <!-- Static menu items as fallback -->
                    <li class="nav-item {{ request()->is('/') ? 'active' : '' }}"><a href="{{ url('/') }}" class="nav-link">Home</a></li>
                    <li class="nav-item {{ request()->is('about') ? 'active' : '' }}"><a href="{{ url('about') }}" class="nav-link">About</a></li>
                    <li class="nav-item {{ request()->is('teachings') ? 'active' : '' }}"><a href="{{ url('teachings') }}" class="nav-link">Teachings</a></li>
                    <li class="nav-item {{ request()->is('songs') ? 'active' : '' }}"><a href="{{ url('songs') }}" class="nav-link">Songs</a></li>
                    <li class="nav-item"><a href="{{ url('bookstore') }}" class="nav-link">Books</a></li>
                    <li class="nav-item {{ request()->is('partnership-giving') ? 'active' : '' }}"><a href="{{ url('partnership-giving') }}" class="nav-link">Give</a></li>
                    <li class="nav-item"><a href="{{ url('contact') }}" class="nav-link">Contact</a></li>
                    <li class="nav-item cta"><a href="{{ $siteSettings['partnership_url'] ?? url('partnership-giving') }}" class="nav-link btn btn-primary">{{ $siteSettings['partnership_label'] ?? 'Partner With Us' }}</a></li>
                @else
                    <!-- Dynamic menu items from database -->
                    @foreach($menuItems[''] as $item)
                        @php
                            // Map old URLs to new routes
                            // Dynamically normalize URL — no hardcoded page mappings needed
                            $url = $item->url;
                            $isActive = false;
                            if ($url == 'index.php' || $url == '/' || $url == '') {
                                $url = url('/');
                                $isActive = request()->is('/');
                            } elseif (strpos($url, 'http') === 0 || strpos($url, '#') === 0) {
                                // External URL or anchor — use as-is
                                $isActive = false;
                            } else {
                                // Strip .php extension if present, generate proper URL
                                $cleanPath = ltrim(preg_replace('/\.php$/', '', $url), '/');
                                $url = url($cleanPath);
                                $isActive = request()->is($cleanPath);
                            }
                            
                            $hasChildren = isset($menuItems[(string)$item->id]);
                        @endphp
                        @if($hasChildren)
                            <li class="nav-item dropdown {{ $isActive ? 'active' : '' }}">
                                <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="dropdown{{ $item->id }}" role="button" aria-haspopup="true" aria-expanded="false">
                                    {{ htmlspecialchars($item->title) }}
                                </a>
                                <div class="dropdown-menu" aria-labelledby="dropdown{{ $item->id }}">
                                    @foreach($menuItems[(string)$item->id] as $child)
                                        @php
                                            $childUrl = $child->url;
                                            if (strpos($childUrl, 'http') === 0 || strpos($childUrl, '#') === 0) {
                                                // External URL or anchor — use as-is
                                            } else {
                                                // Strip .php extension if present, generate proper URL
                                                $childUrl = url(ltrim(preg_replace('/\.php$/', '', $childUrl), '/'));
                                            }
                                        @endphp
                                        <a class="dropdown-item" href="{{ $childUrl }}" target="{{ $child->target }}">{{ htmlspecialchars($child->title) }}</a>
                                    @endforeach
                                </div>
                            </li>
                        @else
                            <li class="nav-item {{ $item->is_cta ? 'cta' : '' }} {{ $isActive ? 'active' : '' }}">
                                <a href="{{ $url }}" class="nav-link {{ $item->is_cta ? 'btn btn-primary' : '' }}" target="{{ $item->target }}">{{ htmlspecialchars($item->title) }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
</nav>

<script>
$(document).ready(function() {
    // ── Side Drawer Toggle ──
    var $drawer = $('#ftco-nav');
    var $overlay = $('#navOverlay');
    var $toggleBtn = $('#sideDrawerToggle');
    var $closeBtn = $('#drawerClose');



    function openDrawer() {
        $drawer.addClass('open');
        $overlay.addClass('show');
        $toggleBtn.addClass('active');
        $('body').css('overflow', 'hidden');
    }

    function closeDrawer() {
        $drawer.removeClass('open');
        $overlay.removeClass('show');
        $toggleBtn.removeClass('active');
        $('body').css('overflow', '');
    }

    $toggleBtn.on('click', function(e) {
        e.preventDefault();
        if ($drawer.hasClass('open')) {
            closeDrawer();
        } else {
            openDrawer();
        }
    });

    $closeBtn.on('click', function(e) {
        e.preventDefault();
        closeDrawer();
    });

    $overlay.on('click', function() {
        closeDrawer();
    });

    // Close drawer on escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $drawer.hasClass('open')) {
            closeDrawer();
        }
    });

    // Close drawer on window resize past breakpoint
    $(window).on('resize', function() {
        if (window.innerWidth > 991.98) {
            closeDrawer();
        }
    });

    // Close drawer after clicking a nav link or dropdown item (mobile)
    // Exclude dropdown-toggle so the parent label doesn't close the drawer
    $(document).on('click', '#ftco-nav .nav-link:not(.dropdown-toggle), #ftco-nav .dropdown-item', function() {
        if (window.innerWidth <= 991.98) {
            closeDrawer();
        }
    });

    // ── End Side Drawer ──

    // Custom dropdown toggle — toggles .open class which triggers CSS max-height transition
    // Bound to both click and touchend, using stopImmediatePropagation to block any other libraries (like Bootstrap)
    function handleDropdownToggle(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        var $toggle   = $(this);
        var $dropdown = $toggle.closest('.dropdown');
        var $menu     = $dropdown.find('.dropdown-menu');
        var isOpen    = $menu.hasClass('open');

        // Collapse all other drawers first
        $('.navbar-nav .dropdown-menu').not($menu).removeClass('open show');
        $('.navbar-nav .dropdown').not($dropdown).removeClass('show')
            .find('.dropdown-toggle').attr('aria-expanded', 'false');

        if (isOpen) {
            $menu.removeClass('open show');
            $dropdown.removeClass('show');
            $toggle.attr('aria-expanded', 'false');
        } else {
            $menu.addClass('open show');
            $dropdown.addClass('show');
            $toggle.attr('aria-expanded', 'true');
        }
    }

    $(document).on('click', '.navbar-nav .dropdown-toggle', handleDropdownToggle);
    $(document).on('touchend', '.navbar-nav .dropdown-toggle', handleDropdownToggle);

    // ── Ghost-click fix for mobile nav links ──
    // touchstart fires on "Books", touchend fires on "Books",
    // but the browser's 300ms-delayed synthetic click fires on "Contact" because
    // the dropdown collapses in that gap and the layout shifts.
    // Fix: intercept touchend, cancel the ghost click via preventDefault(),
    // and navigate manually — exactly like the FastClick library does.
    $(document).on('touchend', '#ftco-nav .dropdown-item, #ftco-nav .nav-link:not(.dropdown-toggle)', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        var href   = $(this).attr('href');
        var target = $(this).attr('target') || '_self';

        // Start closing the drawer immediately on mobile
        if (window.innerWidth <= 991.98) {
            closeDrawer();
        }

        // Navigate to the link — bypasses the ghost-click delay entirely
        if (href && href !== '#' && href !== 'javascript:void(0)') {
            if (target === '_blank') {
                window.open(href, '_blank');
            } else {
                window.location.href = href;
            }
        }
    });
});
</script>

@yield('content')

<!-- The Wordfare Radio Section -->
<section class="wordfare-radio-section py-5 bg-dark text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="radio-player-container text-center">
                    <h2 class="radio-title mb-4">{{ $siteSettings['radio_heading'] ?? '' }}</h2>
                    
                    <!-- Current Sermon Info -->
                    <div class="current-sermon-info mb-3">
                        <h5 id="currentSermonTitle" class="sermon-title">Loading sermon...</h5>
                        <p id="currentPreacher" class="preacher-name">Please wait...</p>
                    </div>
                    
                    <div class="radio-player">
                        <div class="player-controls">
                            <!-- Play/Pause Button -->
                            <button class="play-btn" id="radioPlayBtn">
                                <i class="fa fa-play" id="playIcon"></i>
                            </button>
                            
                            <div class="live-indicator">
                                <span class="live-dot"></span>
                                <span class="live-text">LIVE</span>
                            </div>
                            
                            <div class="volume-control">
                                <i class="fa fa-volume-up"></i>
                                <input type="range" class="volume-slider" min="0" max="100" value="50">
                            </div>
                        </div>
                        
                        <!-- Audio Element -->
                        <audio id="radioStream" preload="metadata" crossorigin="anonymous">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Subscribe Banner -->
<section class="newsletter-subscribe-section">
    <div class="container">
        <div class="newsletter-banner-grid">
            <!-- Left Panel: Heading -->
            <div class="newsletter-banner-left">
                <h3 class="newsletter-title">Newsletter &mdash; Get Updates &amp; Latest News</h3>
                <p class="newsletter-subtitle">Stay informed with devotionals, sermons &amp; church updates.</p>
            </div>

            <!-- Center Panel: Diamond + Envelope -->
            <div class="newsletter-banner-center">
                <div class="newsletter-icon-box">
                    <div class="newsletter-icon-bg"></div>
                    <i class="fa fa-envelope newsletter-icon"></i>
                </div>
            </div>

            <!-- Right Panel: Subscribe Form -->
            <div class="newsletter-banner-right">
                <form method="POST" action="{{ route('newsletter.subscribe.store') }}" class="newsletter-form-row">
                    @csrf
                    <input type="text" name="name" class="newsletter-input" placeholder="Your Name" required>
                    <input type="email" name="email" class="newsletter-input" placeholder="Email Address" required>
                    <button type="submit" class="newsletter-btn" aria-label="Subscribe">
                        <i class="fa fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<footer class="ftco-footer ftco-section bg-light text-dark ftco-footer-custom">
    <div class="container">
        <div class="row mb-5">
            <!-- Contact Info -->
            <div class="col-md-6 col-lg-4 mb-4 mb-md-0">
                <div class="footer-widget">
                    <h2 class="footer-heading">{{ $siteSettings['footer_contact_heading'] ?? 'Contact Info' }}</h2>
                    <ul class="list-unstyled footer-contact-info">
                        <li class="d-flex mb-3 align-items-start">
                            <span class="footer-icon-wrap"><i class="fa fa-map-marker text-primary mt-1"></i></span>
                            <span class="text-dark">{{ $siteSettings['address'] ?? '' }}</span>
                        </li>
                        <li class="d-flex mb-3 align-items-start">
                            <span class="footer-icon-wrap"><i class="fa fa-phone text-primary mt-1"></i></span>
                            <span class="text-dark">{{ $siteSettings['phone'] ?? '' }}</span>
                        </li>
                        <li class="d-flex mb-3 align-items-start">
                            <span class="footer-icon-wrap"><i class="fa fa-envelope text-primary mt-1"></i></span>
                            <span class="text-dark">{{ $siteSettings['email'] ?? '' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Connect With Us Section -->
            <div class="col-md-6 col-lg-4 mb-4 mb-md-0">
                <div class="footer-widget">
                    <h2 class="footer-heading">{{ $siteSettings['footer_social_heading'] ?? 'Connect With Us' }}</h2>
                    <p class="footer-description mb-3">{{ $siteSettings['footer_social_desc'] ?? 'Stay connected with us on social media for the latest updates, sermons, and events.' }}</p>
                    <div class="social-icons-wrapper">
                        <div class="d-flex flex-wrap footer-icon-gap">
                            @if(!empty($siteSettings['facebook']))
                            <a href="{{ $siteSettings['facebook'] }}" class="social-icon-link" data-toggle="tooltip" data-placement="top" title="Facebook">
                                    <span class="fa fa-facebook"></span>
                                </a>
                            @endif
                            @if(!empty($siteSettings['instagram']))
                            <a href="{{ $siteSettings['instagram'] }}" class="social-icon-link" data-toggle="tooltip" data-placement="top" title="Instagram">
                                    <span class="fa fa-instagram"></span>
                                </a>
                            @endif
                            @if(!empty($siteSettings['twitter']))
                            <a href="{{ $siteSettings['twitter'] }}" class="social-icon-link" data-toggle="tooltip" data-placement="top" title="Twitter">
                                    <span class="fa fa-twitter"></span>
                                </a>
                            @endif
                            @if(!empty($siteSettings['youtube']))
                            <a href="{{ $siteSettings['youtube'] }}" class="social-icon-link" data-toggle="tooltip" data-placement="top" title="YouTube">
                                    <span class="fa fa-youtube"></span>
                                </a>
                            @endif
                            @if(!empty($siteSettings['telegram']))
                            <a href="{{ $siteSettings['telegram'] }}" class="social-icon-link" data-toggle="tooltip" data-placement="top" title="Telegram">
                                    <span class="fa fa-telegram"></span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Download Our Apps -->
            <div class="col-md-6 col-lg-4 mb-4 mb-md-0 footer-col-no-pad">
                <div class="footer-widget">
                    <h2 class="footer-heading">{{ $siteSettings['footer_apps_heading'] ?? 'Our Apps' }}</h2>
                    <div class="app-download pr-3 pr-lg-0">
                        <a href="#" class="d-block text-decoration-none mb-3 store-badge-modern">
                            <div class="d-flex align-items-center py-2 pl-3">
                                <div class="app-icon mr-3 d-flex align-items-center justify-content-center footer-app-icon">
                                    <i class="fa fa-book"></i>
                                </div>
                                <div class="store-badge-text">
                                    <small class="d-block store-badge-small">Wordfare Daily</small>
                                    <strong class="d-block store-badge-title">Daily Devotional</strong>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="d-block text-decoration-none store-badge-modern">
                            <div class="d-flex align-items-center py-2 pl-3">
                                <div class="app-icon mr-3 d-flex align-items-center justify-content-center footer-app-icon">
                                    <i class="fa fa-play-circle"></i>
                                </div>
                                <div class="store-badge-text">
                                    <small class="d-block store-badge-small">Wordfare TV</small>
                                    <strong class="d-block store-badge-title">Live & Sermons</strong>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="row">
            <div class="col-md-12">
                <div class="footer-bottom text-center">
                    <div class="copyright-text">
                        <p class="copyright mb-2">
                            {!! \App\Helpers\HtmlHelper::sanitize($siteSettings['footer_text'] ?? '') !!}
                        </p>
                        <p class="tagline">
                            <em>{{ $siteSettings['footer_tagline'] ?? '' }}</em>
                        </p>
                        <p class="footer-credit">
                            {!! \App\Helpers\HtmlHelper::sanitize($siteSettings['footer_credit'] ?? '') !!}
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Loader -->
<div id="ftco-loader" class="show fullscreen">
    <svg class="circular" width="48px" height="48px">
        <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
        <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00" />
    </svg>
</div>

<script src="{{ asset('js/jquery-migrate-3.0.1.min.js') }}"></script>
<script src="{{ asset('js/popper.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/jquery.easing.1.3.js') }}"></script>
<script src="{{ asset('js/jquery.waypoints.min.js') }}"></script>
<script src="{{ asset('js/jquery.stellar.min.js') }}"></script>
<script src="{{ asset('js/jquery.animateNumber.min.js') }}"></script>
<script src="{{ asset('js/bootstrap-datepicker.js') }}"></script>
<script src="{{ asset('js/jquery.timepicker.min.js') }}"></script>
<script src="{{ asset('js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('js/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ asset('js/main.js') }}?v={{ filemtime(public_path('js/main.js')) }}"></script>
<script>
    // After Bootstrap loads:
    // 1. Unbind main.js hover handlers from side drawer dropdowns
    $('#ftco-nav .dropdown').off('mouseenter mouseleave');
    // 2. Remove Bootstrap's document-level DROPDOWN listeners ONLY
    //    (not modal or other component handlers) to prevent ghost-click-throughs.
    //    We scope removal to the dropdown data-api specifically.
    try {
        $(document).off('click.bs.dropdown.data-api touchstart.bs.dropdown.data-api keydown.bs.dropdown.data-api');
    } catch(e) {}
</script>
<script src="{{ asset('js/radio-player.js') }}?v={{ filemtime(public_path('js/radio-player.js')) }}"></script>
<script src="{{ asset('js/dynamic-content.js') }}?v={{ filemtime(public_path('js/dynamic-content.js')) }}"></script>

@stack('scripts')

@stack('modals')

</body>
</html>
