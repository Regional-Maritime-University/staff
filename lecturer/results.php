<?php
session_start();

if (!isset($_SESSION["staffLoginSuccess"]) || $_SESSION["staffLoginSuccess"] == false || !isset($_SESSION["staff"]["number"]) || empty($_SESSION["staff"]["number"])) {
    header("Location: ../index.php");
}

$isUser = false;
if (strtolower($_SESSION["staff"]["role"]) == "admin" || strtolower($_SESSION["staff"]["role"]) == "developers" || strtolower($_SESSION["staff"]["role"]) == "lecturer") $isUser = true;

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

$pageTitle = "Results";
$activePage = "results";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Lecturer Portal - Exam Results</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="./css/results.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar -->
    <?php require_once '../components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">

        <?php require_once '../components/header.php'; ?>

        <div class="results-content">
            <!-- Results Filters -->
            <div class="results-filters">
                <div class="filter-group">
                    <select class="filter-select" id="semesterFilter">
                        <option value="current">First Semester 2023/2024</option>
                        <option value="previous">Second Semester 2022/2023</option>
                        <option value="all">All Semesters</option>
                    </select>
                    <select class="filter-select" id="courseFilter">
                        <option value="all">All Courses</option>
                        <option value="ME101">ME101 - Introduction to Marine Engineering</option>
                        <option value="ME302">ME302 - Marine Propulsion Systems</option>
                        <option value="ME405">ME405 - Ship Design and Construction</option>
                    </select>
                    <select class="filter-select" id="statusFilter">
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="filter-btn secondary" id="resetFiltersBtn">
                        <i class="fas fa-sync-alt"></i> Reset Filters
                    </button>
                    <button class="filter-btn primary" id="uploadResultsBtn">
                        <i class="fas fa-upload"></i> Upload Results
                    </button>
                </div>
            </div>

            <!-- Results Cards -->
            <div class="results-cards">
                <!-- Result Card 1 -->
                <div class="result-card">
                    <div class="result-header">
                        <h3 class="result-title">ME101 - Introduction to Marine Engineering</h3>
                        <span class="result-status pending">Pending</span>
                    </div>
                    <div class="result-info">
                        <div class="info-item">
                            <div class="info-label">Semester</div>
                            <div class="info-value">First Semester 2023/2024</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Students</div>
                            <div class="info-value">45</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Due Date</div>
                            <div class="info-value">Dec 15, 2023</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value">Nov 28, 2023</div>
                        </div>
                    </div>
                    <div class="result-progress">
                        <div class="progress-label">
                            <span>Completion</span>
                            <span>80%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 80%;"></div>
                        </div>
                    </div>
                    <div class="result-actions">
                        <button class="result-btn primary">
                            <i class="fas fa-edit"></i> Continue Grading
                        </button>
                        <button class="result-btn secondary">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                    </div>
                </div>

                <!-- Result Card 2 -->
                <div class="result-card">
                    <div class="result-header">
                        <h3 class="result-title">ME302 - Marine Propulsion Systems</h3>
                        <span class="result-status submitted">Submitted</span>
                    </div>
                    <div class="result-info">
                        <div class="info-item">
                            <div class="info-label">Semester</div>
                            <div class="info-value">First Semester 2023/2024</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Students</div>
                            <div class="info-value">32</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Due Date</div>
                            <div class="info-value">Dec 15, 2023</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value">Dec 5, 2023</div>
                        </div>
                    </div>
                    <div class="result-progress">
                        <div class="progress-label">
                            <span>Completion</span>
                            <span>100%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 100%;"></div>
                        </div>
                    </div>
                    <div class="result-actions">
                        <button class="result-btn primary">
                            <i class="fas fa-check-circle"></i> View Submission
                        </button>
                        <button class="result-btn secondary">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                </div>

                <!-- Result Card 3 -->
                <div class="result-card">
                    <div class="result-header">
                        <h3 class="result-title">ME405 - Ship Design and Construction</h3>
                        <span class="result-status approved">Approved</span>
                    </div>
                    <div class="result-info">
                        <div class="info-item">
                            <div class="info-label">Semester</div>
                            <div class="info-value">First Semester 2023/2024</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Students</div>
                            <div class="info-value">28</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Due Date</div>
                            <div class="info-value">Dec 10, 2023</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value">Dec 8, 2023</div>
                        </div>
                    </div>
                    <div class="result-progress">
                        <div class="progress-label">
                            <span>Completion</span>
                            <span>100%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 100%;"></div>
                        </div>
                    </div>
                    <div class="result-actions">
                        <button class="result-btn primary">
                            <i class="fas fa-eye"></i> View Results
                        </button>
                        <button class="result-btn secondary">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                </div>
            </div>

            <!-- Results Table -->
            <div class="results-table-container">
                <h3 class="section-title">Recent Results</h3>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>Semester</th>
                            <th>Students</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>ME101</td>
                            <td>Introduction to Marine Engineering</td>
                            <td>First Semester 2023/2024</td>
                            <td>45</td>
                            <td><span class="result-status pending">Pending</span></td>
                            <td>Dec 15, 2023</td>
                            <td>
                                <div class="table-actions">
                                    <button class="table-btn view" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="table-btn edit" title="Edit Results">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="table-btn delete" title="Delete Results">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>ME302</td>
                            <td>Marine Propulsion Systems</td>
                            <td>First Semester 2023/2024</td>
                            <td>32</td>
                            <td><span class="result-status submitted">Submitted</span></td>
                            <td>Dec 15, 2023</td>
                            <td>
                                <div class="table-actions">
                                    <button class="table-btn view" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="table-btn edit" title="Edit Results">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="table-btn delete" title="Delete Results">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>ME405</td>
                            <td>Ship Design and Construction</td>
                            <td>First Semester 2023/2024</td>
                            <td>28</td>
                            <td><span class="result-status approved">Approved</span></td>
                            <td>Dec 10, 2023</td>
                            <td>
                                <div class="table-actions">
                                    <button class="table-btn view" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="table-btn edit" title="Edit Results">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="table-btn delete" title="Delete Results">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>ME203</td>
                            <td>Fluid Mechanics for Marine Engineers</td>
                            <td>Second Semester 2022/2023</td>
                            <td>38</td>
                            <td><span class="result-status approved">Approved</span></td>
                            <td>Jul 20, 2023</td>
                            <td>
                                <div class="table-actions">
                                    <button class="table-btn view" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="table-btn edit" title="Edit Results">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="table-btn delete" title="Delete Results">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="pagination">
                    <button class="page-btn">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Results Modal -->
    <div class="modal" id="uploadResultsModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Upload Exam Results</h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="uploadCourse">Select Course</label>
                        <select id="uploadCourse" required>
                            <option value="">Select a course</option>
                            <option value="ME101">ME101 - Introduction to Marine Engineering</option>
                            <option value="ME302">ME302 - Marine Propulsion Systems</option>
                            <option value="ME405">ME405 - Ship Design and Construction</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="uploadSemester">Semester</label>
                        <select id="uploadSemester" required>
                            <option value="current">First Semester 2023/2024</option>
                            <option value="previous">Second Semester 2022/2023</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Upload Results File</label>
                        <div class="file-upload">
                            <div class="file-input-wrapper">
                                <input type="file" id="resultsFile" accept=".xlsx, .csv">
                                <div class="file-input-btn">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File or Drop File Here
                                </div>
                            </div>
                            <div class="file-name" id="fileName">No file chosen</div>
                            <div class="file-format-info">Accepted formats: Excel (.xlsx) or CSV (.csv)</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="uploadNotes">Additional Notes (Optional)</label>
                        <textarea id="uploadNotes" rows="3" placeholder="Enter any additional notes or comments"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" data-dismiss="modal">Cancel</button>
                    <button class="submit-btn" id="submitUploadBtn">Upload Results</button>
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

        // Modal functionality
        const uploadResultsModal = document.getElementById('uploadResultsModal');

        // Open modal
        document.getElementById('uploadResultsBtn').addEventListener('click', function() {
            uploadResultsModal.classList.add('active');
        });

        // Close modal
        document.querySelectorAll('.close-btn, .cancel-btn').forEach(button => {
            button.addEventListener('click', function() {
                uploadResultsModal.classList.remove('active');
            });
        });

        // File upload
        const resultsFile = document.getElementById('resultsFile');
        const fileName = document.getElementById('fileName');

        resultsFile.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileName.textContent = this.files[0].name;
            } else {
                fileName.textContent = 'No file chosen';
            }
        });

        // Submit upload
        document.getElementById('submitUploadBtn').addEventListener('click', function() {
            const course = document.getElementById('uploadCourse').value;
            const semester = document.getElementById('uploadSemester').value;
            const file = document.getElementById('resultsFile').value;

            if (!course || !semester || !file) {
                alert('Please fill all required fields and select a file.');
                return;
            }

            // In a real application, you would upload the file to the server
            alert('Results uploaded successfully!');
            uploadResultsModal.classList.remove('active');
        });

        // Reset filters
        document.getElementById('resetFiltersBtn').addEventListener('click', function() {
            document.getElementById('semesterFilter').value = 'current';
            document.getElementById('courseFilter').value = 'all';
            document.getElementById('statusFilter').value = 'all';
        });

        // Filter functionality
        const filterResults = () => {
            const semester = document.getElementById('semesterFilter').value;
            const course = document.getElementById('courseFilter').value;
            const status = document.getElementById('statusFilter').value;

            // In a real application, you would filter the results based on the selected filters
            console.log('Filtering results:', {
                semester,
                course,
                status
            });
        };

        document.getElementById('semesterFilter').addEventListener('change', filterResults);
        document.getElementById('courseFilter').addEventListener('change', filterResults);
        document.getElementById('statusFilter').addEventListener('change', filterResults);

        // Table actions
        document.querySelectorAll('.table-btn').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.getAttribute('title');
                const row = this.closest('tr');
                const courseCode = row.cells[0].textContent;
                const courseTitle = row.cells[1].textContent;

                alert(`${action} for ${courseCode} - ${courseTitle}`);
            });
        });

        // Result card buttons
        document.querySelectorAll('.result-btn').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.textContent.trim();
                const card = this.closest('.result-card');
                const courseTitle = card.querySelector('.result-title').textContent;

                alert(`${action} for ${courseTitle}`);
            });
        });

        // Pagination
        document.querySelectorAll('.page-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (!this.classList.contains('active') && this.textContent) {
                    document.querySelector('.page-btn.active').classList.remove('active');
                    this.classList.add('active');

                    // In a real application, you would load the corresponding page
                    console.log('Loading page:', this.textContent);
                }
            });
        });
    </script>
</body>

</html>