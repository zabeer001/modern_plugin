<?php
/**
 * Plugin Name: Zabeer Auth 
 * Description: A react.js powered admin interface for WordPress. Adds 'vendor' role and creates pages via controller.
 * Version: 1.1.1
 * Author: zabeer
 * Author URI: https://zabeer.dev
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: react-admin
 */

if (!defined('ABSPATH')) exit; // Prevent direct access

// Define plugin path
define('ZABEER_AUTH_PATH', plugin_dir_path(__FILE__));

// Require controller
require_once ZABEER_AUTH_PATH . 'app/class/PageCreationController.php';
require_once ZABEER_AUTH_PATH . 'app/class/RoleCreationController.php';

require_once ZABEER_AUTH_PATH . 'app/class/ExtraInfoController.php';

/**
 * Redirect users to custom login page after logout.
 */
add_action('wp_logout', function () {
    $rootUrl = site_url('/?logged_out=true'); // Add query param to trigger cleanup script
    wp_redirect($rootUrl);
    exit;
});

// Register your page creation/removal hooks
register_activation_hook(__FILE__, ['RoleCreationController', 'add_role']);
register_activation_hook(__FILE__, ['PageCreationController', 'zabeer_auth_create_page']);

register_deactivation_hook(__FILE__, ['PageCreationController', 'zabeer_auth_remove_page']);
register_deactivation_hook(__FILE__, ['RoleCreationController', 'remove_role']);

/**
 * REST API: Get current user details
 */
add_action('rest_api_init', function () {
    register_rest_route('zabeer-auth/v1', '/me', [
        'methods'  => 'GET',
        'callback' => function (\WP_REST_Request $request) {
            $user = wp_get_current_user();

            if (!$user || 0 === $user->ID) {
                return new \WP_Error('not_logged_in', 'You are not authenticated.', ['status' => 401]);
            }

            // Return user data
            $data = [
                'id'            => $user->ID,
                'username'      => $user->user_login,
                'email'         => $user->user_email,
                'display_name'  => $user->display_name,
                'roles'         => $user->roles,
                'first_name'    => $user->first_name,
                'last_name'     => $user->last_name,
                'registered_at' => $user->user_registered,
                'meta'          => get_user_meta($user->ID),
            ];

            return rest_ensure_response($data);
        },
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ]);
});

/**
 * Inject cleanup JS after logout redirect.
 */
add_action('wp_footer', function () {
    if (isset($_GET['logged_out']) && $_GET['logged_out'] === 'true') :
        ?>
        <script>
            (function() {
                try {
                    // ✅ Clear browser storage
                    localStorage.clear();
                    sessionStorage.clear();

                    // ✅ Clear cookies
                    document.cookie.split(";").forEach(c => {
                        document.cookie = c
                            .replace(/^ +/, "")
                            .replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
                    });

                    // ✅ Clear service worker caches
                    if ('caches' in window) {
                        caches.keys().then(names => {
                            for (let name of names) caches.delete(name);
                        });
                    }

                    console.log("✅ Cleared all session data after logout.");
                } catch (e) {
                    console.error("Logout cleanup failed:", e);
                }
            })();
        </script>
        <?php
    endif;
});
