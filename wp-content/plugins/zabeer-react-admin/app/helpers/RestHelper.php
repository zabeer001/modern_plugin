<?php

class RestHelper {
    /**
     * Parse request data based on content type.
     *
     * @param WP_REST_Request $request The REST request object.
     * @return array|WP_REST_Response Parsed data or error response if JSON is invalid.
     * json data post data or formdata 
     */
    public static function parse_request_data(WP_REST_Request $request) {
        // Check content type
        if ($request->get_content_type() === 'application/json') {
            $raw_body = $request->get_body();
            $data = json_decode($raw_body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return new WP_REST_Response([
                    'status' => 'error',
                    'message' => 'Invalid JSON format',
                ], 400);
            }
        } else {
            // Handle form-data or x-www-form-urlencoded
            $data = $request->get_body_params();
        }

        return $data;
    }
}