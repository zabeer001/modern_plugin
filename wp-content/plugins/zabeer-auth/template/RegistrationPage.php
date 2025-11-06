<?php
// /var/www/wordpress/modern_plugin/wp-content/plugins/zabeer-auth/template/RegistrationPage.php

ob_start();

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['zabeer_register'])) {

    $username  = sanitize_user($_POST['username']);
    $email     = sanitize_email($_POST['email']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name  = sanitize_text_field($_POST['last_name']);
    $address    = sanitize_text_field($_POST['address']);
    $postal     = sanitize_text_field($_POST['postal_code']);
    $city       = sanitize_text_field($_POST['city']);
    $phone      = sanitize_text_field($_POST['phone']);
    $targets    = isset($_POST['targets']) ? implode(', ', array_map('sanitize_text_field', $_POST['targets'])) : '';
    $description = sanitize_textarea_field($_POST['description']);

    // ✅ Handle file upload
    $logo_url = '';
    if (!empty($_FILES['vendor_logo']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $uploaded = wp_handle_upload($_FILES['vendor_logo'], ['test_form' => false]);
        if (!isset($uploaded['error'])) {
            $logo_url = esc_url($uploaded['url']);
        }
    }

    // ✅ Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $error = "⚠️ Please fill in all required fields.";
    } elseif ($password !== $confirm) {
        $error = "❌ Passwords do not match.";
    } elseif (username_exists($username)) {
        $error = "❌ Username already exists.";
    } elseif (email_exists($email)) {
        $error = "❌ Email already registered.";
    } else {
        // ✅ Create new user
        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            $error = "⚠️ " . $user_id->get_error_message();
        } else {
            // ✅ Assign vendor role and save meta
            $user = new WP_User($user_id);
            $user->set_role('vendor');
            update_user_meta($user_id, 'is_vendor', true);
            update_user_meta($user_id, 'first_name', $first_name);
            update_user_meta($user_id, 'last_name', $last_name);
            update_user_meta($user_id, 'vendor_address', $address);
            update_user_meta($user_id, 'zipcode', $postal);
            update_user_meta($user_id, 'vendor_city', $city);
            update_user_meta($user_id, 'vendor_phone', $phone);
            update_user_meta($user_id, 'vendor_description', $description);
            if (!empty($logo_url)) {
                update_user_meta($user_id, 'vendor_logo_url', $logo_url);
            }

            // ✅ Success message to trigger toast in JS
            $success = true;
        }
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <style>
        .toast {
            position: fixed;
            top: 25px;
            right: 25px;
            background-color: #16a34a;
            color: white;
            padding: 14px 22px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.4s ease;
            z-index: 9999;
            font-weight: 500;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-rose-700 to-gray-900 min-h-screen flex justify-center items-center">

    <!-- Toast -->
    <div id="toast" class="toast">✅ Registration successful! Redirecting...</div>

    <div class="bg-white rounded-2xl shadow-xl p-8 max-w-lg w-full transform transition hover:-translate-y-1 hover:shadow-2xl">
        <div class="text-center mb-6">
            <h3 class="text-3xl font-bold text-gray-800">Create Vendor Account</h3>
            <p class="text-gray-500 mt-2">Fill out your vendor details below</p>
        </div>

        <?php if (!empty($error)): ?>
            <p class="text-center text-red-500 mb-4 font-semibold"><?php echo esc_html($error); ?></p>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" enctype="multipart/form-data" class="space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <input type="text" name="first_name" placeholder="Vorname*" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                <input type="text" name="last_name" placeholder="Nachname*" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
            </div>

            <input type="text" name="username" placeholder="Benutzername*" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-500">

            <div class="grid grid-cols-2 gap-4">
                <input type="password" name="password" placeholder="Passwort*" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                <input type="password" name="confirm_password" placeholder="Passwort bestätigen*" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
            </div>

            <input type="email" name="email" placeholder="Email*" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
            <input type="text" name="address" placeholder="Adresse*" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
            <input type="text" name="postal_code" placeholder="Postleitzahl*" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
            <input type="text" name="city" placeholder="Stadt*" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
            <input type="text" name="phone" placeholder="Telefonnummer*" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-500">



            <textarea name="description" placeholder="Beschreibung*" required rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Logo / Bild (optional)</label>
                <input type="file" name="vendor_logo" accept="image/*" class="w-full text-gray-700 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500">
            </div>

            <button type="submit" name="zabeer_register" class="w-full py-3 rounded-lg text-white font-semibold text-lg bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 transition-all">
                ✨ Register
            </button>
        </form>

        <div class="flex items-center my-6">
            <div class="flex-grow border-t border-gray-300"></div>
            <span class="mx-3 text-gray-400 text-sm">or</span>
            <div class="flex-grow border-t border-gray-300"></div>
        </div>

        <p class="text-center text-gray-600 mt-4 text-sm">
            Already have an account?
            <a href="/sign-in" class="ml-2 text-rose-500 font-semibold hover:text-rose-600 hover:underline transition">Log in</a>
        </p>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pageContent = document.querySelector('.page-content');
            if (pageContent) {
                pageContent.classList.add('flex', 'items-center', 'justify-center');

            }

        });
    </script>

    <?php if (!empty($success)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {


                const toast = document.getElementById('toast');
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                    window.location.href = "<?php echo site_url('/sign-in?registered=1'); ?>";
                }, 2000);


            });
        </script>
    <?php endif; ?>

</body>

</html>