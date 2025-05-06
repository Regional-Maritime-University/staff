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
        $query = "SELECT s.*, a.`name` AS academic_year, a.start_date AS academic_year_start_date, a.end_date AS academic_year_end_date 
                FROM `semester` AS s, `academic_year` AS a WHERE a.`active` = 1 AND s.`active` = 1 AND a.`id` = s.`fk_academic_year`";
        return $this->dm->getData($query);
    }
}
