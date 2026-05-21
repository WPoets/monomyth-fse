/**
 * Monomyth FSE - Custom JavaScript
 * 
 * Add your custom JavaScript here. This file is enqueued on the front-end
 * and can be used to add interactivity to your theme.
 * 
 * The monomythData object is available with the following properties:
 * - monomythData.ajaxUrl    - WordPress AJAX URL
 * - monomythData.nonce      - Security nonce
 * - monomythData.homeUrl    - Home URL
 * - monomythData.themeUrl   - Theme directory URL
 * - monomythData.isLoggedIn - Whether user is logged in
 */

(function($) {
    'use strict';

    /**
     * Initialize theme functionality when DOM is ready
     */
    $(document).ready(function() {
        Monomyth.init();
    });

    /**
     * Monomyth Theme Object
     */
    var Monomyth = {
        
        /**
         * Initialize all modules
         */
        init: function() {
            this.smoothScroll();
            this.mobileNavigation();
            this.lazyLoadImages();
            this.skipLink();
            
            // Trigger custom event for Awesome Enterprise integration
            $(document).trigger('monomyth:ready');
        },

        /**
         * Smooth scroll for anchor links
         */
        smoothScroll: function() {
            $('a[href*="#"]:not([href="#"])').on('click', function(e) {
                if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') && 
                    location.hostname === this.hostname) {
                    
                    var target = $(this.hash);
                    target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                    
                    if (target.length) {
                        e.preventDefault();
                        $('html, body').animate({
                            scrollTop: target.offset().top - 100
                        }, 500);
                        
                        // Update URL hash
                        if (history.pushState) {
                            history.pushState(null, null, this.hash);
                        }
                    }
                }
            });
        },

        /**
         * Mobile navigation enhancements
         */
        mobileNavigation: function() {
            // Add toggle functionality for mobile menu (if using custom mobile menu)
            var $mobileToggle = $('.mobile-nav-toggle');
            var $mobileMenu = $('.mobile-navigation');
            
            $mobileToggle.on('click', function(e) {
                e.preventDefault();
                $mobileMenu.toggleClass('is-active');
                $(this).attr('aria-expanded', $mobileMenu.hasClass('is-active'));
            });

            // Close mobile menu on escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $mobileMenu.hasClass('is-active')) {
                    $mobileMenu.removeClass('is-active');
                    $mobileToggle.attr('aria-expanded', 'false').focus();
                }
            });
        },

        /**
         * Lazy load images (native browser support fallback)
         */
        lazyLoadImages: function() {
            // Add loading="lazy" to images that don't have it
            $('img:not([loading])').attr('loading', 'lazy');
        },

        /**
         * Skip link functionality for accessibility
         */
        skipLink: function() {
            var $skipLink = $('.skip-link');
            
            $skipLink.on('click', function(e) {
                e.preventDefault();
                var target = $($(this).attr('href'));
                
                if (target.length) {
                    target.attr('tabindex', '-1').focus();
                }
            });
        },

        /**
         * Utility: Debounce function
         */
        debounce: function(func, wait) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    func.apply(context, args);
                }, wait);
            };
        },

        /**
         * Utility: Throttle function
         */
        throttle: function(func, limit) {
            var lastFunc, lastRan;
            return function() {
                var context = this, args = arguments;
                if (!lastRan) {
                    func.apply(context, args);
                    lastRan = Date.now();
                } else {
                    clearTimeout(lastFunc);
                    lastFunc = setTimeout(function() {
                        if ((Date.now() - lastRan) >= limit) {
                            func.apply(context, args);
                            lastRan = Date.now();
                        }
                    }, limit - (Date.now() - lastRan));
                }
            };
        }
    };

    // Expose Monomyth object globally
    window.Monomyth = Monomyth;

})(jQuery);

/**
 * Awesome Enterprise Integration
 * 
 * This section provides hooks for integrating with the Awesome No Code Platform.
 * The SPA library (if loaded) will work seamlessly with this theme.
 */

// Listen for Awesome Enterprise ready event
document.addEventListener('DOMContentLoaded', function() {
    // Check if SPA library is loaded
    if (typeof spa !== 'undefined') {
        console.log('Monomyth FSE: SPA library detected');
        
        // You can initialize SPA-specific functionality here
        // spa.app.start() would typically be called by the platform
    }
});
