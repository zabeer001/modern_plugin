<?php
if (!defined('ABSPATH')) exit;

class React_Frontend
{
    function __construct()
    {
        add_action('admin_menu', function () {
            add_menu_page(
                'Commander Admin',
                'Commander Admin',
                'manage_options',
                'react-admin',
                [$this, 'react_admin_render'],
                'dashicons-admin-site',
                6
            );
        });

        add_shortcode('react_admin', [$this, 'react_frontend_commander_shortcode']); // Separate render function for shortcode
        add_action('wp_enqueue_scripts', [$this, 'load_frontend_scripts']); // New hook for frontend scripts

        add_action('admin_head', [$this, 'react_preamble']);
        add_action('wp_head', [$this, 'react_preamble']);
        add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts']); // Separate function for admin scripts
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
        
            <div id="root" "></div>
       
<?php
    }

    function react_frontend_commander_shortcode()
    {
        ob_start(); // Start output buffering to return the HTML
?>
        <script src="https://cdn.tailwindcss.com"></script>
        <div class="react-admin-container flex justify-center items-center min-h-screen" style="margin: 0 auto !important;">
            <div id="root" class="w-full max-w-4xl"></div>
        </div>
<?php
        return ob_get_clean(); // Return the buffered content
    }

 
}

new React_Frontend();
?>