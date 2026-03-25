<?php
/**
 * Plugin Name: Location Fetch
 * Description: Fetches location data from a remote API and serves it via a custom REST endpoint.
 * Version: 1.0.0
 * Author: Forazitech
 * Author URI: https://www.forazitech.com
 * License: GPL2
 */

/**
 * Main plugin class for Location Fetch.
 */
class Location_Fetch {
    /**
     * Singleton instance
     * @var Location_Fetch|null
     */
    private static $instance = null;
    private $map_api_key = '';

    /**
     * Get the singleton instance
     * @return Location_Fetch
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor. Initializes the plugin.
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize plugin: define constants, include files, set up hooks.
     * @return void
     */
    private function init() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define plugin constants.
     * @return void
     */
    private function define_constants() {
        if (!defined('LOCATION_FETCH_VERSION')) {
            define('LOCATION_FETCH_VERSION', time());
        }
        if (!defined('LOCATION_FETCH_PLUGIN_DIR')) {
            define('LOCATION_FETCH_PLUGIN_DIR', plugin_dir_path(__FILE__));
        }
        if (!defined('LOCATION_FETCH_PLUGIN_URL')) {
            define('LOCATION_FETCH_PLUGIN_URL', plugin_dir_url(__FILE__));
        }
    }

    /**
     * Include required files.
     * @return void
     */
    private function includes() {
        require_once LOCATION_FETCH_PLUGIN_DIR . 'includes/map_rest_api_set.php';
    }

    /**
     * Initialize plugin hooks.
     * @return void
     */
    private function init_hooks() {
        new Map_Rest_API_Set();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        add_shortcode('location_fetch_map', [$this, 'fetch_locaiton_rendar']);
    }

    public function enqueue_frontend_scripts() {
        wp_register_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $this->map_api_key, [], null, true );
        wp_register_script( 'google-maps-markerclusterer', 'https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js', [], null, true );
        wp_register_script('location-fetch-frontend', LOCATION_FETCH_PLUGIN_URL . 'assets/js/location-fetch.js', [], LOCATION_FETCH_VERSION, true);
    }

    /**
     * Summary of fetch_locaiton_rendar
     * @return bool|string
     */
    public function fetch_locaiton_rendar(){
        wp_enqueue_script('google-maps');
        wp_enqueue_script('google-maps-markerclusterer');
        wp_enqueue_script('location-fetch-frontend');
        ob_start();
        ?>
        <style>
            #map {
                min-height: 200px;
                width: 100%;
            }
        </style>
        <div id="map"></div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the plugin on plugins_loaded
add_action('plugins_loaded', ['Location_Fetch', 'get_instance']);