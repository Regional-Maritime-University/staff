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
        return $this->dm->getData($query, $params);
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
}
