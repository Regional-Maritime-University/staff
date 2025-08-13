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
        $this->dm = new DatabaseMethods($db, $user, $pass);
        $this->log = new Log($db, $user, $pass);
    }

    public function getLecturerClasses($lecturerId)
    {
        try {
            $classes = (new Classes($this->db, $_SESSION['staff']['number'], $_SESSION['staff']['password']))
                ->fetch('lecturer', $lecturerId);
            return ['success' => true, 'data' => $classes];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error fetching classes: ' . $e->getMessage()];
        }
    }
    public function getLecturerCourses($lecturerId)
    {
        try {
            $courses = (new Course($this->db, $_SESSION['staff']['number'], $_SESSION['staff']['password']))
                ->fetch('lecturer', $lecturerId);
            return ['success' => true, 'data' => $courses];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error fetching courses: ' . $e->getMessage()];
        }
    }
    public function getLecturerPrograms($lecturerId)
    {
        try {
            $programs = (new Program($this->db, $_SESSION['staff']['number'], $_SESSION['staff']['password']))
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
            $students = (new Student($this->db, $_SESSION['staff']['number'], $_SESSION['staff']['password']))
                ->fetch('lecturer', $lecturerId);
            return ['success' => true, 'data' => $students];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error fetching students: ' . $e->getMessage()];
        }
    }
}
