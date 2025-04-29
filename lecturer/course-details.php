<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Lecturer Portal - Course Details</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./css/course-details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="RMU Logo" class="logo-img">
            <h2>RMU Portal</h2>
        </div>
        <div class="user-profile">
            <div class="avatar">
                <img src="avatar.jpg" alt="User Avatar">
            </div>
            <div class="user-info">
                <h3>Dr. John Doe</h3>
                <p>Lecturer</p>
            </div>
        </div>
        <div class="menu-groups">
            <div class="menu-group">
                <h3>Main Menu</h3>
                <div class="menu-items">
                    <a href="dashboard.html" class="menu-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="courses.html" class="menu-item active">
                        <i class="fas fa-book"></i>
                        <span>My Courses</span>
                    </a>
                    <a href="results.html" class="menu-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Exam Results</span>
                    </a>
                    <a href="students.html" class="menu-item">
                        <i class="fas fa-user-graduate"></i>
                        <span>Students</span>
                    </a>
                </div>
            </div>
            <div class="menu-group">
                <h3>Communication</h3>
                <div class="menu-items">
                    <a href="messages.html" class="menu-item">
                        <i class="fas fa-envelope"></i>
                        <span>Messages</span>
                        <span class="badge">3</span>
                    </a>
                    <a href="notifications.html" class="menu-item">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                        <span class="badge">7</span>
                    </a>
                </div>
            </div>
            <div class="menu-group">
                <h3>Settings</h3>
                <div class="menu-items">
                    <a href="profile.html" class="menu-item">
                        <i class="fas fa-user-cog"></i>
                        <span>Profile</span>
                    </a>
                    <a href="change-password.html" class="menu-item">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                    </a>
                    <a href="login.html" class="menu-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <button class="toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Course Details</h1>
            </div>
            <div class="header-right">
                <div class="header-actions">
                    <button class="action-btn">
                        <i class="fas fa-bell"></i>
                        <span class="badge">7</span>
                    </button>
                    <button class="action-btn">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">3</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="course-details-content">
            <!-- Course Header Card -->
            <div class="course-header-card">
                <div class="course-header-info">
                    <div class="course-title-section">
                        <h2 class="course-title">Introduction to Marine Engineering</h2>
                        <div class="course-code">ME101</div>
                    </div>
                    <div class="course-meta">
                        <div class="meta-item">
                            <i class="fas fa-building"></i>
                            <span>Marine Engineering Department</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-layer-group"></i>
                            <span>Level 100</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-user-graduate"></i>
                            <span>45 Students</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span>Mon, Wed 9:00-10:30 AM</span>
                        </div>
                    </div>
                    <span class="course-status active">Active</span>
                </div>
                <div class="course-header-actions">
                    <!-- <button class="course-btn primary" id="uploadResourceBtn">
                        <i class="fas fa-upload"></i> Upload Resource
                    </button>
                    <button class="course-btn secondary" id="editCourseBtn">
                        <i class="fas fa-edit"></i> Edit Course
                    </button> -->
                    <button class="course-btn primary" id="emailStudentsBtn">
                        <i class="fas fa-envelope"></i> Email Students
                    </button>
                </div>
            </div>

            <!-- Course Tabs -->
            <div class="course-tabs">
                <button class="course-tab active" data-tab="overview">Overview</button>
                <button class="course-tab" data-tab="students">Students</button>
                <button class="course-tab" data-tab="resources">Resources</button>
                <button class="course-tab" data-tab="schedule">Schedule</button>
                <button class="course-tab" data-tab="results">Results</button>
            </div>

            <!-- Course Content -->
            <div class="course-content">
                <!-- Overview Tab -->
                <div class="tab-pane active" id="overview">
                    <h3 class="section-title">Course Description</h3>
                    <div class="course-description">
                        <p>This course provides an introduction to the principles of marine engineering, covering the basic concepts of ship propulsion, power generation, and auxiliary systems. Students will learn about the different types of marine engines, their operation, and maintenance requirements. The course also covers the fundamentals of naval architecture and ship design.</p>
                        <p>Through a combination of lectures, practical demonstrations, and hands-on exercises, students will develop a solid foundation in marine engineering principles that will prepare them for more advanced courses in the field.</p>
                    </div>

                    <h3 class="section-title">Course Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Course Code</div>
                            <div class="info-value">ME101</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Credits</div>
                            <div class="info-value">3</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Level</div>
                            <div class="info-value">100</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Semester</div>
                            <div class="info-value">First Semester 2023/2024</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Schedule</div>
                            <div class="info-value">Mon, Wed 9:00-10:30 AM</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Location</div>
                            <div class="info-value">Engineering Block, Room 101</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Prerequisites</div>
                            <div class="info-value">None</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Students Enrolled</div>
                            <div class="info-value">45</div>
                        </div>
                    </div>

                    <h3 class="section-title">Course Objectives</h3>
                    <div class="course-objectives">
                        <ul class="objectives-list">
                            <li>Understand the basic principles of marine engineering and naval architecture</li>
                            <li>Identify and explain the function of major components in marine propulsion systems</li>
                            <li>Analyze the performance characteristics of different types of marine engines</li>
                            <li>Understand the fundamentals of ship stability and buoyancy</li>
                            <li>Develop basic skills in reading and interpreting marine engineering drawings</li>
                            <li>Recognize the environmental considerations in modern marine engineering</li>
                        </ul>
                    </div>
                </div>

                <!-- Students Tab -->
                <div class="tab-pane" id="students">
                    <div class="students-actions">
                        <div class="students-filters">
                            <select class="filter-select" id="programFilter">
                                <option value="all">All Programs</option>
                                <option value="bsc">BSc Marine Engineering</option>
                                <option value="btec">BTech Naval Architecture</option>
                                <option value="diploma">Diploma in Maritime Studies</option>
                            </select>
                            <select class="filter-select" id="sortFilter">
                                <option value="name">Sort by Name</option>
                                <option value="id">Sort by ID</option>
                                <option value="program">Sort by Program</option>
                            </select>
                        </div>
                        <button class="course-btn primary" id="downloadStudentListBtn">
                            <i class="fas fa-download"></i> Download List
                        </button>
                    </div>
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Program</th>
                                <th>Email</th>
                                <th>Attendance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>RMU/2023/001</td>
                                <td class="student-name">
                                    <div class="student-avatar">
                                        <img src="student1.jpg" alt="Student">
                                    </div>
                                    <span>John Smith</span>
                                </td>
                                <td>BSc Marine Engineering</td>
                                <td>john.smith@rmu.edu</td>
                                <td>85%</td>
                                <td>
                                    <div class="student-actions">
                                        <button class="student-action view" title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="student-action message" title="Send Message">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <button class="student-action grade" title="Enter Grades">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>RMU/2023/002</td>
                                <td class="student-name">
                                    <div class="student-avatar">
                                        <img src="student2.jpg" alt="Student">
                                    </div>
                                    <span>Sarah Johnson</span>
                                </td>
                                <td>BSc Marine Engineering</td>
                                <td>sarah.johnson@rmu.edu</td>
                                <td>92%</td>
                                <td>
                                    <div class="student-actions">
                                        <button class="student-action view" title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="student-action message" title="Send Message">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <button class="student-action grade" title="Enter Grades">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>RMU/2023/003</td>
                                <td class="student-name">
                                    <div class="student-avatar">
                                        <img src="student3.jpg" alt="Student">
                                    </div>
                                    <span>Michael Brown</span>
                                </td>
                                <td>BTech Naval Architecture</td>
                                <td>michael.brown@rmu.edu</td>
                                <td>78%</td>
                                <td>
                                    <div class="student-actions">
                                        <button class="student-action view" title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="student-action message" title="Send Message">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <button class="student-action grade" title="Enter Grades">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Resources Tab -->
                <div class="tab-pane" id="resources">
                    <div class="resources-actions">
                        <h3 class="section-title">Course Resources</h3>
                        <button class="course-btn primary" id="addResourceBtn">
                            <i class="fas fa-plus"></i> Add Resource
                        </button>
                    </div>
                    <div class="resources-grid">
                        <div class="resource-card">
                            <div class="resource-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="resource-title">Course Syllabus</div>
                            <div class="resource-info">PDF, 2.3 MB, Uploaded: Sep 5, 2023</div>
                            <div class="resource-actions">
                                <button class="resource-btn">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="resource-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        </div>
                        <div class="resource-card">
                            <div class="resource-icon">
                                <i class="fas fa-file-powerpoint"></i>
                            </div>
                            <div class="resource-title">Lecture 1: Introduction</div>
                            <div class="resource-info">PPTX, 5.7 MB, Uploaded: Sep 10, 2023</div>
                            <div class="resource-actions">
                                <button class="resource-btn">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="resource-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        </div>
                        <div class="resource-card">
                            <div class="resource-icon">
                                <i class="fas fa-file-word"></i>
                            </div>
                            <div class="resource-title">Assignment 1</div>
                            <div class="resource-info">DOCX, 1.2 MB, Uploaded: Sep 15, 2023</div>
                            <div class="resource-actions">
                                <button class="resource-btn">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="resource-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        </div>
                        <div class="resource-card">
                            <div class="resource-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="resource-title">Textbook Chapter 1</div>
                            <div class="resource-info">PDF, 8.5 MB, Uploaded: Sep 20, 2023</div>
                            <div class="resource-actions">
                                <button class="resource-btn">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button class="resource-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schedule Tab -->
                <div class="tab-pane" id="schedule">
                    <h3 class="section-title">Course Schedule</h3>
                    <div class="schedule-grid">
                        <div class="day-header">Monday</div>
                        <div class="day-header">Tuesday</div>
                        <div class="day-header">Wednesday</div>
                        <div class="day-header">Thursday</div>
                        <div class="day-header">Friday</div>
                        <div class="day-header">Saturday</div>
                        <div class="day-header">Sunday</div>

                        <div class="schedule-cell">
                            <div class="schedule-item">
                                <div class="schedule-item-title">Lecture</div>
                                <div class="schedule-item-details">9:00-10:30 AM, Room 101</div>
                            </div>
                        </div>
                        <div class="schedule-cell"></div>
                        <div class="schedule-cell">
                            <div class="schedule-item">
                                <div class="schedule-item-title">Lecture</div>
                                <div class="schedule-item-details">9:00-10:30 AM, Room 101</div>
                            </div>
                        </div>
                        <div class="schedule-cell"></div>
                        <div class="schedule-cell">
                            <div class="schedule-item">
                                <div class="schedule-item-title">Lab Session</div>
                                <div class="schedule-item-details">2:00-4:00 PM, Lab 3</div>
                            </div>
                        </div>
                        <div class="schedule-cell"></div>
                        <div class="schedule-cell"></div>
                    </div>
                </div>

                <!-- Results Tab -->
                <div class="tab-pane" id="results">
                    <div class="results-actions">
                        <h3 class="section-title">Exam Results</h3>
                        <div>
                            <button class="course-btn primary" id="saveResultsBtn">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <button class="course-btn secondary" id="exportResultsBtn">
                                <i class="fas fa-file-export"></i> Export Results
                            </button>
                        </div>
                    </div>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Assignment 1 (20%)</th>
                                <th>Assignment 2 (20%)</th>
                                <th>Mid-Term (20%)</th>
                                <th>Final Exam (40%)</th>
                                <th>Total (100%)</th>
                                <th>Grade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>RMU/2023/001</td>
                                <td>John Smith</td>
                                <td><input type="number" class="grade-input" value="16" min="0" max="20"></td>
                                <td><input type="number" class="grade-input" value="15" min="0" max="20"></td>
                                <td><input type="number" class="grade-input" value="17" min="0" max="20"></td>
                                <td><input type="number" class="grade-input" value="32" min="0" max="40"></td>
                                <td>80</td>
                                <td>A</td>
                                <td><span class="grade-status pending">Pending</span></td>
                            </tr>
                            <tr>
                                <td>RMU/2023/002</td>
                                <td>Sarah Johnson</td>
                                <td><input type="number" class="grade-input" value="18" min="0" max="20"></td>
                                <td><input type="number" class="grade-input" value="19" min="0" max="20"></td>
                                <td><input type="number" class="grade-input" value="18" min="0" max="20"></td>
                                <td><input type="number" class="grade-input" value="36" min="0" max="40"></td>
                                <td>91</td>
                                <td>A+</td>
                                <td><span class="grade-status pending">Pending</span></td>
                            </tr>
                            <tr>
                                <td>RMU/2023/003</td>
                                <td>Michael Brown</td>
                                <td><input type="number" class="grade-input" value="14" min="0" max="20"></td>
                                <td><input type="number" class="grade-input" value="13" min="0" max="20"></td>
                                <td><input type="number" class="grade-input" value="15" min="0" max="20"></td>
                                <td><input type="number" class="grade-input" value="28" min="0" max="40"></td>
                                <td>70</td>
                                <td>B</td>
                                <td><span class="grade-status pending">Pending</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Resource Modal -->
    <div class="modal" id="uploadResourceModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Upload Resource</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="resourceTitle">Resource Title</label>
                        <input type="text" id="resourceTitle" placeholder="Enter resource title" required>
                    </div>
                    <div class="form-group">
                        <label for="resourceType">Resource Type</label>
                        <select id="resourceType" required>
                            <option value="">Select resource type</option>
                            <option value="syllabus">Syllabus</option>
                            <option value="lecture">Lecture Notes</option>
                            <option value="assignment">Assignment</option>
                            <option value="textbook">Textbook/Reading</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="resourceFile">Upload File</label>
                        <input type="file" id="resourceFile" required>
                    </div>
                    <div class="form-group">
                        <label for="resourceDescription">Description (Optional)</label>
                        <textarea id="resourceDescription" placeholder="Enter resource description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="saveResourceBtn">Upload Resource</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        // Tab functionality
        document.querySelectorAll('.course-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.course-tab').forEach(t => {
                    t.classList.remove('active');
                });

                // Add active class to clicked tab
                this.classList.add('active');

                // Hide all tab panes
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('active');
                });

                // Show the corresponding tab pane
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Modal functionality
        const uploadResourceModal = document.getElementById('uploadResourceModal');

        // Open modal
        document.getElementById('uploadResourceBtn').addEventListener('click', function() {
            uploadResourceModal.classList.add('active');
        });

        document.getElementById('addResourceBtn').addEventListener('click', function() {
            uploadResourceModal.classList.add('active');
        });

        // Close modal
        document.querySelectorAll('.close-btn, .cancel-btn').forEach(button => {
            button.addEventListener('click', function() {
                uploadResourceModal.classList.remove('active');
            });
        });

        // Save resource
        document.getElementById('saveResourceBtn').addEventListener('click', function() {
            const title = document.getElementById('resourceTitle').value;
            const type = document.getElementById('resourceType').value;
            const file = document.getElementById('resourceFile').value;

            if (!title || !type || !file) {
                alert('Please fill all required fields.');
                return;
            }

            // In a real application, you would upload the file to the server
            alert('Resource uploaded successfully!');
            uploadResourceModal.classList.remove('active');
        });

        // Calculate grades
        document.querySelectorAll('.grade-input').forEach(input => {
            input.addEventListener('change', function() {
                const row = this.closest('tr');
                const inputs = row.querySelectorAll('.grade-input');
                let total = 0;

                inputs.forEach(input => {
                    total += parseInt(input.value) || 0;
                });

                row.cells[6].textContent = total;

                // Determine grade
                let grade = '';
                if (total >= 90) grade = 'A+';
                else if (total >= 80) grade = 'A';
                else if (total >= 75) grade = 'B+';
                else if (total >= 70) grade = 'B';
                else if (total >= 65) grade = 'C+';
                else if (total >= 60) grade = 'C';
                else if (total >= 55) grade = 'D+';
                else if (total >= 50) grade = 'D';
                else grade = 'F';

                row.cells[7].textContent = grade;
            });
        });

        // Save results
        document.getElementById('saveResultsBtn').addEventListener('click', function() {
            // In a real application, you would save the results to the database
            document.querySelectorAll('.grade-status').forEach(status => {
                status.textContent = 'Submitted';
                status.classList.remove('pending');
                status.classList.add('submitted');
            });

            alert('Results saved successfully!');
        });

        // Export results
        document.getElementById('exportResultsBtn').addEventListener('click', function() {
            // In a real application, you would generate a CSV or Excel file
            alert('Exporting results...');
        });

        // Download student list
        document.getElementById('downloadStudentListBtn').addEventListener('click', function() {
            // In a real application, you would generate a CSV or Excel file
            alert('Downloading student list...');
        });

        // Student actions
        document.querySelectorAll('.student-action').forEach(action => {
            action.addEventListener('click', function() {
                const actionType = this.classList.contains('view') ? 'View Profile' :
                    this.classList.contains('message') ? 'Send Message' : 'Enter Grades';

                const row = this.closest('tr');
                const studentId = row.cells[0].textContent;
                const studentName = row.cells[1].textContent.trim();

                alert(`${actionType} for ${studentName} (${studentId})`);
            });
        });

        // Resource actions
        document.querySelectorAll('.resource-btn').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.textContent.trim();
                const resourceCard = this.closest('.resource-card');
                const resourceTitle = resourceCard.querySelector('.resource-title').textContent;

                alert(`${action} ${resourceTitle}`);
            });
        });

        // Edit course button
        document.getElementById('editCourseBtn').addEventListener('click', function() {
            alert('Edit course functionality would open a form to edit course details.');
        });

        // Email students button
        document.getElementById('emailStudentsBtn').addEventListener('click', function() {
            alert('Email students functionality would open a form to send an email to all students.');
        });
    </script>
</body>

</html>