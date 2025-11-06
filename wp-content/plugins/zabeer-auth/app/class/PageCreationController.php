<?php
if (!defined('ABSPATH')) exit; // Prevent direct access

class PageCreationController
{
    public function __construct()
    {
        // Register shortcodes when class is loaded
        add_shortcode('zabeer_hello', [$this, 'zabeer_auth_shortcode_hello']);
        add_shortcode('zabeer_register', [$this, 'zabeer_auth_shortcode_register']);
    }

    // === Create Pages on Plugin Activation ===
    public static function zabeer_auth_create_page()
    {
        // Sign In page
        $signin_title   = 'Sign In';
        $signin_slug    = 'sign-in';
        $signin_content = '[zabeer_hello]';

        $signin_page = get_page_by_path($signin_slug, OBJECT, 'page');
        if (!$signin_page) {
            wp_insert_post([
                'post_title'   => $signin_title,
                'post_content' => $signin_content,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_name'    => $signin_slug,
            ]);
        }

        // Registration page
        $register_title   = 'Registration';
        $register_slug    = 'registration';
        $register_content = '[zabeer_register]';

        $register_page = get_page_by_path($register_slug, OBJECT, 'page');
        if (!$register_page) {
            wp_insert_post([
                'post_title'   => $register_title,
                'post_content' => $register_content,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_name'    => $register_slug,
            ]);
        }
    }

    // === Remove Pages on Plugin Deactivation ===
    public static function zabeer_auth_remove_page()
    {
        // Delete by slug to be resilient to title edits
        foreach (['sign-in', 'registration'] as $slug) {
            $page = get_page_by_path($slug, OBJECT, 'page');
            if ($page) {
                wp_delete_post($page->ID, true); // true = force delete, skip trash
            }
        }
    }

    // === Shortcode handlers ===
    public function zabeer_auth_shortcode_hello()
    {
        ob_start();

        // If user is logged in
        if (is_user_logged_in()) {
            $logout_url = wp_logout_url(site_url('/?logged_out=true'));
?>
            <div style="text-align:center; padding:40px;">
                <h2>👋 Hello, <?php echo esc_html(wp_get_current_user()->display_name); ?>!</h2>
                <p>You are already logged in.</p>
                <a href="<?php echo esc_url($logout_url); ?>"
                    style="display:inline-block;padding:10px 20px;background:#c0392b;color:#fff;text-decoration:none;border-radius:6px;">
                    🔒 Logout
                </a>
            </div>
        <?php
        } else {
            // If user is not logged in, render your login page
            include ZABEER_AUTH_PATH . 'template/LoginPage.php';
        }

        return ob_get_clean();
    }

    public function zabeer_auth_shortcode_register()
    {
        ob_start();

        // ✅ If the user is already logged in, show a message (or redirect)
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
        ?>
            <div class="!bg-gradient-to-br !from-rose-700 !to-gray-900 !min-h-screen !flex !justify-center !items-center">
                <div class="!bg-white !rounded-2xl !shadow-xl !p-8 !max-w-md !w-full !text-center">
                    <h3 class="!text-2xl !font-bold !text-gray-800 mb-3">👋 Hi, <?php echo esc_html($current_user->display_name); ?>!</h3>
                    <p class="!text-gray-600 mb-6">You’re already registered and logged in.</p>
                    <a href="<?php echo esc_url(admin_url()); ?>"
                        class="!inline-block !px-6 !py-3 !rounded-lg !text-white !font-semibold !bg-gradient-to-r !from-red-500 !to-rose-600 hover:!from-red-600 hover:!to-rose-700 !transition-all">
                        Go to Dashboard
                    </a>
                </div>
            </div>
<?php
        } else {
            // ✅ Otherwise, render the registration page template
            include ZABEER_AUTH_PATH . 'template/RegistrationPage.php';
        }

        return ob_get_clean();
    }
}

new PageCreationController();
