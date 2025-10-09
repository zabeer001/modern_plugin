<?php
/**
 * Plugin Name: Zabeer React Admin
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




// require_once REACT_ADMIN_PLUGIN . 'app/controller/signInController.php';

require_once REACT_ADMIN_PLUGIN . 'app/routes/RestApiRoutes.php';

// Load the main admin class
require_once REACT_ADMIN_PLUGIN . 'app/react/React_Admin.php';





