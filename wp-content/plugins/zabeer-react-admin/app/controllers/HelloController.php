<?php
if (!defined('ABSPATH')) exit;

// Include the utils class
require_once __DIR__ . '/../helpers/Utils.php';
require_once __DIR__ . '/../helpers/RestHelper.php';



class HelloController
{

    public function hello_world(WP_REST_Request $request)
    {
        // Use the helper function
        $message = Utils::format_message('Hello World from WordPress REST API!');

        return [
            'message' => $message,
            'timestamp' => Utils::get_timestamp()
        ];
    }


    public function goodbye_world(WP_REST_Request $request)
    {
        // Get the current logged-in user
        $current_user = wp_get_current_user();

        if ($current_user->ID === 0) {
            return new WP_Error('unauthorized', 'You must be logged in', ['status' => 401]);
        }

        // Parse request data using RestHelper
        $form_data = RestHelper::parse_request_data($request);

        if (!is_array($form_data)) {
            return $form_data; // already a WP_REST_Response with error
        }

        $name = isset($form_data['name']) ? sanitize_text_field($form_data['name']) : 'Guest';
        $message = isset($form_data['message']) ? sanitize_text_field($form_data['message']) : '';

        // Build response
        $response_data = [
            'status' => 'success',
            'current_user' => [
                'ID' => $current_user->ID,
                'username' => $current_user->user_login,
                'email' => $current_user->user_email,
                'roles' => $current_user->roles
            ],
            'received' => [
                'name' => $name,
                'message' => $message,
            ],
            'message' => "Hello, {$current_user->user_login}! Your message was: $message"
        ];

        return new WP_REST_Response($response_data, 200);
    }
}
