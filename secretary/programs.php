<?php
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

$staffData = $_SESSION["staff"] ?? null;
$_SESSION["lastAccessed"] = time();

require_once('../bootstrap.php');

use Src\Controller\SecretaryController;
use Src\Core\Base;
use Src\Core\Course;
use Src\Core\CourseCategory;

require_once('../inc/admin-database-con.php');

$secretary          = new SecretaryController($db, $user, $pass);
$course_category    = new CourseCategory($db, $user, $pass);
$course             = new Course($db, $user, $pass);
$base               = new Base($db, $user, $pass);

$pageTitle = "Programs";
$activePage = "programs";

$departmentId = $_SESSION["staff"]["department_id"] ?? null;
$semesterId = 2; //$_SESSION["semester"] ?? null;
$archived = false;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMU Staff Portal - Programs</title>
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
            display: flex;
            min-height: 100vh;
            background-color: var(--light-bg);
            overflow: hidden;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--accent-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: var(--text-color);
            padding: 20px 0;
            transition: all var(--transition-speed) ease;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            top: 0;
            left: 0;
            z-index: 100;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar.collapsed+.main-content {
            margin-left: 70px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 20px 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background-color: var(--text-color);
            padding: 5px;
        }

        .logo h2 {
            font-size: 1.5rem;
            font-weight: 600;
            transition: opacity var(--transition-speed);
        }

        .sidebar.collapsed .logo h2 {
            opacity: 0;
            width: 0;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 20px 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--accent-color);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            transition: opacity var(--transition-speed);
        }

        .user-info h3 {
            font-size: 1rem;
            font-weight: 600;
        }

        .user-info p {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .sidebar.collapsed .user-info {
            opacity: 0;
            width: 0;
        }

        .menu-groups {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .menu-group {
            margin-bottom: 15px;
        }

        .menu-group h3 {
            font-size: 0.8rem;
            text-transform: uppercase;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.6);
            transition: opacity var(--transition-speed);
            padding: 0 20px;
        }

        .sidebar.collapsed .menu-group h3 {
            opacity: 0;
        }

        .menu-items {
            display: flex;
            flex-direction: column;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            text-decoration: none;
            color: var(--text-color);
            transition: background-color var(--transition-speed);
            position: relative;
        }

        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .menu-item.active {
            background-color: var(--accent-color);
            font-weight: 500;
        }

        .menu-item.active::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: var(--text-color);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .menu-item span {
            transition: opacity var(--transition-speed);
        }

        .sidebar.collapsed .menu-item span {
            opacity: 0;
            width: 0;
        }

        .badge {
            background-color: var(--danger-color);
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 0.7rem;
            margin-left: auto;
            transition: opacity var(--transition-speed);
        }

        .sidebar.collapsed .badge {
            opacity: 0;
            width: 0;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 20px;
            transition: all var(--transition-speed) ease;
            margin-left: 250px;
            height: 100vh;
            overflow-y: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-left h1 {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .toggle-sidebar {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            font-size: 1.2rem;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color var(--transition-speed);
        }

        .toggle-sidebar:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 5px 10px;
            width: 300px;
        }

        .search-bar input {
            border: none;
            background: transparent;
            padding: 8px;
            width: 100%;
            outline: none;
        }

        .search-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            background: none;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            cursor: pointer;
            position: relative;
            transition: background-color var(--transition-speed);
        }

        .action-btn:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .action-btn .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            margin-left: 0;
        }

        /* Programs Content */
        .programs-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .programs-filters {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: space-between;
            align-items: center;
        }

        .filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter-select {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            color: var(--secondary-color);
            outline: none;
            min-width: 200px;
        }

        .filter-select:focus {
            border-color: var(--accent-color);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
        }

        .filter-btn {
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all var(--transition-speed);
        }

        .filter-btn.primary {
            background-color: var(--accent-color);
            color: white;
        }

        .filter-btn.secondary {
            background-color: var(--light-bg);
            color: var(--primary-color);
        }

        .filter-btn:hover {
            transform: translateY(-2px);
        }

        .filter-btn.primary:hover {
            background-color: var(--primary-color);
        }

        .filter-btn.secondary:hover {
            background-color: #e9ecef;
        }

        /* Programs Grid */
        .programs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .program-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            padding: 20px;
            transition: transform var(--transition-speed);
            position: relative;
            overflow: hidden;
        }

        .program-card:hover {
            transform: translateY(-5px);
        }

        .program-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-color), var(--primary-color));
        }

        .program-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .program-title {
            font-size: 1.3rem;
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .program-code {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }

        .program-level {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .program-level.undergraduate {
            background-color: #e8f5e9;
            color: var(--success-color);
        }

        .program-level.postgraduate {
            background-color: #e3f2fd;
            color: var(--accent-color);
        }

        .program-level.diploma {
            background-color: #fff3e0;
            color: var(--warning-color);
        }

        .program-description {
            color: var(--secondary-color);
            line-height: 1.5;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }

        .program-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 10px;
            background-color: var(--light-bg);
            border-radius: 8px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 3px;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
        }

        .program-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--secondary-color);
        }

        .info-item i {
            color: var(--accent-color);
            width: 16px;
        }

        .program-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .program-btn {
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all var(--transition-speed);
            font-weight: 500;
        }

        .program-btn.primary {
            background-color: var(--accent-color);
            color: white;
        }

        .program-btn.secondary {
            background-color: var(--light-bg);
            color: var(--primary-color);
            border: 1px solid var(--border-color);
        }

        .program-btn:hover {
            transform: translateY(-2px);
        }

        .program-btn.primary:hover {
            background-color: var(--primary-color);
        }

        .program-btn.secondary:hover {
            background-color: #e9ecef;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            overflow: auto;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-dialog {
            width: 100%;
            max-width: 900px;
            margin: 20px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-content {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .modal-header {
            padding: 15px 20px;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .modal-header h2 {
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color var(--transition-speed);
        }

        .close-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .modal-filters {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        .modal-search {
            flex: 1;
            min-width: 250px;
            display: flex;
            align-items: center;
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 5px 10px;
        }

        .modal-search input {
            border: none;
            background: transparent;
            padding: 8px;
            width: 100%;
            outline: none;
        }

        .modal-search-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
        }

        .modal-filter-select {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.9rem;
            color: var(--secondary-color);
            outline: none;
            min-width: 150px;
        }

        .modal-filter-select:focus {
            border-color: var(--accent-color);
        }

        .modal-body {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .modal-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background-color: var(--light-bg);
            border-radius: 8px;
            transition: all var(--transition-speed);
            border: 1px solid transparent;
        }

        .list-item:hover {
            background-color: #e9ecef;
            border-color: var(--accent-color);
        }

        .list-item-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .list-item-title {
            font-weight: 500;
            color: var(--primary-color);
            font-size: 1rem;
        }

        .list-item-subtitle {
            font-size: 0.9rem;
            color: #666;
        }

        .list-item-meta {
            display: flex;
            gap: 15px;
            font-size: 0.8rem;
            color: #666;
        }

        .list-item-actions {
            display: flex;
            gap: 5px;
        }

        .list-item-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 3px;
            transition: all var(--transition-speed);
        }

        .list-item-btn.view {
            background-color: var(--accent-color);
            color: white;
        }

        .list-item-btn.edit {
            background-color: var(--warning-color);
            color: white;
        }

        .list-item-btn:hover {
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ccc;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .programs-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .header-right {
                width: 100%;
                justify-content: space-between;
            }

            .search-bar {
                width: 100%;
            }

            .programs-filters {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-group {
                width: 100%;
            }

            .filter-select {
                width: 100%;
            }

            .filter-actions {
                width: 100%;
                justify-content: flex-end;
            }

            .programs-grid {
                grid-template-columns: 1fr;
            }

            .program-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .program-actions {
                grid-template-columns: 1fr;
            }

            .modal-dialog {
                margin: 10px;
                max-height: 95vh;
            }

            .modal-filters {
                flex-direction: column;
                align-items: flex-start;
            }

            .modal-search {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .program-info {
                grid-template-columns: 1fr;
            }

            .program-stats {
                grid-template-columns: 1fr;
            }

            .list-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .list-item-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <?php require_once '../components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">

        <?php require_once '../components/header.php'; ?>

        <div class="programs-content">
            <!-- Programs Filters -->
            <div class="programs-filters">
                <div class="filter-group">
                    <select class="filter-select" id="levelFilter">
                        <option value="all">All Levels</option>
                        <option value="undergraduate">Undergraduate</option>
                        <option value="postgraduate">Postgraduate</option>
                        <option value="diploma">Diploma</option>
                    </select>
                    <select class="filter-select" id="departmentFilter">
                        <option value="all">All Departments</option>
                        <option value="marine-engineering">Marine Engineering</option>
                        <option value="nautical-science">Nautical Science</option>
                        <option value="port-management">Port Management</option>
                        <option value="maritime-law">Maritime Law</option>
                    </select>
                    <select class="filter-select" id="statusFilter">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="filter-btn secondary" id="resetFiltersBtn">
                        <i class="fas fa-sync-alt"></i> Reset Filters
                    </button>
                    <button class="filter-btn primary" id="addProgramBtn">
                        <i class="fas fa-plus"></i> Add Program
                    </button>
                </div>
            </div>

            <!-- Programs Grid -->
            <div class="programs-grid" id="programsGrid">
                <!-- Program cards will be dynamically generated here -->
            </div>
        </div>
    </div>

    <!-- Curriculum Modal -->
    <div class="modal" id="curriculumModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-list-alt"></i> <span id="curriculumModalTitle">Program Curriculum</span></h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-filters">
                    <div class="modal-search">
                        <input type="text" placeholder="Search courses..." id="curriculumSearch">
                        <button class="modal-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <select class="modal-filter-select" id="curriculumYearFilter">
                        <option value="all">All Years</option>
                        <option value="1">Year 1</option>
                        <option value="2">Year 2</option>
                        <option value="3">Year 3</option>
                        <option value="4">Year 4</option>
                    </select>
                    <select class="modal-filter-select" id="curriculumSemesterFilter">
                        <option value="all">All Semesters</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                    </select>
                </div>
                <div class="modal-body">
                    <div class="modal-list" id="curriculumList">
                        <!-- Curriculum items will be dynamically generated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Classes Modal -->
    <div class="modal" id="classesModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-users"></i> <span id="classesModalTitle">Program Classes</span></h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-filters">
                    <div class="modal-search">
                        <input type="text" placeholder="Search classes..." id="classesSearch">
                        <button class="modal-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <select class="modal-filter-select" id="classesYearFilter">
                        <option value="all">All Years</option>
                        <option value="1">Year 1</option>
                        <option value="2">Year 2</option>
                        <option value="3">Year 3</option>
                        <option value="4">Year 4</option>
                    </select>
                    <select class="modal-filter-select" id="classesStatusFilter">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="modal-body">
                    <div class="modal-list" id="classesList">
                        <!-- Classes items will be dynamically generated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Modal -->
    <div class="modal" id="studentsModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-user-graduate"></i> <span id="studentsModalTitle">Program Students</span></h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-filters">
                    <div class="modal-search">
                        <input type="text" placeholder="Search students..." id="studentsSearch">
                        <button class="modal-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <select class="modal-filter-select" id="studentsYearFilter">
                        <option value="all">All Years</option>
                        <option value="1">Year 1</option>
                        <option value="2">Year 2</option>
                        <option value="3">Year 3</option>
                        <option value="4">Year 4</option>
                    </select>
                    <select class="modal-filter-select" id="studentsStatusFilter">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="graduated">Graduated</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <div class="modal-body">
                    <div class="modal-list" id="studentsList">
                        <!-- Students items will be dynamically generated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Courses Modal -->
    <div class="modal" id="coursesModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-book"></i> <span id="coursesModalTitle">Program Courses</span></h2>
                    <button class="close-btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-filters">
                    <div class="modal-search">
                        <input type="text" placeholder="Search courses..." id="coursesSearch">
                        <button class="modal-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <select class="modal-filter-select" id="coursesTypeFilter">
                        <option value="all">All Types</option>
                        <option value="core">Core</option>
                        <option value="elective">Elective</option>
                        <option value="practical">Practical</option>
                    </select>
                    <select class="modal-filter-select" id="coursesStatusFilter">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="modal-body">
                    <div class="modal-list" id="coursesList">
                        <!-- Courses items will be dynamically generated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sample data
        const programsData = [{
                id: 1,
                title: "Bachelor of Marine Engineering",
                code: "BME",
                level: "undergraduate",
                department: "marine-engineering",
                description: "A comprehensive program covering marine propulsion systems, ship design, and maritime technology.",
                duration: "4 Years",
                credits: 120,
                students: 145,
                courses: 32,
                classes: 8,
                status: "active"
            },
            {
                id: 2,
                title: "Master of Nautical Science",
                code: "MNS",
                level: "postgraduate",
                department: "nautical-science",
                description: "Advanced studies in navigation, ship handling, and maritime operations management.",
                duration: "2 Years",
                credits: 60,
                students: 78,
                courses: 18,
                classes: 4,
                status: "active"
            },
            {
                id: 3,
                title: "Diploma in Port Management",
                code: "DPM",
                level: "diploma",
                department: "port-management",
                description: "Specialized training in port operations, logistics, and terminal management.",
                duration: "18 Months",
                credits: 45,
                students: 92,
                courses: 15,
                classes: 6,
                status: "active"
            },
            {
                id: 4,
                title: "Bachelor of Maritime Law",
                code: "BML",
                level: "undergraduate",
                department: "maritime-law",
                description: "Comprehensive study of maritime law, international shipping regulations, and marine insurance.",
                duration: "4 Years",
                credits: 120,
                students: 67,
                courses: 28,
                classes: 5,
                status: "active"
            },
            {
                id: 5,
                title: "Master of Marine Engineering",
                code: "MME",
                level: "postgraduate",
                department: "marine-engineering",
                description: "Advanced research and development in marine engineering technologies and systems.",
                duration: "2 Years",
                credits: 60,
                students: 34,
                courses: 16,
                classes: 3,
                status: "active"
            },
            {
                id: 6,
                title: "Certificate in Maritime Safety",
                code: "CMS",
                level: "diploma",
                department: "nautical-science",
                description: "Essential training in maritime safety protocols and emergency response procedures.",
                duration: "6 Months",
                credits: 20,
                students: 156,
                courses: 8,
                classes: 12,
                status: "inactive"
            }
        ];

        const curriculumData = {
            1: [ // BME curriculum
                {
                    id: 1,
                    code: "ME101",
                    title: "Introduction to Marine Engineering",
                    year: 1,
                    semester: 1,
                    credits: 3,
                    type: "core"
                },
                {
                    id: 2,
                    code: "MA101",
                    title: "Engineering Mathematics I",
                    year: 1,
                    semester: 1,
                    credits: 4,
                    type: "core"
                },
                {
                    id: 3,
                    code: "PH101",
                    title: "Engineering Physics",
                    year: 1,
                    semester: 1,
                    credits: 3,
                    type: "core"
                },
                {
                    id: 4,
                    code: "ME102",
                    title: "Marine Thermodynamics",
                    year: 1,
                    semester: 2,
                    credits: 3,
                    type: "core"
                },
                {
                    id: 5,
                    code: "ME201",
                    title: "Marine Propulsion Systems",
                    year: 2,
                    semester: 1,
                    credits: 4,
                    type: "core"
                },
                {
                    id: 6,
                    code: "ME202",
                    title: "Ship Design Principles",
                    year: 2,
                    semester: 2,
                    credits: 4,
                    type: "core"
                },
                {
                    id: 7,
                    code: "ME301",
                    title: "Advanced Marine Engineering",
                    year: 3,
                    semester: 1,
                    credits: 4,
                    type: "core"
                },
                {
                    id: 8,
                    code: "ME302",
                    title: "Marine Electrical Systems",
                    year: 3,
                    semester: 2,
                    credits: 3,
                    type: "elective"
                }
            ],
            2: [ // MNS curriculum
                {
                    id: 9,
                    code: "NS201",
                    title: "Advanced Navigation",
                    year: 1,
                    semester: 1,
                    credits: 4,
                    type: "core"
                },
                {
                    id: 10,
                    code: "NS202",
                    title: "Ship Handling",
                    year: 1,
                    semester: 1,
                    credits: 3,
                    type: "core"
                },
                {
                    id: 11,
                    code: "NS203",
                    title: "Maritime Operations",
                    year: 1,
                    semester: 2,
                    credits: 4,
                    type: "core"
                },
                {
                    id: 12,
                    code: "NS301",
                    title: "Port Management",
                    year: 2,
                    semester: 1,
                    credits: 3,
                    type: "elective"
                },
                {
                    id: 13,
                    code: "NS302",
                    title: "Maritime Law",
                    year: 2,
                    semester: 2,
                    credits: 3,
                    type: "core"
                }
            ]
        };

        const classesData = {
            1: [ // BME classes
                {
                    id: 1,
                    name: "BME Class A - Year 1",
                    year: 1,
                    students: 35,
                    status: "active",
                    lecturer: "Dr. Smith"
                },
                {
                    id: 2,
                    name: "BME Class B - Year 1",
                    year: 1,
                    students: 32,
                    status: "active",
                    lecturer: "Prof. Johnson"
                },
                {
                    id: 3,
                    name: "BME Class A - Year 2",
                    year: 2,
                    students: 28,
                    status: "active",
                    lecturer: "Dr. Brown"
                },
                {
                    id: 4,
                    name: "BME Class A - Year 3",
                    year: 3,
                    students: 25,
                    status: "active",
                    lecturer: "Dr. Wilson"
                },
                {
                    id: 5,
                    name: "BME Class A - Year 4",
                    year: 4,
                    students: 25,
                    status: "active",
                    lecturer: "Prof. Davis"
                }
            ],
            2: [ // MNS classes
                {
                    id: 6,
                    name: "MNS Class A - Year 1",
                    year: 1,
                    students: 40,
                    status: "active",
                    lecturer: "Capt. Anderson"
                },
                {
                    id: 7,
                    name: "MNS Class A - Year 2",
                    year: 2,
                    students: 38,
                    status: "active",
                    lecturer: "Capt. Thompson"
                }
            ]
        };

        const studentsData = {
            1: [ // BME students
                {
                    id: 1,
                    name: "John Smith",
                    studentId: "BME2021001",
                    year: 3,
                    status: "active",
                    gpa: 3.45
                },
                {
                    id: 2,
                    name: "Sarah Johnson",
                    studentId: "BME2021002",
                    year: 3,
                    status: "active",
                    gpa: 3.78
                },
                {
                    id: 3,
                    name: "Michael Brown",
                    studentId: "BME2020001",
                    year: 4,
                    status: "active",
                    gpa: 3.23
                },
                {
                    id: 4,
                    name: "Emily Davis",
                    studentId: "BME2022001",
                    year: 2,
                    status: "active",
                    gpa: 3.89
                },
                {
                    id: 5,
                    name: "Robert Wilson",
                    studentId: "BME2019001",
                    year: 4,
                    status: "graduated",
                    gpa: 3.56
                }
            ],
            2: [ // MNS students
                {
                    id: 6,
                    name: "James Anderson",
                    studentId: "MNS2022001",
                    year: 2,
                    status: "active",
                    gpa: 3.67
                },
                {
                    id: 7,
                    name: "Lisa Thompson",
                    studentId: "MNS2023001",
                    year: 1,
                    status: "active",
                    gpa: 3.45
                },
                {
                    id: 8,
                    name: "David Miller",
                    studentId: "MNS2022002",
                    year: 2,
                    status: "suspended",
                    gpa: 2.34
                }
            ]
        };

        const coursesData = {
            1: [ // BME courses
                {
                    id: 1,
                    code: "ME101",
                    title: "Introduction to Marine Engineering",
                    credits: 3,
                    type: "core",
                    status: "active"
                },
                {
                    id: 2,
                    code: "ME201",
                    title: "Marine Propulsion Systems",
                    credits: 4,
                    type: "core",
                    status: "active"
                },
                {
                    id: 3,
                    code: "ME301",
                    title: "Advanced Marine Engineering",
                    credits: 4,
                    type: "core",
                    status: "active"
                },
                {
                    id: 4,
                    code: "ME302",
                    title: "Marine Electrical Systems",
                    credits: 3,
                    type: "elective",
                    status: "active"
                },
                {
                    id: 5,
                    code: "ME401",
                    title: "Ship Design Project",
                    credits: 6,
                    type: "practical",
                    status: "active"
                }
            ],
            2: [ // MNS courses
                {
                    id: 6,
                    code: "NS201",
                    title: "Advanced Navigation",
                    credits: 4,
                    type: "core",
                    status: "active"
                },
                {
                    id: 7,
                    code: "NS202",
                    title: "Ship Handling",
                    credits: 3,
                    type: "core",
                    status: "active"
                },
                {
                    id: 8,
                    code: "NS301",
                    title: "Port Management",
                    credits: 3,
                    type: "elective",
                    status: "active"
                }
            ]
        };

        let filteredPrograms = [...programsData];

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            renderPrograms();
            initializeEventListeners();
        });

        // Render programs
        function renderPrograms() {
            const programsGrid = document.getElementById('programsGrid');

            if (filteredPrograms.length === 0) {
                programsGrid.innerHTML = `
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <i class="fas fa-graduation-cap"></i>
                        <h3>No Programs Found</h3>
                        <p>No programs match your current filters. Try adjusting your search criteria.</p>
                    </div>
                `;
                return;
            }

            programsGrid.innerHTML = filteredPrograms.map(program => `
                <div class="program-card" data-program-id="${program.id}">
                    <div class="program-header">
                        <div>
                            <div class="program-title">${program.title}</div>
                            <div class="program-code">${program.code}</div>
                        </div>
                        <span class="program-level ${program.level}">${program.level}</span>
                    </div>
                    <div class="program-description">${program.description}</div>
                    <div class="program-stats">
                        <div class="stat-item">
                            <div class="stat-value">${program.students}</div>
                            <div class="stat-label">Students</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${program.courses}</div>
                            <div class="stat-label">Courses</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${program.classes}</div>
                            <div class="stat-label">Classes</div>
                        </div>
                    </div>
                    <div class="program-info">
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <span>${program.duration}</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-certificate"></i>
                            <span>${program.credits} Credits</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-building"></i>
                            <span>${program.department.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-circle ${program.status === 'active' ? 'text-success' : 'text-danger'}"></i>
                            <span>${program.status.charAt(0).toUpperCase() + program.status.slice(1)}</span>
                        </div>
                    </div>
                    <div class="program-actions">
                        <button class="program-btn primary" onclick="openCurriculumModal(${program.id})">
                            <i class="fas fa-list-alt"></i> Curriculum
                        </button>
                        <button class="program-btn secondary" onclick="openClassesModal(${program.id})">
                            <i class="fas fa-users"></i> Classes
                        </button>
                        <button class="program-btn primary" onclick="openStudentsModal(${program.id})">
                            <i class="fas fa-user-graduate"></i> Students
                        </button>
                        <button class="program-btn secondary" onclick="openCoursesModal(${program.id})">
                            <i class="fas fa-book"></i> Courses
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Initialize event listeners
        function initializeEventListeners() {
            // Toggle sidebar
            document.querySelector('.toggle-sidebar').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
            });

            // Global search
            document.getElementById('globalSearch').addEventListener('input', function() {
                filterPrograms();
            });

            // Filter dropdowns
            document.getElementById('levelFilter').addEventListener('change', filterPrograms);
            document.getElementById('departmentFilter').addEventListener('change', filterPrograms);
            document.getElementById('statusFilter').addEventListener('change', filterPrograms);

            // Reset filters
            document.getElementById('resetFiltersBtn').addEventListener('click', function() {
                document.getElementById('globalSearch').value = '';
                document.getElementById('levelFilter').value = 'all';
                document.getElementById('departmentFilter').value = 'all';
                document.getElementById('statusFilter').value = 'all';
                filterPrograms();
            });

            // Modal close buttons
            document.querySelectorAll('.close-btn, [data-dismiss="modal"]').forEach(button => {
                button.addEventListener('click', function() {
                    closeAllModals();
                });
            });

            // Modal search and filter event listeners
            setupModalFilters();
        }

        // Filter programs
        function filterPrograms() {
            const searchTerm = document.getElementById('globalSearch').value.toLowerCase();
            const levelFilter = document.getElementById('levelFilter').value;
            const departmentFilter = document.getElementById('departmentFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            filteredPrograms = programsData.filter(program => {
                const matchesSearch = program.title.toLowerCase().includes(searchTerm) ||
                    program.code.toLowerCase().includes(searchTerm) ||
                    program.description.toLowerCase().includes(searchTerm);

                const matchesLevel = levelFilter === 'all' || program.level === levelFilter;
                const matchesDepartment = departmentFilter === 'all' || program.department === departmentFilter;
                const matchesStatus = statusFilter === 'all' || program.status === statusFilter;

                return matchesSearch && matchesLevel && matchesDepartment && matchesStatus;
            });

            renderPrograms();
        }

        // Modal functions
        function openCurriculumModal(programId) {
            const program = programsData.find(p => p.id === programId);
            document.getElementById('curriculumModalTitle').textContent = `${program.title} - Curriculum`;
            renderCurriculumList(programId);
            document.getElementById('curriculumModal').classList.add('active');
        }

        function openClassesModal(programId) {
            const program = programsData.find(p => p.id === programId);
            document.getElementById('classesModalTitle').textContent = `${program.title} - Classes`;
            renderClassesList(programId);
            document.getElementById('classesModal').classList.add('active');
        }

        function openStudentsModal(programId) {
            const program = programsData.find(p => p.id === programId);
            document.getElementById('studentsModalTitle').textContent = `${program.title} - Students`;
            renderStudentsList(programId);
            document.getElementById('studentsModal').classList.add('active');
        }

        function openCoursesModal(programId) {
            const program = programsData.find(p => p.id === programId);
            document.getElementById('coursesModalTitle').textContent = `${program.title} - Courses`;
            renderCoursesList(programId);
            document.getElementById('coursesModal').classList.add('active');
        }

        function closeAllModals() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.classList.remove('active');
            });
        }

        // Render modal lists
        function renderCurriculumList(programId) {
            const curriculum = curriculumData[programId] || [];
            const listContainer = document.getElementById('curriculumList');

            if (curriculum.length === 0) {
                listContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-list-alt"></i>
                        <h3>No Curriculum Found</h3>
                        <p>No curriculum data available for this program.</p>
                    </div>
                `;
                return;
            }

            listContainer.innerHTML = curriculum.map(course => `
                <div class="list-item" data-year="${course.year}" data-semester="${course.semester}">
                    <div class="list-item-info">
                        <div class="list-item-title">${course.code} - ${course.title}</div>
                        <div class="list-item-subtitle">Year ${course.year}, Semester ${course.semester} • ${course.credits} Credits • ${course.type}</div>
                    </div>
                    <div class="list-item-actions">
                        <button class="list-item-btn view" onclick="viewCourseDetails(${course.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function renderClassesList(programId) {
            const classes = classesData[programId] || [];
            const listContainer = document.getElementById('classesList');

            if (classes.length === 0) {
                listContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Classes Found</h3>
                        <p>No classes data available for this program.</p>
                    </div>
                `;
                return;
            }

            listContainer.innerHTML = classes.map(classItem => `
                <div class="list-item" data-year="${classItem.year}" data-status="${classItem.status}">
                    <div class="list-item-info">
                        <div class="list-item-title">${classItem.name}</div>
                        <div class="list-item-subtitle">${classItem.students} Students • Lecturer: ${classItem.lecturer}</div>
                        <div class="list-item-meta">
                            <span>Status: ${classItem.status}</span>
                        </div>
                    </div>
                    <div class="list-item-actions">
                        <button class="list-item-btn view" onclick="viewClassDetails(${classItem.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="list-item-btn edit" onclick="editClass(${classItem.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function renderStudentsList(programId) {
            const students = studentsData[programId] || [];
            const listContainer = document.getElementById('studentsList');

            if (students.length === 0) {
                listContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-user-graduate"></i>
                        <h3>No Students Found</h3>
                        <p>No students data available for this program.</p>
                    </div>
                `;
                return;
            }

            listContainer.innerHTML = students.map(student => `
                <div class="list-item" data-year="${student.year}" data-status="${student.status}">
                    <div class="list-item-info">
                        <div class="list-item-title">${student.name}</div>
                        <div class="list-item-subtitle">ID: ${student.studentId} • Year ${student.year}</div>
                        <div class="list-item-meta">
                            <span>GPA: ${student.gpa}</span>
                            <span>Status: ${student.status}</span>
                        </div>
                    </div>
                    <div class="list-item-actions">
                        <button class="list-item-btn view" onclick="viewStudentDetails(${student.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="list-item-btn edit" onclick="editStudent(${student.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function renderCoursesList(programId) {
            const courses = coursesData[programId] || [];
            const listContainer = document.getElementById('coursesList');

            if (courses.length === 0) {
                listContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-book"></i>
                        <h3>No Courses Found</h3>
                        <p>No courses data available for this program.</p>
                    </div>
                `;
                return;
            }

            listContainer.innerHTML = courses.map(course => `
                <div class="list-item" data-type="${course.type}" data-status="${course.status}">
                    <div class="list-item-info">
                        <div class="list-item-title">${course.code} - ${course.title}</div>
                        <div class="list-item-subtitle">${course.credits} Credits • ${course.type}</div>
                        <div class="list-item-meta">
                            <span>Status: ${course.status}</span>
                        </div>
                    </div>
                    <div class="list-item-actions">
                        <button class="list-item-btn view" onclick="viewCourseDetails(${course.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="list-item-btn edit" onclick="editCourse(${course.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Setup modal filters
        function setupModalFilters() {
            // Curriculum filters
            document.getElementById('curriculumSearch').addEventListener('input', filterCurriculum);
            document.getElementById('curriculumYearFilter').addEventListener('change', filterCurriculum);
            document.getElementById('curriculumSemesterFilter').addEventListener('change', filterCurriculum);

            // Classes filters
            document.getElementById('classesSearch').addEventListener('input', filterClasses);
            document.getElementById('classesYearFilter').addEventListener('change', filterClasses);
            document.getElementById('classesStatusFilter').addEventListener('change', filterClasses);

            // Students filters
            document.getElementById('studentsSearch').addEventListener('input', filterStudents);
            document.getElementById('studentsYearFilter').addEventListener('change', filterStudents);
            document.getElementById('studentsStatusFilter').addEventListener('change', filterStudents);

            // Courses filters
            document.getElementById('coursesSearch').addEventListener('input', filterCourses);
            document.getElementById('coursesTypeFilter').addEventListener('change', filterCourses);
            document.getElementById('coursesStatusFilter').addEventListener('change', filterCourses);
        }

        // Modal filter functions
        function filterCurriculum() {
            const searchTerm = document.getElementById('curriculumSearch').value.toLowerCase();
            const yearFilter = document.getElementById('curriculumYearFilter').value;
            const semesterFilter = document.getElementById('curriculumSemesterFilter').value;

            const items = document.querySelectorAll('#curriculumList .list-item');
            items.forEach(item => {
                const title = item.querySelector('.list-item-title').textContent.toLowerCase();
                const year = item.dataset.year;
                const semester = item.dataset.semester;

                const matchesSearch = title.includes(searchTerm);
                const matchesYear = yearFilter === 'all' || year === yearFilter;
                const matchesSemester = semesterFilter === 'all' || semester === semesterFilter;

                if (matchesSearch && matchesYear && matchesSemester) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function filterClasses() {
            const searchTerm = document.getElementById('classesSearch').value.toLowerCase();
            const yearFilter = document.getElementById('classesYearFilter').value;
            const statusFilter = document.getElementById('classesStatusFilter').value;

            const items = document.querySelectorAll('#classesList .list-item');
            items.forEach(item => {
                const title = item.querySelector('.list-item-title').textContent.toLowerCase();
                const year = item.dataset.year;
                const status = item.dataset.status;

                const matchesSearch = title.includes(searchTerm);
                const matchesYear = yearFilter === 'all' || year === yearFilter;
                const matchesStatus = statusFilter === 'all' || status === statusFilter;

                if (matchesSearch && matchesYear && matchesStatus) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function filterStudents() {
            const searchTerm = document.getElementById('studentsSearch').value.toLowerCase();
            const yearFilter = document.getElementById('studentsYearFilter').value;
            const statusFilter = document.getElementById('studentsStatusFilter').value;

            const items = document.querySelectorAll('#studentsList .list-item');
            items.forEach(item => {
                const title = item.querySelector('.list-item-title').textContent.toLowerCase();
                const year = item.dataset.year;
                const status = item.dataset.status;

                const matchesSearch = title.includes(searchTerm);
                const matchesYear = yearFilter === 'all' || year === yearFilter;
                const matchesStatus = statusFilter === 'all' || status === statusFilter;

                if (matchesSearch && matchesYear && matchesStatus) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function filterCourses() {
            const searchTerm = document.getElementById('coursesSearch').value.toLowerCase();
            const typeFilter = document.getElementById('coursesTypeFilter').value;
            const statusFilter = document.getElementById('coursesStatusFilter').value;

            const items = document.querySelectorAll('#coursesList .list-item');
            items.forEach(item => {
                const title = item.querySelector('.list-item-title').textContent.toLowerCase();
                const type = item.dataset.type;
                const status = item.dataset.status;

                const matchesSearch = title.includes(searchTerm);
                const matchesType = typeFilter === 'all' || type === typeFilter;
                const matchesStatus = statusFilter === 'all' || status === statusFilter;

                if (matchesSearch && matchesType && matchesStatus) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Action functions (these would typically navigate to detail pages or open edit modals)
        function viewCourseDetails(courseId) {
            alert(`Viewing course details for course ID: ${courseId}`);
            // In a real application, this would navigate to a course details page
            // window.location.href = `course-details.html?id=${courseId}`;
        }

        function viewClassDetails(classId) {
            alert(`Viewing class details for class ID: ${classId}`);
            // In a real application, this would navigate to a class details page
            // window.location.href = `class-details.html?id=${classId}`;
        }

        function viewStudentDetails(studentId) {
            alert(`Viewing student details for student ID: ${studentId}`);
            // In a real application, this would navigate to a student details page
            // window.location.href = `student-details.html?id=${studentId}`;
        }

        function editClass(classId) {
            alert(`Editing class with ID: ${classId}`);
            // In a real application, this would open an edit modal or navigate to an edit page
        }

        function editStudent(studentId) {
            alert(`Editing student with ID: ${studentId}`);
            // In a real application, this would open an edit modal or navigate to an edit page
        }

        function editCourse(courseId) {
            alert(`Editing course with ID: ${courseId}`);
            // In a real application, this would open an edit modal or navigate to an edit page
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                closeAllModals();
            }
        });

        // Keyboard navigation for modals
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAllModals();
            }
        });
    </script>
</body>

</html>