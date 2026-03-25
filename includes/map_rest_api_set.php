<?php
/**
 * Class Map_Rest_API_Set
 * Handles the registration of the REST API endpoint for locations.
 */
class Map_Rest_API_Set {
    /**
     * Map_Rest_API_Set constructor.
     * Registers the REST API route on init.
     */
    public function __construct() {
        add_action('rest_api_init', [$this, 'init']);
    }

    /**
     * Registers the custom REST API route.
     *
     * @return void
     */
    public function init() {
        register_rest_route('map/v1', '/locations', array(
            'methods' => 'GET',
            'callback' => [$this, 'location_api_handler'],
            'permission_callback' => '__return_true', // Public endpoint
        ));
    }

    /**
     * Handles the REST API request for locations.
     *
     * @param WP_REST_Request $request
     * @return array|WP_REST_Response
     */
    public function location_api_handler($request) {
        $params = $request->get_params();
        $remote_url = 'https://www.podotherapiehermanns.nl/wp-json/map/v1/locations';
        if (!empty($params)) {
            $remote_url = add_query_arg($params, $remote_url);
        }

        // Create a unique cache key based on the URL and params
        $cache_key = 'location_fetch_' . md5($remote_url);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return json_decode($cached, true);
        }

        $locations = wp_remote_get($remote_url);
        if (is_wp_error($locations)) {
            return new WP_REST_Response([
                'error' => 'Unable to retrieve location data',
                'details' => $locations->get_error_message(),
            ], 500);
        }

        $body = wp_remote_retrieve_body($locations);
        if (!empty($body)) {
            set_transient($cache_key, $body, 10 * MINUTE_IN_SECONDS); // Cache for 10 minutes
            return json_decode($body, true);
        } else {
            return new WP_REST_Response([
                'error' => 'Empty response from remote server.'
            ], 500);
        }
    }
}






