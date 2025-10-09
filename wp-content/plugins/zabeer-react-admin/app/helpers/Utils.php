<?php
if (!defined('ABSPATH')) exit;

class Utils {

    // Example: Format a string
    public static function format_message($message) {
        return strtoupper($message);
    }

    // Example: Return current timestamp
    public static function get_timestamp() {
        return current_time('mysql'); // WordPress time
    }

    public static function check_secret_key(WP_REST_Request $request) {
    // Retrieve the 'x-api-key' header
    $api_key = $request->get_header('x-api-key');

    // Check if the header exists
    if ($api_key) {
        // Compare the received key with the expected key
        if ($api_key === REACT_ADMIN_API_KEY) {
            return true; // Return true to allow the request to proceed
        } else {
            return new WP_Error('invalid_api_key', 'Invalid API key provided', ['status' => 403]);
        }
    }

    return new WP_Error('missing_api_key', 'No API key sent', ['status' => 403]);
}




}
