<?php

namespace Src\Core;

use Src\Base\Log;
use Src\System\DatabaseMethods;

class Classes
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
            case 'code':
                $concat_stmt = "AND c.`code` = :v";
                break;

            case 'department':
                $concat_stmt = "AND p.`department` = :v";
                break;

            case 'program':
                $concat_stmt = "AND c.`fk_program` = :v";
                break;

            default:
                $concat_stmt = "";
                break;
        }

        $query = "SELECT 
                    c.`code`, c.`fk_program` AS program_id, p.`name` AS program_name, 
                    p.`index_code` AS program_code, d.`id` AS department_id, d.`name` AS department_name 
                FROM `class` AS c, `programs` AS p, `department` AS d 
                WHERE c.`fk_program` = p.`id` AND p.`department` = d.`id` $concat_stmt";
        $params = $value ? array(":v" => $value) : array();
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
