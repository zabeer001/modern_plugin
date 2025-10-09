<?php
if (!defined('ABSPATH')) exit; // Prevent direct access

class React_Admin {

    function __construct() {
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

        add_action('admin_head', [$this, 'react_preamble']);
        add_action('admin_enqueue_scripts', [$this, 'load_scripts']);
      
    }

    function react_preamble() {
        ?>
        <script type="module">
            import RefreshRuntime from 'http://localhost:5173/@react-refresh'
            RefreshRuntime.injectIntoGlobalHook(window)
            window.$RefreshReg$ = () => {}
        </script>
        <?php
    }

    function load_scripts($hook) {
        if ($hook !== 'toplevel_page_react-admin') return;

        wp_enqueue_script_module(
            'vite-react-admin-js',
            'http://localhost:5173/src/main.jsx',
            [],
            time(),
            true
        );
    }

    function react_admin_render() {
        ?>
        <div class="wrap">
            <div id="root"></div>
        </div>
        <?php
    }

   
}

new React_Admin();