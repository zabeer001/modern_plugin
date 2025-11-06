<?php
if (!defined('ABSPATH')) exit; // Prevent direct access

class React_Production
{
    function __construct()
    {
        add_action('admin_menu', function () {
            add_menu_page(
                'React Admin',
                'Picky Admin',
                'read',
                'react-admin',
                [$this, 'react_admin_render'],
                'dashicons-admin-site',
                6
            );
        });

        // Register shortcodes
        add_shortcode('react_admin', [$this, 'react_admin_render_shortcode']);
        add_shortcode('react_pickmybeauty_frontend', [$this, 'react_frontend_shortcode']);

        add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'load_frontend_scripts']);
        add_action('rest_api_init', [$this, 'register_api_routes']);
        add_action('wp_head', [$this, 'react_preamble']);
    }

    function load_admin_scripts($hook)
    {
        if ($hook !== 'toplevel_page_react-admin') return;

        $plugin_url = plugin_dir_url(__FILE__);

        // Correct paths relative to this PHP file
        $js_file  = 'build/admin/assets/admin.js';
        $css_file = 'build/admin/assets/admin.css';

        $js_path  = __DIR__ . '/' . $js_file;
        $css_path = __DIR__ . '/' . $css_file;

        // Enqueue CSS
        if (file_exists($css_path)) {
            wp_enqueue_style(
                'react-admin-style',
                $plugin_url . $css_file, // Fixed: was incorrectly using $js_file
                [],
                filemtime($css_path)
            );
        } else {
            echo '<pre>CSS file not found!</pre>';
        }

        // Enqueue JS
        if (file_exists($js_path)) {
            wp_enqueue_script(
                'react-admin-script',
                $plugin_url . $js_file,
                [],
                filemtime($js_path),
                true
            );
        } else {
            echo '<pre>JS file not found!</pre>';
        }
    }

    function load_frontend_scripts()
    {
        $plugin_url = plugin_dir_url(__FILE__);

        // Correct paths relative to this PHP file
        $js_file  = 'build/frontend/assets/frontend.js';
        $css_file = 'build/frontend/assets/frontend.css';

        $js_path  = __DIR__ . '/' . $js_file;
        $css_path = __DIR__ . '/' . $css_file;

        // Enqueue CSS
        if (file_exists($css_path)) {
            wp_enqueue_style(
                'react-frontend-style',
                $plugin_url . $css_file,
                [],
                filemtime($css_path)
            );
        }

        // Enqueue JS
        if (file_exists($js_path)) {
            wp_enqueue_script(
                'react-frontend-script',
                $plugin_url . $js_file,
                [],
                filemtime($js_path),
                true
            );
        }
    }

    function react_admin_render_shortcode()
    {
        ob_start();
?>
        <script src="https://cdn.tailwindcss.com"></script>
        <div class="wrap container">
            <div id="root"></div>
        </div>
    <?php
        return ob_get_clean();
    }

    function react_frontend_shortcode()
    {
        ob_start();
    ?>
        <script src="https://cdn.tailwindcss.com"></script>
        <div id="frontend-root"></div>
    <?php
        return ob_get_clean();
    }

    function react_admin_render()
    {
    ?>

        <div class="wrap container">
            <div id="root"></div>
        </div>
    <?php
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

    function react_preamble()
    {
        // Add any meta tags, scripts, or other head content needed for the frontend
    ?>
        <meta name="react-app" content="React Frontend">
        <script>
            // Global configuration or initialization for React app
            window.REACT_APP_CONFIG = {
                apiUrl: '<?php echo esc_url(rest_url('react-admin/v1')); ?>',
                siteUrl: '<?php echo esc_url(home_url()); ?>'
            };
        </script>
<?php
    }
}

new React_Production();
