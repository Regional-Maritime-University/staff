<?php
session_name("rmu_staff_portal");
session_start();

if (!isset($_SESSION["staffLoginSuccess"]) || $_SESSION["staffLoginSuccess"] == false || !isset($_SESSION["staff"]["number"]) || empty($_SESSION["staff"]["number"])) {
    header("Location: ../index.php");
}

$isUser = false;
if (strtolower($_SESSION["staff"]["role"]) == "admin" || strtolower($_SESSION["staff"]["role"]) == "developers" || strtolower($_SESSION["staff"]["role"]) == "secretary") $isUser = true;

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

    header('Location: ../index.php');
}

$_SESSION["lastAccessed"] = time();

require_once('../bootstrap.php');

use Src\Controller\SecretaryController;

require_once('../inc/admin-database-con.php');

$admin = new SecretaryController($db, $user, $pass);

$pageTitle = "Lecturer Courses";
$activePage = "lecturer-courses";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Staff Portal - Lecturer Courses</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./css/lecturer-courses.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar -->
    <?php require_once '../components/sidebar.php'; ?>

    <div class="main-content">

        <?php require_once '../components/header.php'; ?>

        <div class="dashboard-content">
            <!-- Lecturer Header -->
            <div class="lecturer-header">
                <div class="lecturer-avatar">
                    <img src="lecturer1.jpg" alt="Dr. John Doe">
                </div>
                <div class="lecturer-info">
                    <h2 class="lecturer-name">Dr. John Doe</h2>
                    <div class="lecturer-title">Associate Professor, Marine Engineering</div>
                    <div class="lecturer-meta">
                        <span><i class="fas fa-envelope"></i> john.doe@rmu.edu</span>
                        <span><i class="fas fa-phone"></i> +233 55 123 4567</span>
                        <span><i class="fas fa-book"></i> 3 Current Courses</span>
                    </div>
                </div>
                <div class="lecturer-actions">
                    <button class="section-btn primary" id="assignCourseBtn">
                        <i class="fas fa-plus"></i> Assign Course
                    </button>
                    <button class="section-btn secondary" id="viewProfileBtn">
                        <i class="fas fa-user"></i> View Profile
                    </button>
                </div>
            </div>

            <!-- Semester Selector -->
            <div class="semester-selector">
                <h3>Select Semester</h3>
                <div class="semester-options">
                    <div class="semester-option active" data-semester="current">First Semester 2023/2024</div>
                    <div class="semester-option" data-semester="previous">Second Semester 2022/2023</div>
                    <div class="semester-option" data-semester="upcoming">Second Semester 2023/2024</div>
                    <div class="semester-option" data-semester="all">All Semesters</div>
                </div>
            </div>

            <!-- Course Statistics -->
            <div class="course-stats">
                <div class="stat-card">
                    <div class="stat-icon courses">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-value">3</div>
                    <div class="stat-label">Current Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon students">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-value">105</div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon hours">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">12</div>
                    <div class="stat-label">Teaching Hours/Week</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon credits">
                        <i class="fas fa-award"></i>
                    </div>
                    <div class="stat-value">10</div>
                    <div class="stat-label">Credit Hours</div>
                </div>
            </div>

            <!-- Current Courses Section -->
            <div class="course-section" id="currentCourses">
                <div class="section-header">
                    <h3 class="section-title">Current Courses (First Semester 2023/2024)</h3>
                    <div class="section-actions">
                        <button class="section-btn secondary" id="exportCurrentBtn">
                            <i class="fas fa-file-export"></i> Export
                        </button>
                        <button class="section-btn secondary" id="printCurrentBtn">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="course-table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Title</th>
                                <th>Level</th>
                                <th>Credits</th>
                                <th>Students</th>
                                <th>Schedule</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="course-code">ME101</td>
                                <td>Introduction to Marine Engineering</td>
                                <td>100</td>
                                <td>3</td>
                                <td>45</td>
                                <td>Mon, Wed 9:00-10:30 AM</td>
                                <td><span class="course-status active">Active</span></td>
                                <td>
                                    <div class="course-actions">
                                        <button class="course-action view" title="View Course">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="course-action edit" title="Edit Assignment">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="course-action remove" title="Remove Assignment">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="course-code">ME302</td>
                                <td>Marine Propulsion Systems</td>
                                <td>300</td>
                                <td>4</td>
                                <td>32</td>
                                <td>Tue, Thu 1:00-3:00 PM</td>
                                <td><span class="course-status active">Active</span></td>
                                <td>
                                    <div class="course-actions">
                                        <button class="course-action view" title="View Course">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="course-action edit" title="Edit Assignment">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="course-action remove" title="Remove Assignment">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="course-code">ME405</td>
                                <td>Ship Design and Construction</td>
                                <td>400</td>
                                <td>3</td>
                                <td>28</td>
                                <td>Fri 9:00-12:00 PM</td>
                                <td><span class="course-status active">Active</span></td>
                                <td>
                                    <div class="course-actions">
                                        <button class="course-action view" title="View Course">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="course-action edit" title="Edit Assignment">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="course-action remove" title="Remove Assignment">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Previous Courses Section -->
            <div class="course-section" id="previousCourses">
                <div class="section-header">
                    <h3 class="section-title">Previous Courses</h3>
                    <div class="section-actions">
                        <button class="section-btn secondary" id="exportPreviousBtn">
                            <i class="fas fa-file-export"></i> Export
                        </button>
                    </div>
                </div>
                <div class="course-grid">
                    <div class="course-card">
                        <div class="course-header">
                            <div class="course-title">Fluid Mechanics for Marine Engineers</div>
                            <div class="course-code">ME203</div>
                        </div>
                        <div class="course-details">
                            <div class="course-detail">
                                <span class="course-detail-label">Level:</span>
                                <span class="course-detail-value">200</span>
                            </div>
                            <div class="course-detail">
                                <span class="course-detail-label">Credits:</span>
                                <span class="course-detail-value">3</span>
                            </div>
                            <div class="course-detail">
                                <span class="course-detail-label">Students:</span>
                                <span class="course-detail-value">38</span>
                            </div>
                            <div class="course-detail">
                                <span class="course-detail-label">Semester:</span>
                                <span class="course-detail-value">2022/2023 Second</span>
                            </div>
                        </div>
                        <div class="course-footer">
                            <span class="course-status completed">Completed</span>
                            <div class="card-actions">
                                <button class="course-action view" title="View Course">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="course-card">
                        <div class="course-header">
                            <div class="course-title">Marine Engineering Systems</div>
                            <div class="course-code">ME301</div>
                        </div>
                        <div class="course-details">
                            <div class="course-detail">
                                <span class="course-detail-label">Level:</span>
                                <span class="course-detail-value">300</span>
                            </div>
                            <div class="course-detail">
                                <span class="course-detail-label">Credits:</span>
                                <span class="course-detail-value">4</span>
                            </div>
                            <div class="course-detail">
                                <span class="course-detail-label">Students:</span>
                                <span class="course-detail-value">35</span>
                            </div>
                            <div class="course-detail">
                                <span class="course-detail-label">Semester:</span>
                                <span class="course-detail-value">2022/2023 First</span>
                            </div>
                        </div>
                        <div class="course-footer">
                            <span class="course-status completed">Completed</span>
                            <div class="card-actions">
                                <button class="course-action view" title="View Course">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Courses Section -->
            <div class="course-section" id="upcomingCourses">
                <div class="section-header">
                    <h3 class="section-title">Upcoming Courses</h3>
                    <div class="section-actions">
                        <button class="section-btn primary" id="assignUpcomingBtn">
                            <i class="fas fa-plus"></i> Assign Course
                        </button>
                    </div>
                </div>
                <div class="course-grid">
                    <div class="course-card">
                        <div class="course-header">
                            <div class="course-title">Advanced Marine Engineering</div>
                            <div class="course-code">ME501</div>
                        </div>
                        <div class="course-details">
                            <div class="course-detail">
                                <span class="course-detail-label">Level:</span>
                                <span class="course-detail-value">500</span>
                            </div>
                            <div class="course-detail">
                                <span class="course-detail-label">Credits:</span>
                                <span class="course-detail-value">4</span>
                            </div>
                            <div class="course-detail">
                                <span class="course-detail-label">Students:</span>
                                <span class="course-detail-value">TBD</span>
                            </div>
                            <div class="course-detail">
                                <span class="course-detail-label">Semester:</span>
                                <span class="course-detail-value">2023/2024 Second</span>
                            </div>
                        </div>
                        <div class="course-footer">
                            <span class="course-status upcoming">Upcoming</span>
                            <div class="card-actions">
                                <button class="course-action edit" title="Edit Assignment">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="course-action remove" title="Remove Assignment">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Course Modal -->
    <div class="modal" id="assignCourseModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Assign Course to Dr. John Doe</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="courseSemester">Semester</label>
                        <select id="courseSemester" required>
                            <option value="1" selected>First Semester 2023/2024</option>
                            <option value="2">Second Semester 2023/2024</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="courseSelect">Select Course</label>
                        <select id="courseSelect" required>
                            <option value="">Select a course</option>
                            <option value="CS101">CS101 - Introduction to Computer Science</option>
                            <option value="ME201">ME201 - Thermodynamics</option>
                            <option value="ME401">ME401 - Advanced Ship Design</option>
                            <option value="NS202">NS202 - Maritime Safety</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="courseSchedule">Schedule</label>
                        <input type="text" id="courseSchedule" placeholder="e.g., Mon, Wed 10:00-11:30 AM" required>
                    </div>
                    <div class="form-group">
                        <label for="courseRoom">Room/Venue</label>
                        <input type="text" id="courseRoom" placeholder="e.g., Engineering Block, Room 205" required>
                    </div>
                    <div class="form-group">
                        <label for="courseNotes">Additional Notes</label>
                        <textarea id="courseNotes" rows="3" placeholder="Any special instructions or notes"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="notifyLecturer">
                            <input type="checkbox" id="notifyLecturer" checked>
                            Notify lecturer about course assignment
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="saveAssignmentBtn">Save Assignment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        // Semester selector functionality
        document.querySelectorAll('.semester-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                document.querySelectorAll('.semester-option').forEach(opt => {
                    opt.classList.remove('active');
                });

                // Add active class to clicked option
                this.classList.add('active');

                // Get selected semester
                const semester = this.getAttribute('data-semester');

                // Update section title based on selected semester
                if (semester === 'current') {
                    document.querySelector('#currentCourses .section-title').textContent = 'Current Courses (First Semester 2023/2024)';
                } else if (semester === 'previous') {
                    document.querySelector('#currentCourses .section-title').textContent = 'Current Courses (Second Semester 2022/2023)';
                } else if (semester === 'upcoming') {
                    document.querySelector('#currentCourses .section-title').textContent = 'Current Courses (Second Semester 2023/2024)';
                } else {
                    document.querySelector('#currentCourses .section-title').textContent = 'All Courses';
                }

                // In a real application, you would fetch and display courses for the selected semester
                alert(`Selected semester: ${semester}. In a real application, this would load courses for the selected semester.`);
            });
        });

        // Modal functionality
        const assignCourseModal = document.getElementById('assignCourseModal');

        // Open modal
        document.getElementById('assignCourseBtn').addEventListener('click', function() {
            assignCourseModal.classList.add('active');
        });

        document.getElementById('assignUpcomingBtn').addEventListener('click', function() {
            assignCourseModal.classList.add('active');
        });

        // Close modal
        document.querySelectorAll('.close-btn, .cancel-btn').forEach(button => {
            button.addEventListener('click', function() {
                assignCourseModal.classList.remove('active');
            });
        });

        // Save assignment
        document.getElementById('saveAssignmentBtn').addEventListener('click', function() {
            const semester = document.getElementById('courseSemester').value;
            const course = document.getElementById('courseSelect').value;
            const schedule = document.getElementById('courseSchedule').value;
            const room = document.getElementById('courseRoom').value;

            if (!course || !schedule || !room) {
                alert('Please fill all required fields.');
                return;
            }

            // In a real application, you would save the assignment to the database
            alert('Course assigned successfully!');
            assignCourseModal.classList.remove('active');
        });

        // View profile button
        document.getElementById('viewProfileBtn').addEventListener('click', function() {
            window.location.href = 'lecturer-profile.php';
        });

        // Export functionality (mock)
        document.getElementById('exportCurrentBtn').addEventListener('click', function() {
            alert('Exporting current courses data...');
        });

        document.getElementById('exportPreviousBtn').addEventListener('click', function() {
            alert('Exporting previous courses data...');
        });

        // Print functionality
        document.getElementById('printCurrentBtn').addEventListener('click', function() {
            window.print();
        });

        // Course actions
        document.querySelectorAll('.course-action').forEach(action => {
            action.addEventListener('click', function() {
                const actionType = this.classList.contains('view') ? 'View' :
                    this.classList.contains('edit') ? 'Edit' : 'Remove';

                const courseRow = this.closest('tr');
                const courseCard = this.closest('.course-card');

                let courseCode, courseTitle;

                if (courseRow) {
                    courseCode = courseRow.querySelector('.course-code').textContent;
                    courseTitle = courseRow.cells[1].textContent;
                } else if (courseCard) {
                    courseCode = courseCard.querySelector('.course-code').textContent;
                    courseTitle = courseCard.querySelector('.course-title').textContent;
                }

                if (actionType === 'Remove') {
                    if (confirm(`Are you sure you want to remove ${courseCode} - ${courseTitle} from Dr. John Doe's assignments?`)) {
                        // In a real application, you would remove the assignment from the database
                        if (courseRow) courseRow.remove();
                        if (courseCard) courseCard.remove();
                        alert(`${courseCode} has been removed from Dr. John Doe's assignments.`);
                    }
                } else {
                    alert(`${actionType} ${courseCode} - ${courseTitle}`);
                }
            });
        });
    </script>
</body>

</html>