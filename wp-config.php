<?php


/**
 * The base configuration for WordPress
 *
 * @package WordPress
 */

// Helper function for Docker environments
if (!function_exists('getenv_docker')) {
	function getenv_docker($env, $default) {
		if ($fileEnv = getenv($env . '_FILE')) {
			return rtrim(file_get_contents($fileEnv), "\r\n");
		} else if (($val = getenv($env)) !== false) {
			return $val;
		} else {
			return $default;
		}
	}
}

/** Database settings **/
define( 'DB_NAME', getenv_docker('WORDPRESS_DB_NAME', 'wordpress') );
define( 'DB_USER', getenv_docker('WORDPRESS_DB_USER', 'example username') );
define( 'DB_PASSWORD', getenv_docker('WORDPRESS_DB_PASSWORD', 'example password') );
define( 'DB_HOST', getenv_docker('WORDPRESS_DB_HOST', 'mysql') );
define( 'DB_CHARSET', getenv_docker('WORDPRESS_DB_CHARSET', 'utf8') );
define( 'DB_COLLATE', getenv_docker('WORDPRESS_DB_COLLATE', '') );

define('FS_METHOD', 'direct');

/** Authentication unique keys and salts. */
define( 'AUTH_KEY',         getenv_docker('WORDPRESS_AUTH_KEY',         '3bf7dfd35469c5d63855336116a0c033fe8654ee') );
define( 'SECURE_AUTH_KEY',  getenv_docker('WORDPRESS_SECURE_AUTH_KEY',  'fcbf7055836b5f1b07acef43d7ac9f6febd20b45') );
define( 'LOGGED_IN_KEY',    getenv_docker('WORDPRESS_LOGGED_IN_KEY',    '77fbf22ad2463bdda5cd64c62c7bee565651c822') );
define( 'NONCE_KEY',        getenv_docker('WORDPRESS_NONCE_KEY',        'f65182b3197287be4ee291899eb59e4699f78aee') );
define( 'AUTH_SALT',        getenv_docker('WORDPRESS_AUTH_SALT',        '6c88cc19710383e0037a13ce95c0711a8dac8897') );
define( 'SECURE_AUTH_SALT', getenv_docker('WORDPRESS_SECURE_AUTH_SALT', '4385303d54f30628f979f80ad9dbb54b100653c9') );
define( 'LOGGED_IN_SALT',   getenv_docker('WORDPRESS_LOGGED_IN_SALT',   '77f3d4e98efb9672df437fb822fe27e4fd7f5836') );
define( 'NONCE_SALT',       getenv_docker('WORDPRESS_NONCE_SALT',       '8e0e66b9ac4eec8df9905d45bb0e4ea1081c1c31') );

/** Table prefix **/
$table_prefix = getenv_docker('WORDPRESS_TABLE_PREFIX', 'wp_');

/** JWT Authentication constants **/
define('JWT_AUTH_SECRET_KEY', 'your-top-secret-key'); // Use a long random key
define('JWT_AUTH_CORS_ENABLE', true);

/* Add any custom values between this line and the "stop editing" line. */

// HTTPS reverse proxy detection
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
	$_SERVER['HTTPS'] = 'on';
}

// Optional extra Docker config
if ($configExtra = getenv_docker('WORDPRESS_CONFIG_EXTRA', '')) {
	eval($configExtra);
}

/** ✅ Debugging setup **/
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
@ini_set('display_errors', 1);

// Optional: specify absolute debug log path if needed
// define('WP_DEBUG_LOG', '/home/username/public_html/wp-content/debug.log');

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
