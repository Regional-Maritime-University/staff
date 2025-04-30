<?php
session_start();

$_SESSION["lastAccessed"] = time();

if (isset($_SESSION["staffLoginSuccess"]) && $_SESSION["staffLoginSuccess"] == true && isset($_SESSION["staff"]) && !empty($_SESSION["staff"]["number"])) {
    header("Location: {$_SESSION['role']}");
}

if (!isset($_SESSION["_staffLogToken"]) || empty($_SESSION["_staffLogToken"])) {
    $rstrong = true;
    $_SESSION["_staffLogToken"] = hash('sha256', bin2hex(openssl_random_pseudo_bytes(64, $rstrong)));
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Staff Portal - Login</title>
    <link rel="stylesheet" href="./assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <img src="./assets/img/logo.png" alt="RMU Logo">
            </div>
            <h1 class="login-title">RMU Staff Portal</h1>
            <p class="login-subtitle">Sign in to access your account</p>
        </div>
        <div class="login-body">
            <div class="error-message" id="errorMessage">
                <i class="fas fa-exclamation-circle"></i>
                <span class="message">Invalid email or password. Please try again.</span>
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
                <input type="hidden" name="_vALToken" value="<?php echo $_SESSION["_staffLogToken"]; ?>">
            </form>
        </div>
        <div class="login-footer">
            <p>Having trouble? <a href="mailto:support@rmu.edu">Contact Support</a></p>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
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

            $.ajax({
                type: "POST",
                url: "endpoint/login",
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                success: function(result) {
                    console.log(result);
                    if (result.success) {
                        window.location.href = result.message;
                    } else {
                        errorMessage.querySelector('.message').textContent = result.message || "Invalid email or password. Please try again.";
                        errorMessage.classList.add('show');
                        setTimeout(() => {
                            errorMessage.classList.remove('show');
                        }, 3000);
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
        });

        $(document).on({
            ajaxStart: function() {
                $(".login-btn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
            },
            ajaxStop: function() {
                $(".login-btn").prop("disabled", false).html('Sign In');
            }
        });
    </script>

</body>

</html>