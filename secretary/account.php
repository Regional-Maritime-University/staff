<?php
session_start();

if (!isset($_SESSION["adminLogSuccess"]) || $_SESSION["adminLogSuccess"] == false || !isset($_SESSION["user"]) || empty($_SESSION["user"])) {
    header("Location: ../index.php");
}

$isUser = false;
if (strtolower($_SESSION["role"]) == "admin" || strtolower($_SESSION["role"]) == "developers" || strtolower($_SESSION["role"]) == "secretary") $isUser = true;

if (isset($_GET['logout']) || !$isUser) {
    session_destroy();
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    header('Location: ../login.php');
}

$_SESSION["lastAccessed"] = time();

require_once('../bootstrap.php');

use Src\Controller\SecretaryController;

require_once('../inc/admin-database-con.php');

$admin = new SecretaryController($db, $user, $pass);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Staff Portal - Account</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/account.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar -->
    <?php require_once '../components/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Account Settings</h1>
            </div>
            <div class="header-right">
                <div class="search-bar">
                    <input type="text" placeholder="Search...">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                </div>
                <div class="header-actions">
                    <button class="action-btn notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge">5</span>
                    </button>
                    <button class="action-btn messages">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">3</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="account-container">
            <div class="account-sidebar">
                <div class="account-tabs">
                    <button class="account-tab active" data-tab="profile">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </button>
                    <button class="account-tab" data-tab="security">
                        <i class="fas fa-lock"></i>
                        <span>Security</span>
                    </button>
                    <button class="account-tab" data-tab="preferences">
                        <i class="fas fa-sliders-h"></i>
                        <span>Preferences</span>
                    </button>
                    <button class="account-tab" data-tab="activity">
                        <i class="fas fa-history"></i>
                        <span>Activity Log</span>
                    </button>
                </div>
            </div>

            <div class="account-content">
                <!-- Profile Tab -->
                <div class="account-tab-content active" id="profile">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <img src="/placeholder.svg?height=100&width=100" alt="User Avatar">
                            <button class="change-avatar-btn">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                        <div class="profile-info">
                            <h2>Jane Doe</h2>
                            <p>Department Secretary</p>
                            <p>Maritime Studies Department</p>
                        </div>
                    </div>

                    <div class="profile-form">
                        <div class="form-section">
                            <h3>Personal Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="firstName" value="Jane">
                                </div>
                                <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" id="lastName" value="Doe">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" value="jane.doe@rmu.edu">
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" value="+233 20 123 4567">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" rows="3">123 University Avenue, Accra, Ghana</textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Work Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="department">Department</label>
                                    <select id="department">
                                        <option value="1" selected>Maritime Studies</option>
                                        <option value="2">Marine Engineering</option>
                                        <option value="3">Nautical Science</option>
                                        <option value="4">Logistics and Transport</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="position">Position</label>
                                    <input type="text" id="position" value="Department Secretary">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="employeeId">Employee ID</label>
                                    <input type="text" id="employeeId" value="RMU-SEC-2023-001" disabled>
                                </div>
                                <div class="form-group">
                                    <label for="joinDate">Join Date</label>
                                    <input type="date" id="joinDate" value="2023-01-15" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button class="cancel-btn">Cancel</button>
                            <button class="submit-btn" id="saveProfileBtn">Save Changes</button>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div class="account-tab-content" id="security">
                    <div class="form-section">
                        <h3>Change Password</h3>
                        <div class="form-group">
                            <label for="currentPassword">Current Password</label>
                            <input type="password" id="currentPassword">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="newPassword">New Password</label>
                                <input type="password" id="newPassword">
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm New Password</label>
                                <input type="password" id="confirmPassword">
                            </div>
                        </div>
                        <div class="password-strength">
                            <div class="strength-meter">
                                <div class="strength-bar" style="width: 0%;"></div>
                            </div>
                            <span class="strength-text">Password strength: Not set</span>
                        </div>
                        <div class="password-requirements">
                            <p>Password must:</p>
                            <ul>
                                <li>Be at least 8 characters long</li>
                                <li>Include at least one uppercase letter</li>
                                <li>Include at least one lowercase letter</li>
                                <li>Include at least one number</li>
                                <li>Include at least one special character</li>
                            </ul>
                        </div>
                        <div class="form-actions">
                            <button class="submit-btn" id="changePasswordBtn">Change Password</button>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Two-Factor Authentication</h3>
                        <div class="toggle-switch-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="twoFactorToggle">
                                <span class="toggle-slider"></span>
                            </label>
                            <span>Enable Two-Factor Authentication</span>
                        </div>
                        <p class="helper-text">Two-factor authentication adds an extra layer of security to your account by requiring a verification code in addition to your password.</p>
                        <div class="two-factor-setup" style="display: none;">
                            <button class="setup-btn" id="setupTwoFactorBtn">
                                <i class="fas fa-qrcode"></i> Setup Two-Factor Authentication
                            </button>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Login Sessions</h3>
                        <div class="sessions-list">
                            <div class="session-item current">
                                <div class="session-icon">
                                    <i class="fas fa-desktop"></i>
                                </div>
                                <div class="session-details">
                                    <h4>Chrome on Windows</h4>
                                    <p>Accra, Ghana • Current Session</p>
                                    <span class="session-time">Active now</span>
                                </div>
                            </div>
                            <div class="session-item">
                                <div class="session-icon">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <div class="session-details">
                                    <h4>Safari on iPhone</h4>
                                    <p>Accra, Ghana</p>
                                    <span class="session-time">Last active: 2 days ago</span>
                                </div>
                                <button class="end-session-btn">End Session</button>
                            </div>
                        </div>
                        <button class="danger-btn" id="endAllSessionsBtn">
                            <i class="fas fa-sign-out-alt"></i> End All Other Sessions
                        </button>
                    </div>
                </div>

                <!-- Preferences Tab -->
                <div class="account-tab-content" id="preferences">
                    <div class="form-section">
                        <h3>Display Settings</h3>
                        <div class="form-group">
                            <label>Theme</label>
                            <div class="theme-options">
                                <div class="theme-option active">
                                    <div class="theme-preview light-theme"></div>
                                    <span>Light</span>
                                </div>
                                <div class="theme-option">
                                    <div class="theme-preview dark-theme"></div>
                                    <span>Dark</span>
                                </div>
                                <div class="theme-option">
                                    <div class="theme-preview system-theme"></div>
                                    <span>System</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="fontSize">Font Size</label>
                            <select id="fontSize">
                                <option value="small">Small</option>
                                <option value="medium" selected>Medium</option>
                                <option value="large">Large</option>
                            </select>
                        </div>
                        <div class="toggle-switch-container">
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                            <span>Enable animations</span>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Language & Region</h3>
                        <div class="form-group">
                            <label for="language">Language</label>
                            <select id="language">
                                <option value="en" selected>English</option>
                                <option value="fr">French</option>
                                <option value="es">Spanish</option>
                                <option value="ar">Arabic</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="timeZone">Time Zone</label>
                            <select id="timeZone">
                                <option value="GMT" selected>GMT (Greenwich Mean Time)</option>
                                <option value="EST">EST (Eastern Standard Time)</option>
                                <option value="CST">CST (Central Standard Time)</option>
                                <option value="PST">PST (Pacific Standard Time)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="dateFormat">Date Format</label>
                            <select id="dateFormat">
                                <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                                <option value="DD/MM/YYYY" selected>DD/MM/YYYY</option>
                                <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="cancel-btn">Reset to Defaults</button>
                        <button class="submit-btn" id="savePreferencesBtn">Save Preferences</button>
                    </div>
                </div>

                <!-- Activity Log Tab -->
                <div class="account-tab-content" id="activity">
                    <div class="activity-filters">
                        <div class="form-group">
                            <label for="activityType">Filter by Type</label>
                            <select id="activityType">
                                <option value="all">All Activities</option>
                                <option value="login">Login</option>
                                <option value="profile">Profile Updates</option>
                                <option value="course">Course Management</option>
                                <option value="result">Results Management</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="activityDate">Filter by Date</label>
                            <select id="activityDate">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                    </div>

                    <div class="activity-timeline">
                        <div class="timeline-date">Today</div>
                        <div class="timeline-item">
                            <div class="timeline-icon bg-primary">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Login</h4>
                                <p>You logged in from Chrome on Windows</p>
                                <span class="timeline-time">10:30 AM</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon bg-accent">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Course Assignment</h4>
                                <p>You assigned Navigation Systems (NS302) to Prof. Sarah Johnson</p>
                                <span class="timeline-time">11:45 AM</span>
                            </div>
                        </div>

                        <div class="timeline-date">Yesterday</div>
                        <div class="timeline-item">
                            <div class="timeline-icon bg-success">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Deadline Set</h4>
                                <p>You set a deadline for Marine Engineering (ME101) results</p>
                                <span class="timeline-time">3:15 PM</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon bg-warning">
                                <i class="fas fa-upload"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Courses Uploaded</h4>
                                <p>You uploaded 15 new courses for the Fall 2023 semester</p>
                                <span class="timeline-time">10:20 AM</span>
                            </div>
                        </div>

                        <div class="timeline-date">Last Week</div>
                        <div class="timeline-item">
                            <div class="timeline-icon bg-primary">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Profile Updated</h4>
                                <p>You updated your profile information</p>
                                <span class="timeline-time">Monday, 2:30 PM</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon bg-danger">
                                <i class="fas fa-lock"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Password Changed</h4>
                                <p>You changed your account password</p>
                                <span class="timeline-time">Monday, 11:05 AM</span>
                            </div>
                        </div>
                    </div>

                    <div class="load-more">
                        <button class="load-more-btn">
                            <i class="fas fa-sync"></i> Load More
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Two-Factor Authentication Modal -->
    <div class="modal" id="twoFactorModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Setup Two-Factor Authentication</h2>
                    <button class="close-btn" id="closeTwoFactorModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="two-factor-steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h3>Download an authenticator app</h3>
                                <p>Download and install an authenticator app like Google Authenticator or Authy on your mobile device.</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h3>Scan the QR code</h3>
                                <p>Open your authenticator app and scan the QR code below.</p>
                                <div class="qr-code">
                                    <img src="/placeholder.svg?height=200&width=200" alt="QR Code">
                                </div>
                                <div class="manual-key">
                                    <p>Or enter this key manually:</p>
                                    <div class="key-code">ABCD-EFGH-IJKL-MNOP</div>
                                </div>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h3>Enter verification code</h3>
                                <p>Enter the 6-digit verification code from your authenticator app.</p>
                                <div class="verification-code-input">
                                    <input type="text" maxlength="6" placeholder="000000">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" id="cancelTwoFactor">Cancel</button>
                    <button class="submit-btn" id="verifyTwoFactor">Verify & Enable</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/account.js"></script>
</body>

</html>