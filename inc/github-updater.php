<?php
/**
 * GitHub Theme Updater
 * 
 * Enables automatic theme updates from GitHub releases.
 * 
 * INSTALLATION:
 * 1. Copy this file to your theme's /inc/ folder
 * 2. Add to your functions.php:
 * 
 *     require_once get_template_directory() . '/inc/github-updater.php';
 *     
 *     Monomyth_GitHub_Updater::init( array(
 *         'slug' => 'your-theme-slug',        // Theme folder name
 *         'repo' => 'username/repo-name',     // GitHub username/repo
 *     ) );
 * 
 * GITHUB RELEASE SETUP:
 * - Create releases with version tags (e.g., v1.0.0 or 1.0.0)
 * - Optionally attach a .zip file as release asset
 * - The version tag must be greater than style.css Version header
 * 
 * @package Monomyth_FSE
 * @version 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( class_exists( 'Monomyth_GitHub_Updater' ) ) {
    return;
}

class Monomyth_GitHub_Updater {

    private static $instance = null;
    private $config = array();
    private $release_data = null;

    /**
     * Initialize the updater
     */
    public static function init( $config = array() ) {
        if ( null === self::$instance ) {
            self::$instance = new self( $config );
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct( $config ) {
        // Only run in admin
        if ( ! is_admin() ) {
            return;
        }

        // Get theme data
        $theme = wp_get_theme( isset( $config['slug'] ) ? $config['slug'] : '' );
        
        $this->config = wp_parse_args( $config, array(
            'slug'         => '',
            'repo'         => '',
            'version'      => $theme->exists() ? $theme->get( 'Version' ) : '1.0.0',
            'token'        => '',
            'cache_hours'  => 6,
            'requires'     => '6.0',
            'requires_php' => '7.4',
        ) );

        // Validate
        if ( empty( $this->config['slug'] ) || empty( $this->config['repo'] ) ) {
            return;
        }

        // Hooks
        add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_update' ) );
        add_filter( 'themes_api', array( $this, 'theme_info' ), 20, 3 );
        add_filter( 'upgrader_source_selection', array( $this, 'fix_folder_name' ), 10, 4 );
        add_action( 'switch_theme', array( $this, 'clear_cache' ) );
    }

    /**
     * Fetch latest release from GitHub
     */
    private function fetch_release() {
        $transient_key = 'github_update_' . $this->config['slug'];
        $cached = get_transient( $transient_key );

        if ( $cached !== false ) {
            return $cached;
        }

        $url = sprintf( 'https://api.github.com/repos/%s/releases/latest', $this->config['repo'] );

        $args = array(
            'timeout' => 10,
            'headers' => array(
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ),
            ),
        );

        if ( ! empty( $this->config['token'] ) ) {
            $args['headers']['Authorization'] = 'Bearer ' . $this->config['token'];
        }

        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return false;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ) );

        if ( empty( $data->tag_name ) ) {
            return false;
        }

        set_transient( $transient_key, $data, $this->config['cache_hours'] * HOUR_IN_SECONDS );

        return $data;
    }

    /**
     * Get clean version from tag (removes v prefix)
     */
    private function parse_version( $tag ) {
        return ltrim( $tag, 'vV' );
    }

    /**
     * Get download URL from release
     */
    private function get_package_url( $release ) {
        // Prefer attached .zip asset
        if ( ! empty( $release->assets ) ) {
            foreach ( $release->assets as $asset ) {
                if ( preg_match( '/\.zip$/i', $asset->name ) ) {
                    return $asset->browser_download_url;
                }
            }
        }
        // Fallback to source zipball
        return $release->zipball_url;
    }

    /**
     * Check for updates
     */
    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $release = $this->fetch_release();
        if ( ! $release ) {
            return $transient;
        }

        $this->release_data = $release;
        $new_version = $this->parse_version( $release->tag_name );

        if ( version_compare( $new_version, $this->config['version'], '>' ) ) {
            $transient->response[ $this->config['slug'] ] = array(
                'theme'        => $this->config['slug'],
                'new_version'  => $new_version,
                'url'          => $release->html_url,
                'package'      => $this->get_package_url( $release ),
                'requires'     => $this->config['requires'],
                'requires_php' => $this->config['requires_php'],
            );
        }

        return $transient;
    }

    /**
     * Theme info popup
     */
    public function theme_info( $result, $action, $args ) {
        if ( $action !== 'theme_information' || ! isset( $args->slug ) || $args->slug !== $this->config['slug'] ) {
            return $result;
        }

        $release = $this->release_data ?: $this->fetch_release();
        if ( ! $release ) {
            return $result;
        }

        $theme = wp_get_theme( $this->config['slug'] );

        $info = (object) array(
            'name'          => $theme->get( 'Name' ),
            'slug'          => $this->config['slug'],
            'version'       => $this->parse_version( $release->tag_name ),
            'author'        => $theme->get( 'Author' ),
            'homepage'      => $theme->get( 'ThemeURI' ),
            'requires'      => $this->config['requires'],
            'requires_php'  => $this->config['requires_php'],
            'download_link' => $this->get_package_url( $release ),
            'last_updated'  => isset( $release->published_at ) ? date( 'Y-m-d', strtotime( $release->published_at ) ) : '',
            'sections'      => array(
                'description' => $theme->get( 'Description' ),
                'changelog'   => $this->format_changelog( $release->body ?? '' ),
            ),
        );

        return $info;
    }

    /**
     * Format changelog markdown to HTML
     */
    private function format_changelog( $text ) {
        if ( empty( $text ) ) {
            return '<p>See GitHub release for details.</p>';
        }
        
        $text = esc_html( $text );
        $text = preg_replace( '/^### (.+)$/m', '<h4>$1</h4>', $text );
        $text = preg_replace( '/^## (.+)$/m', '<h3>$1</h3>', $text );
        $text = preg_replace( '/^[\-\*] (.+)$/m', '<li>$1</li>', $text );
        $text = preg_replace( '/(<li>.*<\/li>)+/s', '<ul>$0</ul>', $text );
        $text = nl2br( $text );
        
        return $text;
    }

    /**
     * Fix folder name after extraction
     * GitHub zipball creates "user-repo-hash" folders
     */
    public function fix_folder_name( $source, $remote_source, $upgrader, $hook_extra = array() ) {
        global $wp_filesystem;

        if ( ! isset( $hook_extra['theme'] ) || $hook_extra['theme'] !== $this->config['slug'] ) {
            return $source;
        }

        $correct_name = $this->config['slug'];
        $current_name = basename( untrailingslashit( $source ) );

        if ( $current_name === $correct_name ) {
            return $source;
        }

        $new_source = trailingslashit( dirname( untrailingslashit( $source ) ) ) . $correct_name . '/';

        if ( $wp_filesystem->move( untrailingslashit( $source ), untrailingslashit( $new_source ) ) ) {
            return $new_source;
        }

        return $source;
    }

    /**
     * Clear update cache
     */
    public function clear_cache() {
        delete_transient( 'github_update_' . $this->config['slug'] );
    }
}
