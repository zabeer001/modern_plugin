<?php
if (!defined('ABSPATH')) exit;
require_once __DIR__ . '/../helpers/Utils.php';
require_once __DIR__ . '/../helpers/RestHelper.php';

class UserController
{


    // public function index()
    // {


    //     // Get all users
    //     $users = get_users([
    //         'fields' => ['ID', 'user_login', 'user_email', 'display_name', 'roles']
    //     ]);

    //     // Format users as an array
    //     $formatted_users = [];

    //     foreach ($users as $user) {
    //         $formatted_users[] = [
    //             'id'           => $user->ID,
    //             'username'     => $user->user_login,
    //             'email'        => $user->user_email,
    //             'display_name' => $user->display_name,
    //             'roles'        => $user->roles,
    //         ];
    //     }

    //     // Return as JSON
    //     wp_send_json([
    //         'success' => true,
    //         'count'   => count($formatted_users),
    //         'users'   => $formatted_users,
    //     ]);
    // }
    public function index(\WP_REST_Request $request)
    {
        $role       = sanitize_text_field($request->get_param('role')) ?: '';
        $search     = sanitize_text_field($request->get_param('search')) ?: '';
        $user_id    = absint($request->get_param('user_id')); // 👈 your new param
        $page       = (int) ($request->get_param('page') ?: 1);
        $per_page   = (int) ($request->get_param('per_page') ?: 20);

        $args = [
            'number'  => $per_page,
            'paged'   => $page,
            'orderby' => 'display_name',
            'order'   => 'ASC',
            'fields'  => ['ID', 'display_name', 'user_email'],
        ];

        // 🔍 Filter by role if provided
        if (!empty($role)) {
            $args['role'] = $role;
        }

        // 🔎 Search by email, login, or display name
        if (!empty($search)) {
            $args['search'] = '*' . esc_attr($search) . '*';
            $args['search_columns'] = ['user_login', 'user_email', 'display_name'];
        }

        // 🎯 Filter by specific user_id (optional)
        if (!empty($user_id)) {
            $args['include'] = [$user_id];
        }

        $users = get_users($args);

        $results = [];
        foreach ($users as $user) {
            $user_obj = get_userdata($user->ID);
            $results[] = [
                'id'    => (int) $user->ID,
                'name'  => $user->display_name,
                'email' => $user->user_email,
                'role'  => $user_obj && !empty($user_obj->roles) ? $user_obj->roles[0] : null,
            ];
        }

        return new \WP_REST_Response([
            'status'  => true,
            'message' => 'Users retrieved successfully',
            'data'    => $results,
        ], 200);
    }

    public function me(\WP_REST_Request $request)
    {
        $user = wp_get_current_user();

        if ($user->ID === 0) {
            return new \WP_Error(
                'not_logged_in',
                __('User not authenticated', 'kibsterlp'),
                ['status' => 401]
            );
        }

        // Construct user data
        $userData = [
            'id'          => $user->ID,
            'username'    => $user->user_login,
            'email'       => $user->user_email,
            'name'        => $user->display_name,
            'roles'       => $user->roles,
            'registered'  => $user->user_registered,
        ];

        // Build standard response format
        $response = [
            'status'  => true,
            'message' => __('User retrieved successfully', 'kibsterlp'),
            'data'    => $userData,
        ];

        return rest_ensure_response($response);
    }
}
