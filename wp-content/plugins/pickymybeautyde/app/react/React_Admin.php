<?php
if (!defined('ABSPATH')) exit;

class React_Admin
{

 function __construct()
{
    add_action('admin_menu', function () {

        // ✅ Get the current logged-in user
        $user = wp_get_current_user();

        // ✅ Allow both admin and vendor roles
        if (in_array('administrator', $user->roles) || in_array('vendor', $user->roles)) {

            add_menu_page(
                'Commander Admin',
                'Commander Admin',
                'read', // ✅ minimal capability so both roles can access
                'react-admin',
                [$this, 'react_admin_render'],
                'dashicons-admin-site',
                6
            );
        }
    });

    // ✅ Frontend Shortcode support
    add_shortcode('react_admin', [$this, 'react_admin_render_shortcode']);

    // ✅ Load Vite React build in frontend
    add_action('wp_enqueue_scripts', [$this, 'load_frontend_scripts']);

    // ✅ Hot reload runtime for Vite
    add_action('admin_head', [$this, 'react_preamble']);
    add_action('wp_head', [$this, 'react_preamble']);

    // ✅ Admin-only scripts
    add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts']);

    // ✅ Register REST routes for your custom APIs
    add_action('rest_api_init', [$this, 'register_api_routes']);
}


    function react_preamble()
    {
?>
        <script type="module">
            import RefreshRuntime from 'http://localhost:5173/@react-refresh'
            RefreshRuntime.injectIntoGlobalHook(window)
            window.$RefreshReg$ = () => {}
        </script>
    <?php
    }

    function load_admin_scripts($hook)
    {
        // Enqueue scripts ONLY on the specific admin page
        if ($hook !== 'toplevel_page_react-admin') return;

        wp_enqueue_script_module(
            'vite-react-admin-js',
            'http://localhost:5173/src/main.jsx',
            [],
            time(),
            true
        );
    }

    function load_frontend_scripts()
    {
        // Enqueue scripts ONLY on the frontend
        wp_enqueue_script_module(
            'vite-react-admin-js',
            'http://localhost:5173/src/main.jsx',
            [],
            time(),
            true
        );
    }


    function react_admin_render()
    {
    ?>
        <script src="https://cdn.tailwindcss.com"></script>
        <div class="wrap">
            <div id="root"></div>
        </div>
<?php
    }

    // A new function to handle the shortcode render, so it can enqueue scripts separately
    function react_admin_render_shortcode()
    {
        ob_start(); // Start output buffering to return the HTML
    ?>
        <script src="https://cdn.tailwindcss.com"></script>
        <div id="root"></div>
<?php
        return ob_get_clean(); // Return the buffered content
    }

    function register_api_routes()
    {
        register_rest_route('react-admin/v1', '/hello', [
            'methods' => 'GET',
            'callback' => [$this, 'hello_world_api'],
            'permission_callback' => '__return_true',
        ]);
    }

    function hello_world_api()
    {
        return ['message' => 'Hello World'];
    }
}

new React_Admin();