(function($) {
	"use strict";

	$(window).stellar({
		responsive: true,
		parallaxBackgrounds: true,
		parallaxElements: true,
		horizontalScrolling: false,
		hideDistantElements: false,
		scrollProperty: 'scroll'
	});

	var fullHeight = function() {
		$('.js-fullheight').each(function() {
			if ($(this).hasClass('hero-wrap-2') || $(this).closest('.hero-wrap-2').length) {
				$(this).css('height', Math.round($(window).height() * 0.5));
			} else {
				$(this).css('height', $(window).height());
			}
		});
		$(window).resize(function(){
			$('.js-fullheight').each(function() {
				if ($(this).hasClass('hero-wrap-2') || $(this).closest('.hero-wrap-2').length) {
					$(this).css('height', Math.round($(window).height() * 0.5));
				} else {
					$(this).css('height', $(window).height());
				}
			});
		});
	};
	fullHeight();

	var loader = function() {
		setTimeout(function() { 
			if($('#ftco-loader').length > 0) {
				$('#ftco-loader').removeClass('show');
			}
		}, 1);
	};
	loader();

	var carousel = function() {
		$('.home-slider').owlCarousel({
			loop:true,
			autoplay: true,
			margin:0,
			animateOut: 'fadeOutLeft',
			animateIn: 'fadeInDown',
			nav:true,
			dots: true,
			autoplayTimeout:6000,
			autoplayHoverPause: false,
			items: 1,
			navText : ["<span class='ion-ios-arrow-back'></span>","<span class='ion-ios-arrow-forward'></span>"],
			responsive:{
				0:{ items:1 },
				600:{ items:1 },
				1000:{ items:1 }
			}
		});
	};
	carousel();

	// Video background handling on slide change (text is static from hero_settings)
	if ($('.hero-showcase').length && $('.hero-showcase').data('sliders')) {
		$('.home-slider').on('changed.owl.carousel', function(event) {
			// Pause all videos, play current video background
			var $allVideos = $('.home-slider .slider-item').find('video.hero-video-bg, iframe.hero-video-bg');
			$allVideos.each(function() {
				try { this.contentWindow && this.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*'); } catch(e){}
				try { this.pause && this.pause(); } catch(e){}
			});

			// Find the video in the active slide
			var $activeItem = $('.home-slider .owl-item.active .slider-item');
			var $activeVideo = $activeItem.find('video.hero-video-bg');
			if ($activeVideo.length) {
				try { $activeVideo[0].play(); } catch(e){}
			}
		});
	}

	$('nav .dropdown').hover(function(){
		var $this = $(this);
		// Don't apply hover behavior inside the mobile side drawer
		if ($this.closest('.side-drawer').length) return;
		$this.addClass('show');
		$this.find('> a').attr('aria-expanded', true);
		$this.find('.dropdown-menu').addClass('show');
	}, function(){
		var $this = $(this);
		// Don't apply hover behavior inside the mobile side drawer
		if ($this.closest('.side-drawer').length) return;
		$this.removeClass('show');
		$this.find('> a').attr('aria-expanded', false);
		$this.find('.dropdown-menu').removeClass('show');
	});

	$('#dropdown04').on('show.bs.dropdown', function () {
		console.log('show');
	});

	$('.image-popup').magnificPopup({
		type: 'image',
		closeOnContentClick: true,
		closeBtnInside: false,
		fixedContentPos: true,
		mainClass: 'mfp-no-margins mfp-with-zoom',
		gallery: {
			enabled: true,
			navigateByImgClick: true,
			preload: [0,1]
		},
		image: {
			verticalFit: true
		},
		zoom: {
			enabled: true,
			duration: 300
		}
	});

	$('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
		disableOn: 700,
		type: 'iframe',
		mainClass: 'mfp-fade',
		removalDelay: 160,
		preloader: false,
		fixedContentPos: false
	});

	var counter = function() {
		$('#section-counter, .ftco-appointment').waypoint( function( direction ) {
			if( direction === 'down' && !$(this.element).hasClass('ftco-animated') ) {
				var comma_separator_number_step = $.animateNumber.numberStepFactories.separator(',')
				$('.number').each(function(){
					var $this = $(this),
						num = $this.data('number');
					console.log(num);
					$this.animateNumber({
						number: num,
						numberStep: comma_separator_number_step
					}, 7000);
				});
			}
		} , { offset: '95%' } );
	}
	counter();

	var contentWayPoint = function() {
		var i = 0;
		$('.ftco-animate').waypoint( function( direction ) {
			if( direction === 'down' && !$(this.element).hasClass('ftco-animated') ) {
				i++;
				$(this.element).addClass('item-animate');
				setTimeout(function(){
					$('body .ftco-animate.item-animate').each(function(k){
						var el = $(this);
						setTimeout( function () {
							var effect = el.data('animate-effect');
							if ( effect === 'fadeIn') {
								el.addClass('fadeIn ftco-animated');
							} else if ( effect === 'fadeInLeft') {
								el.addClass('fadeInLeft ftco-animated');
							} else if ( effect === 'fadeInRight') {
								el.addClass('fadeInRight ftco-animated');
							} else {
								el.addClass('fadeInUp ftco-animated');
							}
							el.removeClass('item-animate');
						},  k * 50, 'easeInOutExpo' );
					});
				}, 100);
			}
		} , { offset: '95%' } );
	};
	contentWayPoint();

	$('.appointment_date').datepicker({
		'format': 'm/d/yyyy',
		'autoclose': true
	});

	$('.appointment_time').timepicker();



})(jQuery);

// Add this after the existing carousel function
var featuredSectionsCarousel = function() {
    // Events slider
    $('.events-slider').owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        margin: 0,
        nav: true,
        dots: true,
        items: 1,
        navText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"],
        responsive: {
            0: { items: 1 },
            600: { items: 1 },
            1000: { items: 1 }
        }
    });
    
    // Quotes slider
    $('.quotes-slider').owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 6000,
        autoplayHoverPause: true,
        margin: 0,
        nav: true,
        dots: true,
        items: 1,
        navText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"],
        responsive: {
            0: { items: 1 },
            600: { items: 1 },
            1000: { items: 1 }
        }
    });
};

// Call the function after DOM is ready
$(document).ready(function() {
    featuredSectionsCarousel();
});
// Remove the infinite loop implementation below this line
