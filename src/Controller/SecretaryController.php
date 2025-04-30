<?php

namespace Src\Controller;

use Exception;
use Src\System\DatabaseMethods;
use Src\Controller\ExposeDataController;
use PhpOffice\PhpWord\TemplateProcessor;
use Src\Base\Log;
use Src\Core\Course;
use Src\Core\Staff;

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

    public function fetchActiveCourses($departmentId = null, $semester = null, $archived = false)
    {
        $select = "";
        $from = "";
        $where = "";
        $params = array(":ar" => $archived);

        if ($departmentId) {
            $select = " , `fk_department` AS `department_id`, d.`name` AS `department_name` ";
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
                `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `department_id`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `course_assignments` AS ca 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND d.`id` = :d AND c.`archived` = :ar AND ca.`fk_course` = c.`id` AND ca.`fk_user` = :u";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId));
    }

    public function fetchSubmittedCourses($departmentId = null, $archived = false)
    {
        $query = "SELECT `code`, `name`, `credit_hours`, `contact_hours`, `semester`, `level`, `archived`, 
                `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `department_id`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `course_assignments` AS ca 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND d.`id` = :d AND c.`archived` = :ar AND ca.`fk_course` = c.`id` AND ca.`fk_user` = :u AND ca.`submitted` = 1";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId));
    }

    public function fetchPendingDeadlines($departmentId = null, $archived = false)
    {
        $query = "SELECT `code`, `name`, `credit_hours`, `contact_hours`, `semester`, `level`, `archived`, 
                `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `department_id`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `course_assignments` AS ca 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND d.`id` = :d AND c.`archived` = :ar AND ca.`fk_course` = c.`id` AND ca.`fk_user` = :u AND ca.`submitted` = 0";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId));
    }

    public function fetchUpcomingDeadlines($departmentId = null, $archived = false)
    {
        $query = "SELECT `code`, `name`, `credit_hours`, `contact_hours`, `semester`, `level`, `archived`, 
                `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `department_id`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `course_assignments` AS ca 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND d.`id` = :d AND c.`archived` = :ar AND ca.`fk_course` = c.`id` AND ca.`fk_user` = :u AND ca.`submitted` = 0 AND ca.`deadline_date` > NOW()";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId));
    }

    public function fetchRecentActivities($departmentId = null, $archived = false)
    {
        $query = "SELECT `code`, `name`, `credit_hours`, `contact_hours`, `semester`, `level`, `archived`, 
                `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `department_id`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `course_assignments` AS ca 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND d.`id` = :d AND c.`archived` = :ar AND ca.`fk_course` = c.`id` AND ca.`fk_user` = :u AND ca.`submitted` = 1 AND ca.`deadline_date` < NOW()";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId));
    }

    public function fetchRegisteredCourses($departmentId = null, $archived = false)
    {
        $query = "SELECT `code`, `name`, `credit_hours`, `contact_hours`, `semester`, `level`, `archived`, 
                `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `department_id`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `course_assignments` AS ca 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND d.`id` = :d AND c.`archived` = :ar AND ca.`fk_course` = c.`id` AND ca.`fk_user` = :u AND ca.`submitted` = 1 AND ca.`deadline_date` < NOW()";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId));
    }

    public function fetchRunningSemesterCourses($departmentId = null, $archived = false, $semesterId = null)
    {
        $query = "SELECT c.`code`, c.`name`, c.`credit_hours`, c.`contact_hours`, c.`semester`, c.`level`, c.`archived`, 
                `fk_category` AS category_id, cg.`name` AS category, `fk_department` AS `department_id`, d.`name` AS `department_name` 
                FROM `course` AS c, `course_category` AS cg, `department` AS d, `course_assignments` AS ca 
                WHERE c.`fk_category` = cg.`id` AND c.`fk_department` = d.`id` AND d.`id` = :d AND c.`archived` = :ar AND ca.`fk_course` = c.`id` AND ca.`fk_user` = :u AND ca.`submitted` = 1 AND ca.`deadline_date` < NOW() AND s.id = :s";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId, ":s" => $semesterId));
    }

    public function fetchCurrentSemester()
    {
        $query = "SELECT s.*, a.`name` AS `academic_year`, a.`start_month`, a.`end_month`, a.`start_year`, a.`end_year`, a.`active` AS `academic_year_status`
                FROM `semester` AS s, `academic_year` AS a 
                WHERE s.`fk_academic_year` = a.`id` AND s.`active` = 1 AND a.`active` = 1";
        return $this->dm->getData($query);
    }

    public function fetchAllLecturers($departmentId = null, $archived = false)
    {
        $query = "SELECT s.`number`, s.`prefix`, s.`gender`, s.`first_name`, s.`middle_name`, s.`last_name`, s.`designation`, s.`role`,
                d.`name` AS `department_name`, d.`id` AS `department_id` 
                FROM `staff` AS s, `department` AS d 
                WHERE s.`fk_department` = d.`id` AND d.`id` = :d AND s.`archived` = :ar AND s.`role` = 'lecturer'";
        return $this->dm->getData($query, array(":ar" => $archived, ":d" => $departmentId));
    }

    public function assignCourseToLecturer($courseCode, $lecturerId, $semesterId, $departmentId, $notes)
    {
        // Check if the course is already assigned to any lecturer or the same lecturer for the same semester
        $query1 = "SELECT * FROM `lecture_course_assignments` WHERE `course_code` = :cc AND `semester_id` = :si";
        $result1 = $this->dm->getData($query1, array(":cc" => $courseCode, ":si" => $semesterId));
        if ($result1) {
            // Check if the course is already assigned to the same lecturer for the same semester
            if ($result1[0]['lecturer_id'] == $lecturerId) {
                return array(
                    "success" => false,
                    "message" => "Course is already assigned to this lecturer for the same semester."
                );
            }
            return array(
                "success" => false,
                "message" => "Course is already assigned to a lecturer for the same semester."
            );
        }
        // Check if the lecturer is already assigned to the course for the same semester

        $query2 = "INSERT INTO `lecture_course_assignments` (`course_code`, `lecturer_id`, `semester_id`, `department_id`, `notes`) VALUES (:cc, :sn, :si, :di, :nt)";
        $result2 = $this->dm->inputData($query2, array(":cc" => $courseCode, ":sn" => $lecturerId, ":si" => $semesterId, ":di" => $departmentId, ":nt" => $notes));
        if (!$result2) {
            return array(
                "success" => false,
                "message" => "Failed to assign course to lecturer."
            );
        }

        // Fetch lecturer details
        $lecturer = (new Staff($this->db, $this->user, $this->pass))->fetch(key: "number", value: $lecturerId, archived: false)[0];
        // Fetch course details
        $course = (new Course($this->db, $this->user, $this->pass))->fetch(key: "code", value: $courseCode, archived: false)[0];

        $this->log->activity($_SESSION["staff"]["number"], "INSERT", "secretary", "Course Assignment", "Assigned {$lecturer["name"]} to {$course["name"]} ({$courseCode})");
        return array(
            "success" => true,
            "message" => "Course assigned to lecturer successfully."
        );
    }

    public function fetchSemesterCourseAssignmentsByLecturer($lecturerId, $semesterId)
    {
        $query = "SELECT * FROM `lecture_course_assignments` WHERE `lecturer_id` = :sn AND `semester_id` = :si";
        return $this->dm->getData($query, array(":sn" => $lecturerId, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsGroupByLecturer($departmentId, $semesterId)
    {
        $query = "SELECT * FROM `lecture_course_assignments` WHERE `department_id` = :di AND `semester_id` = :si GROUP BY `lecturer_id`";
        return $this->dm->getData($query, array(":di" => $departmentId, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsByCourse($courseCode, $semesterId)
    {
        $query = "SELECT * FROM `lecture_course_assignments` WHERE `course_code` = :cc AND `semester_id` = :si";
        return $this->dm->getData($query, array(":cc" => $courseCode, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsGroupByCourse($courseCode, $semesterId)
    {
        $query = "SELECT * FROM `lecture_course_assignments` WHERE `course_code` = :cc AND `semester_id` = :si GROUP BY `course_code`";
        return $this->dm->getData($query, array(":cc" => $courseCode, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsByDepartment($departmentId, $semesterId)
    {
        $query = "SELECT * FROM `lecture_course_assignments` WHERE `department_id` = :di AND `semester_id` = :si";
        return $this->dm->getData($query, array(":di" => $departmentId, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsGroupByDepartment($departmentId, $semesterId)
    {
        $query = "SELECT * FROM `lecture_course_assignments` WHERE `department_id` = :di AND `semester_id` = :si GROUP BY `department_id`";
        return $this->dm->getData($query, array(":di" => $departmentId, ":si" => $semesterId));
    }

    public function fetchSemesterCourseAssignmentsBySemester($semesterId)
    {
        $query = "SELECT * FROM `lecture_course_assignments` WHERE `semester_id` = :si";
        return $this->dm->getData($query, array(":si" => $semesterId));
    }
}
