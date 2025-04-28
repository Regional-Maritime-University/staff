<?php

namespace Src\Controller;

use Exception;
use Src\System\DatabaseMethods;
use Src\Controller\ExposeDataController;
use PhpOffice\PhpWord\TemplateProcessor;

class SecretaryController
{
    private $dm = null;
    private $expose = null;

    public function __construct($db, $user, $pass)
    {
        $this->dm = new DatabaseMethods($db, $user, $pass);
        $this->expose = new ExposeDataController($db, $user, $pass);
    }

    public function profile()
    {
        $user = $this->dm->getData($_SESSION['user_id']);
        if ($user) {
            return $user;
        } else {
            throw new Exception("User not found.");
        }
    }

    public function getAllCourses()
    {
        // Fetch all courses from the database
        // This is a placeholder. You should implement the actual logic to fetch courses.
    }

    public function getAllLecturers()
    {
        // Fetch all lecturers from the database
        // This is a placeholder. You should implement the actual logic to fetch lecturers.
    }

    public function getAllStudents()
    {
        // Fetch all students from the database
        // This is a placeholder. You should implement the actual logic to fetch students.
    }
}
