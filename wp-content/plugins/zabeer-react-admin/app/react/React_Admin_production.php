<?php
if (!defined('ABSPATH')) exit; // Prevent direct access

class React_Admin
{

    function __construct()
    {
        add_action('admin_menu', function () {
            add_menu_page(
                'React Admin',
                'React Admin',
                'manage_options',
                'react-admin',
                [$this, 'react_admin_render'],
                'dashicons-admin-site',
                6
            );
        });


        add_action('admin_enqueue_scripts', [$this, 'load_scripts']);
        add_action('rest_api_init', [$this, 'register_api_routes']);
    }


function load_scripts($hook)
{
    if ($hook !== 'toplevel_page_react-admin') return;

    $plugin_url = plugin_dir_url(__FILE__);

    // Correct paths relative to this PHP file
    $js_file  = 'build/assets/index-B1s4Rmae.js';
    $css_file = 'build/assets/index-Cu4k2a9s.css';

    $js_path  = __DIR__ . '/' . $js_file;
    $css_path = __DIR__ . '/' . $css_file;

    // Print paths for debugging
    // echo '<pre>';
    // echo 'Checking CSS file: ' . $css_path . "\n";
    // echo 'Checking JS file: ' . $js_path . "\n";
    // echo '</pre>';

    // Enqueue CSS
    if (file_exists($css_path)) {
        wp_enqueue_style(
            'react-admin-style',
            $plugin_url . $css_file,
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


    function react_admin_render()
    {
?>
        <div class="wrap">
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
}

new React_Admin();



//  function react_preamble()
