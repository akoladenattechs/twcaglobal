// Base URL for API calls (supports subdirectory installs like /bli-laravel)
var API_BASE = window.BASE_URL || '';

// ── XSS Protection Helpers ────────────────────────────────────────────────
// Escape plain text before inserting it into innerHTML templates.
function htmlEscape(value) {
    if (value === null || value === undefined) return '';
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Whitelist-based sanitizer for rich HTML (WYSIWYG content).
// Strips scripts, event handlers, and dangerous URLs; keeps safe formatting.
function htmlSanitize(value) {
    if (!value) return '';
    var ALLOWED_TAGS = ['p','br','b','i','em','strong','u','s','ul','ol','li','h1','h2','h3','h4','h5','h6','div','span','blockquote','a','img','hr','pre','code','table','thead','tbody','tr','th','td','figure','figcaption','small','sub','sup'];
    var REMOVE_TAGS = ['script','style','iframe','object','embed','form','input','button','select','textarea','svg','math','link','meta','base','template'];
    var ATTRS_BY_TAG = {
        'a': ['href','title','target','rel'],
        'img': ['src','alt','title','width','height'],
        '*': ['class']
    };
    var ALLOWED_PROTOCOLS = ['http:', 'https:', 'mailto:'];

    var doc = new DOMParser().parseFromString(String(value), 'text/html');

    function cleanNode(node) {
        if (node.nodeType === Node.TEXT_NODE) return;
        if (node.nodeType !== Node.ELEMENT_NODE) {
            if (node.parentNode) node.parentNode.removeChild(node);
            return;
        }
        var tag = node.tagName.toLowerCase();
        if (ALLOWED_TAGS.indexOf(tag) === -1) {
            if (REMOVE_TAGS.indexOf(tag) !== -1) {
                if (node.parentNode) node.parentNode.removeChild(node);
                return;
            }
            // Unknown tag — unwrap, keep children.
            while (node.firstChild) node.parentNode.insertBefore(node.firstChild, node);
            if (node.parentNode) node.parentNode.removeChild(node);
            return;
        }
        var allowedAttrs = ATTRS_BY_TAG[tag] || ATTRS_BY_TAG['*'];
        for (var i = node.attributes.length - 1; i >= 0; i--) {
            var attr = node.attributes[i];
            var name = attr.name.toLowerCase();
            if (allowedAttrs.indexOf(name) === -1) {
                node.removeAttribute(attr.name);
                continue;
            }
            if (name === 'href' || name === 'src') {
                var v = attr.value.trim();
                var lower = v.toLowerCase();
                if (lower.indexOf('javascript:') === 0 || lower.indexOf('vbscript:') === 0 || lower.indexOf('data:') === 0 || lower.indexOf('file:') === 0) {
                    node.removeAttribute(attr.name);
                    continue;
                }
                var colonIdx = lower.indexOf(':');
                if (colonIdx > 0 && ALLOWED_PROTOCOLS.indexOf(lower.substring(0, colonIdx + 1)) === -1) {
                    node.removeAttribute(attr.name);
                    continue;
                }
            }
            if (name === 'target' && attr.value !== '_blank' && attr.value !== '_self') {
                node.removeAttribute(attr.name);
            }
            if (name === 'rel') {
                node.setAttribute('rel', 'noopener noreferrer nofollow');
            }
        }
        if (tag === 'a' && node.getAttribute('target') === '_blank' && !node.getAttribute('rel')) {
            node.setAttribute('rel', 'noopener noreferrer');
        }
        Array.prototype.slice.call(node.childNodes).forEach(cleanNode);
    }

    cleanNode(doc.body);
    return doc.body.innerHTML;
}

// URL for href/src attributes — block dangerous schemes, escape the rest.
function safeHref(value) {
    if (!value) return '';
    var s = String(value).trim();
    var lower = s.toLowerCase();
    if (lower.indexOf('javascript:') === 0 || lower.indexOf('vbscript:') === 0 || lower.indexOf('data:') === 0 || lower.indexOf('file:') === 0) return '';
    return htmlEscape(s);
}

// CSS values used inside generated stylesheets (colors, fonts, sizes).
// Rejects anything that could break out of a declaration.
function safeCssValue(value, pattern) {
    if (!value) return '';
    var s = String(value).trim();
    if (pattern && !pattern.test(s)) return '';
    return s.replace(/[;}{]/g, '');
}
var CSS_COLOR_VALUE = /^(#[0-9a-fA-F]{3,8}|rgb(a)?\([0-9,\s.%]+\)|hsl(a)?\([0-9,\s.%deg]+\)|[a-zA-Z]+)$/;
var CSS_SIZE_VALUE = /^[\d.]+(px|rem|em|%|vw|vh)?$/;
var CSS_FONT_VALUE = /^[a-zA-Z0-9 .\-]+$/;

// Class-name tokens used in generated markup/classList — only safe chars.
function safeClassToken(value) {
    if (!value) return '';
    var s = String(value).trim();
    return /^[a-zA-Z0-9_-]+$/.test(s) ? s : '';
}

// Note: Contact form is handled server-side via POST route — no JS interception needed.

document.addEventListener('DOMContentLoaded', function() {
    // Load homepage content
    loadHomepageContent();
    // Load and apply site settings (appearance, layout, typography, advanced)
    loadSiteSettings();
});

// Function to load homepage content
function loadHomepageContent() {
    fetch(API_BASE + '/api/homepage')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update sliders
                if (data.data.sliders && data.data.sliders.length > 0) {
                    updateSliders(data.data.sliders);
                }
                
                // Update sections
                if (data.data.sections && data.data.sections.length > 0) {
                    renderDynamicSections(data.data.sections);
                }
                
                // Update quotes
                if (data.data.quotes && data.data.quotes.length > 0) {
                    updateQuotes(data.data.quotes);
                }
                
                // Apply site settings
                if (data.data.settings) {
                    applySiteSettings(data.data.settings);
                }
            } else {
                console.error('Error loading homepage content:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

// Function to render dynamic sections
function renderDynamicSections(sections) {
    const sectionsContainer = document.getElementById('dynamic-sections');
    if (!sectionsContainer) return;
    
    sectionsContainer.innerHTML = '';
    
    sections.forEach(section => {
        const sectionDiv = document.createElement('div');
        sectionDiv.className = `section ${safeClassToken(section.section_type)}-section`;
        
        // Create section HTML based on type
        switch(section.section_type) {
            case 'about':
                sectionDiv.innerHTML = `
                    <div class="container">
                        <div class="row">
                            <div class="col-md-6">
                                <img src="${safeHref(section.image_url)}" class="img-fluid" alt="${htmlEscape(section.title)}">
                            </div>
                            <div class="col-md-6">
                                <h2>${htmlEscape(section.title)}</h2>
                                <h3>${htmlEscape(section.subtitle)}</h3>
                                <div class="content">${htmlSanitize(section.content)}</div>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'services':
                sectionDiv.innerHTML = `
                    <div class="container">
                        <div class="row justify-content-center mb-5">
                            <div class="col-md-7 text-center">
                                <h2>${htmlEscape(section.title)}</h2>
                                <h3>${htmlEscape(section.subtitle)}</h3>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="content">${htmlSanitize(section.content)}</div>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'testimonials':
                sectionDiv.innerHTML = `
                    <div class="container">
                        <div class="row justify-content-center mb-5">
                            <div class="col-md-7 text-center">
                                <h2>${htmlEscape(section.title)}</h2>
                                <h3>${htmlEscape(section.subtitle)}</h3>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="content">${htmlSanitize(section.content)}</div>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'cta':
                sectionDiv.innerHTML = `
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-md-10 text-center">
                                <h2>${htmlEscape(section.title)}</h2>
                                <h3>${htmlEscape(section.subtitle)}</h3>
                                <div class="content">${htmlSanitize(section.content)}</div>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            default: // custom
                sectionDiv.innerHTML = `
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                <h2>${htmlEscape(section.title)}</h2>
                                <h3>${htmlEscape(section.subtitle)}</h3>
                                <div class="content">${htmlSanitize(section.content)}</div>
                            </div>
                        </div>
                    </div>
                `;
        }
        
        sectionsContainer.appendChild(sectionDiv);
    });
}

// Similar functions for other sections
function updateSliders(sliders) {
    // Sliders are rendered server-side; this stub exists to prevent
    // reference errors when loadHomepageContent() is called on pages
    // that do not display the hero slider carousel.
}

function updateQuotes(quotes) {
    // Implementation for quotes/testimonials
}

// Add this function to the beginning of your dynamic-content.js file

// Function to load and apply site settings
function loadSiteSettings() {
    fetch(API_BASE + '/api/settings')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                applySiteSettings(data.data);
            } else {
                console.error('Error loading site settings:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

// Function to apply site settings to the page
// Enhance the applySiteSettings function to handle new appearance settings
function applySiteSettings(settings) {
    // Apply general settings
    if (settings.general) {
        // Update page title
        if (settings.general.site_title) {
            document.title = settings.general.site_title;
        }
        
        // Update meta description
        if (settings.general.site_description) {
            const metaDesc = document.querySelector('meta[name="description"]');
            if (metaDesc) {
                metaDesc.setAttribute('content', settings.general.site_description);
            }
        }
        
        // Update meta keywords
        const metaKeywordsValue = settings.advanced?.meta_keywords || settings.general?.site_keywords;
        if (metaKeywordsValue) {
            const metaKeywords = document.querySelector('meta[name="keywords"]');
            if (metaKeywords) {
                metaKeywords.setAttribute('content', metaKeywordsValue);
            }
        }
        
        // Update footer text
        if (settings.general.footer_text) {
            const footerText = document.querySelector('.ftco-footer-widget.mb-4 p');
            if (footerText) {
                footerText.innerHTML = htmlSanitize(settings.general.footer_text);
            }
        }
    }
    
    // Apply contact information
    if (settings.contact) {
        // Update address
        if (settings.contact.address) {
            const addressElements = document.querySelectorAll('.location');
            addressElements.forEach(el => {
                el.innerHTML = `<span class="fa fa-map-marker mr-2"></span> ${htmlEscape(settings.contact.address)}`;
            });
        }
        
        // Update other contact info in contact page
        if (document.querySelector('.contact-info')) {
            if (settings.contact.phone) {
                const phoneElements = document.querySelectorAll('.contact-phone');
                phoneElements.forEach(el => {
                    el.innerHTML = `<span class="fa fa-phone mr-2"></span> ${htmlEscape(settings.contact.phone)}`;
                });
            }
            
            if (settings.contact.email) {
                const emailElements = document.querySelectorAll('.contact-email');
                emailElements.forEach(el => {
                    el.innerHTML = `<span class="fa fa-envelope mr-2"></span> ${htmlEscape(settings.contact.email)}`;
                });
            }
        }
    }
    
    // Apply social media links
    if (settings.social) {
        const socialContainer = document.querySelector('.social-media p.mb-0');
        if (socialContainer) {
            socialContainer.innerHTML = '';
            
            if (settings.social.facebook) {
                socialContainer.innerHTML += `<a href="${safeHref(settings.social.facebook)}" class="d-flex align-items-center justify-content-center"><span class="fa fa-facebook"><i class="sr-only">Facebook</i></span></a>`;
            }
            
            if (settings.social.twitter) {
                socialContainer.innerHTML += `<a href="${safeHref(settings.social.twitter)}" class="d-flex align-items-center justify-content-center"><span class="fa fa-twitter"><i class="sr-only">Twitter</i></span></a>`;
            }
            
            if (settings.social.instagram) {
                socialContainer.innerHTML += `<a href="${safeHref(settings.social.instagram)}" class="d-flex align-items-center justify-content-center"><span class="fa fa-instagram"><i class="sr-only">Instagram</i></span></a>`;
            }
            
            if (settings.social.youtube) {
                socialContainer.innerHTML += `<a href="${safeHref(settings.social.youtube)}" class="d-flex align-items-center justify-content-center"><span class="fa fa-youtube"><i class="sr-only">YouTube</i></span></a>`;
            }
        }
    }
    
    // Apply appearance settings
    if (settings.appearance) {
        // Create or update custom CSS
        let styleElement = document.getElementById('dynamic-styles');
        if (!styleElement) {
            styleElement = document.createElement('style');
            styleElement.id = 'dynamic-styles';
            document.head.appendChild(styleElement);
        }
        
        let customCSS = '';
        
        if (settings.appearance.primary_color) {
            const pc = safeCssValue(settings.appearance.primary_color, CSS_COLOR_VALUE);
            // Calculate a lighter transparent version for dropdown hover
            const pcLight = pc + '18'; // ~10% opacity hex
            customCSS += `
                :root {
                    --primary-color: ${pc};
                    --primary-color-light: ${pcLight};
                    --primary-color-light-50: ${pc + '80'};
                }

                /* Bootstrap core — primary elements */
                .btn-primary, .bg-primary, .badge-primary, .nav-pills .nav-link.active {
                    background-color: var(--primary-color) !important;
                    border-color: var(--primary-color) !important;
                }
                .btn-outline-primary {
                    color: var(--primary-color) !important;
                    border-color: var(--primary-color) !important;
                }

                /* Base link colour — no !important so specific overrides (buttons, etc.) still work */
                a {
                    color: var(--primary-color);
                }
                a:hover, a:focus {
                    color: var(--primary-color) !important;
                }
                .text-primary {
                    color: var(--primary-color) !important;
                }

                /* ── Navigation ── */
                .ftco-navbar-light .navbar-nav > .nav-item.active > a {
                    color: var(--primary-color) !important;
                }
                .ftco-navbar-light .navbar-nav > .nav-item > .nav-link:hover {
                    color: var(--primary-color) !important;
                }
                .ftco-navbar-light .navbar-nav > .nav-item.cta > a,
                .ftco-navbar-light .navbar-nav .nav-item.cta .nav-link.btn {
                    background: var(--primary-color) !important;
                    border: 1px solid var(--primary-color) !important;
                    color: #fff !important;
                }
                .ftco-navbar-light .navbar-nav > .nav-item.cta > a:hover,
                .ftco-navbar-light .navbar-nav .nav-item.cta .nav-link.btn:hover {
                    background: transparent !important;
                    border-color: var(--primary-color) !important;
                    color: var(--primary-color) !important;
                }
                .ftco-navbar-light .navbar-nav .dropdown-item:hover,
                .ftco-navbar-light .navbar-nav .dropdown-item:focus {
                    color: var(--primary-color) !important;
                    background-color: var(--primary-color-light) !important;
                }

                /* ── Hero / Slider ── */
                .hero-wrap .slider-text h1 span {
                    color: var(--primary-color) !important;
                    border-bottom-color: var(--primary-color) !important;
                }
                .hero-wrap .slider-text .subheading {
                    color: var(--primary-color) !important;
                }
                .hero-wrap .slider-text .breadcrumbs span a:hover,
                .hero-wrap .slider-text .breadcrumbs span a:focus {
                    color: var(--primary-color) !important;
                }
                .breadcrumbs span a:hover i,
                .breadcrumbs span a:focus i {
                    color: var(--primary-color) !important;
                }
                .owl-carousel.home-slider .slider-text h2 {
                    color: var(--primary-color) !important;
                }

                /* ── Section headings ── */
                .heading-section .subheading {
                    color: var(--primary-color) !important;
                }
                .heading-section .subheading:before,
                .heading-section .subheading:after {
                    background: var(--primary-color) !important;
                }

                /* ── Services (Worship / Prayer / God's Love) ── */
                .services-2 .text span.subheading {
                    color: var(--primary-color) !important;
                }
                .services-2 .text span.subheading:after {
                    background: var(--primary-color) !important;
                }
                .services-2.services-block {
                    background: var(--primary-color) !important;
                }

                /* ── Sermon cards ── */
                .sermon-wrap .text h2 a {
                    color: var(--primary-color) !important;
                }

                /* ── Upcoming Events + Nuggets (featured cards) ── */
                .featured-card .card-header {
                    background: var(--primary-color) !important;
                }
                .event-item {
                    border-left-color: var(--primary-color) !important;
                }
                .event-date {
                    background: var(--primary-color) !important;
                }
                .quote-item {
                    border-left-color: var(--primary-color) !important;
                }
                .quote-content .blockquote .text-primary {
                    color: var(--primary-color) !important;
                }

                /* ── Radio section ── */
                /* Background stays dark (original CSS) — only player controls use primary */
                .play-btn {
                    background: var(--primary-color) !important;
                    border-color: var(--primary-color) !important;
                    color: #fff !important;
                }
                .play-btn:hover {
                    box-shadow: 0 0 20px var(--primary-color-light-50) !important;
                }
                .live-indicator {
                    border-color: var(--primary-color) !important;
                }
                .live-dot {
                    background: var(--primary-color) !important;
                }
                .live-text {
                    color: var(--primary-color) !important;
                }
                .volume-slider::-webkit-slider-thumb {
                    background: var(--primary-color) !important;
                }
                .volume-slider::-moz-range-thumb {
                    background: var(--primary-color) !important;
                }

                /* ── Footer ── */
                .social-icon-link {
                    background: var(--primary-color) !important;
                    color: #fff !important;
                }
                .social-icon-link:hover {
                    background: transparent !important;
                    color: var(--primary-color) !important;
                }
                .ftco-footer.bg-light .fa,
                .ftco-footer.bg-light a:hover {
                    color: var(--primary-color) !important;
                }
                /* Social icons inside footer — override .ftco-footer.bg-light .fa */
                .social-icons-wrapper .social-icon-link .fa,
                .social-icons-wrapper .social-icon-link .fab,
                .social-icons-wrapper .social-icon-link .fas,
                .social-icons-wrapper .social-icon-link .far {
                    color: #fff !important;
                }

                /* ── Testimony ── */
                .testimony-wrap .quote {
                    background: var(--primary-color) !important;
                }

                /* ── Staff ── */
                .staff .text {
                    background: var(--primary-color) !important;
                }

                /* ── Blog entries ── */
                .blog-entry .text:after {
                    background: var(--primary-color) !important;
                }
                .blog-entry:hover .text:after {
                    background: var(--primary-color) !important;
                }
                .blog-entry .text .heading a:hover,
                .blog-entry .text .heading a:focus,
                .blog-entry .text .heading a:active {
                    color: var(--primary-color) !important;
                }
                .blog-entry .text .meta-chat {
                    color: var(--primary-color) !important;
                }
                .blog-entry .meta > div a {
                    color: var(--primary-color) !important;
                }

                /* ── Block-21 ── */
                .block-21 .text .heading a:hover,
                .block-21 .text .heading a:active,
                .block-21 .text .heading a:focus {
                    color: var(--primary-color) !important;
                }

                /* ── Pagination ── */
                .block-27 ul li.active a,
                .block-27 ul li.active span {
                    background: var(--primary-color) !important;
                }
                .pagination .page-item.active .page-link {
                    background-color: var(--primary-color) !important;
                    border-color: var(--primary-color) !important;
                }
                .pagination .page-item:not(.active):not(.disabled) .page-link:hover {
                    border-color: var(--primary-color) !important;
                    color: var(--primary-color) !important;
                }

                /* ── Contact page ── */
                .contact-wrap {
                    background: var(--primary-color) !important;
                }
                .dbox .icon {
                    background: var(--primary-color) !important;
                }
                .dbox p a {
                    color: var(--primary-color) !important;
                }
                .contactForm .form-control:focus,
                .contactForm .form-control:active {
                    border-color: var(--primary-color) !important;
                }

                /* ── Search ── */
                .content-search-form .btn-search {
                    background: var(--primary-color) !important;
                    border: 1px solid var(--primary-color) !important;
                }
                .content-search-form .btn-search:hover {
                    color: var(--primary-color) !important;
                }

                /* ── Comments ── */
                .comment-list li .comment-body .reply {
                    background: var(--primary-color) !important;
                }

                /* ── Categories ── */
                .categories li a:hover,
                .categories li a:focus {
                    color: var(--primary-color) !important;
                }
                .categories li.active a {
                    color: var(--primary-color) !important;
                }
                .categories li.active a span {
                    color: var(--primary-color) !important;
                }

                /* ── Social media hover (top bar) ── */
                .social-media p a:hover {
                    background: var(--primary-color) !important;
                    border-color: var(--primary-color) !important;
                }

                /* ── bg-tertiary / bg-quarternary ── */
                .bg-tertiary, .bg-quarternary {
                    background: var(--primary-color) !important;
                }

                /* ── Custom btn-primary variants ── */
                .btn.btn-primary {
                    background: var(--primary-color) !important;
                    border: 1px solid var(--primary-color) !important;
                }
                .btn.btn-primary:hover {
                    background: transparent !important;
                    border-color: var(--primary-color) !important;
                    color: var(--primary-color) !important;
                }
                .btn.btn-primary.btn-outline-primary {
                    border: 1px solid var(--primary-color) !important;
                    color: var(--primary-color) !important;
                }
                .btn.btn-primary.btn-outline-primary:hover {
                    background: var(--primary-color) !important;
                    color: #fff !important;
                }

                /* ── Blockquote custom hover ── */
                .blockquote-custom:hover {
                    border-left-color: var(--primary-color) !important;
                }
            `;
        }
        
        if (settings.appearance.secondary_color) {
            const sc = safeCssValue(settings.appearance.secondary_color, CSS_COLOR_VALUE);
            customCSS += `
                :root {
                    --secondary-color: ${sc};
                }
                .btn-secondary, .bg-secondary, .badge-secondary {
                    background-color: var(--secondary-color) !important;
                    border-color: var(--secondary-color) !important;
                }
                .text-secondary {
                    color: var(--secondary-color) !important;
                }
                .book-section .heading-section h3,
                .book-title,
                .devotional-title {
                    color: var(--secondary-color) !important;
                }
            `;
        }
        
        // Resolve a stored asset path that may be a full URL (R2/CDN) or a
        // relative path — only prepend the site base for relative paths.
        function resolveAssetUrl(path) {
            if (!path) return '';
            if (/^(https?:)?\/\//i.test(path)) return path;
            return API_BASE + '/' + String(path).replace(/^\/+/, '');
        }

        // Update logo
        if (settings.appearance.logo) {
            const logoElements = document.querySelectorAll('.navbar-brand img');
            logoElements.forEach(el => {
                el.src = resolveAssetUrl(settings.appearance.logo);
            });
        }
        
        // Update favicon
        if (settings.appearance.favicon) {
            const faviconUrl = resolveAssetUrl(settings.appearance.favicon);
            const faviconElement = document.querySelector('link[rel="icon"]');
            if (faviconElement) {
                faviconElement.href = faviconUrl;
            } else {
                const newFavicon = document.createElement('link');
                newFavicon.rel = 'icon';
                newFavicon.href = faviconUrl;
                document.head.appendChild(newFavicon);
            }
        }
        
        // Apply font family
        if (settings.appearance.font_family) {
            customCSS += `
                body, p, li, a, .form-control, span:not(.fa):not(.fas):not(.far):not(.fab) {
                    font-family: '${safeCssValue(settings.appearance.font_family, CSS_FONT_VALUE)}', sans-serif !important;
                }
                /* Restore icon fonts */
                .fa, .fas, .far, .fab {
                    font-family: 'FontAwesome' !important;
                }
            `;
        }
        
        // Apply button style
        if (settings.appearance.button_style) {
            const btnStyle = settings.appearance.button_style;
            if (btnStyle === 'rounded') {
                customCSS += `
                    .btn, button, input[type="submit"] {
                        border-radius: 50px !important;
                    }
                `;
            } else if (btnStyle === 'square') {
                customCSS += `
                    .btn, button, input[type="submit"] {
                        border-radius: 0 !important;
                    }
                `;
            } else if (btnStyle === 'pill') {
                customCSS += `
                    .btn, button, input[type="submit"] {
                        border-radius: 30px !important;
                        padding-left: 25px !important;
                        padding-right: 25px !important;
                    }
                `;
            }
        }
        
        // Apply header style
        if (settings.appearance.header_style) {
            const hdrStyle = settings.appearance.header_style;
            if (hdrStyle === 'fixed') {
                customCSS += `
                    header, .ftco-navbar-light {
                        position: fixed !important;
                        top: 0;
                        left: 0;
                        right: 0;
                        z-index: 9999;
                    }
                    body {
                        padding-top: 100px;
                    }
                `;
            } else if (hdrStyle === 'sticky') {
                customCSS += `
                    header, .ftco-navbar-light {
                        position: sticky !important;
                        top: 0;
                        z-index: 9999;
                    }
                `;
            }
        }
        
        styleElement.textContent = customCSS;
    }
    
    // Apply layout settings
    if (settings.layout) {
        let layoutCSS = '';
        
        // Apply navigation style
        // Support both 'nav_style' (JS key) and 'navigation_style' (admin form key)
        const navStyle = settings.layout.navigation_style || settings.layout.nav_style;
        if (navStyle) {
            const navElement = document.querySelector('.ftco-navbar-light');
            if (navElement) {
                // Remove existing style classes
                navElement.classList.remove('nav-standard', 'nav-centered', 'nav-transparent');
                // Add new style class
                navElement.classList.add(`nav-${navStyle}`);
                
                // Add specific CSS for each navigation style
                if (navStyle === 'transparent') {
                    layoutCSS += `
                        .ftco-navbar-light {
                            background: transparent !important;
                            position: absolute;
                            left: 0;
                            right: 0;
                            z-index: 3;
                        }
                        .ftco-navbar-light .navbar-brand {
                            color: #fff;
                        }
                        .ftco-navbar-light .navbar-nav > .nav-item > .nav-link {
                            color: rgba(255, 255, 255, 0.9) !important;
                        }
                    `;
                } else if (navStyle === 'centered') {
                    layoutCSS += `
                        .ftco-navbar-light .navbar-brand {
                            margin: 0 auto;
                        }
                        .ftco-navbar-light .navbar-nav {
                            margin: 0 auto;
                            text-align: center;
                        }
                    `;
                }
            }
        }
        
        // Apply footer layout
        if (settings.layout.footer_layout) {
            const footerElement = document.querySelector('footer.ftco-footer');
            if (footerElement) {
                // Remove existing style classes
                footerElement.classList.remove('footer-standard', 'footer-expanded', 'footer-minimal');
                // Add new style class
                footerElement.classList.add(`footer-${safeClassToken(settings.layout.footer_layout)}`);
                
                // Add specific CSS for each footer layout
                if (settings.layout.footer_layout === 'minimal') {
                    layoutCSS += `
                        .footer-minimal .ftco-footer-widget {
                            display: none;
                        }
                        .footer-minimal .row:first-child {
                            display: none;
                        }
                        .footer-minimal .row:last-child {
                            margin-top: 0;
                        }
                    `;
                } else if (settings.layout.footer_layout === 'expanded') {
                    layoutCSS += `
                        .footer-expanded .ftco-footer-widget {
                            margin-bottom: 40px;
                        }
                        .footer-expanded .ftco-footer-widget h2 {
                            font-size: 24px;
                        }
                    `;
                }
            }
        }
        
        // Apply sidebar position
        const sidebarPosition = settings.layout.sidebar_position || 'right';
        if (sidebarPosition === 'left') {
            layoutCSS += `
                .sidebar {
                    order: -1;
                }
                @media (min-width: 768px) {
                    .sidebar + .content-area {
                        float: right;
                    }
                }
            `;
        } else if (sidebarPosition === 'none') {
            layoutCSS += `
                .sidebar {
                    display: none;
                }
                .content-area {
                    width: 100% !important;
                    flex: 0 0 100% !important;
                    max-width: 100% !important;
                }
            `;
        }
        
        // Apply homepage layout
        if (settings.layout.homepage_layout && window.location.pathname.endsWith('index.html') || window.location.pathname === '/') {
            const mainElement = document.querySelector('main');
            if (mainElement) {
                // Remove existing style classes
                mainElement.classList.remove('layout-default', 'layout-alternative', 'layout-minimal');
                // Add new style class
                mainElement.classList.add(`layout-${safeClassToken(settings.layout.homepage_layout)}`);
                
                // Add specific CSS for each homepage layout
                if (settings.layout.homepage_layout === 'minimal') {
                    // Hide certain sections in minimal layout
                    layoutCSS += `
                        .layout-minimal .ftco-section:not(:first-child):not(:nth-child(2)):not(:last-child) {
                            display: none;
                        }
                    `;
                } else if (settings.layout.homepage_layout === 'alternative') {
                    // Rearrange sections in alternative layout
                    layoutCSS += `
                        .layout-alternative .ftco-section {
                            display: flex;
                            flex-direction: column;
                        }
                        .layout-alternative .ftco-section:nth-child(even) .row {
                            flex-direction: row-reverse;
                        }
                    `;
                }
            }
        }
        
        // Add layout CSS to the page
        if (layoutCSS) {
            let layoutStyleElement = document.getElementById('dynamic-layout-styles');
            if (!layoutStyleElement) {
                layoutStyleElement = document.createElement('style');
                layoutStyleElement.id = 'dynamic-layout-styles';
                document.head.appendChild(layoutStyleElement);
            }
            layoutStyleElement.textContent = layoutCSS;
        }
    }
    
    // Apply typography settings
    if (settings.typography) {
        let typographyCSS = '';
        
        // Apply heading font
        if (settings.typography.heading_font) {
            typographyCSS += `
                h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
                    font-family: '${safeCssValue(settings.typography.heading_font, CSS_FONT_VALUE)}', sans-serif !important;
                }
            `;
            
            // Dynamically load the font if not already loaded
            loadGoogleFont(settings.typography.heading_font);
        }
        
        // Apply body font
        if (settings.typography.body_font) {
            typographyCSS += `
                body {
                    font-family: '${safeCssValue(settings.typography.body_font, CSS_FONT_VALUE)}', sans-serif !important;
                }
            `;
            
            // Dynamically load the font if not already loaded
            loadGoogleFont(settings.typography.body_font);
        }
        
        // Apply base font size
        if (settings.typography.base_font_size) {
            typographyCSS += `
                body {
                    font-size: ${safeCssValue(settings.typography.base_font_size, CSS_SIZE_VALUE)} !important;
                }
            `;
        }
        
        // Apply line height
        if (settings.typography.line_height) {
            typographyCSS += `
                body, p, li, .form-control {
                    line-height: ${safeCssValue(settings.typography.line_height, CSS_SIZE_VALUE)} !important;
                }
            `;
        }
        
        // Add typography CSS to the page
        if (typographyCSS) {
            let typographyStyleElement = document.getElementById('dynamic-typography-styles');
            if (!typographyStyleElement) {
                typographyStyleElement = document.createElement('style');
                typographyStyleElement.id = 'dynamic-typography-styles';
                document.head.appendChild(typographyStyleElement);
            }
            typographyStyleElement.textContent = typographyCSS;
        }
    }
    
    // Apply advanced settings
    if (settings.advanced) {
        // Apply custom CSS
        if (settings.advanced.custom_css) {
            let customStyleElement = document.getElementById('custom-css');
            if (!customStyleElement) {
                customStyleElement = document.createElement('style');
                customStyleElement.id = 'custom-css';
                document.head.appendChild(customStyleElement);
            }
            customStyleElement.textContent = settings.advanced.custom_css;
        }
        
        // Apply custom JavaScript
        if (settings.advanced.custom_js) {
            try {
                // Create a new script element
                const scriptElement = document.createElement('script');
                scriptElement.textContent = settings.advanced.custom_js;
                document.body.appendChild(scriptElement);
            } catch (error) {
                console.error('Error executing custom JavaScript:', error);
            }
        }
        
        // Apply Google Analytics
        if (settings.advanced.google_analytics_id) {
            const gaId = settings.advanced.google_analytics_id;
            // Check if GA script is already loaded
            if (!document.querySelector(`script[src*="googletagmanager.com/gtag/js?id=${gaId}"]`)) {
                const gaScript = document.createElement('script');
                gaScript.async = true;
                gaScript.src = `https://www.googletagmanager.com/gtag/js?id=${gaId}`;
                document.head.appendChild(gaScript);
                
                const gaInit = document.createElement('script');
                gaInit.textContent = `
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments);}
                    gtag('js', new Date());
                    gtag('config', '${gaId}');
                `;
                document.head.appendChild(gaInit);
            }
        }
        
        // Apply header scripts - these should be loaded by the server-side
        // Apply footer scripts - these should be loaded by the server-side
    }
    
    // Apply currency settings
    if (settings.currency) {
        // Store currency settings globally for use by other functions
        window.currencySettings = {
            symbol: settings.currency.currency_symbol || '₦',
            code: settings.currency.default_currency || 'NGN',
            position: settings.currency.currency_position || 'before',
            decimalPlaces: parseInt(settings.currency.decimal_places) || 2,
            thousandsSep: settings.currency.thousands_separator || ',',
            decimalSep: settings.currency.decimal_separator || '.',
            exchangeRate: parseFloat(settings.currency.exchange_rate) || 1
        };
        
        // Update all elements with currency data attributes or classes
        document.querySelectorAll('[data-currency]').forEach(el => {
            const amount = parseFloat(el.getAttribute('data-currency'));
            if (!isNaN(amount)) {
                el.textContent = formatCurrency(amount);
            }
        });
        
        // Update elements with .currency-value class
        document.querySelectorAll('.currency-value').forEach(el => {
            const amount = parseFloat(el.textContent.replace(/[^0-9.-]+/g, ''));
            if (!isNaN(amount)) {
                el.textContent = formatCurrency(amount);
            }
        });
    }
}

/**
 * Format a number as currency using the global currency settings
 * @param {number} amount - The amount to format
 * @returns {string} The formatted currency string
 */
function formatCurrency(amount) {
    const cs = window.currencySettings || {
        symbol: '₦',
        position: 'before',
        decimalPlaces: 2,
        thousandsSep: ',',
        decimalSep: '.'
    };
    
    // Format the number
    const parts = Math.abs(amount).toFixed(cs.decimalPlaces).split('.');
    const intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, cs.thousandsSep);
    const decPart = parts[1];
    const formatted = intPart + cs.decimalSep + decPart;
    
    // Apply position
    const result = cs.position === 'after' 
        ? formatted + cs.symbol 
        : cs.symbol + formatted;
    
    // Handle negative numbers
    return amount < 0 ? '-' + result : result;
}

// Helper function to load Google Fonts dynamically
function loadGoogleFont(fontFamily) {
    const clean = safeCssValue(fontFamily, CSS_FONT_VALUE);
    if (!clean) return;
    const encoded = encodeURIComponent(clean);
    const fontLink = document.querySelector(`link[href*="fonts.googleapis.com/css?family=${encoded}"]`);
    if (!fontLink) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = `https://fonts.googleapis.com/css?family=${encoded}:300,400,500,700&display=swap`;
        document.head.appendChild(link);
    }
}

function updateTextSlider(quotes) {
    const textSlider = document.getElementById('text-slider');
    if (!textSlider) return;
    
    // Clear existing content
    textSlider.innerHTML = '';
    
    // Add quotes to the slider
    quotes.forEach(quote => {
        const quoteHtml = `
            <div class="item">
                <h4 style="font-size: clamp(1.1rem, 2.5vw, 1.35rem); line-height: 1.6; margin: 0; color: #dc3545; font-weight: 500;">${htmlSanitize(quote.content)}</h4>
                ${quote.author ? `<p style="margin-top: 1rem; color: #dc3545; font-size: clamp(0.875rem, 2vw, 1rem);"><b>~ ${htmlEscape(quote.author)}</b></p>` : ''}
            </div>
        `;
        textSlider.innerHTML += quoteHtml;
    });
    
    // Initialize Owl Carousel
    $(textSlider).owlCarousel({
        items: 1,
        loop: true,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        nav: false,
        dots: true,
        animateIn: 'fadeIn',
        animateOut: 'fadeOut'
    });
}