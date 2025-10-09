<?php
// /var/www/wordpress/modern_plugin/wp-content/plugins/zabeer-auth/template/LoginPage.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Tailwind CSS CDN (only for this page) -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Custom styles scoped to this page */
        .gradient-bg {
            background: linear-gradient(135deg, #6B7280, #1F2937);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .btn-gradient {
            background: linear-gradient(to right, #3B82F6, #8B5CF6);
            transition: background 0.3s ease;
        }
        .btn-gradient:hover {
            background: linear-gradient(to right, #2563EB, #7C3AED);
        }
        .input-focus {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .input-focus:focus {
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
            outline: none;
        }
    </style>
</head>
<body class="gradient-bg">
    <div class="container mx-auto px-4">
        <div class="flex justify-center items-center">
            <div class="card bg-white rounded-2xl shadow-lg p-8 max-w-md w-full">
                <div class="text-center mb-6">
                    <h3 class="text-3xl font-bold text-gray-800">Welcome Back</h3>
                    <p class="text-gray-500 mt-2">Sign in to your account</p>
                </div>
                <form id="zabeer-login-form">
                    <div class="mb-5">
                        <input 
                            type="text" 
                            id="username" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-700 input-focus" 
                            placeholder="Username" 
                            required
                        >
                    </div>
                    <div class="mb-6">
                        <input 
                            type="password" 
                            id="password" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-700 input-focus" 
                            placeholder="Password" 
                            required
                        >
                    </div>
                    <button 
                        type="submit" 
                        class="btn-gradient w-full py-3 rounded-lg text-white font-semibold text-lg"
                    >
                        Login
                    </button>
                </form>
                <p class="text-center text-gray-500 mt-6">
                    Forgot your password? <a href="#" class="text-blue-500 hover:underline">Reset it</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        const API_URL = '<?php echo site_url(); ?>/wp-json/jwt-auth/v1/token';
        const LOGIN_URL = '<?php echo site_url(); ?>/wp-login.php';

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('zabeer-login-form');

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;

                try {
                    // Call JWT API
                    const response = await fetch(API_URL, {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ username, password })
                    });

                    const data = await response.json();
                    console.log(data);

                    if (data.token) {
                        sessionStorage.setItem('jwt_token', data.token);

                        // Trigger WordPress login
                        const wpForm = document.createElement('form');
                        wpForm.method = 'POST';
                        wpForm.action = LOGIN_URL;

                        const userField = document.createElement('input');
                        userField.type = 'hidden';
                        userField.name = 'log';
                        userField.value = username;
                        wpForm.appendChild(userField);

                        const passField = document.createElement('input');
                        passField.type = 'hidden';
                        passField.name = 'pwd';
                        passField.value = password;
                        wpForm.appendChild(passField);

                        const redirectField = document.createElement('input');
                        redirectField.type = 'hidden';
                        redirectField.name = 'redirect_to';
                        redirectField.value = '<?php echo admin_url(); ?>';
                        wpForm.appendChild(redirectField);

                        document.body.appendChild(wpForm);
                        wpForm.submit();
                    } else {
                        alert('Login failed!');
                    }
                } catch (error) {
                    console.error("Error:", error);
                }
            });
        });
    </script>
</body>
</html>