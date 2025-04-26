<?php
session_start();

if ((isset($_SESSION["adminLogSuccess"]) && $_SESSION["adminLogSuccess"] == true) || (isset($_SESSION["user"]) && empty($_SESSION["user"]))) {
    header("Location: index.php");
}

if (!isset($_SESSION["_adminLogToken"])) {
    $rstrong = true;
    $_SESSION["_adminLogToken"] = hash('sha256', bin2hex(openssl_random_pseudo_bytes(64, $rstrong)));
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Lecturer Portal - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #003262;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --text-color: #ecf0f1;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --light-bg: #f5f6fa;
            --border-color: #ddd;
            --card-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s;
        }

        body {
            background-color: var(--light-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Login Container */
        .login-container {
            width: 100%;
            max-width: 420px;
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .login-header {
            background-color: var(--primary-color);
            color: var(--text-color);
            padding: 30px 20px;
            text-align: center;
        }

        .login-logo {
            width: 80px;
            height: 80px;
            background-color: white;
            border-radius: 50%;
            padding: 10px;
            margin: 0 auto 15px;
        }

        .login-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .login-title {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .login-subtitle {
            font-size: 1rem;
            opacity: 0.8;
        }

        .login-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--secondary-color);
            font-weight: 500;
        }

        .form-group .input-group {
            position: relative;
        }

        .form-group .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color var(--transition-speed);
        }

        .form-group input:focus {
            border-color: var(--accent-color);
            outline: none;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary-color);
        }

        .remember-me input {
            width: 16px;
            height: 16px;
        }

        .forgot-password {
            color: var(--accent-color);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color var(--transition-speed);
        }

        .login-btn:hover {
            background-color: var(--primary-color);
        }

        .login-footer {
            text-align: center;
            padding: 20px 30px;
            border-top: 1px solid var(--border-color);
            color: var(--secondary-color);
        }

        .login-footer a {
            color: var(--accent-color);
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .error-message {
            background-color: #ffebee;
            color: var(--danger-color);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        /* Toggle Password Visibility */
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <img src="logo.png" alt="RMU Logo">
            </div>
            <h1 class="login-title">RMU Lecturer Portal</h1>
            <p class="login-subtitle">Sign in to access your account</p>
        </div>
        <div class="login-body">
            <div class="error-message" id="errorMessage">
                <i class="fas fa-exclamation-circle"></i> Invalid email or password. Please try again.
            </div>
            <form id="loginForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" id="rememberMe" name="rememberMe">
                        <span>Remember me</span>
                    </label>
                    <a href="forgot-password.html" class="forgot-password">Forgot Password?</a>
                </div>
                <button type="submit" class="login-btn">Sign In</button>
            </form>
        </div>
        <div class="login-footer">
            <p>Having trouble? <a href="mailto:support@rmu.edu">Contact Support</a></p>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Form submission
        const loginForm = document.getElementById('loginForm');
        const errorMessage = document.getElementById('errorMessage');

        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // For demo purposes, we'll use a simple validation
            // In a real application, this would be a server request
            if (email === 'lecturer@rmu.edu' && password === 'password123') {
                // Successful login
                window.location.href = 'dashboard.html';
            } else {
                // Show error message
                errorMessage.classList.add('show');

                // Hide error message after 3 seconds
                setTimeout(() => {
                    errorMessage.classList.remove('show');
                }, 3000);
            }
        });
    </script>
    <script src="js/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            $("#adminLoginForm").on("submit", function(e) {
                e.preventDefault();

                if (!$("#yourUsername").val()) {
                    alert("Username required!");
                    return;
                }

                if (!$("#yourPassword").val()) {
                    alert("Password required!");
                    return;
                }


                $.ajax({
                    type: "POST",
                    url: "endpoint/admin-login",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            window.location.href = "./";
                        } else {
                            alert(result['message']);
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            $(document).on({
                ajaxStart: function() {
                    $("#submitBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
                },
                ajaxStop: function() {
                    $("#submitBtn").prop("disabled", false).html('Login');
                }
            });

        });
    </script>

</body>

</html>