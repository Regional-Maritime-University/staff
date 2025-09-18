<?php

namespace Src\Controller;

use Exception;
use Src\System\DatabaseMethods;
use Src\Controller\ExposeDataController;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Src\Base\Log;
use Src\Core\Base;
use Src\Core\Classes;
use Src\Core\Course;
use Src\Core\Program;
use Src\Core\Staff;
use Src\Core\Student;

class SecretaryController
{
    private $dm = null;
    private $db = null;
    private $user = null;
    private $pass = null;
    private $expose = null;
    private $log = null;
    private $base = null;

    public function __construct($db, $user, $pass)
    {
        $this->db = $db;
        $this->user = $user;
        $this->pass = $pass;
        $this->dm = new DatabaseMethods($db, $user, $pass);
        $this->log = new Log($db, $user, $pass);
        $this->expose = new ExposeDataController($db, $user, $pass);
        $this->base = new Base($db, $user, $pass);
    }

    public function setInitialSessions($data)
    {
        if (!isset($_SESSION["staff"])) {
            $_SESSION['staffLoginSuccess']  = true;
            $_SESSION["staff"] = $data;
        }
        if (!isset($_SESSION["active_semesters"])) {
            $activeSemesters = $this->base->getActiveSemesters();
            $_SESSION["active_semesters"] = $activeSemesters;
        }
    }

    public function getTimeStamp($period)
    {
        $timeAgo = '';
        $activityTimestamp = strtotime($period);
        $currentTimestamp = time();
        $timeDifference = $currentTimestamp - $activityTimestamp;

        if ($timeDifference < 60) {
            $timeAgo = $timeDifference == 1 ? '1 sec ago' : "$timeDifference sec ago";
        } elseif ($timeDifference < 3600) {
            $minutes = floor($timeDifference / 60);
            $timeAgo = $minutes == 1 ? '1 min ago' : "$minutes min ago";
        } elseif ($timeDifference < 86400) {
            $hours = floor($timeDifference / 3600);
            $timeAgo = $hours == 1 ? '1 hr ago' : "$hours hr ago";
        } elseif ($timeDifference < 172800) {
            $timeAgo = 'Yesterday';
        } elseif ($timeDifference < 604800) {
            $days = floor($timeDifference / 86400);
            $timeAgo = $days == 1 ? '1 day ago' : "$days days ago";
        } elseif ($timeDifference < 2592000) {
            $weeks = floor($timeDifference / 604800);
            $timeAgo = $weeks == 1 ? '1 week ago' : "$weeks weeks ago";
        } elseif ($timeDifference < 31536000) {
            $months = floor($timeDifference / 2592000);
            $timeAgo = $months == 1 ? '1 month ago' : "$months months ago";
        } else {
            $years = floor($timeDifference / 31536000);
            $timeAgo = $years == 1 ? '1 year ago' : "$years years ago";
        }
        return $timeAgo;
    }

    public function fetchStudents($departmentId)
    {
        $query = "SELECT * FROM student WHERE fk_department = :dp";
        return $this->dm->getData($query, array(":dp" => $departmentId));
    }
    public function fetchActiveCourses($departmentId = null, $semester = null, $archived = false)
    {
        $params = [":ar" => $archived];
        $where = " WHERE c.archived = :ar ";
        $joins = "";
        $selectExtra = ", cg.id AS category_id, cg.name AS category_name ";

        // Department filter
        if ($departmentId) {
            $joins .= " LEFT JOIN department AS d ON c.fk_department = d.id ";
            $where .= " AND d.id = :d ";
            $params[":d"] = $departmentId;
            $selectExtra .= ", c.fk_department AS department_id, d.name AS department_name ";
        }

        // Semester filter
        if ($semester) {
            $where .= " AND c.semester = :s ";
            $params[":s"] = $semester;
        }

        // Join for category
        $joins .= " LEFT JOIN course_category AS cg ON c.fk_category = cg.id ";

        // Join lecturer_courses (for lecturer assignments)
        $joins .= " LEFT JOIN lecturer_courses AS lca 
                   ON lca.fk_course = c.code 
                  AND lca.fk_semester = c.semester ";

        // Join staff (lecturer)
        $joins .= " LEFT JOIN staff AS s ON lca.fk_staff = s.number ";

        // Join deadlines (to fetch due_date)
        $joins .= " LEFT JOIN deadlines AS dl
                   ON dl.fk_course = c.code
                  AND dl.fk_semester = c.semester
                  AND dl.fk_staff = lca.fk_staff
                  AND dl.fk_department = c.fk_department ";

        $select = "
        c.code, 
        c.name, 
        c.credit_hours, 
        c.contact_hours, 
        c.semester, 
        c.level, 
        c.archived, 
        cg.name AS category
        {$selectExtra},
        s.number AS lecturer_number, 
        s.avatar AS lecturer_avatar, 
        s.email AS lecturer_email, 
        s.prefix AS lecturer_prefix, 
        s.first_name AS lecturer_first_name, 
        s.last_name AS lecturer_last_name, 
        dl.due_date AS deadline_date,
        dl.note AS deadline_note,
        dl.status AS deadline_status,
        lca.status AS course_status,
        lca.notes AS lecturer_course_notes
    ";

        $query = "SELECT {$select} 
              FROM course AS c 
              {$joins} 
              {$where} 
              ORDER BY c.code ASC";

        return $this->dm->getData($query, $params);
    }

    public function fetchAssignedCourses($departmentId = null, $archived = false)
    {
        $query = "SELECT `code`, `name`, `credit_hours`, `contact_hours`, `semester`, `level`, `archived`, 
                `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `fk_department`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `course_assignments` AS ca 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND d.`id` = :d AND c.`archived` = :ar AND ca.`fk_course` = c.`id` AND ca.`fk_user` = :u";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId));
    }

    public function fetchSubmittedCourses($departmentId = null, $archived = false)
    {
        $query = "SELECT `code`, `name`, `credit_hours`, `contact_hours`, `semester`, `level`, `archived`, 
                `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `fk_department`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `course_assignments` AS ca 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND d.`id` = :d AND c.`archived` = :ar AND ca.`fk_course` = c.`id` AND ca.`fk_user` = :u AND ca.`submitted` = 1";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId));
    }

    public function assignCourseSubmissionDeadline(array $data)
    {
        $successCount = $failedCount = 0;
        $successCourses = $failedCourses = [];
        $errorMessage = [];

        // Check that courses were passed in
        if (empty($data["courses"])) {
            return [
                "success" => false,
                "message" => "No courses provided for deadline assignment."
            ];
        }

        foreach ($data["courses"] as $course) {
            // fetch course details
            $courseQuery = "SELECT `name` FROM `course` WHERE `code` = :c";
            $courseData = $this->dm->getData($courseQuery, [":c" => $course]);

            // fetch all classes assigned this course in the selected semester
            $classQuery = "SELECT `fk_class` FROM `section` 
                       WHERE `fk_course` = :course AND `fk_semester` = :semester";
            $classParams = [":course" => $course, ":semester" => $data["semester"]];
            $assignedClasses = $this->dm->getData($classQuery, $classParams);

            if (empty($assignedClasses)) {
                $errorMessage[] = "No classes assigned for course {$courseData[0]['name']} ({$course}) in the selected semester.";
                continue;
            }

            // insert deadlines for each class
            foreach ($assignedClasses as $class) {
                // Check if deadline already exists
                $checkQuery = "SELECT COUNT(*) AS cnt 
                           FROM `deadlines` 
                           WHERE `fk_department` = :department
                             AND `fk_semester` = :semester
                             AND `fk_course` = :course
                             AND `fk_class` = :class
                             AND `fk_staff` = :lecturer";
                $checkParams = [
                    ":department" => $data["department"],
                    ":semester" => $data["semester"],
                    ":course" => $course,
                    ":class" => $class["fk_class"],
                    ":lecturer" => $data["lecturer"]
                ];

                $checkResult = $this->dm->getData($checkQuery, $checkParams);

                if (!empty($checkResult) && $checkResult[0]['cnt'] > 0) {
                    continue;
                }

                $insertQuery = "INSERT INTO `deadlines` (`fk_department`, `fk_semester`, `fk_course`, `fk_class`, `fk_staff`, `due_date`, `note`, `status`) 
                VALUES (:department, :semester, :course, :class, :lecturer, :date, :note, 'pending')";

                $insertParams = [
                    ":department" => $data["department"],
                    ":semester" => $data["semester"],
                    ":course" => $course,
                    ":class" => $class["fk_class"],
                    ":lecturer" => $data["lecturer"],
                    ":date" => $data["date"],
                    ":note" => $data["note"]
                ];

                $insertResult = $this->dm->inputData($insertQuery, $insertParams);

                if ($insertResult) {
                    $this->log->activity(
                        $_SESSION["staff"]["number"],
                        "UPDATE",
                        "secretary",
                        "Results Submission Deadline",
                        "Set a deadline on {$courseData[0]["name"]} ({$course}) for {$class["fk_class"]}"
                    );
                    $successCourses[] = $course;
                    $successCount++;
                } else {
                    $failedCourses[] = $course;
                    $failedCount++;
                }
            }

            // Update lecturer_courses status to marking
            $query = "UPDATE `lecturer_courses` 
                        SET `status` = 'marking', `updated_at` = NOW()
                        WHERE `fk_department` = :dp 
                            AND `fk_semester` = :sm 
                            AND `fk_course` = :cs 
                            AND `fk_staff` = :st";
            $params = [
                ":dp" => $data["department"],
                ":sm" => $data["semester"],
                ":cs" => $course,
                ":st" => $data["lecturer"]
            ];

            $result = $this->dm->inputData($query, $params);
            if ($result) {
                $this->log->activity(
                    $_SESSION["staff"]["number"],
                    "UPDATE",
                    "secretary",
                    "Results Submission Deadline",
                    "Set a deadline for {$courseData[0]["name"]} ({$course})"
                );
            }
        }

        $successStatus = $successCount > 0;

        return [
            "success" => $successStatus,
            "message" => $successStatus
                ? "Successfully set deadline(s) for results submission for {$successCount} courses (" . implode(", ", $successCourses) . ") ! " . implode(", ", $errorMessage)
                : "Failed to set deadline(s) for results submission for {$failedCount} courses (" . implode(", ", $failedCourses) . ")! " . implode(", ", $errorMessage)
        ];
    }

    public function fetchPendingDeadlinesByClass($departmentId = null, $archived = false, $semesterId = null, $courseCode = null, $deadlineStatus = null)
    {
        $params = [":d" => $departmentId, ":ar" => $archived];
        $where = " WHERE dl.`fk_department` = :d AND dl.`due_date` IS NOT NULL AND c.`archived` = :ar ";

        if ($semesterId) {
            $where .= " AND dl.`fk_semester` = :si ";
            $params[":si"] = $semesterId;
        }

        if ($courseCode) {
            $where .= " AND dl.`fk_course` = :cc ";
            $params[":cc"] = $courseCode;
        }

        if ($deadlineStatus) {
            $where .= " AND dl.`status` = :ds ";
            $params[":ds"] = $deadlineStatus;
        }

        return $this->fetchDeadlinesQueryGrpByClass($where, $params);
    }

    private function fetchDeadlinesQueryGrpByClass($where, $params)
    {
        $query = "SELECT 
                dl.`fk_course` AS course_code, 
                c.`name` AS course_name, 
                c.`credit_hours`, 
                c.`contact_hours`, 
                c.`semester` AS course_semester, 
                c.`level` AS course_level, 
                c.`archived` AS course_status, 
                c.`fk_category` AS category_id, 
                cg.`name` AS category,

                -- Deadline info
                dl.`id` AS deadline_id, 
                dl.`note` AS deadline_note,
                dl.`due_date`, 
                dl.`status` AS deadline_status, 
                dl.`created_at`, 
                dl.`updated_at`, 
                dl.`fk_semester` AS semester_id,

                -- exam results info/status 
                er.`status` AS result_status,

                -- Lecturer info
                dl.`fk_staff` AS staff_number, 
                CONCAT(sf.`prefix`, ' ', sf.`first_name`, ' ', sf.`last_name`) AS lecturer_name, 

                -- Department + Semester info
                d.`id` AS department_id, 
                d.`name` AS department_name,
                CONCAT('SEMESTER ', s.`name`) AS semester_name, 

                -- Class info
                cl.`code` AS class_code,
                cl.`year` AS class_year,
                cl.`category` AS class_category,
                sec.`id` AS section_id,
                sec.`notes` AS section_notes,

                -- Total students
                (SELECT COUNT(*) 
                 FROM `student_courses` scr 
                 WHERE scr.`fk_course` = dl.`fk_course` 
                   AND scr.`fk_semester` = dl.`fk_semester` 
                   AND scr.`registered` = 1) AS total_registered_students,
                (SELECT COUNT(*) 
                 FROM `student_courses` scr2 
                 WHERE scr2.`fk_course` = dl.`fk_course` 
                   AND scr2.`fk_semester` = dl.`fk_semester`) AS total_assigned_students
                  
              FROM `deadlines` AS dl 
              JOIN `department` AS d ON dl.`fk_department` = d.`id`
              JOIN `staff` AS sf ON dl.`fk_staff` = sf.`number`
              JOIN `course` AS c ON dl.`fk_course` = c.`code`
              JOIN `semester` AS s ON dl.`fk_semester` = s.`id`
              JOIN `course_category` AS cg ON c.`fk_category` = cg.`id`
              LEFT JOIN `exam_results` AS er 
                    ON c.`code` = er.`fk_course` 
                    AND s.`id` = er.`fk_semester` 
              LEFT JOIN `section` AS sec 
                     ON sec.`fk_course` = dl.`fk_course`
                    AND sec.`fk_semester` = dl.`fk_semester`
                    AND sec.`fk_class` = dl.`fk_class`
              LEFT JOIN `class` AS cl 
                     ON sec.`fk_class` = cl.`code`
                     AND dl.`fk_class` = cl.`code`
              {$where}
              GROUP BY dl.`fk_course`, dl.`fk_class`
              ORDER BY (MAX(dl.`status`) = 'pending') DESC, MAX(dl.`due_date`) ASC";
        return $this->dm->getData($query, $params);
    }

    public function fetchPendingDeadlinesByCourse($departmentId = null, $archived = false, $semesterId = null, $courseCode = null, $deadlineStatus = null)
    {
        $params = [":d" => $departmentId, ":ar" => $archived];
        $where = " WHERE dl.`fk_department` = :d AND dl.`due_date` IS NOT NULL AND c.`archived` = :ar ";

        if ($semesterId) {
            $where .= " AND dl.`fk_semester` = :si ";
            $params[":si"] = $semesterId;
        }

        if ($courseCode) {
            $where .= " AND dl.`fk_course` = :cc ";
            $params[":cc"] = $courseCode;
        }

        if ($deadlineStatus) {
            $where .= " AND dl.`status` = :ds ";
            $params[":ds"] = $deadlineStatus;
        }

        return $this->fetchDeadlinesQueryGrpByCourse($where, $params);
    }

    private function fetchDeadlinesQueryGrpByCourse($where, $params)
    {
        $query = "SELECT 
                dl.`fk_course` AS course_code, 
                c.`name` AS course_name, 
                c.`credit_hours`, 
                c.`contact_hours`, 
                c.`semester` AS course_semester, 
                c.`level` AS course_level, 
                c.`archived` AS course_status, 
                c.`fk_category` AS category_id, 
                cg.`name` AS category,

                -- Deadline info
                MAX(dl.`id`) AS deadline_id, 
                MAX(dl.`note`) AS deadline_note,
                MAX(dl.`due_date`) AS due_date, 
                MAX(dl.`status`) AS deadline_status, 
                MAX(dl.`created_at`) AS created_at, 
                MAX(dl.`updated_at`) AS updated_at, 
                MAX(dl.`fk_semester`) AS semester_id,

                -- Lecturer info
                dl.`fk_staff` AS staff_number, 
                CONCAT(sf.`prefix`, ' ', sf.`first_name`, ' ', sf.`last_name`) AS lecturer_name, 

                -- Department + Semester info
                d.`id` AS department_id, 
                d.`name` AS department_name,
                CONCAT('SEMESTER ', s.`name`) AS semester_name, 

                -- Section/Class info (aggregated)
                GROUP_CONCAT(DISTINCT sec.`id`) AS section_ids,
                GROUP_CONCAT(DISTINCT cl.`code`) AS class_codes,

                -- Total students
                (SELECT COUNT(*) 
                 FROM `student_courses` scr 
                 WHERE scr.`fk_course` = dl.`fk_course` 
                   AND scr.`fk_semester` = dl.`fk_semester` 
                   AND scr.`registered` = 1) AS total_registered_students,
                (SELECT COUNT(*) 
                 FROM `student_courses` scr2 
                 WHERE scr2.`fk_course` = dl.`fk_course` 
                   AND scr2.`fk_semester` = dl.`fk_semester`) AS total_assigned_students
                  
              FROM `deadlines` AS dl 
              JOIN `department` AS d ON dl.`fk_department` = d.`id`
              JOIN `staff` AS sf ON dl.`fk_staff` = sf.`number`
              JOIN `course` AS c ON dl.`fk_course` = c.`code`
              JOIN `semester` AS s ON dl.`fk_semester` = s.`id`
              JOIN `course_category` AS cg ON c.`fk_category` = cg.`id`
              LEFT JOIN `section` AS sec 
                     ON sec.`fk_course` = dl.`fk_course`
                    AND sec.`fk_semester` = dl.`fk_semester`
              LEFT JOIN `class` AS cl 
                     ON sec.`fk_class` = cl.`code`
              
              {$where}
              GROUP BY dl.`fk_course`
              ORDER BY (MAX(dl.`status`) = 'pending') DESC, MAX(dl.`due_date`) ASC";
        return $this->dm->getData($query, $params);
    }

    public function fetchAllDeadlines($departmentId = null, $archived = false)
    {
        $query = "SELECT 
                dl.`fk_course` AS course_code, 
                c.`name` AS course_name, 
                c.`credit_hours`, 
                c.`contact_hours`, 
                c.`semester` AS course_semester, 
                c.`level` AS course_level, 
                c.`archived` AS course_status, 
                c.`fk_category` AS category_id, 
                cg.`name` AS category, 

                -- Deadline info
                MAX(dl.`id`) AS deadline_id, 
                MAX(dl.`note`) AS deadline_note,
                MAX(dl.`due_date`) AS due_date, 
                MAX(dl.`status`) AS deadline_status, 
                MAX(dl.`created_at`) AS created_at, 
                MAX(dl.`updated_at`) AS updated_at,

                -- Lecturer info
                dl.`fk_staff` AS staff_number, 
                CONCAT(sf.`prefix`, ' ', sf.`first_name`, ' ', sf.`last_name`) AS lecturer_name,

                -- Department/Semester info
                d.`id` AS department_id, 
                d.`name` AS department_name,
                CONCAT('SEMESTER ', s.`name`) AS semester_name, 

                -- Total students
                (SELECT COUNT(*) 
                 FROM `student_courses` scr 
                 WHERE scr.`fk_course` = dl.`fk_course` 
                   AND scr.`fk_semester` = dl.`fk_semester` 
                   AND scr.`registered` = 1) AS total_registered_students,
                (SELECT COUNT(*) 
                 FROM `student_courses` scr2 
                 WHERE scr2.`fk_course` = dl.`fk_course` 
                   AND scr2.`fk_semester` = dl.`fk_semester`) AS total_assigned_students
                
              FROM `deadlines` AS dl 
              JOIN `department` AS d ON dl.`fk_department` = d.`id` 
              JOIN `staff` AS sf ON dl.`fk_staff` = sf.`number` 
              JOIN `course` AS c ON dl.`fk_course` = c.`code` 
              JOIN `semester` AS s ON dl.`fk_semester` = s.`id` 
              JOIN `course_category` AS cg ON c.`fk_category` = cg.`id` 
              WHERE dl.`fk_department` = :d 
                AND dl.`due_date` IS NOT NULL 
                AND c.`archived` = :ar 
              GROUP BY dl.`fk_course`
              ORDER BY (MAX(dl.`status`) = 'pending') DESC, MAX(dl.`due_date`) ASC";
        return $this->dm->getData($query, [":d" => $departmentId, ":ar" => $archived]);
    }

    public function fetchUpcomingDeadlines($departmentId = null, $archived = false)
    {
        $query = "SELECT `code`, `name`, `credit_hours`, `contact_hours`, `semester`, `level`, `archived`, 
                `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `fk_department`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `course_assignments` AS ca 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND d.`id` = :d AND c.`archived` = :ar AND ca.`fk_course` = c.`id` AND ca.`fk_user` = :u AND ca.`submitted` = 0 AND ca.`deadline_date` > NOW()";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId));
    }

    public function fetchRecentActivities($departmentId = null, $archived = false, $limit = 3)
    {
        if (!$limit) $limit = 4;
        $query = "SELECT 
                    al.`id`, al.`user_id`, al.`operation`, al.`type`, al.`action`, al.`description`, al.`timestamp`
                FROM 
                    `activity_logs` AS al
                    JOIN `department` AS dp ON al.`fk_department` = dp.`id`
                WHERE 
                    dp.`id` = :d AND dp.`archived` = :ar ORDER BY `timestamp` DESC 
                LIMIT $limit
                ";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId));
    }

    public function fetchRegisteredCourses($departmentId = null, $archived = false)
    {
        $query = "SELECT `code`, `name`, `credit_hours`, `contact_hours`, `semester`, `level`, `archived`, 
                `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `fk_department`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `course_assignments` AS ca 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND d.`id` = :d AND c.`archived` = :ar AND ca.`fk_course` = c.`id` AND ca.`fk_user` = :u AND ca.`submitted` = 1 AND ca.`deadline_date` < NOW()";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId));
    }

    public function fetchRunningSemesterCourses($departmentId = null, $archived = false, $semesterId = null)
    {
        $query = "SELECT c.`code`, c.`name`, c.`credit_hours`, c.`contact_hours`, c.`semester`, c.`level`, c.`archived`, 
                `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `fk_department`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `course_assignments` AS ca 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND d.`id` = :d AND c.`archived` = :ar AND ca.`fk_course` = c.`id` AND ca.`fk_user` = :u AND ca.`submitted` = 1 AND ca.`deadline_date` < NOW() AND s.id = :s";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId, ":s" => $semesterId));
    }

    public function fetchActiveSemesters()
    {
        $query = "SELECT s.*, a.`id` AS academic_year, a.`name` AS academic_year_name, a.`active` AS `academic_year_status`, 
                    a.`start_month` AS academic_year_start_month, a.`end_month` AS academic_year_end_month, 
                    a.`start_year` AS academic_year_start_year, a.`end_year` AS academic_year_end_year 
                FROM `semester` AS s, `academic_year` AS a 
                WHERE a.`active` = 1 AND s.`active` = 1 AND a.`id` = s.`fk_academic_year`";
        return $this->dm->getData($query);
    }

    public function fetchAllLecturers($departmentId = null, $archived = false)
    {
        $query = "SELECT s.`number`, s.`prefix`, s.`gender`, s.`first_name`, s.`middle_name`, s.`last_name`, s.`designation`, s.`role`,
                d.`name` AS `department_name`, d.`id` AS `fk_department` 
                FROM `staff` AS s, `department` AS d 
                WHERE s.`fk_department` = d.`id` AND d.`id` = :d AND s.`archived` = :ar AND s.`role` IN ('lecturer', 'hod')";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId));
    }

    public function assignCourses($data)
    {
        $result = array();

        switch ($data["action"]) {
            case 'lecturer':
                $result = $this->assignCoursesToLecturer($data);
                break;

            case 'student':
                $result = $this->assignCoursesToStudent($data);
                break;

            case 'class':
                $result = $this->assignCoursesToClass($data);
                break;

            case 'program':
                $result = $this->assignCoursesToProgram($data);
                break;

            default:
                $result = array("success" => false, "message" => "No match found for selected action!");
                break;
        }

        return $result;
    }

    public function assignCoursesToLecturer($data)
    {
        $errorEncountered = 0;
        $successEncountered = 0;
        $errors = [];
        $coursesAssigned = [];

        foreach ($data["courses"] as $course) {
            // Fetch lecturer details
            $lecturerData = (new Staff($this->db, $this->user, $this->pass))->fetch(key: "number", value: $data["lecturer"], archived: false);
            if ($lecturerData["success"]) $lecturerData = $lecturerData["data"][0];

            // Fetch course details
            $courseData = (new Course($this->db, $this->user, $this->pass))->fetch(key: "code", value: $course, archived: false);
            if ($courseData["success"]) $courseData = $courseData["data"][0];

            // Check if the course is already assigned to any lecturer or the same lecturer
            $query1 = "SELECT * FROM `lecturer_courses` WHERE `fk_department` = :dt AND `fk_course` = :cc AND `fk_semester` = :si";
            $result1 = $this->dm->getData($query1, array(":dt" => $data["department"], ":cc" => $course, ":si" => $data["semester"]));

            if ($result1) {
                $errorEncountered++;
                if ($result1[0]["fk_staff"] == $data["lecturer"]) {
                    array_push($errors, "{$courseData["name"]} ({$courseData["code"]}) is already assigned to {$lecturerData["full_name"]}.");
                } else {
                    array_push($errors, "{$courseData["name"]} ({$courseData["code"]}) is already assigned to another lecturer.");
                }
                continue;
            }

            $query2 = "INSERT INTO `lecturer_courses` (`fk_department`, `fk_staff`, `fk_course`, `fk_semester`, `notes`) VALUES (:di, :sn, :cc, :si, :nt)";
            $result2 = $this->dm->inputData($query2, array(":di" => $data["department"], ":sn" => $data["lecturer"],  ":cc" => $course, ":si" => $data["semester"], ":nt" => $data["notes"]));

            if (!$result2) {
                $errorEncountered++;
                array_push($errors, "Fatal error occurred while in server!");
            }

            $successEncountered++;
            array_push($coursesAssigned, $course);
            $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Course Assignment", "Assigned {$lecturerData["full_name"]} to {$courseData["name"]} ({$course})");
        }

        $messageStatus = $successEncountered ? true : false;
        $courses = $coursesAssigned ? implode(", ", $coursesAssigned) : "";
        $errors = implode(" | ", $errors);
        $message = $messageStatus ? "Successfully assigned {$successEncountered} [{$courses}] course(s) to {$lecturerData["full_name"]}!" : $errors;

        return array(
            "success" => $messageStatus,
            "message" => $message
        );
    }

    public function assignCoursesToStudent($data)
    {
        $errorEncountered = 0;
        $successEncountered = 0;
        $errors = [];
        $coursesAssigned = [];

        foreach ($data["courses"] as $course) {
            // Fetch student details
            $studentData = (new Student($this->db, $this->user, $this->pass))->fetch(key: "index_number", value: $data["student"], archived: false);
            if ($studentData["success"]) $studentData = $studentData["data"][0];

            // Fetch course details
            $courseData = (new Course($this->db, $this->user, $this->pass))->fetch(key: "code", value: $course, archived: false);
            if ($courseData["success"]) $courseData = $courseData["data"][0];

            // Check if the course is already assigned to any student or the same student
            $query1 = "SELECT * FROM `student_courses` WHERE `fk_student` = :st AND `fk_course` = :cc";
            $result1 = $this->dm->getData($query1, array(":st" => $data["student"], ":cc" => $course));

            $studentFullName = "{$studentData["prefix"]} {$studentData["first_name"]} {$studentData["last_name"]} ({$studentData["index_number"]})";

            if ($result1) {
                $errorEncountered++;
                if ($result1[0]["fk_student"] == $data["student"]) {
                    array_push($errors, "{$courseData["name"]} ({$courseData["code"]}) is already assigned to  {$studentFullName}.");
                } else {
                    array_push($errors, "{$courseData["name"]} ({$courseData["code"]}) is already assigned to another student.");
                }
                continue;
            }

            // fetch student course details from section table using fk_class and fk_course
            $query2 = "SELECT * FROM `section` WHERE `fk_class` = :cl AND `fk_course` = :co";
            $result2 = $this->dm->getData($query2, array(":co" => $course, ":cl" => $studentData["class_code"]));

            if (empty($result2)) {
                $errorEncountered++;
                array_push(
                    $errors,
                    "Curricullum not set for this student's class or course {$courseData["name"]} ({$courseData["code"]}) is not assigned to the class of  {$studentFullName}."
                );
                continue;
            }

            $query3 = "INSERT INTO `student_courses` (`fk_student`, `fk_course`, `fk_semester`, `notes`, `credit_hours`, `level`, `semester`) 
                        VALUES (:si, :co, :st, :nt, :ch, :lv, :sm)";
            $result3 = $this->dm->inputData(
                $query3,
                array(
                    ":si" => $data["student"],
                    ":co" => $course,
                    ":st" => $data["semester"],
                    ":nt" => $data["notes"],
                    ":ch" => $result2[0]["credit_hours"],
                    ":lv" => $result2[0]["level"],
                    ":sm" => $result2[0]["semester"]
                )
            );

            if (!$result3) {
                $errorEncountered++;
                array_push($errors, "Fatal error occurred while in server!");
            }

            $successEncountered++;
            array_push($coursesAssigned, $course);
            $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Course Assignment", "Assigned {$studentFullName} to {$courseData["name"]} ({$course})");
        }

        $messageStatus = $successEncountered ? true : false;
        $courses = $coursesAssigned ? implode(", ", $coursesAssigned) : "";
        $errors = implode(" | ", $errors);
        $message = $messageStatus ? "Successfully assigned {$successEncountered} [{$courses}] course(s) to {$studentFullName}!" : $errors;

        return array(
            "success" => $messageStatus,
            "message" => $message
        );
    }

    public function assignCoursesToClass($data)
    {
        $errorEncountered = 0;
        $successEncountered = 0;
        $errors = [];
        $coursesAssigned = [];

        foreach ($data["courses"] as $course) {
            // Fetch class details
            $classData = (new Classes($this->db, $this->user, $this->pass))->fetch(key: "code", value: $data["class"], archived: false);
            if ($classData["success"]) $classData = $classData["data"];

            // Fetch course details
            $courseData = (new Course($this->db, $this->user, $this->pass))->fetch(key: "code", value: $course, archived: false);
            if ($courseData["success"]) $courseData = $courseData["data"];

            // Check if the course is already assigned to any class or the same class
            $query1 = "SELECT * FROM `section` WHERE `fk_class` = :cs AND `fk_course` = :cc";
            $result1 = $this->dm->getData($query1, array(":cs" => $data["class"], ":cc" => $course));

            if ($result1) {
                $errorEncountered++;
                if ($result1[0]["fk_class"] == $data["class"]) {
                    array_push($errors, "{$courseData[0]["name"]} ({$courseData[0]["code"]}) is already assigned to {$data["class"]}.");
                }
                continue;
            }

            // fetch class course details from curriculum table using fk_class and fk_course
            $query2 = "SELECT * FROM `curriculum` WHERE `fk_program` = :pg AND `fk_course` = :co";
            $result2 = $this->dm->getData($query2, array(":pg" => $classData[0]["program_id"], ":co" => $course));

            if (empty($result2)) {
                $errorEncountered++;
                array_push($errors, "Curricullum not set for this class {$data["class"]} on course {$courseData[0]["name"]} ({$courseData[0]["code"]}).");
                continue;
            }

            // fetch all students in the class
            $studentData = (new Student($this->db, $this->user, $this->pass))->fetch(key: "class", value: $data["class"], archived: false);
            if ($studentData["success"]) $studentData = $studentData["data"];
            else return $studentData;

            // assign course and semester to all students in the class in the student_courses table
            foreach ($studentData as $student) {
                $query4 = "INSERT INTO `student_courses` (`fk_student`, `fk_course`, `fk_semester`, `notes`, `credit_hours`, `level`, `semester`) 
                            VALUES (:si, :co, :st, :nt, :ch, :lv, :sm)";
                $params4 = array(
                    ":si" => $student["index_number"],
                    ":co" => $course,
                    ":st" => $data["semester"],
                    ":nt" => $data["notes"],
                    ":ch" => $courseData[0]["credit_hours"],
                    ":lv" => $courseData[0]["level"],
                    ":sm" => $courseData[0]["semester"]
                );
                $result4 = $this->dm->inputData($query4, $params4);

                if (!$result4) {
                    $errorEncountered++;
                    array_push($errors, "Failed to assign course {$courseData[0]["name"]} ({$courseData[0]["code"]}) to student {$student["index_number"]}.1");
                    $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Course Assignment", "Failed to assign {$courseData[0]["name"]} ({$course}) to student {$student["index_number"]}.1");
                } else {
                    $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Course Assignment", "Assigned {$courseData[0]["name"]} ({$course}) to student {$student["index_number"]}.1");
                    $query5 = "INSERT INTO `student_results` (`fk_student`, `fk_course`, `fk_semester`) VALUES (:si, :co, :st)";
                    $params5 = array(":si" => $student["index_number"], ":co" => $course, ":st" => $data["semester"]);
                    $result5 = $this->dm->inputData($query5, $params5);
                    if (!$result5) {
                        $errorEncountered++;
                        array_push($errors, "Failed to assign course {$courseData[0]["name"]} ({$courseData[0]["code"]}) to student {$student["index_number"]}.2");
                        $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Course Assignment", "Failed to assign {$courseData[0]["name"]} ({$course}) to student {$student["index_number"]}.2");
                    } else {
                        $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Course Assignment", "Assigned {$courseData[0]["name"]} ({$course}) to student {$student["index_number"]}.2");
                    }
                }
            }

            $query3 = "INSERT INTO `section` (`fk_class`, `fk_course`, `fk_semester`, `notes`, `credit_hours`, `level`, `semester`) 
                        VALUES (:cc, :co, :st, :nt, :ch, :lv, :sm)";
            $result3 = $this->dm->inputData(
                $query3,
                array(
                    ":cc" => $data["class"],
                    ":co" => $course,
                    ":st" => $data["semester"],
                    ":nt" => $data["notes"],
                    ":ch" => $courseData[0]["credit_hours"],
                    ":lv" => $courseData[0]["level"],
                    ":sm" => $courseData[0]["semester"]
                )
            );

            if (!$result3) {
                $errorEncountered++;
                array_push($errors, "Fatal error occurred while in server!");
            }

            $successEncountered++;
            array_push($coursesAssigned, $course);
            $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Course Assignment", "Assigned {$data["class"]} to {$courseData[0]["name"]} ({$course})");
        }

        $messageStatus = $successEncountered ? true : false;
        $courses = $coursesAssigned ? implode(", ", $coursesAssigned) : "";
        $errors = implode(" | ", $errors);
        $message = $messageStatus ? "Successfully assigned {$successEncountered} [{$courses}] course(s) to {$data["class"]}!" : $errors;

        return array(
            "success" => $messageStatus,
            "message" => $message
        );
    }

    public function assignCoursesToProgram($data)
    {
        $errorEncountered = 0;
        $successEncountered = 0;
        $errors = [];
        $coursesAssigned = [];

        foreach ($data["courses"] as $course) {
            // Fetch lecturer details
            $programData = (new Program($this->db, $this->user, $this->pass))->fetch(key: "id", value: $data["program"], archived: false);
            if ($programData["success"]) $programData = $programData["data"][0];

            // Fetch course details
            $courseData = (new Course($this->db, $this->user, $this->pass))->fetch(key: "code", value: $course, archived: false);
            if ($courseData["success"]) $courseData = $courseData["data"][0];

            // Check if the course is already assigned to any lecturer or the same lecturer
            $query1 = "SELECT * FROM `curriculum` WHERE `fk_program` = :dt AND `fk_course` = :cc";
            $result1 = $this->dm->getData($query1, array(":dt" => $data["program"], ":cc" => $course));

            if ($result1) {
                $errorEncountered++;
                if ($result1[0]["fk_program"] == $data["program"]) {
                    array_push($errors, "{$courseData["name"]} ({$courseData["code"]}) is already assigned to {$programData["name"]}.");
                } else {
                    array_push($errors, "{$courseData["name"]} ({$courseData["code"]}) is already assigned to another program.");
                }
                continue;
            }

            $query2 = "INSERT INTO `curriculum` (`fk_program`, `fk_course`) VALUES (:p, :c)";
            $result2 = $this->dm->inputData($query2, array(":p" => $data["program"], ":c" => $course));

            if (!$result2) {
                $errorEncountered++;
                array_push($errors, "Fatal error occurred while in server!");
            }

            $successEncountered++;
            array_push($coursesAssigned, $course);
            $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Course Assignment", "Assigned{$courseData["name"]} ({$course}) to {$programData["name"]}");
        }

        $messageStatus = $successEncountered ? true : false;
        $courses = $coursesAssigned ? implode(", ", $coursesAssigned) : "";
        $errors = implode(" | ", $errors);
        $message = $messageStatus ? "Successfully assigned {$successEncountered} [{$courses}] course(s) to {$programData["name"]}!" : $errors;

        return array(
            "success" => $messageStatus,
            "message" => $message
        );
    }

    public function fetchSemesterCourseAssignmentsByLecturer($lecturerId, $semesterId)
    {
        $query = "SELECT * FROM `lecturer_courses` WHERE `fk_staff` = :sn AND `fk_semester` = :si";
        return $this->dm->getData($query, array(":sn" => $lecturerId, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsGroupByLecturer($departmentId, $semesterId)
    {
        $query = "SELECT lca.*, st.`number` AS lecturer_number, st.`avatar` AS lecturer_avatar, st.`email` AS lecturer_email, 
                st.`prefix` AS lecturer_prefix, st.`first_name` AS lecturer_first_name, st.`last_name` AS lecturer_last_name, 
                cc.`name` AS course_name, cc.`credit_hours` AS course_credit_hours, cc.`contact_hours` AS course_contact_hours,
                cc.`semester` AS course_semester, cc.`level` AS course_level 
                FROM `lecturer_courses` AS lca, `course` AS cc, `staff` AS st 
                WHERE lca.`fk_course` = cc.`code` AND lca.`fk_staff` = st.`number` AND 
                    lca.`fk_department` = :di AND lca.`fk_semester` = :si GROUP BY `fk_staff`";
        return $this->dm->getData($query, array(":di" => $departmentId, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsByCourse($courseCode, $semesterId)
    {
        $query = "SELECT * FROM `lecturer_courses` WHERE `fk_course` = :cc AND `fk_semester` = :si";
        return $this->dm->getData($query, array(":cc" => $courseCode, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsGroupByCourse($courseCode, $semesterId)
    {
        $query = "SELECT * FROM `lecturer_courses` WHERE `fk_course` = :cc AND `fk_semester` = :si GROUP BY `fk_course`";
        return $this->dm->getData($query, array(":cc" => $courseCode, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsByDepartment($departmentId, $semesterId)
    {
        $query = "SELECT 
                    lca.`id`, lca.`notes`, lca.`created_at`, lca.`updated_at`,
                    lca.`fk_department` AS department_id, d.`code` AS department_code, d.`name` AS department_name, d.`archived` AS department_archived, 
                    lca.`fk_staff`, sf.`number` AS staff_number, sf.`prefix` AS lecturer_prefix, sf.`gender`, 
                    sf.`first_name` AS lecturer_first_name, sf.`middle_name` AS lecturer_middle_name, sf.`last_name` AS lecturer_last_name, 
                    sf.`designation` AS lecturer_designation, sf.`role` AS lecturer_role, sf.`fk_department` AS lecturer_fk_department, 
                    lca.`fk_course` AS course_code, c.`name` AS course_name, c.`credit_hours` AS course_credit_hours, c.`contact_hours` AS course_contact_hours, c.`semester` AS course_semester,
                    c.`level` AS course_level, c.`archived` AS course_archived, c.`fk_category` AS course_category_id, cg.`name` AS course_category_name,
                    cg.`archived` AS course_category_archived, c.`fk_department` AS course_fk_department 
                FROM 
                    `lecturer_courses` AS lca 
                    JOIN `department` AS d ON lca.`fk_department` = d.`id` 
                    JOIN `staff` AS sf ON lca.`fk_staff` = sf.`number` 
                    JOIN `course` AS c ON lca.`fk_course` = c.`code` 
                    JOIN `course_category` AS cg ON c.`fk_category` = cg.`id` 
                    JOIN `semester` AS s ON lca.`fk_semester` = s.`id` 
                WHERE lca.`fk_department` = :di AND lca.`fk_semester` = :si";
        return $this->dm->getData($query, array(":di" => $departmentId, ":si" => $semesterId));
    }

    public function fetchAssignedSemesterCoursesByDepartment($departmentId)
    {
        $query = "SELECT 
                    lca.`id`, lca.`notes`, lca.`created_at`, lca.`updated_at`,
                    lca.`fk_department`, d.`code` AS department_code, d.`name` AS department_name, d.`archived` AS department_archived, 
                    lca.`fk_staff`, sf.`number` AS staff_number, sf.`prefix` AS lecturer_prefix, sf.`gender`, 
                    sf.`first_name` AS lecturer_first_name, sf.`middle_name` AS lecturer_middle_name, sf.`last_name` AS lecturer_last_name, 
                    sf.`designation` AS lecturer_designation, sf.`role` AS lecturer_role, sf.`fk_department` AS lecturer_fk_department, 
                    lca.`fk_course` AS course_code, c.`name` AS course_name, c.`credit_hours` AS course_credit_hours, c.`contact_hours` AS course_contact_hours, c.`semester` AS course_semester,
                    c.`level` AS course_level, c.`archived` AS course_archived, c.`fk_category` AS course_category_id, cg.`name` AS course_category_name,
                    cg.`archived` AS course_category_archived, c.`fk_department` AS course_fk_department 
                FROM 
                    `lecturer_courses` AS lca 
                    JOIN `department` AS d ON lca.`fk_department` = d.`id` 
                    JOIN `staff` AS sf ON lca.`fk_staff` = sf.`number` 
                    JOIN `course` AS c ON lca.`fk_course` = c.`code` 
                    JOIN `course_category` AS cg ON c.`fk_category` = cg.`id` 
                    JOIN `semester` AS s ON lca.`fk_semester` = s.`id` 
                WHERE lca.`fk_department` = :di AND s.`active` = 1";
        return $this->dm->getData($query, array(":di" => $departmentId));
    }

    public function fetchAssignedSemesterCoursesByDepartmentGroupByClass($departmentId)
    {
        $query = "SELECT 
                    lca.`id`, lca.`notes`, lca.`created_at`, lca.`updated_at`,
                    lca.`fk_department`, d.`code` AS department_code, d.`name` AS department_name, d.`archived` AS department_archived, 
                    lca.`fk_staff`, sf.`number` AS staff_number, sf.`prefix` AS lecturer_prefix, sf.`gender`, 
                    sf.`first_name` AS lecturer_first_name, sf.`middle_name` AS lecturer_middle_name, sf.`last_name` AS lecturer_last_name, 
                    sf.`designation` AS lecturer_designation, sf.`role` AS lecturer_role, sf.`fk_department` AS lecturer_fk_department, 
                    lca.`fk_course` AS course_code, c.`name` AS course_name, c.`credit_hours` AS course_credit_hours, c.`contact_hours` AS course_contact_hours, c.`semester` AS course_semester,
                    c.`level` AS course_level, c.`archived` AS course_archived, c.`fk_category` AS course_category_id, cg.`name` AS course_category_name,
                    cg.`archived` AS course_category_archived, c.`fk_department` AS course_fk_department 
                FROM 
                    `lecturer_courses` AS lca 
                    JOIN `department` AS d ON lca.`fk_department` = d.`id` 
                    JOIN `staff` AS sf ON lca.`fk_staff` = sf.`number` 
                    JOIN `course` AS c ON lca.`fk_course` = c.`code` 
                    JOIN `course_category` AS cg ON c.`fk_category` = cg.`id`
                WHERE lca.`fk_department` = :di
                GROUP BY lca.fk_course";
        return $this->dm->getData($query, array(":di" => $departmentId));
    }

    public function fetchAssignedSemesterCoursesWithNoDeadlinesByDepartment($departmentId)
    {
        $query = "SELECT 
                lca.`id`, 
                lca.`notes`, 
                lca.`created_at`, 
                lca.`updated_at`,
                
                -- Department info
                lca.`fk_department` AS department_id, 
                d.`code` AS department_code, 
                d.`name` AS department_name, 
                d.`archived` AS department_archived, 
                
                -- Lecturer info
                lca.`fk_staff`, 
                sf.`number` AS staff_number, 
                sf.`prefix` AS lecturer_prefix, 
                sf.`gender`, 
                sf.`first_name` AS lecturer_first_name, 
                sf.`middle_name` AS lecturer_middle_name, 
                sf.`last_name` AS lecturer_last_name, 
                sf.`designation` AS lecturer_designation, 
                sf.`role` AS lecturer_role, 
                sf.`fk_department` AS lecturer_fk_department, 
                
                -- Course info
                lca.`fk_course` AS course_code, 
                c.`name` AS course_name, 
                c.`credit_hours` AS course_credit_hours, 
                c.`contact_hours` AS course_contact_hours, 
                c.`semester` AS course_semester,
                c.`level` AS course_level, 
                c.`archived` AS course_archived, 
                c.`fk_category` AS course_category_id, 
                cg.`name` AS course_category_name,
                cg.`archived` AS course_category_archived, 
                c.`fk_department` AS course_fk_department 
                
            FROM `lecturer_courses` AS lca 
            JOIN `department` AS d ON lca.`fk_department` = d.`id` 
            JOIN `staff` AS sf ON lca.`fk_staff` = sf.`number` 
            JOIN `course` AS c ON lca.`fk_course` = c.`code` 
            JOIN `course_category` AS cg ON c.`fk_category` = cg.`id` 
            
            -- LEFT JOIN deadlines to check missing ones
            LEFT JOIN `deadlines` AS dl 
                ON dl.`fk_course` = lca.`fk_course`
               AND dl.`fk_semester` = lca.`fk_semester`
               AND dl.`fk_department` = lca.`fk_department`
               AND dl.`fk_staff` = lca.`fk_staff`
               
            WHERE 
                lca.`fk_department` = :di
                AND dl.`id` IS NULL";

        return $this->dm->getData($query, [":di" => $departmentId]);
    }


    public function fetchSemesterCourseAssignmentsGroupByDepartment($departmentId, $semesterId)
    {
        $query = "SELECT * FROM `lecturer_courses` WHERE `fk_department` = :di AND `fk_semester` = :si GROUP BY `fk_department`";
        return $this->dm->getData($query, array(":di" => $departmentId, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsBySemester($semesterId)
    {
        $query = "SELECT * FROM `lecturer_courses` WHERE `fk_semester` = :si";
        return $this->dm->getData($query, array(":si" => $semesterId));
    }

    public function fetchAllActiveStudents($departmentId = null, $archived = false)
    {
        $result = (new Student($this->db, $this->user, $this->pass))->fetch(key: "department", value: $departmentId, archived: $archived);
        return $result["success"] ? $result["data"] : 0;
    }

    public function fetchAllActiveStudentsExamAndAssessment(array $students, $semesterId = null)
    {
        if (isset($students) && !empty($students)) {
            $studentsData = $students;  // Extract the data array if it exists
            $responseData = [];

            foreach ($studentsData as $student) {  // Use reference to modify the original array
                $query = "CALL `calculate_gpa_cgpa`(:s, :m)";
                $params = array(":s" => $student["index_number"], ":m" => $semesterId);
                $result = $this->dm->getData($query, $params);
                if (!empty($result)) {
                    $student["gpa"] = $result[0]["gpa"] ? $result[0]["gpa"] : 0;
                    $student["cgpa"] = $result[0]["cgpa"] ? $result[0]["cgpa"] : 0;
                    $student["total_credit_hours"] = $result[0]["total_credits"] ? $result[0]["total_credits"] : 0;
                    $student["total_courses"] = $result[0]["total_courses"] ? $result[0]["total_courses"] : 0;
                } else {
                    $student["gpa"] = 0;
                    $student["cgpa"] = 0;
                    $student["total_credit_hours"] = 0;
                    $student["total_courses"] = 0;
                }
                $responseData[] = $student;  // Collect the modified student data
            }
            return $responseData;
        }
        return false;
    }


    public function fetchSemesterCourses($semester)
    {
        $courses = (new Course($this->db, $this->user, $this->pass))->fetch(key: "semester", value: $semester);
        return $courses;
    }

    public function fetchAllActivePrograms($departmentId = null, $archived = false)
    {
        return (new Program($this->db, $this->user, $this->pass))->fetch(key: "department", value: $departmentId, archived: $archived);
    }

    // For classes

    public function fetchAllActiveClasses($departmentId = null, $archived = false)
    {
        return (new Classes($this->db, $this->user, $this->pass))->fetch(key: "department", value: $departmentId, archived: $archived)["data"];
    }

    public function fetchAllCummulativeProgramsDetails($departmentId = null, $archived = false)
    {
        $query = "SELECT 
                    p.`id` AS `id`,
                    p.`name` AS `name`,
                    p.`category` AS `category`,
                    p.`group` AS `group`,
                    p.`code` AS `code`,
                    p.`index_code` AS `index_code`,
                    f.`name` AS `type`,
                    d.`id` AS `department_id`,
                    d.`name` AS `department_name`,
                    p.`duration`,
                    p.`dur_format`, 
                    p.`num_of_semesters`,
                    p.regular AS `regular`,
                    p.weekend AS `weekend`,
                    p.`archived` AS `status`,

                    CASE 
                        WHEN p.archived = 1 THEN 'archived'
                        ELSE 'active'
                    END AS status,
                    
                    COUNT(DISTINCT s.index_number) AS total_students,
                    COUNT(DISTINCT cls.code) AS total_classes,
                    COUNT(DISTINCT cur.fk_course) AS total_courses,
                    SUM(DISTINCT cr.credit_hours) AS total_credit_hours

                FROM programs p
                INNER JOIN department d ON d.id = p.department
                LEFT JOIN student s ON s.fk_program = p.id
                LEFT JOIN class cls ON cls.fk_program = p.id
                LEFT JOIN curriculum cur ON cur.fk_program = p.id
                LEFT JOIN course cr ON cr.code = cur.fk_course 
                LEFT JOIN forms f ON f.id = p.type 

                WHERE d.id = :d AND p.archived = :a  -- Replace ? with the desired department ID

                GROUP BY 
                    p.id, p.name, p.code, p.type, d.name, p.duration, p.num_of_semesters, p.archived;";
        $params = array(":d" => $departmentId, ":a" => $archived);
        return $this->dm->getData($query, $params);
    }

    public function fetchCoursesNotInCurriculum($programId, $departmentId = null, $archived = false)
    {
        $query = "SELECT DISTINCT 
                        c.`code`, 
                        c.`name`, 
                        c.`credit_hours`, 
                        c.`contact_hours`, 
                        c.`semester`, 
                        c.`level`, 
                        c.`archived`, 
                        c.`fk_category` AS category_id, 
                        cg.`name` AS category, 
                        c.`fk_department` AS fk_department, 
                        d.`name` AS department_name
                    FROM `course` AS c
                    JOIN `course_category` AS cg ON c.`fk_category` = cg.`id`
                    JOIN `department` AS d ON c.`fk_department` = d.`id`
                    LEFT JOIN `curriculum` AS cu 
                        ON cu.`fk_course` = c.`code` 
                        AND cu.`fk_program` = :p
                        AND c.`archived` = :ar
                    WHERE cu.`fk_course` IS NULL
                    AND d.`id` = :d";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId, ":p" => $programId));
    }

    public function fetchProgramCurriculum($programId, $departmentId = null, $archived = false)
    {
        $query = "SELECT DISTINCT c.`code`, c.`name`, c.`credit_hours`, c.`contact_hours`, c.`semester`, c.`level`, c.`archived`, 
                `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `fk_department`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `curriculum` AS cu 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND cu.`fk_course` = c.`code` AND cu.`fk_program` = :p AND d.`id` = :d AND cu.`archived` = :ar";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId, ":p" => $programId));
    }

    public function fetchProgramClasses($programId)
    {
        $query = "SELECT 
                    cls.`code`, cls.`year`, COUNT(DISTINCT st.index_number) AS total_students,

                    -- Assuming status is derived from class archived flag (set your actual logic here)
                    CASE 
                        WHEN cls.archived = 1 THEN 'Inactive'
                        ELSE 'Active'
                    END AS status,

                    CONCAT(sf.first_name, ' ', sf.last_name) AS lecturer

                FROM class cls
                LEFT JOIN student st ON st.fk_class = cls.code
                LEFT JOIN staff sf ON sf.number = cls.fk_staff

                WHERE cls.fk_program = :p

                GROUP BY 
                    cls.code, cls.archived, sf.first_name, sf.last_name;";
        return $this->dm->getData($query, array(":p" => $programId));
    }

    public function fetchProgramStudents($programId)
    {
        $query = "SELECT 
                    DISTINCT s.`index_number` AS id, s.`first_name`, s.`last_name`, s.`gender`, s.`email`, s.`phone_number`, s.`photo`, 
                    CONCAT(s.`prefix`, ' ', s.`first_name`, ' ', s.`last_name`) AS full_name, 
                    cls.`code` AS class_code, l.`level`, p.`name` AS program_name, p.`code` AS program_code

                FROM `student` s
                LEFT JOIN `level` l ON l.`fk_student` = s.`index_number`
                LEFT JOIN `department` d ON d.`id` = s.`fk_department`
                LEFT JOIN `class` cls ON cls.`code` = s.`fk_class`
                LEFT JOIN `programs` p ON p.`id` = s.`fk_program`

                WHERE s.fk_program = :p AND s.archived = 0 AND l.`active` = 1";
        return $this->dm->getData($query, array(":p" => $programId));
    }

    public function fetchProgramCourses($programId, $departmentId = null)
    {
        $query = "SELECT 
                    c.`code`, c.`name`, c.`credit_hours`, c.`contact_hours`, c.`semester`, c.`level`, c.`archived`, 
                    `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `fk_department`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `curriculum` AS cu 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND cu.`fk_course` = c.`code` AND cu.`fk_program` = :p AND d.`id` = :d AND c.`archived` = 0";
        return $this->dm->getData($query, array(":p" => $programId, ":d" => $departmentId));
    }

    public function fetchSemesterCourseResults($semesterId, $courseCode, $classCode)
    {
        // fetch exam results and check if is project based
        $query = "SELECT r.`exam_score_weight`, r.`project_score_weight`, r.`assessment_score_weight`, r.`project_based`, cr.`name` AS course, sm.`name` AS semester
                FROM `exam_results` AS r 
                JOIN `course` AS cr ON r.`fk_course` = cr.`code` 
                JOIN `class` AS cs ON r.`fk_class` = cs.`code` 
                JOIN `semester` AS sm ON r.`fk_semester` = sm.`id` 
                WHERE r.`fk_semester` = :sm AND r.`fk_course` = :cr AND r.`fk_class` = :cs";
        $params = [":sm" => $semesterId, ":cr" => $courseCode, ":cs" => $classCode];
        $results = $this->dm->getData($query, $params);

        if (empty($results)) {
            return ["success" => false, "message" => "No records found"];
        }

        // fetch results body
        $query2 = "SELECT sc.`fk_student` AS student_id, sc.`fk_course` AS course_code, sc.`credit_hours` AS course_credit_hours, 
                    sc.`level` AS course_level, sc.`fk_semester` AS semester_id, sc.semester, sr.`continues_assessments_score` AS ass_score, 
                    sr.`project_score`, sr.`exam_score`, sr.`final_score`, sr.`grade`, sr.`gpa` 
                    FROM `student` AS st, `student_courses` AS sc, `student_results` AS sr 
                    WHERE st.`index_number` = sc.`fk_student` AND st.`index_number` = sr.`fk_student` AND 
                        sc.`fk_course` = sr.`fk_course` AND sc.`fk_semester` = sr.`fk_semester` AND 
                        sc.`fk_course` = :cr AND sc.`fk_semester` = :sm AND st.`fk_class` = :cs";
        $params2 = [":sm" => $semesterId, ":cr" => $courseCode, ":cs" => $classCode];
        $results2 = $this->dm->getData($query2, $params2);

        if (empty($results2)) {
            return ["success" => false, "message" => "No records found in uploaded data."];
        }

        // Determine if the course is project based (assume all results have the same course)
        $isProjectBased = (bool) $results[0]['project_based'];

        $response = [];
        $response["success"] = true;

        if ($isProjectBased) {
            $response["data"] = [
                "project_based" => true,
                "headers" => ["Student ID", "Exam Score (40%)", "Project Score (20%)", "Ass. Score (40%)", "ACH Mark", "Grade"]
            ];
        } else {
            $response["data"] = [
                "project_based" => false,
                "headers" => ["Student ID", "Exam Score (60%)", "Ass. Score (40%)", "ACH Mark", "Grade"]
            ];
        }
        $response["data"]["values"] = $results;
        $response["data"]["body"] = $results2;
        return $response;
    }

    public function extractResultExcelData($data)
    {
        // target Location
        $targetPath = UPLOAD_DIR . "/results/" . $data["file_name"];
        $endRow = 0;
        $startRow = 13;

        $Reader = new Xlsx();
        $spreadSheet = $Reader->load($targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetArray = $excelSheet->toArray();

        if ($endRow == 0) $endRow = count($spreadSheetArray);
        if ($startRow > 1) $startRow -= 1;

        $dataset = array();

        if ($data["project_based"] == 0) {
            for ($i = $startRow; $i <= $endRow - 1; $i++) {
                $studentId = $spreadSheetArray[$i][0];
                $examScore = $spreadSheetArray[$i][11];
                $assessmentScore = $spreadSheetArray[$i][12];
                $final_score = $spreadSheetArray[$i][13];

                if (!$studentId || !$examScore || !$assessmentScore) {
                    continue;
                }

                array_push($dataset, array(
                    "student_id" => $studentId,
                    "exam_score" => $examScore,
                    "assessment_score" => $assessmentScore,
                    "final_score" => $final_score,
                ));
            }
        } else {
            for ($i = $startRow; $i <= $endRow - 1; $i++) {
                $studentId = $spreadSheetArray[$i][0];
                $examScore = $spreadSheetArray[$i][11];
                $projectScore = $spreadSheetArray[$i][12];
                $assessmentScore = $spreadSheetArray[$i][13];
                $final_score = $spreadSheetArray[$i][14];

                if (!$studentId || !$examScore || !$assessmentScore) {
                    continue;
                }

                array_push($dataset, array(
                    "student_id" => $studentId,
                    "exam_score" => $examScore,
                    "project_score" => $projectScore,
                    "assessment_score" => $assessmentScore,
                    "final_score" => $final_score,
                ));
            }
        }

        return $dataset;
    }

    public function setApprovedStudentsSemesterCourseResults($data)
    {
        $extracted_data = $this->extractResultExcelData($data);
        if (empty($extracted_data)) return array("success" => false, "message" => "No data found in the file!");

        $error_list = [];
        $errorsEncountered = 0;
        $successEncountered = 0;

        // Check if class exists
        $classQuery = "SELECT `code` FROM `class` WHERE `code` = :ci";
        $classData = $this->dm->getData($classQuery, array(":ci" => $data["class_code"]));
        if (empty($classData)) {
            array_push($error_list, "Class with code {$data['class_code']} does not exist!");
            $errorsEncountered += 1;
            return array("success" => false, "message" => implode(", ", $error_list));
        }

        // Check if course exists
        $courseQuery = "SELECT `code` FROM `course` WHERE `code` = :ci";
        $courseData = $this->dm->getData($courseQuery, array(":ci" => $data["course_code"]));
        if (empty($courseData)) {
            array_push($error_list, "Course with code {$data['course_code']} does not exist!");
            $errorsEncountered += 1;
            return array("success" => false, "message" => implode(", ", $error_list));
        }

        // Check if semester exists
        $semesterQuery = "SELECT `id` FROM `semester` WHERE `id` = :si";
        $semesterData = $this->dm->getData($semesterQuery, array(":si" => $data["semester_id"]));
        if (empty($semesterData)) {
            array_push($error_list, "Semester with ID {$data['semester_id']} does not exist!");
            $errorsEncountered += 1;
            return array("success" => false, "message" => implode(", ", $error_list));
        }

        // Check if staff exists
        $staffQuery = "SELECT `number` FROM `staff` WHERE `number` = :si";
        $staffData = $this->dm->getData($staffQuery, array(":si" => $_SESSION["staff"]["number"]));
        if (empty($staffData)) {
            array_push($error_list, "Staff with Number {$_SESSION["staff"]["number"]} does not exist!");
            $errorsEncountered += 1;
            return array("success" => false, "message" => implode(", ", $error_list));
        }

        // $error_list = [];

        $output = [];
        $count = 0;

        // add results for each applicant to db
        foreach ($extracted_data as $result) {
            // Check if student exists
            $studentQuery = "SELECT `index_number` FROM `student` WHERE `index_number` = :si";
            $studentData = $this->dm->getData($studentQuery, array(":si" => $result["student_id"]));

            if (empty($studentData)) {
                array_push($error_list, "Student with ID {$result['student_id']} does not exist!");
                $errorsEncountered += 1;
                continue;
            }

            // Prepare insert query and params
            $resultInsertQuery = "";
            $resultInsertParams = [];

            // check if student result is project based and validate scores
            if ($data["project_based"] == 1) {
                $resultInsertQuery = "UPDATE `student_results` SET 
                                    `continues_assessments_score` = :cas, 
                                    `project_score` = :ps, 
                                    `exam_score` = :es,
                                    `final_score` = :fs 
                                    WHERE `fk_student` = :i AND `fk_course` = :c AND `fk_semester` = :s";
                $resultInsertParams = array(
                    ":i" => $result["student_id"],
                    ":c" => $data["course_code"],
                    ":s" => $data["semester_id"],
                    ":es" => $result["exam_score"],
                    ":ps" => $result["project_score"],
                    ":cas" => $result["assessment_score"],
                    ":fs" => $result["final_score"]
                );
            } else {
                $resultInsertQuery = "UPDATE `student_results` SET 
                                        `continues_assessments_score` = :cas, 
                                        `exam_score` = :es, 
                                        `final_score` = :fs  
                                WHERE `fk_student` = :i AND `fk_course` = :c AND `fk_semester` = :s";
                $resultInsertParams = array(
                    ":i" => $result["student_id"],
                    ":c" => $data["course_code"],
                    ":s" => $data["semester_id"],
                    ":es" => $result["exam_score"],
                    ":cas" => $result["assessment_score"],
                    ":fs" => $result["final_score"]
                );
            }

            $resultInsertOutput = $this->dm->getData($resultInsertQuery, $resultInsertParams);

            if (!$resultInsertOutput) {
                $errorsEncountered += 1;
                array_push($error_list, "Failed to add result for student ID {$result['student_id']} in course {$result['course_code']}!");
            } else {
                $successEncountered += 1;
            }
            $count++;
        }

        $output = array(
            "success" => false,
            "message" => "Successfully updated {$successEncountered} results and {$errorsEncountered} errors encountered! " . (($errorsEncountered > 0 && count($error_list) > 0) ? implode(",", $error_list) : "Check the error list for more details!"),
            "errors" => $error_list
        );

        if ($successEncountered) {
            // Recalculate GPA for this semester
            $this->dm->inputData(
                "CALL recalc_grades_by_semester_course(:sem, :coc)",
                [":sem" => $data["semester_id"], ":coc" => $data["course_code"]]
            );
            $output["success"] = true;
        }

        return $output;
    }

    public function approveSemesterCourseResults($semesterId, $courseCode, $classCode)
    {
        // fetch exam results and check if is project based
        $query = "SELECT r.`fk_course` AS course_code, r.`fk_semester` AS semester_id, r.`fk_class` AS class_code, 
                    r.`exam_score_weight`, r.`project_score_weight`, r.`assessment_score_weight`, 
                    r.`project_based`, cr.`name` AS course, sm.`name` AS semester, r.`file_name` 
                    FROM `exam_results` AS r 
                    JOIN `course` AS cr ON r.`fk_course` = cr.`code` 
                    JOIN `class` AS cs ON r.`fk_class` = cs.`code` 
                    JOIN `semester` AS sm ON r.`fk_semester` = sm.`id` 
                    WHERE r.`fk_semester` = :sm AND r.`fk_course` = :cr AND r.`fk_class` = :cs";
        $params = [":sm" => $semesterId, ":cr" => $courseCode, ":cs" => $classCode];
        $results = $this->dm->getData($query, $params);

        if (empty($results)) {
            return ["success" => false, "message" => "No records found"];
        }

        // fetch results body
        $query2 = "UPDATE `exam_results` SET `status` = 'approved' WHERE `fk_course` = :cr AND `fk_semester` = :sm AND `fk_class` = :cs";
        $params2 = [":sm" => $semesterId, ":cr" => $courseCode, ":cs" => $classCode];
        $results2 = $this->dm->inputData($query2, $params2);

        if (!$results2) {
            return ["success" => false, "message" => "Failed to approve exam result!"];
        }

        $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Exam Results", "Approved exam results for {$classCode} - {$courseCode} in {$results[0]['semester']}");

        // set students semester course results
        $approvedResults = $this->setApprovedStudentsSemesterCourseResults($results[0]);
        return $approvedResults;
        return array("success" => true, "message" => "Successfully approved exam result!", "data" => $approvedResults);
    }

    // Create functions for to perform CRUD operations on lecturers. The adding of a lecturer should send an SMS and also email use the sms and email functions
    // CRUD operations for lecturers

    public function addLecturer($data)
    {
        // Insert lecturer into staff table
        $query = "INSERT INTO `staff` (`number`, `prefix`, `gender`, `first_name`, `middle_name`, `last_name`, `designation`, `role`, `fk_department`, `email`, `phone_number`, `archived`)
                  VALUES (:number, :prefix, :gender, :first_name, :middle_name, :last_name, :designation, 'lecturer', :fk_department, :email, :phone_number, 0)";
        $params = [
            ":number" => $data["number"],
            ":prefix" => $data["prefix"],
            ":gender" => $data["gender"],
            ":first_name" => $data["first_name"],
            ":middle_name" => $data["middle_name"],
            ":last_name" => $data["last_name"],
            ":designation" => $data["designation"],
            ":fk_department" => $data["fk_department"],
            ":email" => $data["email"],
            ":phone_number" => $data["phone_number"]
        ];
        $result = $this->dm->inputData($query, $params);

        if ($result) {
            // Create a user accound for the lecturer using email and a default password 123@Lecturer
            $userQuery = "INSERT INTO `sys_users` (`first_name`, `last_name`, `username`, `password`, `role`, `type`)
                          VALUES (:first_name, :last_name, :username, :password, 'lecturer', 'staff')";

            $userParams = [
                ":first_name" => $data["first_name"],
                ":last_name" => $data["last_name"],
                ":username" => $data["email"],
                ":password" => password_hash("123@Lecturer", PASSWORD_DEFAULT), // Default password
                ":role" => "lecturer",
                ":user" => "staff"
            ];

            $userResult = $this->dm->inputData($userQuery, $userParams);

            if (!$userResult) {
                // If user creation fails, rollback the staff insertion
                $this->dm->inputData("DELETE FROM `staff` WHERE `number` = :number", [":number" => $data["number"]]);
                return ["success" => false, "message" => "Failed to create user account for lecturer."];
            }

            // Send SMS and Email
            $smsMessage = "Dear {$data["prefix"]} {$data["first_name"]}, you have been added to the RMU's staff portal. kindly visit your mailbox for your login credentials.";
            $emailSubject = "Lecturer Registration";
            $emailBody = "Hello {$data["prefix"]} {$data["first_name"]} {$data["last_name"]},<br><br>You have been successfully registered as a lecturer.<br><br>";
            $emailBody .= "Your login credentials are:<br>";
            $emailBody .= "Username: {$data["email"]}<br>";
            $emailBody .= "Password: 123@Lecturer<br><br>";
            $emailBody .= "Please log in to the staff portal to change your password and complete your profile.<br><br>";
            $emailBody .= "Thank you,<br>RMU Staff Portal";

            if (method_exists($this->expose, 'sendSMS')) {
                $this->expose->sendSMS($data["phone_number"], $smsMessage);
            }

            if (method_exists($this->expose, 'sendEmail')) {
                $this->expose->sendEmail($data["email"], $emailSubject, $emailBody);
            }

            $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Lecturer", "Added lecturer {$data["number"]}");
            return ["success" => true, "message" => "Lecturer added successfully."];
        }

        return ["success" => false, "message" => "Failed to add lecturer."];
    }
}
