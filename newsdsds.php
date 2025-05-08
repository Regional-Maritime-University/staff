<?php
session_start();
/*if (!isset($_GET['status']) || !isset($_GET['exttrid'])) header('Location: index.php?status=invalid');
if (isset($_GET['status']) && empty($_GET['status'])) header('Location: index.php?status=invalid');
if (isset($_GET['exttrid']) && empty($_GET['exttrid'])) header('Location: index.php?status=invalid');*/
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="Keywords" content="RMU, University, Apply, Forms, School, Institution">
    <meta name="Description" content="The Regional Maritime University (RMU), Accra, Ghana, is an international tertiary institution. The overall objective for the establishment of RMU is to promote regional co-operation in the maritime industry focusing on the training to ensure the sustained growth and development of the industry.">
    <meta property="og:image" content="https://rmu.edu.gh/wp-content/uploads/2019/09/rmulogo-exp-3-400x75.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="400">
    <meta property="og:image:height" content="75">
    <meta property="og:description" content="Regional Maritime University (RMU) offers a comprehensive range of diploma and degree programs in Marine Engineering, Nautical Science, Electrical Engineering, Mechanical Engineering, Computer Science, Computer Engineering, Information Technology, Logistics, Port and Shipping Management, and other short courses. Explore our programs and gain expertise in the maritime industry. Join us and unlock your potential in the exciting world of maritime education.">
    <title>Form Purchase | Confirm Payment</title>
    <style>
        /* Base Styles */
        :root {
            --primary-color: #0a4d8c;
            --secondary-color: #0f75bd;
            --accent-color: #1a97f5;
            --success-color: #28a745;
            --error-color: #dc3545;
            --warning-color: #ffc107;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --gray-color: #6c757d;
            --border-color: #dee2e6;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f7fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header Styles */
        header {
            background-color: white;
            box-shadow: 0 2px 10px var(--shadow-color);
            padding: 1rem 0;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
        }

        .logo img {
            height: 50px;
            width: auto;
        }

        .site-title {
            margin-left: 1rem;
            font-size: 1.5rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Main Content Styles */
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            flex: 1;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px var(--shadow-color);
            overflow: hidden;
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }

        .card-header h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            font-size: 1rem;
            opacity: 0.9;
        }

        .card-body {
            padding: 2rem;
        }

        /* Status Styles */
        #status-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin-bottom: 2rem;
        }

        .status-icon {
            margin-bottom: 1.5rem;
        }

        .status-message {
            margin-top: 1rem;
        }

        #status-text {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .status-details {
            color: var(--gray-color);
            font-size: 0.9rem;
        }

        /* Loading Animation */
        .circle-loader {
            border: 3px solid rgba(0, 0, 0, 0.1);
            border-left-color: var(--primary-color);
            animation: loader-spin 1.2s infinite linear;
            position: relative;
            display: inline-block;
            vertical-align: top;
            border-radius: 50%;
            width: 80px;
            height: 80px;
        }

        .load-complete {
            -webkit-animation: none;
            animation: none;
            border-color: var(--success-color);
            transition: border 500ms ease-out;
        }

        .load-error {
            -webkit-animation: none;
            animation: none;
            border-color: var(--error-color);
            transition: border 500ms ease-out;
        }

        .checkmark {
            display: none;
        }

        .checkmark.draw:after {
            animation-duration: 800ms;
            animation-timing-function: ease;
            animation-name: checkmark;
            transform: scaleX(-1) rotate(135deg);
        }

        .checkmark:after {
            opacity: 1;
            height: 40px;
            width: 20px;
            transform-origin: left top;
            border-right: 5px solid var(--success-color);
            border-top: 5px solid var(--success-color);
            content: '';
            left: 25px;
            top: 40px;
            position: absolute;
        }

        .error-mark {
            display: none;
            position: relative;
            width: 80px;
            height: 80px;
        }

        .error-mark:before,
        .error-mark:after {
            position: absolute;
            content: '';
            background-color: var(--error-color);
            display: block;
            width: 5px;
            height: 50px;
            top: 15px;
            left: 38px;
        }

        .error-mark:before {
            transform: rotate(45deg);
        }

        .error-mark:after {
            transform: rotate(-45deg);
        }

        /* Button Styles */
        #action-container {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-align: center;
        }

        .primary-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .primary-btn:hover {
            background-color: var(--secondary-color);
        }

        .secondary-btn {
            background-color: white;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .secondary-btn:hover {
            background-color: #f0f5ff;
        }

        /* Footer Styles */
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 1.5rem 0;
            margin-top: auto;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
        }

        .footer-links a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
        }

        /* Utility Classes */
        .hidden {
            display: none !important;
        }

        .success-text {
            color: var(--success-color);
        }

        .error-text {
            color: var(--error-color);
        }

        .warning-text {
            color: var(--warning-color);
        }

        /* Animations */
        @keyframes loader-spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes checkmark {
            0% {
                height: 0;
                width: 0;
                opacity: 1;
            }

            20% {
                height: 0;
                width: 20px;
                opacity: 1;
            }

            40% {
                height: 40px;
                width: 20px;
                opacity: 1;
            }

            100% {
                height: 40px;
                width: 20px;
                opacity: 1;
            }
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                text-align: center;
            }

            .site-title {
                margin-left: 0;
                margin-top: 0.5rem;
            }

            .card-header h2 {
                font-size: 1.5rem;
            }

            #action-container {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .footer-container {
                flex-direction: column;
                text-align: center;
            }

            .footer-links {
                margin-top: 1rem;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .card-body {
                padding: 1.5rem;
            }

            .circle-loader {
                width: 60px;
                height: 60px;
            }

            .checkmark:after {
                height: 30px;
                width: 15px;
                left: 20px;
                top: 30px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="https://rmu.edu.gh/wp-content/uploads/2019/09/rmulogo-exp-3-400x75.png" alt="RMU Logo">
            </div>
            <h1 class="site-title">Regional Maritime University</h1>
        </div>
    </header>

    <main class="container">
        <div class="card">
            <div class="card-header">
                <h2>Payment Confirmation</h2>
                <p class="subtitle">We're verifying your payment details</p>
            </div>

            <div class="card-body">
                <div id="status-container">
                    <div class="status-icon loading">
                        <div class="circle-loader">
                            <div class="checkmark draw"></div>
                        </div>
                    </div>
                    <div class="status-message">
                        <p id="status-text">Connecting to payment server...</p>
                        <p id="status-details" class="status-details"></p>
                    </div>
                </div>

                <div id="action-container" class="hidden">
                    <a href="../dashboard/" class="btn primary-btn">Return to Dashboard</a>
                    <a href="../assign_room/" class="btn secondary-btn">Assign Room</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-info">
                <p>&copy; 2024 Regional Maritime University. All Rights Reserved.</p>
            </div>
            <div class="footer-links">
                <a href="https://rmu.edu.gh/contact-us/" target="_blank">Contact Us</a>
                <a href="https://rmu.edu.gh/privacy-policy/" target="_blank">Privacy Policy</a>
                <a href="https://rmu.edu.gh/" target="_blank">Main Website</a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const exttrid = urlParams.get('exttrid');

            // Status elements
            const statusText = document.getElementById('status-text');
            const statusDetails = document.getElementById('status-details');
            const statusContainer = document.getElementById('status-container');
            const actionContainer = document.getElementById('action-container');
            const circleLoader = document.querySelector('.circle-loader');
            const checkmark = document.querySelector('.checkmark');

            // Validate URL parameters
            if (!status || !exttrid) {
                showError('Invalid request parameters', 'Missing required information. Redirecting to homepage...');
                setTimeout(() => {
                    window.location.href = 'index.php?status=invalid';
                }, 3000);
                return;
            }

            // Update status text
            function updateStatus(message, details = '', type = 'info') {
                statusText.textContent = message;
                statusDetails.textContent = details;

                // Reset classes
                statusText.classList.remove('success-text', 'error-text', 'warning-text');

                // Add appropriate class based on type
                if (type === 'success') {
                    statusText.classList.add('success-text');
                } else if (type === 'error') {
                    statusText.classList.add('error-text');
                } else if (type === 'warning') {
                    statusText.classList.add('warning-text');
                }
            }

            // Show success state
            function showSuccess(message, details = '') {
                updateStatus(message, details, 'success');
                circleLoader.classList.add('load-complete');
                checkmark.style.display = 'block';
                actionContainer.classList.remove('hidden');
            }

            // Show error state
            function showError(message, details = '') {
                updateStatus(message, details, 'error');
                circleLoader.classList.add('load-error');

                // Create error mark if it doesn't exist
                if (!document.querySelector('.error-mark')) {
                    const errorMark = document.createElement('div');
                    errorMark.className = 'error-mark';
                    circleLoader.parentNode.appendChild(errorMark);
                }

                circleLoader.style.display = 'none';
                document.querySelector('.error-mark').style.display = 'block';
                actionContainer.classList.remove('hidden');

                // Only show dashboard button for errors
                const roomButton = document.querySelector('.secondary-btn');
                if (roomButton) {
                    roomButton.style.display = 'none';
                }
            }

            // Payment verification process
            function verifyPayment() {
                // Initial status
                updateStatus('Connecting to payment server...');

                // Simulate connection delay (1.5 seconds)
                setTimeout(() => {
                    updateStatus('Initializing payment verification...');

                    // Simulate initialization delay (1.5 seconds)
                    setTimeout(() => {
                        updateStatus('Processing payment details...');

                        // Make AJAX request to verify payment
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', 'confirm-payment.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                        xhr.onload = function() {
                            if (this.status === 200) {
                                try {
                                    const response = JSON.parse(this.responseText);
                                    console.log(response);

                                    if (response.success) {
                                        showSuccess('Payment Successful!', response.message);

                                        // Redirect after 5 seconds
                                        setTimeout(() => {
                                            window.location.href = '../assign_room/';
                                        }, 5000);
                                    } else {
                                        showError('Payment Verification Failed', response.message || 'Please contact support for assistance.');

                                        // Redirect to dashboard after 5 seconds
                                        setTimeout(() => {
                                            window.location.href = '../dashboard/';
                                        }, 5000);
                                    }
                                } catch (e) {
                                    showError('Error Processing Response', 'Invalid server response. Please try again later.');
                                    console.error('Error parsing JSON:', e);
                                }
                            } else {
                                showError('Server Error', 'Failed to connect to the server. Please try again later.');
                            }
                        };

                        xhr.onerror = function() {
                            showError('Connection Error', 'Failed to connect to the server. Please check your internet connection.');
                        };

                        // Send the request with parameters
                        xhr.send(`status=${encodeURIComponent(status)}&exttrid=${encodeURIComponent(exttrid)}`);
                    }, 1500);
                }, 1500);
            }

            // Start the verification process
            verifyPayment();

            // Add event listeners for buttons
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    // Prevent default only if we're in the middle of a redirect countdown
                    const href = this.getAttribute('href');
                    if (href) {
                        // No need to prevent default, let the link work normally
                    } else {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>

</html>