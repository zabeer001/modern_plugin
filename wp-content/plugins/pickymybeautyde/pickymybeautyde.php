<?php
/**
 * Plugin Name: picky my beauty de Plugin
 * Description: A react.js powered admin interface for WordPress.
 * Version: 1.0.0
 * Author: zabeer
 * Author URI: https://zabeer.dev
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: react-admin
 */

if (!defined('ABSPATH')) exit; // Prevent direct access

define('REACT_ADMIN_API_KEY', 'your_super_secret_key_here');
define('REACT_ADMIN_PLUGIN', plugin_dir_path(__FILE__));

// require_once REACT_ADMIN_PLUGIN . 'app/react/React_Production.php';
require_once REACT_ADMIN_PLUGIN . 'app/react/React_Admin.php';
// require_once REACT_ADMIN_PLUGIN . 'app/react/React_Frontend.php';


// ✅ Load external CDN script (frontend + admin)
add_action('wp_enqueue_scripts', 'react_admin_enqueue_cdn');
add_action('admin_enqueue_scripts', 'react_admin_enqueue_cdn');

function react_admin_enqueue_cdn() {
    wp_enqueue_script(
        'zabeer-cdn', 
        'https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4', // replace with your CDN link
        [], 
        null, 
        true
    );

      // Enqueue local plugin script (example: js/assets.js)
    wp_enqueue_script(
        'zabeer-local',
        plugin_dir_url(__FILE__) . 'js/assets.js', // your plugin root/js/assets.js
        ['jquery'], // dependencies if needed
        filemtime(plugin_dir_path(__FILE__) . 'js/assets.js'), // version = file modified time (cache busting)
        true // load in footer
    );
}