<?php
/**
 * Plugin Name: Zabeer Auth 
 * Description: A react.js powered admin interface for WordPress.
 * Version: 1.0.0
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




// Redirect users to custom login page after logout
add_action('wp_logout', function() {
    $rootUrl = site_url('/'); // Replace with your login page slug
    wp_redirect($rootUrl);
    exit;
});


// Register hooks
register_activation_hook(__FILE__, ['PageCreationController', 'zabeer_auth_create_page']);
register_deactivation_hook(__FILE__, ['PageCreationController', 'zabeer_auth_remove_page']);


