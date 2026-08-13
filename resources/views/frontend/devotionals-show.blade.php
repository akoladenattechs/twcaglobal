@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="hero-wrap hero-wrap-2 js-fullheight" @if(!empty($headerBgUrl)) style="background-image: url('{{ $headerBgUrl }}');" @endif>
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-end js-fullheight">
            <div class="col-md-9 ftco-animate pb-5">
                <p class="breadcrumbs mb-2">
                    <span class="mr-2"><a href="{{ url('/') }}">Home <i class="fa fa-chevron-right"></i></a></span> 
                    <span class="mr-2"><a href="{{ route('devotionals') }}">Devotionals <i class="fa fa-chevron-right"></i></a></span>
                    <span>{{ $devotional->title ?? 'Devotional' }}</span>
                </p>
                <h1 class="mb-0 bread">{{ $devotional->title ?? 'Devotional' }}</h1>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                @if($devotional)
                <!-- Devotional Meta -->
                <div class="devotional-meta mb-4">
                    <p class="text-muted">
                        <i class="fa fa-calendar mr-2"></i>{{ date('F d, Y', strtotime($devotional->devotional_date)) }}
                    </p>
                </div>
                
                <!-- Scripture Highlight -->
                @if(!empty($devotional->scripture_reference))
                <div class="scripture-highlight">
                    <h4 class="text-primary mb-3"><i class="fa fa-book mr-2"></i>Scripture Ref. Text — {{ $devotional->scripture_reference }}</h4>
                    @if(!empty($devotional->scripture_text))
                    <p>"{!! \App\Helpers\ScriptureHelper::linkify(\App\Helpers\HtmlHelper::sanitize($devotional->scripture_text)) !!}"</p>
                    @endif
                </div>
                @endif
                
                <!-- Devotional Content -->
                <div class="devotional-content">
                    {!! \App\Helpers\ScriptureHelper::linkify(\App\Helpers\HtmlHelper::sanitize($devotional->content)) !!}
                </div>
                
                <!-- Prayer/Confession Section -->
                @if(!empty($devotional->prayer))
                <div class="prayer-section">
                    <h4 class="text-primary mb-3"><i class="fa fa-heart mr-2"></i>Confession</h4>
                    <p>{!! nl2br(e($devotional->prayer)) !!}</p>
                </div>
                @endif
                
                <!-- Further Studies Section -->
                @if(!empty($devotional->reflection_questions))
                <div class="reflection-section">
                    <h4 class="text-primary mb-3"><i class="fa fa-lightbulb-o mr-2"></i>Further Studies</h4>
                    <p>{!! \App\Helpers\ScriptureHelper::linkify(nl2br(e($devotional->reflection_questions))) !!}</p>
                </div>
                @endif
                
                <!-- Navigation -->
                <div class="text-center mt-5 mb-4">
                    <a href="{{ route('devotionals') }}" class="btn btn-primary btn-lg px-5">
                        <i class="fa fa-arrow-left mr-2"></i>Back to Devotionals
                    </a>
                </div>
                @else
                <div class="alert alert-danger text-center">
                    <h4>Devotional Not Found</h4>
                    <p>The devotional you're looking for could not be found.</p>
                    <a href="{{ route('devotionals') }}" class="btn btn-primary mt-3">
                        <i class="fa fa-arrow-left mr-2"></i>Back to Devotionals
                    </a>
                </div>
                @endif
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Share Section -->
                <div class="sidebar-box">
                    <h3 class="heading">Share this Devotional</h3>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                           target="_blank" class="btn btn-primary btn-block mb-2" onclick="window.open(this.href, 'share', 'width=600,height=400'); return false;">
                            <i class="fa fa-facebook mr-2"></i> Share on Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($devotional->title . ' - ' . url()->current()) }}" 
                           target="_blank" class="btn btn-primary btn-block mb-2" onclick="window.open(this.href, 'share', 'width=600,height=400'); return false;">
                            <i class="fa fa-twitter mr-2"></i> Share on X (Twitter)
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($devotional->title . ' - ' . url()->current()) }}" 
                           target="_blank" class="btn btn-primary btn-block mb-2" onclick="window.open(this.href, 'share', 'width=600,height=400'); return false;">
                            <i class="fa fa-whatsapp mr-2"></i> Share on WhatsApp
                        </a>
                        <button class="btn btn-primary btn-block" id="shareAsImageBtn">
                            <i class="fa fa-image mr-2"></i> Share as Image
                        </button>
                    </div>
                </div>
                
                <!-- Recent Devotionals -->
                <div class="sidebar-box">
                    <h3 class="heading">Recent Devotionals</h3>
                    @if(!empty($recentDevotionals) && $recentDevotionals->count() > 0)
                        @foreach($recentDevotionals as $recent)
                        <div class="recent-devotional-item">
                            <a href="{{ route('devotionals.show', $recent->slug) }}" class="d-block">
                                <strong>{{ htmlspecialchars($recent->title) }}</strong>
                            </a>
                            <small class="text-muted">
                                <i class="fa fa-calendar mr-1"></i>{{ date('M d, Y', strtotime($recent->devotional_date)) }}
                                @if(!empty($recent->scripture_reference))
                                &nbsp;|&nbsp;<i class="fa fa-book mr-1"></i>{{ $recent->scripture_reference }}
                                @endif
                            </small>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No recent devotionals available.</p>
                    @endif
                </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bible Verse Popup Overlay -->
<div id="bibleOverlay" class="bible-overlay"></div>

<!-- Bible Verse Popup -->
<div id="biblePopup" class="bible-popup">
    <div class="bible-popup-arrow"></div>
    <div class="bible-popup-inner">
        <div class="bible-popup-header">
            <div class="bible-popup-icon">📖</div>
            <span class="bible-popup-ref" id="biblePopupRef">Scripture</span>
            <button type="button" class="bible-popup-close" id="biblePopupClose">&times;</button>
        </div>
        <div class="bible-popup-body">
            <div class="text-center py-4">
                <div class="spinner-border text-warning" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading scripture...</p>
            </div>
        </div>
        <div class="bible-popup-footer">
            <span class="bible-translation-badge">KJV</span>
            <span class="bible-translation-name">King James Version</span>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    var $popup = $('#biblePopup');
    var $overlay = $('#bibleOverlay');
    var $body = $popup.find('.bible-popup-body');
    var $refTitle = $('#biblePopupRef');
    
    function closePopup() {
        $popup.hide();
        $overlay.hide();
        $('.bible-ref.active-ref').removeClass('active-ref');
    }
    
    // Bible reference click handler
    $(document).on('click', '.bible-ref', function(e) {
        e.preventDefault();
        var ref = $(this).data('ref');
        if (!ref) return;
        
        var $target = $(this);
        
        // Track which ref was clicked
        $('.bible-ref.active-ref').removeClass('active-ref');
        $target.addClass('active-ref');
        
        // Set reference in header
        $refTitle.text(ref);
        
        // Set loading state
        $body.html(
            '<div class="text-center py-4">' +
                '<div class="spinner-border text-warning" role="status">' +
                    '<span class="sr-only">Loading...</span>' +
                '</div>' +
                '<p class="mt-2 text-muted">Loading ' + ref + '...</p>' +
            '</div>'
        );
        
        // Show overlay and position popup
        $overlay.show();
        $popup.show();
        $popup.css('animation', 'none');
        setTimeout(function() {
            $popup.css('animation', '');
            positionPopup($target);
        }, 10);
        
        // Fetch from Bible API (KJV)
        var apiUrl = 'https://bible-api.com/' + encodeURIComponent(ref) + '?translation=kjv';
        
        fetch(apiUrl)
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.error) {
                    $body.html(
                        '<div class="alert alert-warning text-center mb-0 py-3">' +
                            '<strong>Scripture Not Found</strong>' +
                            '<p class="mb-0 mt-1 small">Could not find "' + ref + '". Please check the reference.</p>' +
                        '</div>'
                    );
                    repositionPopup();
                    return;
                }
                
                var html = '<div class="bible-ref-title">' + data.reference + '</div>';
                html += '<div class="bible-verses">';
                
                if (data.verses && data.verses.length > 0) {
                    data.verses.forEach(function(v) {
                        var verseNum = v.verse !== undefined ? '<sup class="verse-number">' + v.verse + '</sup>' : '';
                        html += '<p>' + verseNum + v.text + '</p>';
                    });
                } else if (data.text) {
                    html += '<p>' + data.text + '</p>';
                }
                
                html += '</div>';
                $body.html(html);
                
                repositionPopup();
            })
            .catch(function() {
                $body.html(
                    '<div class="alert alert-danger text-center mb-0 py-3">' +
                        '<strong>Error Loading Scripture</strong>' +
                        '<p class="mb-0 mt-1 small">Please check your connection and try again.</p>' +
                    '</div>'
                );
                repositionPopup();
            });
    });
    
    // Position popup relative to the target element
    function positionPopup($target) {
        var targetOffset = $target.offset();
        var targetWidth = $target.outerWidth();
        var popupWidth = $popup.outerWidth();
        var popupHeight = $popup.outerHeight();
        var windowWidth = $(window).width();
        var windowHeight = $(window).height();
        var scrollTop = $(window).scrollTop();
        
        // Horizontal: center popup over target, keep within viewport
        var left = targetOffset.left + (targetWidth / 2) - (popupWidth / 2);
        left = Math.max(12, Math.min(left, windowWidth - popupWidth - 12));
        
        // Convert document-relative target position to viewport-relative for position:fixed
        var viewportTargetTop = targetOffset.top - scrollTop;
        
        // Position above the target. If not enough room, clamp to top of viewport.
        var gap = 14;
        var top = viewportTargetTop - popupHeight - gap;
        if (top < 10) {
            top = 10;
        }
        
        $popup.css({ top: top, left: left });
        
        // Arrow always points down from the popup
        var arrow = $popup.find('.bible-popup-arrow');
        arrow.css({
            bottom: '-7px',
            top: 'auto',
            transform: 'rotate(45deg)',
            boxShadow: '3px 3px 6px rgba(0,0,0,0.1)',
            borderRight: '1px solid #ddd',
            borderBottom: '1px solid #ddd',
            borderTop: 'none',
            borderLeft: 'none'
        });
        
        // Center arrow horizontally over the target
        var arrowCenter = (targetOffset.left - left) + (targetWidth / 2);
        var arrowHalf = 8;
        var arrowMin = 16;
        var arrowMax = popupWidth - 16;
        var arrowLeft = Math.max(arrowMin, Math.min(arrowMax, arrowCenter - arrowHalf));
        arrow.css('left', arrowLeft + 'px');
    }
    
    // Re-position popup after content height changes
    function repositionPopup() {
        var $active = $('.bible-ref.active-ref');
        if ($active.length) {
            positionPopup($active);
        }
    }
    
    // Close handlers
    $('#biblePopupClose').on('click', closePopup);
    $overlay.on('click', closePopup);
    
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $popup.is(':visible')) {
            closePopup();
        }
    });
    
    // Reposition on scroll/resize (debounced)
    var repositionTimer;
    $(window).on('scroll resize', function() {
        var $active = $('.bible-ref.active-ref');
        if ($popup.is(':visible') && $active.length) {
            clearTimeout(repositionTimer);
            repositionTimer = setTimeout(function() {
                positionPopup($active);
            }, 80);
        }
    });
});
</script>

<script>
$('#shareAsImageBtn').on('click', function() {
    var $btn = $(this);
    var originalHtml = $btn.html();
    
    $btn.html('<i class="fa fa-spinner fa-spin mr-2"></i> Generating...').prop('disabled', true);
    
    // Fetch the image and trigger download
    fetch('{{ route("devotionals.share-image", $devotional->slug) }}')
        .then(function(response) { return response.blob(); })
        .then(function(blob) {
            var url = URL.createObjectURL(blob);
            var link = document.createElement('a');
            link.href = url;
            link.download = 'devotional-{{ !empty($devotional->devotional_date) ? date('d-m-Y', strtotime($devotional->devotional_date)) : $devotional->slug }}.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            
            $btn.html(originalHtml).prop('disabled', false);
        })
        .catch(function() {
            // Fallback: open in new tab
            window.open('{{ route("devotionals.share-image", $devotional->slug) }}', '_blank');
            $btn.html(originalHtml).prop('disabled', false);
        });
});
</script>
@endsection