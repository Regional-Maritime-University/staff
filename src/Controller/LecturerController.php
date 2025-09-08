<?php

namespace Src\Controller;

use Exception;
use Src\System\DatabaseMethods;
use Src\Controller\ExposeDataController;
use PhpOffice\PhpWord\TemplateProcessor;
use Src\Base\Log;
use Src\Core\Base;
use Src\Core\Classes;
use Src\Core\Course;
use Src\Core\Program;
use Src\Core\Staff;
use Src\Core\Student;

class LecturerController
{
    private $db;
    private $user;
    private $pass;
    private $dm;
    private $log;

    public function __construct($db, $user, $pass)
    {
        $this->db = $db;
        $this->user = $user;
        $this->pass = $pass;
        $this->dm = new DatabaseMethods($db, $user, $pass);
        $this->log = new Log($db, $user, $pass);
    }

    public function fetchAllActiveClasses($departmentId = null, $archived = false)
    {
        return (new Classes($this->db, $this->user, $this->pass))->fetch(key: "department", value: $departmentId, archived: $archived)["data"];
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

    public function fetchPendingDeadlines($departmentId = null, $archived = false, $semesterId = null, $courseCode = null, $deadlineStatus = null)
    {
        $params = [":d" => $departmentId, ":ar" => $archived];
        $where = " WHERE lca.`fk_department` = :d AND lca.`submission_deadline` IS NOT NULL AND c.`archived` = :ar ";

        if ($semesterId) {
            $where .= " AND lca.`fk_semester` = :si ";
            $params[":si"] = $semesterId;
        }

        if ($courseCode) {
            $where .= " AND lca.`fk_course` = :cc ";
            $params[":cc"] = $courseCode;
        }

        if ($deadlineStatus) {
            $where .= " AND lca.`deadline_status` = :ds ";
            $params[":ds"] = $deadlineStatus;
        }

        return $this->fetchDeadlinesQuery($where, $params);
    }

    private function fetchDeadlinesQuery($where, $params)
    {
        $query = "SELECT 
                lca.`id` AS lca_id, 
                lca.`notes`, 
                lca.`deadline_note`,
                lca.`submission_deadline`, 
                lca.`deadline_status`, 
                lca.`status`,
                lca.`created_at`, 
                lca.`updated_at`, 
                lca.`fk_semester`,
                
                c.`code` AS course_code, 
                c.`name` AS course_name, 
                c.`credit_hours`, 
                c.`contact_hours`, 
                c.`semester` AS course_semester, 
                c.`level` AS course_level, 
                c.`archived` AS course_status, 
                c.`fk_category` AS category_id, 
                cg.`name` AS category, 
                
                lca.`fk_staff` AS staff_number, 
                CONCAT(sf.`prefix`, ' ', sf.`first_name`, ' ', sf.`last_name`) AS lecturer_name, 
                
                s.`id` AS semester_id, CONCAT('SEMESTER ', s.`name`) AS semester_name, 
                d.`id` AS department_id, 
                d.`name` AS department_name,

                -- section + class info
                sec.`id` AS section_id,
                sec.`notes` AS section_notes,
                cl.`code` AS class_code,
                cl.`year` AS class_year,
                cl.`category` AS class_category,

                -- total registered students (per section)
                (SELECT COUNT(*) 
                 FROM `student_courses` scr 
                 WHERE scr.`fk_course` = lca.`fk_course` 
                   AND scr.`fk_semester` = lca.`fk_semester` 
                   AND scr.`registered` = 1) AS total_registered_students,

                -- total assigned students (per section)
                (SELECT COUNT(*) 
                 FROM `student_courses` scr2 
                 WHERE scr2.`fk_course` = lca.`fk_course` 
                   AND scr2.`fk_semester` = lca.`fk_semester`) AS total_assigned_students
                  
              FROM `lecturer_courses` AS lca 
              JOIN `department` AS d ON lca.`fk_department` = d.`id`
              JOIN `staff` AS sf ON lca.`fk_staff` = sf.`number`
              JOIN `course` AS c ON lca.`fk_course` = c.`code`
              JOIN `semester` AS s ON lca.`fk_semester` = s.`id`
              JOIN `course_category` AS cg ON c.`fk_category` = cg.`id`

              -- use LEFT JOIN to avoid NULL issues if some classes don't exist yet
              LEFT JOIN `section` AS sec 
                     ON sec.`fk_course` = lca.`fk_course`
                    AND sec.`fk_semester` = lca.`fk_semester`
              LEFT JOIN `class` AS cl 
                     ON sec.`fk_class` = cl.`code`

              {$where}
              GROUP BY sec.`id`
              ORDER BY (lca.`deadline_status` = 'pending') DESC, lca.`submission_deadline` ASC";
        return $this->dm->getData($query, $params);
    }

    public function getActiveCourses(string $lecturerId)
    {
        $query = "SELECT 
                  lc.`id`, 
                  lc.`fk_course` AS course_id, 
                  c.`code` AS course_code, 
                  c.`name` AS course_name, 
                  c.`level` AS course_level, 
                  lc.`fk_semester` AS semester_id, 
                  sm.`name` AS semester_name, 
                  lc.`status`,
                  COUNT(DISTINCT sc.`fk_student`) AS total_students,
                  GROUP_CONCAT(DISTINCT cls.`code` ORDER BY cls.`code` SEPARATOR ', ') AS class_codes
              FROM `lecturer_courses` AS lc
              JOIN `course` AS c 
                   ON lc.`fk_course` = c.`code`
              JOIN `semester` AS sm 
                   ON lc.`fk_semester` = sm.`id`
              LEFT JOIN `student_courses` AS sc 
                   ON sc.`fk_course` = lc.`fk_course`
                  AND sc.`fk_semester` = lc.`fk_semester`
              LEFT JOIN `section` sec
                   ON sec.`fk_course` = lc.`fk_course`
                  AND sec.`fk_semester` = lc.`fk_semester`
              LEFT JOIN `class` cls
                   ON cls.`code` = sec.`fk_class`
              WHERE lc.`fk_staff` = :lecturerId
                AND lc.`status` = 'active'
              GROUP BY lc.`id`, lc.`fk_course`, c.`code`, c.`name`, c.`level`, 
                       lc.`fk_semester`, sm.`name`, lc.`status`
              ORDER BY c.`code` ASC";

        $params = [":lecturerId" => $lecturerId];
        return $this->dm->getData($query, $params);
    }

    public function getTotalActiveCourses(string $lecturerId)
    {
        $query = "SELECT COUNT(c.`code`) AS total_courses
              FROM lecturer_courses AS lc
              JOIN course AS c ON lc.`fk_course` = c.`code`
              WHERE lc.`fk_staff` = :lecturerId
                AND lc.`status` = 'active'";

        $params = array(":lecturerId" => $lecturerId);
        $result = $this->dm->getData($query, $params);
        return $result ? $result[0]['total_courses'] : 0;
    }

    public function getTotalStudents(string $lecturerId)
    {
        $query = "SELECT COUNT(DISTINCT sc.`fk_student`) AS total_students
              FROM student_courses AS sc
              JOIN lecturer_courses AS lc 
                   ON sc.`fk_course` = lc.`fk_course`
                  AND sc.`fk_semester` = lc.`fk_semester`
              WHERE lc.`fk_staff` = :lecturerId";

        $params = array(":lecturerId" => $lecturerId);
        $result = $this->dm->getData($query, $params);
        return $result ? $result[0]['total_students'] : 0;
    }

    public function getTotalPendingResults(string $lecturerId)
    {
        $query = "SELECT COUNT(sr.`id`) AS total_pending_results
              FROM student_results AS sr
              JOIN lecturer_courses AS lc 
                   ON sr.`fk_course` = lc.`fk_course`
                  AND sr.`fk_semester` = lc.`fk_semester`
              WHERE lc.`fk_staff` = :lecturerId
                AND (sr.`final_score` IS NULL OR sr.`grade` IS NULL)";

        $params = array(":lecturerId" => $lecturerId);
        $result = $this->dm->getData($query, $params);
        return $result ? $result[0]['total_pending_results'] : 0;
    }

    public function getCourseDetails(string $lecturerId, string $courseCode, int $semesterId)
    {
        $query = "SELECT 
                  lc.`id`, 
                  c.`code` AS course_code, 
                  c.`name` AS course_name, 
                  c.`level` AS course_level, 
                  lc.`fk_semester` AS semester_id, 
                  sm.`name` AS semester_name, 
                  d.`id` AS department_id, 
                  d.`name` AS department_name, 
                  COUNT(DISTINCT sc.`fk_student`) AS total_students
              FROM `lecturer_courses` lc
              JOIN `course` c ON lc.`fk_course` = c.`code`
              JOIN `semester` sm ON lc.`fk_semester` = sm.`id`
              JOIN `department` d ON c.`fk_department` = d.`id`
              LEFT JOIN `student_courses` sc 
                     ON sc.`fk_course` = lc.`fk_course` 
                    AND sc.`fk_semester` = lc.`fk_semester`
              WHERE lc.`fk_staff` = :lecturerId
                AND lc.`fk_course` = :courseCode
                AND lc.`fk_semester` = :semesterId
              GROUP BY lc.`id`, c.`code`, c.`name`, c.`level`, 
                       lc.`fk_semester`, sm.`name`, d.`id`, d.`name`";

        $params = [
            ":lecturerId" => $lecturerId,
            ":courseCode" => $courseCode,
            ":semesterId" => $semesterId
        ];

        return $this->dm->getData($query, $params);
    }


    public function getLecturerCourses($lecturerId)
    {
        try {
            $courses = (new Course($this->db, $this->user, $this->pass))->fetch('lecturer', $lecturerId);
            return ['success' => true, 'data' => $courses];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error fetching courses: ' . $e->getMessage()];
        }
    }

    public function getLecturerPrograms($lecturerId)
    {
        try {
            $programs = (new Program($this->db, $this->user, $this->pass))
                ->fetch('lecturer', $lecturerId);
            return ['success' => true, 'data' => $programs];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error fetching programs: ' . $e->getMessage()];
        }
    }

    public function getLecturerResults($lecturerId, $semester = null)
    {
        try {
            $results = [];
            if (!$semester) {
                // Fetch results for the current semester
                $results = $this->getCurrentSemesterResults();
            } else {
                // fetch results for the specified semester
                $results = $this->getSemesterResults($semester);
            }
            return ['success' => true, 'data' => $results];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error fetching results: ' . $e->getMessage()];
        }
    }

    private function getCurrentSemesterResults()
    {
        $currentSemester = (new Base($this->db, $this->user, $this->pass))->getCurrentSemester();
        if ($currentSemester) {
            $query = "SELECT * FROM results WHERE semester = :semester AND lecturer_id = :lecturerId";
            $params = [
                ':semester' => $currentSemester,
                ':lecturerId' => $_SESSION['staff']['number']
            ];
            return $this->dm->getData($query, $params);
        }
        return [];
    }

    private function getSemesterResults($semester)
    {
        $query = "SELECT * FROM results WHERE semester = :semester AND lecturer_id = :lecturerId";
        $params = [
            ':semester' => $semester,
            ':lecturerId' => $_SESSION['staff']['number']
        ];
        return $this->dm->getData($query, $params);
    }

    public function getLecturerStudents($lecturerId)
    {
        try {
            $students = (new Student($this->db, $this->user, $this->pass))
                ->fetch('lecturer', $lecturerId);
            return ['success' => true, 'data' => $students];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error fetching students: ' . $e->getMessage()];
        }
    }
}
