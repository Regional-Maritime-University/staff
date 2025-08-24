<?php

namespace Src\Core;

use Src\Base\Log;
use Src\System\DatabaseMethods;

class Student
{

    private $dm = null;
    private $log = null;

    public function __construct($db, $user, $pass)
    {
        $this->dm = new DatabaseMethods($db, $user, $pass);
        $this->log = new Log($db, $user, $pass);
    }

    public function fetch(string $key = "", string $value = "", bool $archived = false)
    {
        switch ($key) {
            case 'index_number':
                $concat_stmt = "AND s.`index_number` = :v";
                break;

            case 'name':
                $concat_stmt = "AND s.`name` = :v";
                break;

            case 'gender':
                $concat_stmt = "AND s.`gender` = :v";
                break;

            case 'academic_year':
                $concat_stmt = "AND s.`fk_academic_year` = :v";
                break;

            case 'department':
                $concat_stmt = "AND s.`fk_department` = :v";
                break;

            case 'program':
                $concat_stmt = "AND s.`fk_program` = :v";
                break;

            case 'class':
                $concat_stmt = "AND s.`fk_class` = :v";
                break;

            default:
                $concat_stmt = "";
                break;
        }

        $query = "SELECT 
                s.`index_number`, s.`app_number`, s.`email`, s.`phone_number`, 
                s.`prefix`, s.`first_name`, s.`middle_name`, s.`last_name`, s.`suffix`, s.`gender`, 
                s.`dob`, s.`nationality`, s.`photo`, s.`marital_status`, s.`disability`, 
                s.`date_admitted`, s.`term_admitted`, s.`stream_admitted`, s.`level_admitted`, 
                s.`programme_duration`, s.`default_password`, s.`semester_setup`, s.`archived`, 
                s.`fk_academic_year` AS acad_year_id, s.`fk_applicant` AS applicant_id, 
                s.`fk_department` AS department_id, s.`fk_program` AS program_id, s.`fk_class` AS class_code, 
                ay.`name` AS acad_year_name, d.`name` AS department_name, p.`name` AS program_name 
                FROM 
                `student` AS s, `academic_year` AS ay, `department` AS d, `programs` AS p, `class` AS c 
                WHERE 
                s.`fk_academic_year` = ay.`id` AND s.`fk_department` = d.`id` AND 
                s.`fk_program` = p.`id` AND s.`fk_class` = c.`code` AND s.`archived` = :ar $concat_stmt";
        $params = $value ? array(":v" => $value, ":ar" => $archived) : array(":ar" => $archived);

        $result = $this->dm->getData($query, $params);
        return $result ? array("success" => true, "data" => $result) : array("success" => false, "message" => "No students found!");
    }

    public function update(array $data)
    {
        $query = "UPDATE `student` SET 
        `index_number`=:n, `email`=:e, `password`=:fn, `first_name`=:mn, 
        `middle_name`, `last_name`=:ln, `prefix`=:p, `gender`=:g, `role`=:r, 
        `fk_department`=:d, `archived`=:ar WHERE s.`index_number` = :i";
        $params = array(
            ":i" => $data["c_index_number"],
            ":n" => $data["index_number"],
            ":e" => $data["email"],
            ":fn" => $data["first_name"],
            ":mn" => $data["middle_name"],
            ":ln" => $data["last_name"],
            ":p" => $data["prefix"],
            ":g" => $data["gender"],
            ":r" => $data["role"],
            ":d" => $data["fk_department"],
            ":ar" => 0
        );
        $query_result = $this->dm->inputData($query, $params);
        if ($query_result) $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Staff Details Modification", "Updated information for student {$data["id"]}");
        return $query_result;
    }

    public function total(string $key = "", string $value = "", bool $archived = false)
    {
        $concat_stmt = "";
        switch ($key) {
            case 'gender':
                $concat_stmt = "AND s.`gender` = :v";
                break;

            case 'academic_year':
                $concat_stmt = "AND s.`fk_academic_year` = :v";
                break;

            case 'department':
                $concat_stmt = "AND s.`fk_department` = :v";
                break;

            case 'program':
                $concat_stmt = "AND s.`fk_program` = :v";
                break;

            case 'class':
                $concat_stmt = "AND s.`fk_class` = :v";
                break;
        }

        $query = "SELECT COUNT(s.`index_number`) AS total 
                FROM 
                `student` AS s, `academic_year` AS ay, `department` AS d, `programs` AS p, `class` AS c 
                WHERE 
                s.`fk_academic_year` = ay.`id` AND  s.`fk_department` = d.`id` AND s.`fk_program` = p.`id` AND 
                s.`fk_class` = c.`code` AND s.`archived` = :ar $concat_stmt";
        $params = $value ? array(":v" => $value, ":ar" => $archived) : array(":ar" => $archived);
        return $this->dm->getData($query, $params);
    }

    public function archive(string $index_number)
    {
        $query = "UPDATE `student` SET `archived` = 1 WHERE `index_number` = :i";
        $params = array(":i" => $index_number);
        $query_result = $this->dm->inputData($query, $params);
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "ARCHIVE", "secretary", "Student Archive", "Archived student {$index_number}");
            return array("success" => true, "message" => "Student with index number {$index_number} successfully archived!");
        }
        return array("success" => false, "message" => "Failed to archive student!");
    }

    public function unarchive(array $students)
    {
        $unarchived = 0;
        foreach ($students as $student) {
            $query = "UPDATE `student` SET `archived` = 0 WHERE `index_number` = :i";
            $query_result = $this->dm->inputData($query, array(":i" => $student));
            if ($query_result) {
                $this->log->activity($_SESSION["staff"]["number"], "UPDATE", "secretary", "Student Archive", "Unarchived student {$student}");
                $unarchived += 1;
            }
        }
        return array(
            "success" => true,
            "message" => "{$unarchived} successfully unarchived!",
            "errors" => "Failed to unarchive " . (count($students) - $unarchived) . " students"
        );
    }

    public function delete(string $index_number)
    {
        $query = "DELETE FROM `student` WHERE `index_number` = :i";
        $params = array(":i" => $index_number);
        $query_result = $this->dm->inputData($query, $params);
        if ($query_result) {
            $this->log->activity($_SESSION["staff"]["number"], "DELETE", "secretary", "Student Deletion", "Deleted student {$index_number}");
            return array("success" => true, "message" => "Student with index number {$index_number} successfully deleted!");
        }
        return array("success" => false, "message" => "Failed to delete student!");
    }

    public function fetchStudentGrades(string $index_number, int $semester)
    {
        $query = "SELECT 
                    s.`index_number`, sca.`fk_semester` AS semester_id, sca.`fk_course` AS course_code, c.`name` AS course_name, 
                    sca.`credit_hours` AS course_credit_hours, sca.`level` AS course_level, sca.`semester` AS course_semester, 
                    sca.`continues_assessments_score`, sca.`exam_score`, sca.`final_score`, sca.`grade`, sca.`gpa`  
                FROM `student_courses` AS sca 
                JOIN `student` AS s ON sca.`fk_student` = s.`index_number` 
                JOIN `course` AS c ON sca.`fk_course` = c.`code` 
                JOIN `semester` AS m ON sca.`fk_semester` = m.`id` 
                WHERE sca.`fk_student` = :i AND sca.`fk_semester` = :s";
        $params = array(":i" => $index_number, ":s" => $semester);
        $ressult = $this->dm->getData($query, $params);
        if (!$ressult) {
            return array("success" => false, "message" => "No grades found for student {$index_number} in semester {$semester}");
        }
        return array("success" => true, "data" => $ressult);
    }

    // calculate the cgpa of a student
    public function calculateGPAAndCGPA(string $index_number, string $semester = "")
    {
        $query = "CALL calculate_gpa_cgpa(:i, :s)";
        $params = array(":i" => $index_number, ":s" => $semester);
        return $this->dm->getData($query, $params);
    }

    public function calculateAllGPAAndCGPA(string $index_number)
    {
        $query = "CALL calculate_gpa_cgpa(:i, :s)";
        $params = array(":i" => $index_number);
        return $this->dm->getData($query, $params);
    }
}
