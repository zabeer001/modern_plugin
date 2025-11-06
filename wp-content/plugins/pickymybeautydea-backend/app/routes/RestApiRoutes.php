<?php
if (!defined('ABSPATH')) exit;

// Controllers
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../helpers/Utils.php';

class RestApiRoutes
{
    private $categoryController;
    private $orderController;
    private $userController;

    public function __construct()
    {
        $this->categoryController = new CategoryController();
        $this->orderController    = new OrderController();
        $this->userController = new UserController();

        add_action('rest_api_init', [$this, 'register_routes']);
        add_action('init', [$this, 'flush_routes_once']); // TEMP
    }

    public function register_routes()
    {
        // ----------------- Categories -----------------

        // GET /wp-json/kibsterlp-admin/v1/categories (public)
        register_rest_route('kibsterlp-admin/v1', '/categories', [
            'methods'  => 'GET',
            'callback' => [$this->categoryController, 'index'],
            'permission_callback' => '__return_true',
            'args' => [
                'page'     => ['required' => false, 'validate_callback' => 'is_numeric'],
                'per_page' => ['required' => false, 'validate_callback' => 'is_numeric'],
            ],
        ]);



        // POST /wp-json/kibsterlp-admin/v1/categories (create)
        register_rest_route('kibsterlp-admin/v1', '/categories', [
            'methods'  => 'POST',
            'callback' => [$this->categoryController, 'create'],
            //'permission_callback' => function () { return is_user_logged_in(); },
            'args' => [
                'title'       => ['required' => true,  'sanitize_callback' => 'sanitize_text_field'],
                'description' => ['required' => false, 'sanitize_callback' => 'sanitize_textarea_field'],
            ],
        ]);

        // GET /wp-json/kibsterlp-admin/v1/categories/{id} (show)
       register_rest_route('kibsterlp-admin/v1', '/categories/(?P<id>\d+)', [
    'methods'  => 'GET',
    'callback' => [$this->categoryController, 'show'],
    'permission_callback' => '__return_true',
]);

        // PUT/PATCH /wp-json/kibsterlp-admin/v1/categories/{id} (update)
        register_rest_route('kibsterlp-admin/v1', '/categories/(?P<id>\d+)', [
            'methods'  => WP_REST_Server::EDITABLE,
            'callback' => [$this->categoryController, 'update'],
            'args' => [
                'id'          => [
                    'required' => true,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_numeric($value);
                    },
                ],
                'title'       => ['required' => false, 'sanitize_callback' => 'sanitize_text_field'],
                'description' => ['required' => false, 'sanitize_callback' => 'sanitize_textarea_field'],
            ],
        ]);


        // DELETE /wp-json/kibsterlp-admin/v1/categories/{id} (delete)
        register_rest_route('kibsterlp-admin/v1', '/categories/(?P<id>\d+)', [
            'methods'  => 'DELETE',
            'callback' => [$this->categoryController, 'delete'],

            // Old permission (kept for future reference)
            // 'permission_callback' => function () {
            //     return current_user_can('delete_posts');
            // },

            // ✅ Updated: open permission for now
            'permission_callback' => '__return_true',

            'args' => [
                'id' => [
                    'required' => true,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_numeric($value);
                    },
                ],
            ],
        ]);


        // ----------------- Orders -----------------

        // GET /orders
        register_rest_route('kibsterlp-admin/v1', '/orders', [
            'methods'  => 'GET',
            'callback' => [$this->orderController, 'index'],

            // ✅ Require a valid JWT token
            'permission_callback' => function (\WP_REST_Request $request) {
                // Allow only administrators
                return current_user_can('manage_options');
                // return true;
            },

            'args' => [
                'page' => [
                    'required' => false,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_null($value) || is_numeric($value);
                    },
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'required' => false,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_null($value) || is_numeric($value);
                    },
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);


        register_rest_route('kibsterlp-admin/v1', '/my-orders', [
            'methods'  => 'GET',
            'callback' => [$this->orderController, 'myOrder'],

            // ✅ Require a valid JWT token
            'permission_callback' => function (\WP_REST_Request $request) {
                // Get current logged-in user
                $user = wp_get_current_user();

                // Check if user has the 'vendor' role
                return in_array('vendor', (array) $user->roles, true);
            },

            'args' => [
                'page' => [
                    'required' => false,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_null($value) || is_numeric($value);
                    },
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'required' => false,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_null($value) || is_numeric($value);
                    },
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);





        // show

        register_rest_route('kibsterlp-admin/v1', '/orders/(?P<uniq_id>[A-Za-z0-9\-]+)', [
            'methods'  => 'GET',
            'callback' => [$this->orderController, 'show'],
            'permission_callback' => function ($request) {
                return true; // or use current_user_can('manage_options')
            },
        ]);

        //working on vendors
        register_rest_route('kibsterlp-admin/v1', '/accept-order/(?P<order_unique_id>[a-zA-Z0-9-_]+)', [
            'methods'  => 'PUT',
            'callback' => [$this->orderController, 'acceptOrder'],

            // ✅ Vendor-only permission
            'permission_callback' => function () {
                $user = wp_get_current_user();
                return in_array('vendor', (array) $user->roles, true);
            },

            // ✅ Input validation
            'args' => [
                'order_unique_id' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'sharing_status' => [
                    'required' => true,
                    'validate_callback' => function ($value) {
                        $allowed_statuses = ['Accepted'];
                        return in_array($value, $allowed_statuses, true);
                    },
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        //working on admin payment 

        register_rest_route('kibsterlp-admin/v1', '/admin-payment-update/(?P<id>\d+)', [
            'methods'  => 'PUT',
            'callback' => [$this->orderController, 'update_payment_status'],
            'permission_callback' => '__return_true', // Replace with real vendor auth later
            'args' => [
                'id' => [
                    'required' => true,
                    'validate_callback' => function ($value) {
                        return is_numeric($value);
                    },
                    'sanitize_callback' => 'absint',
                ],
                'payment_status' => [
                    'required' => true,
                    'validate_callback' => function ($value) {
                        $allowed_statuses = ['Paid']; // only allow “Accepted”
                        return in_array($value, $allowed_statuses, true);
                    },
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);


        // POST /orders
        register_rest_route('kibsterlp-admin/v1', '/orders', [
            'methods'  => 'POST',
            'callback' => [$this->orderController, 'create'],
            'permission_callback' => '__return_true', // make public if needed
            'args' => [
                'vendor_id' => [
                    'required' => false,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_null($value) || is_numeric($value);
                    },
                    'sanitize_callback' => function ($value, $request, $param) {
                        return absint($value);
                    },
                ],
                'price' => [
                    'required' => false,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_numeric($value);
                    },
                    'sanitize_callback' => function ($value, $request, $param) {
                        return floatval($value);
                    },
                ],
                'shipping_id' => [
                    'required' => false,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_null($value) || is_numeric($value);
                    },
                    'sanitize_callback' => function ($value, $request, $param) {
                        return absint($value);
                    },
                ],
                'shipping' => [
                    'required' => false,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_null($value) || is_array($value);
                    },
                ],
                'budget' => [
                    'required' => false,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_null($value) || is_numeric($value);
                    },
                    'sanitize_callback' => function ($value, $request, $param) {
                        return floatval($value);
                    },
                ],
                'order_title' => [
                    'required' => false,
                    'sanitize_callback' => function ($value, $request, $param) {
                        return sanitize_text_field($value);
                    },
                ],
                'sharing_status' => [
                    'required' => false,
                    'sanitize_callback' => function ($value, $request, $param) {
                        return sanitize_text_field($value);
                    },
                ],
            ],
        ]);

        // PUT/PATCH /orders/{id}
        register_rest_route('kibsterlp-admin/v1', '/orders/(?P<id>\d+)', [
            'methods'  => WP_REST_Server::EDITABLE,
            'callback' => [$this->orderController, 'update'],
            'permission_callback' => function (\WP_REST_Request $request) {
                // Allow only administrators
                return current_user_can('manage_options');
                // return true;
            },
            'args' => [
                'id' => [
                    'required' => true,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_numeric($value);
                    },
                    'sanitize_callback' => function ($value, $request, $param) {
                        return absint($value);
                    },
                ],
                'vendor_id' => [
                    'required' => false,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_null($value) || is_numeric($value);
                    },
                    'sanitize_callback' => function ($value, $request, $param) {
                        return absint($value);
                    },
                ],
                'price' => [
                    'required' => false,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_null($value) || is_numeric($value);
                    },
                    'sanitize_callback' => function ($value, $request, $param) {
                        return floatval($value);
                    },
                ],
                'shipping_id' => [
                    'required' => false,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_null($value) || is_numeric($value);
                    },
                    'sanitize_callback' => function ($value, $request, $param) {
                        return absint($value);
                    },
                ],
                'shipping' => [
                    'required' => false,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_null($value) || is_array($value);
                    },
                ],
                'budget' => [
                    'required' => false,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_null($value) || is_numeric($value);
                    },
                    'sanitize_callback' => function ($value, $request, $param) {
                        return floatval($value);
                    },
                ],
                'order_title' => [
                    'required' => false,
                    'sanitize_callback' => function ($value, $request, $param) {
                        return sanitize_text_field($value);
                    },
                ],
                'sharing_status' => [
                    'required' => false,
                    'sanitize_callback' => function ($value, $request, $param) {
                        return sanitize_text_field($value);
                    },
                ],
            ],
        ]);

        // DELETE /orders/{id}
        register_rest_route('kibsterlp-admin/v1', '/orders/(?P<id>\d+)', [
            'methods'  => 'DELETE',
            'callback' => [$this->orderController, 'delete'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'required' => true,
                    'validate_callback' => function ($value, $request, $param) {
                        return is_numeric($value);
                    },
                    'sanitize_callback' => function ($value, $request, $param) {
                        return absint($value);
                    },
                ],
            ],
        ]);

        register_rest_route('kibsterlp-admin/v1', '/users', [
            'methods'  => 'GET',
            'callback' => [$this->userController, 'index'],

            'permission_callback' => function (\WP_REST_Request $request) {
                // Allow only administrators
                return current_user_can('manage_options');
            },
            'args' => [
                'page' => [
                    'required' => false,
                    'validate_callback' => fn($value, $request, $param) => is_null($value) || is_numeric($value),
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'required' => false,
                    'validate_callback' => fn($value, $request, $param) => is_null($value) || is_numeric($value),
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
    }

    // TEMPORARY: flush old routes once
    public function flush_routes_once()
    {
        global $wp_rest_server;
        if (isset($wp_rest_server)) {
            $wp_rest_server->flush_routes();
        }
        remove_action('init', [$this, 'flush_routes_once']);
    }
}

if (class_exists('RestApiRoutes')) {
    new RestApiRoutes();
}
