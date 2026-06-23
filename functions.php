<?php
/**
 * Monomyth FSE Theme Functions
 *
 * A minimal FSE theme designed for the Awesome XP Platform.
 * All template parts (header, footer, sidebar, etc.) are rendered dynamically
 * from Gutenberg blocks that call Awesome Enterprise services.
 *
 * @package Monomyth_FSE
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Define theme constants
 */
define('MONOMYTH_VERSION', '1.0.1');
define('MONOMYTH_DIR', get_template_directory());
define('MONOMYTH_URI', get_template_directory_uri());

/**
 * ============================================================================
 * AWESOME XP DETECTION
 * ============================================================================
 */

/**
 * Check if Awesome XP is active
 */
function monomyth_is_awesome_active()
{
    return function_exists('aw2_library') ||
        defined('AW2_LIBRARY_VERSION') ||
        class_exists('aw2_library');
}

/**
 * Check if an AE module exists (mimics \aw2_library::post_exists)
 * 
 * @param string $module_name Module name
 * @param string $post_type   Post type to check in
 * @return bool
 */
function monomyth_ae_module_exists($module_name, $post_type = '')
{
    if (!monomyth_is_awesome_active()) {
        return false;
    }

    if (class_exists('aw2_library') && method_exists('aw2_library', 'post_exists')) {
        return \aw2_library::post_exists($module_name, $post_type);
    }

    // Fallback: check if post exists
    if (empty($post_type)) {
        return false;
    }

    $existing = get_posts(array(
        'name' => $module_name,
        'post_type' => $post_type,
        'post_status' => 'publish',
        'numberposts' => 1,
    ));
    return !empty($existing);
}

/**
 * Run an AE module (mimics \aw2_library::module_run)
 * 
 * @param array  $args   Arguments including post_type
 * @param string $module Module name
 * @return string
 */
function monomyth_ae_module_run($args, $module)
{
    if (!monomyth_is_awesome_active()) {
        return '';
    }
    return \aw2_library::module_run($args, $module);

}

/**
 * Get app collection config post type
 */
function monomyth_get_app_collection_post_type()
{
    if (!monomyth_is_awesome_active()) {
        return '';
    }


    $app = &\aw2_library::get_array_ref('app');
    if (isset($app['collection']['config']['post_type'])) {
        return $app['collection']['config']['post_type'];
    }


    return '';
}

/**
 * Get Awesome Core post type constant
 */
function monomyth_get_awesome_core_post_type()
{
    if (defined('AWESOME_CORE_POST_TYPE')) {
        return AWESOME_CORE_POST_TYPE;
    }
    return 'awesome_core'; // Default fallback
}

/**
 * ============================================================================
 * DYNAMIC BLOCK RENDERING FROM AWESOME ENTERPRISE
 * ============================================================================
 * 
 * This registers a single Gutenberg block that can render any Awesome Enterprise 
 * service/module/template. Template parts use this block to be fully dynamic.
 */

/**
 * Register block category
 */
function monomyth_register_block_category($categories)
{
    return array_merge(
        array(
            array(
                'slug' => 'awesome-enterprise',
                'title' => __('Awesome Enterprise', 'monomyth-fse'),
                'icon' => 'star-filled',
            ),
        ),
        $categories
    );
}
add_filter('block_categories_all', 'monomyth_register_block_category', 10, 1);

/**
 * Register the Awesome Enterprise dynamic block
 */
function monomyth_register_ae_blocks()
{
    if (!function_exists('register_block_type')) {
        return;
    }

    // 1. Awesome Block - renders any AE service directly
    register_block_type('monomyth/awesome-block', array(
        'api_version' => 3,
        'title' => __('Awesome Block', 'monomyth-fse'),
        'description' => __('Render content from any Awesome XP/Enterprise module/template.', 'monomyth-fse'),
        'category' => 'awesome-enterprise',
        'icon' => 'superhero-alt',
        'keywords' => array('awesome', 'enterprise', 'module', 'template', 'shortcode'),
        'supports' => array(
            'html' => false,
            'align' => array('wide', 'full'),
            'className' => true,
            'anchor' => true,
        ),
        'attributes' => array(
            'service' => array('type' => 'string', 'default' => ''),
            'wrapper' => array('type' => 'string', 'default' => 'div'),
            'wrapperClass' => array('type' => 'string', 'default' => ''),
            'params' => array('type' => 'string', 'default' => ''),
            'fallback' => array('type' => 'string', 'default' => ''),
            'align' => array('type' => 'string', 'default' => ''),
            'className' => array('type' => 'string', 'default' => ''),
            'anchor' => array('type' => 'string', 'default' => ''),
        ),
        'render_callback' => 'monomyth_render_awesome_block',
        'editor_script' => 'monomyth-ae-block-editor',
        'editor_style' => 'monomyth-ae-block-editor-style',
    ));

    // 2. AE Content Layout - Dynamic layout resolution (like single.php)
    register_block_type('monomyth/ae-content-layout', array(
        'api_version' => 3,
        'title' => __('AE Content Layout', 'monomyth-fse'),
        'description' => __('Dynamically resolves content layout based on post type (like single.php).', 'monomyth-fse'),
        'category' => 'awesome-enterprise',
        'icon' => 'layout',
        'keywords' => array('single', 'content', 'layout', 'dynamic', 'post'),
        'supports' => array(
            'html' => false,
            'align' => array('wide', 'full'),
            'className' => true,
        ),
        'attributes' => array(
            'baseModule' => array('type' => 'string', 'default' => 'single-content-layout'),
            'usePostTypePrefix' => array('type' => 'boolean', 'default' => true),
            'wrapper' => array('type' => 'string', 'default' => 'article'),
            'wrapperClass' => array('type' => 'string', 'default' => 'ae-content-layout'),
            'params' => array('type' => 'string', 'default' => ''),
            'fallbackContent' => array('type' => 'string', 'default' => ''),
            'align' => array('type' => 'string', 'default' => ''),
            'className' => array('type' => 'string', 'default' => ''),
        ),
        'render_callback' => 'monomyth_render_ae_content_layout_block',
        'editor_script' => 'monomyth-ae-block-editor',
        'editor_style' => 'monomyth-ae-block-editor-style',
    ));

    // 3. AE Archive Layout - For archive/taxonomy pages
    register_block_type('monomyth/ae-archive-layout', array(
        'api_version' => 3,
        'title' => __('AE Archive Layout', 'monomyth-fse'),
        'description' => __('Dynamically resolves archive layout based on post type/taxonomy.', 'monomyth-fse'),
        'category' => 'awesome-enterprise',
        'icon' => 'list-view',
        'keywords' => array('archive', 'category', 'taxonomy', 'list', 'index'),
        'supports' => array(
            'html' => false,
            'align' => array('wide', 'full'),
            'className' => true,
        ),
        'attributes' => array(
            'baseModule' => array('type' => 'string', 'default' => 'archive-content-layout'),
            'usePostTypePrefix' => array('type' => 'boolean', 'default' => true),
            'wrapper' => array('type' => 'string', 'default' => 'div'),
            'wrapperClass' => array('type' => 'string', 'default' => 'ae-archive-layout'),
            'params' => array('type' => 'string', 'default' => ''),
            'fallbackContent' => array('type' => 'string', 'default' => ''),
            'align' => array('type' => 'string', 'default' => ''),
            'className' => array('type' => 'string', 'default' => ''),
        ),
        'render_callback' => 'monomyth_render_ae_archive_layout_block',
        'editor_script' => 'monomyth-ae-block-editor',
        'editor_style' => 'monomyth-ae-block-editor-style',
    ));
}
add_action('init', 'monomyth_register_ae_blocks');

/**
 * ============================================================================
 * BLOCK RENDER CALLBACKS
 * ============================================================================
 */

/**
 * Render the Awesome Block (generic service caller)
 */
function monomyth_render_awesome_block($attributes, $content)
{
    $service = isset($attributes['service']) ? trim($attributes['service']) : '';
    $wrapper = isset($attributes['wrapper']) ? $attributes['wrapper'] : 'div';
    $wrapper_class = isset($attributes['wrapperClass']) ? $attributes['wrapperClass'] : '';
    $params = isset($attributes['params']) ? $attributes['params'] : '';
    $fallback = isset($attributes['fallback']) ? $attributes['fallback'] : '';
    $class_name = isset($attributes['className']) ? $attributes['className'] : '';
    $align = isset($attributes['align']) ? $attributes['align'] : '';
    $anchor = isset($attributes['anchor']) ? $attributes['anchor'] : '';

    // Build classes
    $classes = array('wp-block-monomyth-awesome-block');
    if (!empty($wrapper_class))
        $classes[] = $wrapper_class;
    if (!empty($class_name))
        $classes[] = $class_name;
    if (!empty($align))
        $classes[] = 'align' . $align;
    if (!empty($service)) {
        $classes[] = 'ae-service--' . sanitize_html_class(str_replace('.', '-', $service));
    }

    // Build wrapper
    $allowed_wrappers = array('div', 'section', 'article', 'header', 'footer', 'nav', 'aside', 'main', 'span');
    if (!in_array($wrapper, $allowed_wrappers, true)) {
        $wrapper = 'div';
    }

    $wrapper_attrs = 'class="' . esc_attr(implode(' ', $classes)) . '"';
    if (!empty($anchor)) {
        $wrapper_attrs .= ' id="' . esc_attr($anchor) . '"';
    }

    // No service specified
    if (empty($service)) {
        if (!empty($fallback)) {
            return "<{$wrapper} {$wrapper_attrs}>" . do_shortcode($fallback) . "</{$wrapper}>";
        }
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return "<{$wrapper} {$wrapper_attrs}><div class=\"ae-block-placeholder\">" .
                esc_html__('Configure: Enter an Awesome XP/Enterprise service path', 'monomyth-fse') .
                "</div></{$wrapper}>";
        }
        return '';
    }

    // Build and execute shortcode
    $shortcode = '[' . esc_attr($service);
    if (!empty($params)) {
        $shortcode .= ' ' . $params;
    }
    $shortcode .= ' /]';
    $output = '';
    if (monomyth_is_awesome_active()) {

        $output = \aw2_library::parse_shortcode($shortcode);
    }

    // Fallback if empty
    if (empty(trim($output)) && !empty($fallback)) {
        $output = do_shortcode($fallback);
    }

    if (empty(trim($output))) {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return "<{$wrapper} {$wrapper_attrs}><div class=\"ae-block-placeholder\">" .
                sprintf(esc_html__('Service "%s" returned no content', 'monomyth-fse'), $service) .
                "</div></{$wrapper}>";
        }
        return '';
    }

    return "<{$wrapper} {$wrapper_attrs}>{$output}</{$wrapper}>";
}

/**
 * Render AE Content Layout Block
 * 
 * This mimics the logic from single.php:
 * 1. Try {post_type}-{baseModule} in app collection
 * 2. Try {post_type}-{baseModule} in AWESOME_CORE
 * 3. Try {baseModule} in app collection
 * 4. Try {baseModule} in AWESOME_CORE
 */
function monomyth_render_ae_content_layout_block($attributes, $content)
{
    $base_module = isset($attributes['baseModule']) ? $attributes['baseModule'] : 'single-content-layout';
    $use_prefix = isset($attributes['usePostTypePrefix']) ? $attributes['usePostTypePrefix'] : true;
    $wrapper = isset($attributes['wrapper']) ? $attributes['wrapper'] : 'article';
    $wrapper_class = isset($attributes['wrapperClass']) ? $attributes['wrapperClass'] : 'ae-content-layout';
    $params = isset($attributes['params']) ? $attributes['params'] : '';
    $fallback_content = isset($attributes['fallbackContent']) ? $attributes['fallbackContent'] : '';
    $class_name = isset($attributes['className']) ? $attributes['className'] : '';
    $align = isset($attributes['align']) ? $attributes['align'] : '';

    // Get current post type
    global $post;
    $current_post_type = '';
    if ($post) {
        $current_post_type = get_post_type($post);
    } elseif (is_singular()) {
        $current_post_type = get_query_var('post_type');
    }

    // Build classes
    $classes = array('wp-block-monomyth-ae-content-layout', $wrapper_class);
    if (!empty($class_name))
        $classes[] = $class_name;
    if (!empty($align))
        $classes[] = 'align' . $align;
    if (!empty($current_post_type)) {
        $classes[] = 'post-type-' . sanitize_html_class($current_post_type);
    }

    // Allowed wrappers
    $allowed_wrappers = array('div', 'section', 'article', 'main', 'aside');
    if (!in_array($wrapper, $allowed_wrappers, true)) {
        $wrapper = 'article';
    }

    $wrapper_attrs = 'class="' . esc_attr(implode(' ', $classes)) . '"';

    // Editor preview
    if (defined('REST_REQUEST') && REST_REQUEST) {
        $preview_text = sprintf(
            __('AE Content Layout: Will resolve "%s" for post type "%s"', 'monomyth-fse'),
            $base_module,
            $current_post_type ?: 'current'
        );
        return "<{$wrapper} {$wrapper_attrs}><div class=\"ae-block-placeholder ae-content-layout-preview\">" .
            "<strong>" . esc_html__('Dynamic Content Layout', 'monomyth-fse') . "</strong><br>" .
            "<small>" . esc_html($preview_text) . "</small>" .
            "</div></{$wrapper}>";
    }

    // Check if AE is active
    if (!monomyth_is_awesome_active()) {
        if (!empty($fallback_content)) {
            return "<{$wrapper} {$wrapper_attrs}>" . do_shortcode($fallback_content) . "</{$wrapper}>";
        }
        return "<{$wrapper} {$wrapper_attrs}><div class=\"ae-block-error\">" .
            esc_html__('Awesome XP/Enterprise is not active.', 'monomyth-fse') .
            "</div></{$wrapper}>";
    }

    // Set current post in AE context
    if ($post && function_exists('aw2_library::set')) {
        \aw2_library::set('current_post', $post);
    }

    // Resolution logic (mirrors single.php)
    $app_post_type = monomyth_get_app_collection_post_type();
    $core_post_type = monomyth_get_awesome_core_post_type();

    $module_to_use = '';
    $post_type_to_use = '';

    // Build prefixed module name
    $prefixed_module = '';
    if ($use_prefix && !empty($current_post_type)) {
        $prefixed_module = $current_post_type . '-' . $base_module;
    }

    // Resolution order (same as single.php):
    // 1. {post_type}-{baseModule} in app collection
    if (!empty($prefixed_module) && !empty($app_post_type) && monomyth_ae_module_exists($prefixed_module, $app_post_type)) {
        $module_to_use = $prefixed_module;
        $post_type_to_use = $app_post_type;
    }
    // 2. {post_type}-{baseModule} in AWESOME_CORE
    elseif (!empty($prefixed_module) && monomyth_ae_module_exists($prefixed_module, $core_post_type)) {
        $module_to_use = $prefixed_module;
        $post_type_to_use = $core_post_type;
    }
    // 3. {baseModule} in app collection
    elseif (!empty($app_post_type) && monomyth_ae_module_exists($base_module, $app_post_type)) {
        $module_to_use = $base_module;
        $post_type_to_use = $app_post_type;
    }
    // 4. {baseModule} in AWESOME_CORE
    elseif (monomyth_ae_module_exists($base_module, $core_post_type)) {
        $module_to_use = $base_module;
        $post_type_to_use = $core_post_type;
    }

    $output = '';
    // Run the module
    if (!empty($module_to_use) && !empty($post_type_to_use)) {
        $output = monomyth_ae_module_run(array('post_type' => $post_type_to_use), $module_to_use);

        if (!empty(trim($output))) {
            return "<{$wrapper} {$wrapper_attrs}>{$output}</{$wrapper}>";
        }
    }

    // Fallback
    if (!empty($fallback_content)) {
        return "<{$wrapper} {$wrapper_attrs}>" . do_shortcode($fallback_content) . "</{$wrapper}>";
    }

    return "<{$wrapper} {$wrapper_attrs}><em>" . esc_html($base_module) . "</em> " .
        esc_html__('module is missing.', 'monomyth-fse') . "</{$wrapper}>";
}

/**
 * Render AE Archive Layout Block
 */
function monomyth_render_ae_archive_layout_block($attributes, $content)
{
    $base_module = isset($attributes['baseModule']) ? $attributes['baseModule'] : 'archive-content-layout';
    $use_prefix = isset($attributes['usePostTypePrefix']) ? $attributes['usePostTypePrefix'] : true;
    $wrapper = isset($attributes['wrapper']) ? $attributes['wrapper'] : 'div';
    $wrapper_class = isset($attributes['wrapperClass']) ? $attributes['wrapperClass'] : 'ae-archive-layout';
    $params = isset($attributes['params']) ? $attributes['params'] : '';
    $fallback_content = isset($attributes['fallbackContent']) ? $attributes['fallbackContent'] : '';
    $class_name = isset($attributes['className']) ? $attributes['className'] : '';
    $align = isset($attributes['align']) ? $attributes['align'] : '';

    // Get current context
    $current_post_type = get_query_var('post_type');
    if (empty($current_post_type) && is_post_type_archive()) {
        $current_post_type = get_queried_object()->name ?? '';
    }

    // Build classes
    $classes = array('wp-block-monomyth-ae-archive-layout', $wrapper_class);
    if (!empty($class_name))
        $classes[] = $class_name;
    if (!empty($align))
        $classes[] = 'align' . $align;
    if (!empty($current_post_type)) {
        $classes[] = 'archive-type-' . sanitize_html_class($current_post_type);
    }

    $allowed_wrappers = array('div', 'section', 'main');
    if (!in_array($wrapper, $allowed_wrappers, true)) {
        $wrapper = 'div';
    }

    $wrapper_attrs = 'class="' . esc_attr(implode(' ', $classes)) . '"';

    // Editor preview
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return "<{$wrapper} {$wrapper_attrs}><div class=\"ae-block-placeholder ae-archive-layout-preview\">" .
            "<strong>" . esc_html__('Dynamic Archive Layout', 'monomyth-fse') . "</strong><br>" .
            "<small>" . sprintf(esc_html__('Base: %s', 'monomyth-fse'), $base_module) . "</small>" .
            "</div></{$wrapper}>";
    }

    // Check if AE is active
    if (!monomyth_is_awesome_active()) {
        if (!empty($fallback_content)) {
            return "<{$wrapper} {$wrapper_attrs}>" . do_shortcode($fallback_content) . "</{$wrapper}>";
        }
        return '';
    }

    // Resolution logic (similar to content layout)
    $app_post_type = monomyth_get_app_collection_post_type();
    $core_post_type = monomyth_get_awesome_core_post_type();

    $module_to_use = '';
    $post_type_to_use = '';

    $prefixed_module = '';
    if ($use_prefix && !empty($current_post_type)) {
        $prefixed_module = $current_post_type . '-' . $base_module;
    }

    if (!empty($prefixed_module) && !empty($app_post_type) && monomyth_ae_module_exists($prefixed_module, $app_post_type)) {
        $module_to_use = $prefixed_module;
        $post_type_to_use = $app_post_type;
    } elseif (!empty($prefixed_module) && monomyth_ae_module_exists($prefixed_module, $core_post_type)) {
        $module_to_use = $prefixed_module;
        $post_type_to_use = $core_post_type;
    } elseif (!empty($app_post_type) && monomyth_ae_module_exists($base_module, $app_post_type)) {
        $module_to_use = $base_module;
        $post_type_to_use = $app_post_type;
    } elseif (monomyth_ae_module_exists($base_module, $core_post_type)) {
        $module_to_use = $base_module;
        $post_type_to_use = $core_post_type;
    }

    $output = '';
    if (!empty($module_to_use) && !empty($post_type_to_use)) {
        $output = monomyth_ae_module_run(array('post_type' => $post_type_to_use), $module_to_use);

        if (!empty(trim($output))) {
            return "<{$wrapper} {$wrapper_attrs}>{$output}</{$wrapper}>";
        }
    }

    if (!empty($fallback_content)) {
        return "<{$wrapper} {$wrapper_attrs}>" . do_shortcode($fallback_content) . "</{$wrapper}>";
    }

    return '';
}

/**
 * ============================================================================
 * BLOCK EDITOR ASSETS
 * ============================================================================
 */

function monomyth_enqueue_block_editor_assets()
{
    wp_enqueue_script(
        'monomyth-ae-block-editor',
        MONOMYTH_URI . '/assets/js/ae-block-editor.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-block-editor', 'wp-server-side-render'),
        MONOMYTH_VERSION,
        true
    );

    wp_localize_script('monomyth-ae-block-editor', 'monomythAEBlock', array(
        'isAwesomeActive' => monomyth_is_awesome_active(),
        'placeholder' => __('Enter service path (e.g., theme_parts.header)', 'monomyth-fse'),
        'i18n' => array(
            'awesomeBlock' => __('Awesome Block', 'monomyth-fse'),
            'contentLayout' => __('AE Content Layout', 'monomyth-fse'),
            'archiveLayout' => __('AE Archive Layout', 'monomyth-fse'),
            'servicePath' => __('Service Path', 'monomyth-fse'),
            'baseModule' => __('Base Module Name', 'monomyth-fse'),
            'usePostTypePrefix' => __('Prefix with post type', 'monomyth-fse'),
            'wrapper' => __('Wrapper Element', 'monomyth-fse'),
            'wrapperClass' => __('Wrapper CSS Class', 'monomyth-fse'),
            'params' => __('Parameters', 'monomyth-fse'),
            'fallback' => __('Fallback Content', 'monomyth-fse'),
        ),
    ));

    wp_enqueue_style(
        'monomyth-ae-block-editor-style',
        MONOMYTH_URI . '/assets/css/ae-block-editor.css',
        array('wp-edit-blocks'),
        MONOMYTH_VERSION
    );
}
add_action('enqueue_block_editor_assets', 'monomyth_enqueue_block_editor_assets');

/**
 * ============================================================================
 * THEME SETUP
 * ============================================================================
 */

function monomyth_setup()
{
    add_theme_support('automatic-feed-links');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor.css');

    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    add_theme_support('custom-logo', array(
        'height' => 100,
        'width' => 400,
        'flex-height' => true,
        'flex-width' => true,
    ));

    add_theme_support('align-wide');
    add_theme_support('wp-block-styles');
    add_theme_support('appearance-tools');

    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'monomyth-fse'),
        'footer' => esc_html__('Footer Menu', 'monomyth-fse'),
    ));

    load_theme_textdomain('monomyth-fse', MONOMYTH_DIR . '/languages');
}
add_action('after_setup_theme', 'monomyth_setup');

/**
 * Enqueue frontend assets
 */
function monomyth_scripts()
{
    //wp_enqueue_style('monomyth-style', get_stylesheet_uri(), array(), MONOMYTH_VERSION);

    $base_url = rtrim(site_url(), '/');

    // Construct the full virtual path
    $virtual_css_url = $base_url . '/awesome-css/css/stylesheet';

    // Enqueue it using a unique handle
    wp_enqueue_style(
        'awesome-dynamic-styles',
        $virtual_css_url,
        array(),
        null // Pass null for versioning if the virtual route handles its own cache-busting
    );

    if (file_exists(MONOMYTH_DIR . '/assets/css/custom.css')) {
        wp_enqueue_style('monomyth-custom', MONOMYTH_URI . '/assets/css/custom.css', array(), MONOMYTH_VERSION);
    }

    if (file_exists(MONOMYTH_DIR . '/assets/js/custom.js')) {
        wp_enqueue_script('monomyth-custom', MONOMYTH_URI . '/assets/js/custom.js', array('jquery'), MONOMYTH_VERSION, true);
        wp_localize_script('monomyth-custom', 'monomythData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'homeUrl' => home_url('/'),
            'isAwesomeActive' => monomyth_is_awesome_active(),
        ));
    }

}
add_action('wp_enqueue_scripts', 'monomyth_scripts');

/**
 * Body classes
 */
function monomyth_body_classes($classes)
{
    $classes[] = 'monomyth-fse';
    if (monomyth_is_awesome_active()) {
        $classes[] = 'has-awesome-enterprise';
    }

    if (is_user_logged_in()) {
        $classes[] = 'is-logged-in';
    }

    if (is_front_page()) {
        $classes[] = 'is-front-page';
    }

    return $classes;
}
add_filter('body_class', 'monomyth_body_classes');

/**
 * ============================================================================
 * OPTIONAL INCLUDES
 * ============================================================================
 */

if (file_exists(MONOMYTH_DIR . '/inc/template-tags.php')) {
    require_once MONOMYTH_DIR . '/inc/template-tags.php';
}

if (file_exists(MONOMYTH_DIR . '/inc/block-patterns.php')) {
    require_once MONOMYTH_DIR . '/inc/block-patterns.php';
}


/**
 * Load Awesome CSS theme.json integration.
 */
function monomyth_load_awx_integration()
{
    require_once get_template_directory() . '/inc/class-awx-theme-json-integration.php';
    new AWX_Theme_JSON_Integration();
}
add_action('after_setup_theme', 'monomyth_load_awx_integration');

/**
 * ============================================================================
 * AWESOME ENTERPRISE ASSETS (Scripts/Styles from Core "script" module)
 * ============================================================================
 */

/**
 * Output head assets from AE "script" module in core
 */
function monomyth_ae_head_scripts()
{
    if (!monomyth_is_awesome_active()) {
        return;
    }

    $core_post_type = monomyth_get_awesome_core_post_type();
    if (empty($core_post_type)) {
        return;
    }

    // Run "script" module from core with position=head
    if (monomyth_ae_module_exists('header-script', $core_post_type)) {
        echo monomyth_ae_module_run(
            array('post_type' => $core_post_type),
            'header-script'
        );
    }

    //Run "script" module from app if it exists in config
    $app_config_post_type = monomyth_get_app_collection_post_type();

    if (empty($app_config_post_type)) {
        return;
    }
    $output = 'HElo';
    if (monomyth_ae_module_exists('header-script', $app_config_post_type)) {
        $output = monomyth_ae_module_run(
            array('post_type' => $app_config_post_type),
            'header-script'
        );
        echo $output;
    }
}
add_action('wp_head', 'monomyth_ae_head_scripts', 100);

/**
 * Output footer assets from AE "script" module in core
 */
function monomyth_ae_footer_scripts()
{
    if (!monomyth_is_awesome_active()) {
        return;
    }

    $core_post_type = monomyth_get_awesome_core_post_type();
    if (empty($core_post_type)) {
        return;
    }

    // Run "script" module from core with position=footer
    if (monomyth_ae_module_exists('footer-script', $core_post_type)) {
        $output = monomyth_ae_module_run(
            array('post_type' => $core_post_type),
            'footer-script'
        );
        echo $output;
    }
}
add_action('wp_footer', 'monomyth_ae_footer_scripts', 100);

/**
 * ============================================================================
 * GITHUB THEME UPDATER
 * ============================================================================
 */

// Include GitHub updater for automatic updates via GitHub releases
require_once get_template_directory() . '/inc/github-updater.php';

Monomyth_GitHub_Updater::init(array(
    'slug' => 'monomyth-fse',         // Your theme folder name
    'repo' => 'WPoets/monomyth-fse',  // GitHub username/repo
    'cache_hours' => 24,
));
