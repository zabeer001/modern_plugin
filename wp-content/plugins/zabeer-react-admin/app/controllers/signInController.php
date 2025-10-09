<?php
if (!defined('ABSPATH')) exit;

class signInController
{
    public function __construct()
    {
        // Register the shortcode
        add_shortcode('jwt_login_form', [$this, 'render_login_form']);
        // Enqueue JS for handling login
        add_action('wp_footer', [$this, 'enqueue_login_script']);
    }

    /**
     * Shortcode callback to render the login form
     */
    public function render_login_form($atts = [])
    {
        ob_start();
        ?>
        <form id="jwt-login-form" style="max-width:300px; margin:20px 0;">
            <input type="text" id="jwt-username" placeholder="Username" required style="width:100%; padding:8px; margin-bottom:10px;">
            <input type="password" id="jwt-password" placeholder="Password" required style="width:100%; padding:8px; margin-bottom:10px;">
            <button type="submit" style="width:100%; padding:8px;">Login & Generate JWT</button>
            <div id="jwt-login-message" style="margin-top:10px;"></div>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * JS for handling form submission
     */
    public function enqueue_login_script()
    {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('jwt-login-form');
            if (!form) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const username = document.getElementById('jwt-username').value;
                const password = document.getElementById('jwt-password').value;
                const messageDiv = document.getElementById('jwt-login-message');
                messageDiv.textContent = 'Logging in...';

                try {
                    const res = await fetch('<?php echo site_url(); ?>/wp-json/jwt-auth/v1/token', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ username, password })
                    });
                    const data = await res.json();

                    if (data.token) {
                        localStorage.setItem('wp_jwt_token', data.token);
                        messageDiv.textContent = 'Login successful! JWT stored.';
                        console.log('JWT token:', data.token);
                    } else {
                        messageDiv.textContent = data.message || 'Login failed.';
                    }
                } catch (err) {
                    messageDiv.textContent = 'Error logging in.';
                    console.error(err);
                }
            });
        });
        </script>
        <?php
    }
}

// Instantiate the class so shortcode is available
if (class_exists('JwtLoginFormShortcode')) {
    new signInController();
}
