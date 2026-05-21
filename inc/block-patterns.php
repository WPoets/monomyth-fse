<?php
/**
 * Block Patterns for Monomyth FSE
 *
 * @package Monomyth_FSE
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register block patterns
 *
 * Block patterns can also be registered as individual PHP files in the /patterns directory.
 * This file provides an alternative way to register patterns programmatically.
 */
function monomyth_register_block_patterns() {
    
    // Hero Section Pattern
    register_block_pattern(
        'monomyth-fse/hero-section',
        array(
            'title'       => __( 'Hero Section', 'monomyth-fse' ),
            'description' => __( 'A hero section with heading, text, and button.', 'monomyth-fse' ),
            'categories'  => array( 'monomyth', 'featured' ),
            'keywords'    => array( 'hero', 'banner', 'header' ),
            'content'     => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80","right":"var:preset|spacing|30","left":"var:preset|spacing|30"}}},"backgroundColor":"primary","textColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-color has-primary-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--80);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--80);padding-left:var(--wp--preset--spacing--30)">

<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"clamp(2.5rem, 5vw, 4rem)"}}} -->
<h1 class="wp-block-heading has-text-align-center" style="font-size:clamp(2.5rem, 5vw, 4rem)">Welcome to Your Site</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"var:preset|font-size|large"}}} -->
<p class="has-text-align-center" style="font-size:var(--wp--preset--font-size--large)">A brief description of your site and what visitors can expect to find here.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--40)">
<!-- wp:button {"backgroundColor":"base","textColor":"primary"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-primary-color has-base-background-color has-text-color has-background wp-element-button">Get Started</a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button">Learn More</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
<!-- /wp:group -->',
        )
    );

    // Call to Action Pattern
    register_block_pattern(
        'monomyth-fse/call-to-action',
        array(
            'title'       => __( 'Call to Action', 'monomyth-fse' ),
            'description' => __( 'A call to action section with heading, text and button.', 'monomyth-fse' ),
            'categories'  => array( 'monomyth' ),
            'keywords'    => array( 'cta', 'call to action', 'button' ),
            'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","right":"var:preset|spacing|30","left":"var:preset|spacing|30"}}},"backgroundColor":"neutral-light","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-neutral-light-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--30)">

<!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="wp-block-heading has-text-align-center">Ready to Get Started?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Join thousands of satisfied customers who have already made the switch.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--30)">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Start Free Trial</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
<!-- /wp:group -->',
        )
    );

    // Features Grid Pattern
    register_block_pattern(
        'monomyth-fse/features-grid',
        array(
            'title'       => __( 'Features Grid', 'monomyth-fse' ),
            'description' => __( 'A three-column grid showcasing features.', 'monomyth-fse' ),
            'categories'  => array( 'monomyth' ),
            'keywords'    => array( 'features', 'grid', 'columns' ),
            'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">

<!-- wp:heading {"textAlign":"center","level":2,"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--50)">Features</h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns">

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"var:preset|font-size|large"}}} -->
<h3 class="wp-block-heading" style="font-size:var(--wp--preset--font-size--large)">Feature One</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Description of the first feature and how it benefits your users.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"var:preset|font-size|large"}}} -->
<h3 class="wp-block-heading" style="font-size:var(--wp--preset--font-size--large)">Feature Two</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Description of the second feature and how it benefits your users.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"var:preset|font-size|large"}}} -->
<h3 class="wp-block-heading" style="font-size:var(--wp--preset--font-size--large)">Feature Three</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Description of the third feature and how it benefits your users.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->',
        )
    );

    // Testimonial Pattern
    register_block_pattern(
        'monomyth-fse/testimonial',
        array(
            'title'       => __( 'Testimonial', 'monomyth-fse' ),
            'description' => __( 'A testimonial quote with attribution.', 'monomyth-fse' ),
            'categories'  => array( 'monomyth' ),
            'keywords'    => array( 'testimonial', 'quote', 'review' ),
            'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","right":"var:preset|spacing|40","left":"var:preset|spacing|40"}},"border":{"radius":"8px"}},"backgroundColor":"neutral-light","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-neutral-light-background-color has-background" style="border-radius:8px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)">

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"var:preset|font-size|large","fontStyle":"italic"}}} -->
<p class="has-text-align-center" style="font-size:var(--wp--preset--font-size--large);font-style:italic">"This product has completely transformed how we work. Highly recommended!"</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontWeight":"600"}}} -->
<p class="has-text-align-center" style="font-weight:600">— Jane Doe, CEO at Company</p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->',
        )
    );

    // Contact Section Pattern
    register_block_pattern(
        'monomyth-fse/contact-section',
        array(
            'title'       => __( 'Contact Section', 'monomyth-fse' ),
            'description' => __( 'A contact section with information and call to action.', 'monomyth-fse' ),
            'categories'  => array( 'monomyth' ),
            'keywords'    => array( 'contact', 'email', 'address' ),
            'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">

<!-- wp:heading {"textAlign":"center","level":2,"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--40)">Get in Touch</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns">

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Email</h4>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><a href="mailto:hello@example.com">hello@example.com</a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Phone</h4>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><a href="tel:+1234567890">+1 (234) 567-890</a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Address</h4>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>123 Main Street<br>City, State 12345</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->',
        )
    );

}
add_action( 'init', 'monomyth_register_block_patterns' );
