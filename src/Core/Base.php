<?php

namespace Src\Core;

use Src\Base\Log;
use Src\System\DatabaseMethods;

class Base
{

    private $dm = null;
    private $log = null;

    public function __construct($db, $user, $pass)
    {
        $this->dm = new DatabaseMethods($db, $user, $pass);
        $this->log = new Log($db, $user, $pass);
    }

    public function getActiveSemesters()
    {
        $query = "SELECT 
                    s.`id`, s.`atcive`, s.`name`, s.`type`, s.`start_date`, s.`end_date`, s.`exam_registration_start_date`, 
                    s.`exam_registration_end_date`, s.`exam_start_date`, s.`exam_end_date`, s.`resit_exam_start_date`, 
                    s.`resit_exam_end_date`, s.`resit_exam_registration_start_date`, s.`resit_exam_registration_end_date`, 
                    s.`resit_exam_results_uploaded`, s.`course_registration_opened`, s.`registration_end`, s.`exam_results_uploaded`, s.`archived`
                    a.`id` AS academic_year, a.`name` AS academic_year_name, a.start_date AS academic_year_start_date, a.end_date AS academic_year_end_date 
                FROM 
                `semester` AS s 
                JOIN `academic_year` AS a ON s.`fk_academic_year` = a.`id` 
                WHERE a.`active` = 1 AND s.`active` = 1";
        return $this->dm->getData($query);
    }
}
