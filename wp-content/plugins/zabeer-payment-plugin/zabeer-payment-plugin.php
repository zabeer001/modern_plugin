<?php
/**
 * Plugin Name: Zabeer Payment Plugin
 * Description: Minimal plugin to test initialization.
 * Version: 1.0.0
 * Author: zabeer
 * Author URI: https://zabeer.dev
 * Text Domain: zabeer-payment-plugin
 */

if (!defined('ABSPATH')) exit; // Prevent direct access

class ZabeerPaymentPlugin {

    public function __construct() {
        // Hook into WordPress 'init'
        add_action('init', [$this, 'say_hello']);
    }

    public function say_hello() {
        // Simple output
        error_log('Hello World from Zabeer Payment Plugin!');
    }
}

// Initialize the plugin
new ZabeerPaymentPlugin();
