<?php

namespace Src\Controller;

use Exception;
use Src\System\DatabaseMethods;
use Src\Controller\ExposeDataController;
use PhpOffice\PhpWord\TemplateProcessor;
use Src\Base\Log;
use Src\Core\Classes;
use Src\Core\Course;
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

    public function __construct($db, $user, $pass)
    {
        $this->db = $db;
        $this->user = $user;
        $this->pass = $pass;
        $this->dm = new DatabaseMethods($db, $user, $pass);
        $this->log = new Log($db, $user, $pass);
        $this->expose = new ExposeDataController($db, $user, $pass);
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
        $select = "";
        $from = "";
        $where = "";
        $params = array(":ar" => $archived);

        if ($departmentId) {
            $select = " , `fk_department` AS `fk_department`, d.`name` AS `department_name` ";
            $from .= ", `department` AS d ";
            $where .= " AND c.`fk_department` = d.`id` AND d.`id` = :d ";
            $params[":d"] = $departmentId;
        }

        if ($semester) {
            $where .= " AND c.`semester` = :s ";
            $params[":s"] = $semester;
        }

        $query = "SELECT c.`code`, c.`name`, c.`credit_hours`, c.`contact_hours`, c.`semester`, c.`level`, c.`archived`, 
                `fk_category` AS category_id, cg.`name` AS category {$select}
                FROM `course` AS c, `course_category` AS cg {$from}
                WHERE c.`fk_category` = cg.`id` AND c.`archived` = :ar {$where} ORDER BY c.`code` ASC";
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

    public function fetchPendingDeadlines($departmentId = null, $semesterId = null, $archived = false)
    {
        $query = "SELECT dl.`id`, dl.`note`, dl.`date`, dl.`status`, dl.`created_at`, dl.`updated_at`, 
                 dl.`fk_course` AS course_code, c.`name` AS course_name, c.`credit_hours`, c.`contact_hours`, c.`semester` AS course_semester, 
                 c.`level` AS course_level, c.`archived` AS course_status, c.`fk_category` AS category_id, cg.`name` AS category, 
                 dl.`fk_staff` AS staff_number, CONCAT(sf.`prefix`, ' ', sf.`first_name`, ' ', sf.`last_name`) AS lecturer_name 
              FROM `deadlines` AS dl 
              JOIN `department` AS d ON dl.`fk_department` = d.`id` 
              JOIN `staff` AS sf ON dl.`fk_staff` = sf.`number` 
              JOIN `course` AS c ON dl.`fk_course` = c.`code` 
              JOIN `semester` AS s ON dl.`fk_semester` = s.`id` 
              JOIN `course_category` AS cg ON c.`fk_category` = cg.`id` 
              WHERE dl.`fk_department` = :d AND dl.`fk_semester` = :s AND c.`archived` = :ar ORDER BY (dl.`status` = 'pending') DESC, dl.`date` ASC";
        return $this->dm->getData($query, array(":d" => $departmentId, ":s" => $semesterId, ":ar" => $archived));
    }

    public function fetchUpcomingDeadlines($departmentId = null, $archived = false)
    {
        $query = "SELECT `code`, `name`, `credit_hours`, `contact_hours`, `semester`, `level`, `archived`, 
                `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `fk_department`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `course_assignments` AS ca 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND d.`id` = :d AND c.`archived` = :ar AND ca.`fk_course` = c.`id` AND ca.`fk_user` = :u AND ca.`submitted` = 0 AND ca.`deadline_date` > NOW()";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId));
    }

    public function fetchRecentActivities($departmentId = null, $archived = false, $limit = 4)
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
                WHERE s.`fk_department` = d.`id` AND d.`id` = :d AND s.`archived` = :ar AND s.`role` = 'lecturer'";
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
            $lecturerData = (new Staff($this->db, $this->user, $this->pass))->fetch(key: "number", value: $data["lecturer"], archived: false)[0];
            // Fetch course details
            $courseData = (new Course($this->db, $this->user, $this->pass))->fetch(key: "code", value: $course, archived: false)[0];

            // Check if the course is already assigned to any lecturer or the same lecturer
            $query1 = "SELECT * FROM `lecturer_course_assignments` WHERE `fk_department` = :dt AND `fk_course` = :cc AND `fk_semester` = :si";
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

            $query2 = "INSERT INTO `lecturer_course_assignments` (`fk_department`, `fk_staff`, `fk_course`, `fk_semester`, `notes`) VALUES (:di, :sn, :cc, :si, :nt)";
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
            $studentData = (new Student($this->db, $this->user, $this->pass))->fetch(key: "index_number", value: $data["student"], archived: false)[0];
            // Fetch course details
            $courseData = (new Course($this->db, $this->user, $this->pass))->fetch(key: "code", value: $course, archived: false)[0];

            // Check if the course is already assigned to any student or the same student
            $query1 = "SELECT * FROM `student_course_assignments` WHERE `fk_student` = :st AND `fk_course` = :cc";
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

            $query3 = "INSERT INTO `student_course_assignments` (`fk_student`, `fk_course`, `fk_semester`, `notes`, `credit_hours`, `level`, `semester`) 
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
            $classData = (new Classes($this->db, $this->user, $this->pass))->fetch(key: "code", value: $data["class"], archived: false)[0];
            // Fetch course details
            $courseData = (new Course($this->db, $this->user, $this->pass))->fetch(key: "code", value: $course, archived: false)[0];

            // Check if the course is already assigned to any class or the same class
            $query1 = "SELECT * FROM `section` WHERE `fk_class` = :cs AND `fk_course` = :cc";
            $result1 = $this->dm->getData($query1, array(":cs" => $data["class"], ":cc" => $course));

            if ($result1) {
                $errorEncountered++;
                if ($result1[0]["fk_class"] == $data["class"]) {
                    array_push($errors, "{$courseData["name"]} ({$courseData["code"]}) is already assigned to {$data["class"]}.");
                } else {
                    array_push($errors, "{$courseData["name"]} ({$courseData["code"]}) is already assigned to another class.");
                }
                continue;
            }

            // fetch class course details from curriculum table using fk_class and fk_course
            $query2 = "SELECT * FROM `curriculum` WHERE `fk_program` = :pg AND `fk_course` = :co";
            $result2 = $this->dm->getData($query2, array(":pg" => $classData["program_id"], ":co" => $course));

            if (empty($result2)) {
                $errorEncountered++;
                array_push(
                    $errors,
                    "Curricullum not set for this class {$data["class"]}."
                );
                continue;
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

            // fetch all students in the class
            $students = (new Student($this->db, $this->user, $this->pass))->fetch(key: "class", value: $data["class"], archived: false);
            // assign course and semester to all students in the class in the student_course_assignments table
            foreach ($students as $student) {
                $query4 = "INSERT INTO `student_course_assignments` (`fk_student`, `fk_course`, `fk_semester`, `notes`, `credit_hours`, `level`, `semester`) 
                            VALUES (:si, :co, :st, :nt, :ch, :lv, :sm)";
                $result4 = $this->dm->inputData(
                    $query4,
                    array(
                        ":si" => $student["index_number"],
                        ":co" => $course,
                        ":st" => $data["semester"],
                        ":nt" => $data["notes"],
                        ":ch" => $courseData[0]["credit_hours"],
                        ":lv" => $courseData[0]["level"],
                        ":sm" => $courseData[0]["semester"]
                    )
                );
                if (!$result4) {
                    $errorEncountered++;
                    array_push($errors, "Failed to assign course {$courseData["name"]} ({$courseData["code"]}) to student {$student["index_number"]}.");
                    $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Course Assignment", "Failed to assign {$courseData["name"]} ({$course}) to student {$student["index_number"]}.");
                } else {
                    $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Course Assignment", "Assigned {$courseData["name"]} ({$course}) to student {$student["index_number"]}.");
                }
            }

            $successEncountered++;
            array_push($coursesAssigned, $course);
            $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Course Assignment", "Assigned {$data["class"]} to {$courseData["name"]} ({$course})");
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

    public function fetchSemesterCourseAssignmentsByLecturer($lecturerId, $semesterId)
    {
        $query = "SELECT * FROM `lecturer_course_assignments` WHERE `fk_staff` = :sn AND `fk_semester` = :si";
        return $this->dm->getData($query, array(":sn" => $lecturerId, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsGroupByLecturer($departmentId, $semesterId)
    {
        $query = "SELECT * FROM `lecturer_course_assignments` WHERE `fk_department` = :di AND `fk_semester` = :si GROUP BY `fk_staff`";
        return $this->dm->getData($query, array(":di" => $departmentId, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsByCourse($courseCode, $semesterId)
    {
        $query = "SELECT * FROM `lecturer_course_assignments` WHERE `fk_course` = :cc AND `fk_semester` = :si";
        return $this->dm->getData($query, array(":cc" => $courseCode, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsGroupByCourse($courseCode, $semesterId)
    {
        $query = "SELECT * FROM `lecturer_course_assignments` WHERE `fk_course` = :cc AND `fk_semester` = :si GROUP BY `fk_course`";
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
                    `lecturer_course_assignments` AS lca 
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
                    `lecturer_course_assignments` AS lca 
                    JOIN `department` AS d ON lca.`fk_department` = d.`id` 
                    JOIN `staff` AS sf ON lca.`fk_staff` = sf.`number` 
                    JOIN `course` AS c ON lca.`fk_course` = c.`code` 
                    JOIN `course_category` AS cg ON c.`fk_category` = cg.`id` 
                    JOIN `semester` AS s ON lca.`fk_semester` = s.`id` 
                WHERE lca.`fk_department` = :di AND s.`active` = 1";
        return $this->dm->getData($query, array(":di" => $departmentId));
    }

    public function fetchAssignedSemesterCoursesWithNoDeadlinesByDepartment($departmentId)
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
                    `lecturer_course_assignments` AS lca 
                    JOIN `department` AS d ON lca.`fk_department` = d.`id` 
                    JOIN `staff` AS sf ON lca.`fk_staff` = sf.`number` 
                    JOIN `course` AS c ON lca.`fk_course` = c.`code` 
                    JOIN `course_category` AS cg ON c.`fk_category` = cg.`id` 
                    LEFT JOIN `deadlines` AS dl 
                        ON dl.`fk_department` = lca.`fk_department`
                        AND dl.`fk_staff` = lca.`fk_staff`
                        AND dl.`fk_course` = lca.`fk_course`
                WHERE 
                    lca.`fk_department` = :di AND dl.`id` IS NULL";
        return $this->dm->getData($query, array(":di" => $departmentId));
    }

    public function fetchSemesterCourseAssignmentsGroupByDepartment($departmentId, $semesterId)
    {
        $query = "SELECT * FROM `lecturer_course_assignments` WHERE `fk_department` = :di AND `fk_semester` = :si GROUP BY `fk_department`";
        return $this->dm->getData($query, array(":di" => $departmentId, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsBySemester($semesterId)
    {
        $query = "SELECT * FROM `lecturer_course_assignments` WHERE `fk_semester` = :si";
        return $this->dm->getData($query, array(":si" => $semesterId));
    }

    public function fetchAllActiveStudents($departmentId = null, $archived = false)
    {
        $students = (new Student($this->db, $this->user, $this->pass))->fetch(key: "department", value: $departmentId, archived: $archived);
        return $students;
    }

    public function fetchSemesterCourses($semester)
    {
        $courses = (new Course($this->db, $this->user, $this->pass))->fetch(key: "semester", value: $semester);
        return $courses;
    }

    public function fetchAllActiveClasses($departmentId = null, $archived = false)
    {
        $classes = (new Classes($this->db, $this->user, $this->pass))->fetch(key: "department", value: $departmentId, archived: $archived);
        return $classes;
    }
}
