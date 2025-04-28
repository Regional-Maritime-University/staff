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
    <title>RMU Staff Portal - Messages</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/messages.css">
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
                <h1>Messages</h1>
            </div>
            <div class="header-right">
                <div class="search-bar">
                    <input type="text" placeholder="Search messages...">
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

        <div class="messages-container">
            <div class="contacts-sidebar">
                <div class="contacts-header">
                    <h2>Conversations</h2>
                    <button class="new-message-btn" id="newMessageBtn">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="contacts-filter">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="unread">Unread</button>
                    <button class="filter-btn" data-filter="lecturers">Lecturers</button>
                    <button class="filter-btn" data-filter="hods">HODs</button>
                </div>
                <div class="contacts-list">
                    <div class="contact-item active" data-id="1">
                        <div class="contact-avatar">
                            <img src="../assets/img/icons8-user-96.png" alt="Dr. James Wilson">
                            <span class="status-indicator online"></span>
                        </div>
                        <div class="contact-info">
                            <div class="contact-name">Dr. James Wilson</div>
                            <div class="contact-preview">About the Maritime Law course...</div>
                        </div>
                        <div class="contact-meta">
                            <div class="message-time">10:30 AM</div>
                            <div class="unread-badge">2</div>
                        </div>
                    </div>
                    <div class="contact-item" data-id="2">
                        <div class="contact-avatar">
                            <img src="../assets/img/icons8-user-96.png" alt="Prof. Sarah Johnson">
                            <span class="status-indicator offline"></span>
                        </div>
                        <div class="contact-info">
                            <div class="contact-name">Prof. Sarah Johnson</div>
                            <div class="contact-preview">Thank you for the update on...</div>
                        </div>
                        <div class="contact-meta">
                            <div class="message-time">Yesterday</div>
                        </div>
                    </div>
                    <div class="contact-item" data-id="3">
                        <div class="contact-avatar">
                            <img src="../assets/img/icons8-user-96.png" alt="Dr. Michael Brown">
                            <span class="status-indicator away"></span>
                        </div>
                        <div class="contact-info">
                            <div class="contact-name">Dr. Michael Brown</div>
                            <div class="contact-preview">I'll submit the results by...</div>
                        </div>
                        <div class="contact-meta">
                            <div class="message-time">Yesterday</div>
                            <div class="unread-badge">1</div>
                        </div>
                    </div>
                    <div class="contact-item" data-id="4">
                        <div class="contact-avatar">
                            <img src="../assets/img/icons8-user-96.png" alt="Prof. Robert Taylor">
                            <span class="status-indicator online"></span>
                        </div>
                        <div class="contact-info">
                            <div class="contact-name">Prof. Robert Taylor</div>
                            <div class="contact-preview">Can we discuss the deadline...</div>
                        </div>
                        <div class="contact-meta">
                            <div class="message-time">2 days ago</div>
                        </div>
                    </div>
                    <div class="contact-item" data-id="5">
                        <div class="contact-avatar">
                            <img src="../assets/img/icons8-user-96.png" alt="Dr. Emily Davis">
                            <span class="status-indicator offline"></span>
                        </div>
                        <div class="contact-info">
                            <div class="contact-name">Dr. Emily Davis</div>
                            <div class="contact-preview">The course materials are...</div>
                        </div>
                        <div class="contact-meta">
                            <div class="message-time">3 days ago</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chat-area">
                <div class="chat-header">
                    <div class="chat-contact">
                        <div class="contact-avatar">
                            <img src="../assets/img/icons8-user-96.png" alt="Dr. James Wilson">
                            <span class="status-indicator online"></span>
                        </div>
                        <div class="contact-info">
                            <div class="contact-name">Dr. James Wilson</div>
                            <div class="contact-status">Online</div>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <button class="chat-action-btn">
                            <i class="fas fa-phone"></i>
                        </button>
                        <button class="chat-action-btn">
                            <i class="fas fa-video"></i>
                        </button>
                        <button class="chat-action-btn">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    </div>
                </div>

                <div class="chat-messages">
                    <div class="message-date">Today</div>

                    <div class="message received">
                        <div class="message-avatar">
                            <img src="../assets/img/icons8-user-96.png" alt="Dr. James Wilson">
                        </div>
                        <div class="message-content">
                            <div class="message-bubble">
                                Good morning, I wanted to discuss the Maritime Law course assignment for this semester.
                            </div>
                            <div class="message-time">10:15 AM</div>
                        </div>
                    </div>

                    <div class="message received">
                        <div class="message-avatar">
                            <img src="../assets/img/icons8-user-96.png" alt="Dr. James Wilson">
                        </div>
                        <div class="message-content">
                            <div class="message-bubble">
                                I've prepared all the materials and would like to know when the deadline for submission is.
                            </div>
                            <div class="message-time">10:16 AM</div>
                        </div>
                    </div>

                    <div class="message sent">
                        <div class="message-content">
                            <div class="message-bubble">
                                Good morning Dr. Wilson, the deadline for the Maritime Law course is April 20th.
                            </div>
                            <div class="message-time">10:20 AM</div>
                        </div>
                    </div>

                    <div class="message received">
                        <div class="message-avatar">
                            <img src="../assets/img/icons8-user-96.png" alt="Dr. James Wilson">
                        </div>
                        <div class="message-content">
                            <div class="message-bubble">
                                Thank you for the information. I'll make sure to submit everything before the deadline.
                            </div>
                            <div class="message-time">10:25 AM</div>
                        </div>
                    </div>

                    <div class="message received">
                        <div class="message-avatar">
                            <img src="../assets/img/icons8-user-96.png" alt="Dr. James Wilson">
                        </div>
                        <div class="message-content">
                            <div class="message-bubble">
                                Also, I wanted to ask if there are any specific requirements for the submission format?
                            </div>
                            <div class="message-time">10:30 AM</div>
                        </div>
                    </div>
                </div>

                <div class="chat-input-area">
                    <button class="attachment-btn">
                        <i class="fas fa-paperclip"></i>
                    </button>
                    <input type="text" class="chat-input" placeholder="Type a message...">
                    <button class="emoji-btn">
                        <i class="fas fa-smile"></i>
                    </button>
                    <button class="send-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Message Modal -->
    <div class="modal" id="newMessageModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>New Message</h2>
                    <button class="close-btn" id="closeNewMessageModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipientSelect">To:</label>
                        <select id="recipientSelect" required>
                            <option value="">-- Select Recipient --</option>
                            <optgroup label="Lecturers">
                                <option value="1">Dr. James Wilson</option>
                                <option value="2">Prof. Sarah Johnson</option>
                                <option value="3">Dr. Michael Brown</option>
                                <option value="4">Prof. Robert Taylor</option>
                                <option value="5">Dr. Emily Davis</option>
                            </optgroup>
                            <optgroup label="HODs">
                                <option value="6">Prof. John Smith - Maritime Studies</option>
                                <option value="7">Dr. Lisa Anderson - Marine Engineering</option>
                                <option value="8">Prof. David Clark - Nautical Science</option>
                            </optgroup>
                            <optgroup label="Administration">
                                <option value="9">Mr. Richard Thomas - Registrar</option>
                                <option value="10">Mrs. Patricia White - Academic Affairs</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="messageSubject">Subject:</label>
                        <input type="text" id="messageSubject" placeholder="Enter subject">
                    </div>
                    <div class="form-group">
                        <label for="messageContent">Message:</label>
                        <textarea id="messageContent" rows="5" placeholder="Type your message here..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="messageAttachment">Attachment (optional):</label>
                        <input type="file" id="messageAttachment" class="file-input">
                        <label for="messageAttachment" class="file-label">Choose File</label>
                        <span class="selected-file-name" id="selectedFileName">No file selected</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" id="cancelNewMessage">Cancel</button>
                    <button class="submit-btn" id="sendNewMessage">Send Message</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/messages.js"></script>
</body>

</html>