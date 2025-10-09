<?php
if (!defined('ABSPATH')) exit;

// Include the controller
require_once __DIR__ . '/../controllers/HelloController.php';
require_once __DIR__ . '/../helpers/Utils.php';

class RestApiRoutes
{
    private $controller;

    public function __construct()
    {
        $this->controller = new HelloController();
        add_action('rest_api_init', [$this, 'register_routes']);

        // TEMP: flush old routes once
        add_action('init', [$this, 'flush_routes_once']);
        // add_action('wp_login', [$this, 'handle_jwt_on_login'], 10, 2);
    }

    public function register_routes()
    {
        //wp-json/v1/react-admin/v1/goodbye/ 
        // Register /goodbye endpoint
        register_rest_route('react-admin/v1', '/goodbye', [
            'methods'  => 'POST',
            'callback' => [$this->controller, 'goodbye_world'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);
    }

    // TEMPORARY: flush old routes
    public function flush_routes_once()
    {
        global $wp_rest_server;
        if (isset($wp_rest_server)) {
            $wp_rest_server->flush_routes();
        }

        // Remove this action immediately so it only runs once
        remove_action('init', [$this, 'flush_routes_once']);
    }

    public function handle_jwt_on_login($user_login, $user)
    {
        // Use the JWT plugin endpoint filter to generate token
        $token = apply_filters('jwt_auth_generate_token', $user->ID);

        echo 'Generated JWT Token on login: ' . $token;

        if ($token) {
            // Print JS in footer to store JWT in localStorage
            add_action('wp_footer', function () use ($token) {
?>
                <script>
                    localStorage.setItem('wp_jwt_token', '<?php echo $token; ?>');
                    console.log('JWT token stored in localStorage');
                </script>
<?php
            });
        }
    }
}

// Instantiate the routes class only once
if (class_exists('RestApiRoutes')) {
    new RestApiRoutes();
}
