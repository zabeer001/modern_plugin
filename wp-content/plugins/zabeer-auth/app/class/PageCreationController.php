<?php
if (!defined('ABSPATH')) exit; // Prevent direct access

class PageCreationController
{
    public function __construct()
    {
        // Register the shortcode when class is loaded
        add_shortcode('zabeer_hello', [$this, 'zabeer_auth_shortcode_hello']);
    }

    // === Create Page on Plugin Activation ===
   public static function zabeer_auth_create_page()
{
    $page_title   = 'Sign In';
    $page_content = '[zabeer_hello]';

    // Check if page already exists
    $page_check = get_page_by_title($page_title, OBJECT, 'page');

    if (!$page_check) {
        wp_insert_post([
            'post_title'    => $page_title,
            'post_content'  => $page_content,
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'sign-in', // custom slug
        ]);
    }
}
    // === Remove Page on Plugin Deactivation ===
    public static function zabeer_auth_remove_page()
    {
        $page = get_page_by_title('Zabeer Hello Page');
        if ($page) {
            wp_delete_post($page->ID, true);
        }
    }

    // === Shortcode handler ===
    public function zabeer_auth_shortcode_hello()
    {
        ob_start(); // Start output buffering
        include ZABEER_AUTH_PATH . 'template/LoginPage.php';
        return ob_get_clean(); // Return the output as a string
    }
}
new PageCreationController();
