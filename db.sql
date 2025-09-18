DROP TABLE IF EXISTS `course_registration`;
CREATE TABLE `course_registration` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `fk_course` VARCHAR(10),
    `fk_student` VARCHAR(10),
    `fk_semester` INT(11),
    `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL,
    CONSTRAINT `fk_course_registration_course` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_course_registration_student` FOREIGN KEY (`fk_student`) REFERENCES `student` (`index_number`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_course_registration_semester` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE INDEX `course_registration_added_at_idx1` ON `course_registration` (`added_at`);
CREATE INDEX `course_registration_updated_at_idx1` ON `course_registration` (`updated_at`);

ALTER TABLE staff ADD COLUMN `designation` VARCHAR(100) AFTER `gender`;

CREATE TABLE IF NOT EXISTS `lecturer_courses` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `fk_department` INT(11),
    `fk_staff` VARCHAR(10),
    `fk_course` VARCHAR(10),
    `fk_semester` INT(11),
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_lecturer_courses_department` FOREIGN KEY (`fk_department`) REFERENCES `department` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_lecturer_courses_semester` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_lecturer_courses_course` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_lecturer_courses_staff` FOREIGN KEY (`fk_staff`) REFERENCES `staff` (`number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE INDEX `created_at_idx1` ON `lecturer_courses` (`created_at`);
CREATE INDEX `updated_at_idx1` ON `lecturer_courses` (`updated_at`);

ALTER TABLE `activity_logs` ADD COLUMN `type` VARCHAR(50) DEFAULT 'admin' AFTER `operation`;
ALTER TABLE `activity_logs` ADD COLUMN `action` VARCHAR(100) AFTER `type`;
ALTER TABLE `activity_logs` ADD INDEX `activity_logs_type_idx1` (`type`);
ALTER TABLE `activity_logs` ADD INDEX `activity_logs_action_idx1` (`action`);
ALTER TABLE `activity_logs` CHANGE `id` `id` VARCHAR(10) NOT NULL; 

ALTER TABLE `semester` 
ADD COLUMN `type` VARCHAR(30) DEFAULT 'continuing students' AFTER `name`,
ADD COLUMN `start_date` DATE DEFAULT NULL AFTER `type`,
ADD COLUMN `end_date` DATE DEFAULT NULL AFTER `start_date`,
ADD COLUMN `exam_start_date` DATE DEFAULT NULL AFTER `end_date`,
ADD COLUMN `exam_end_date` DATE DEFAULT NULL AFTER `exam_start_date`,
ADD COLUMN `exam_registration_start_date` DATE DEFAULT NULL AFTER `exam_end_date`,
ADD COLUMN `exam_registration_end_date` DATE DEFAULT NULL AFTER `exam_registration_start_date`,
ADD COLUMN `resit_exam_start_date` DATE DEFAULT NULL AFTER `exam_end_date`,
ADD COLUMN `resit_exam_end_date` DATE DEFAULT NULL AFTER `resit_exam_start_date`,
ADD COLUMN `resit_exam_registration_start_date` DATE DEFAULT NULL AFTER `resit_exam_end_date`,
ADD COLUMN `resit_exam_registration_end_date` DATE DEFAULT NULL AFTER `resit_exam_registration_start_date`,
ADD COLUMN `resit_exam_results_uploaded` DATE DEFAULT NULL AFTER `resit_exam_registration_end_date`,
ADD INDEX `semester_type_idx1` (`type`),
ADD INDEX `semester_start_date_idx1` (`start_date`),
ADD INDEX `semester_end_date_idx1` (`end_date`),
ADD INDEX `semester_exam_registration_start_date_idx1` (`exam_registration_start_date`),
ADD INDEX `semester_exam_registration_end_date_idx1` (`exam_registration_end_date`),
ADD INDEX `semester_exam_start_date_idx1` (`exam_start_date`),
ADD INDEX `semester_exam_end_date_idx1` (`exam_end_date`),
ADD INDEX `semester_resit_exam_start_date_idx1` (`resit_exam_start_date`),
ADD INDEX `semester_resit_exam_end_date_idx1` (`resit_exam_end_date`),
ADD INDEX `semester_resit_exam_registration_start_date_idx1` (`resit_exam_registration_start_date`),
ADD INDEX `semester_resit_exam_registration_end_date_idx1` (`resit_exam_registration_end_date`),
ADD INDEX `semester_exam_results_uploaded_idx1` (`exam_results_uploaded`);

DROP TABLE IF EXISTS `deadlines`;
CREATE TABLE IF NOT EXISTS `deadlines` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `fk_department` INT(11),
    `fk_semester` INT,
    `fk_course` VARCHAR(10),
    `fk_staff` VARCHAR(10),
    `date` DATE,
    `note` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_deadlines_department` FOREIGN KEY (`fk_department`) REFERENCES `department` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_deadlines_semester` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_deadlines_staff` FOREIGN KEY (`fk_staff`) REFERENCES `staff` (`number`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_deadlines_course` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `deadlines` ADD COLUMN `fk_class` VARCHAR(10) AFTER `fk_course`;
ALTER TABLE `deadlines` ADD CONSTRAINT `fk_deadlines_class` FOREIGN KEY (`fk_class`) REFERENCES `class` (`code`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE INDEX `deadlines_date_idx1` ON `deadlines` (`date`);
CREATE INDEX `deadlines_created_at_idx1` ON `deadlines` (`created_at`);
CREATE INDEX `deadlines_updated_at_idx1` ON `deadlines` (`updated_at`);
ALTER TABLE `deadlines` ADD COLUMN `fk_class` VARCHAR(10) AFTER `fk_course`;
ALTER TABLE `deadlines` ADD CONSTRAINT `fk_deadlines_class` FOREIGN KEY (`fk_class`) REFERENCES `class` (`code`) ON DELETE CASCADE ON UPDATE CASCADE
ALTER TABLE `deadlines` ADD COLUMN `status` VARCHAR(15) DEFAULT 'pending' AFTER `note`;
ALTER TABLE `deadlines` ADD INDEX `deadlines_status_idx1` (`status`);

ALTER TABLE `activity_logs` 
ADD COLUMN `fk_department` INT DEFAULT 1 AFTER `id`, 
ADD CONSTRAINT `fk_activities_logs_department` FOREIGN KEY (`fk_department`) REFERENCES `department` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

// remember to also alter the code to properly account for the department insertion into the activity logs table

RENAME TABLE `assigned_courses` TO `student_courses`;
ALTER TABLE `student_courses` ADD COLUMN `fk_semester` INT AFTER `fk_course`;
ALTER TABLE `student_courses` ADD CONSTRAINT `fk_student_courses_semester` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `student_courses` ADD COLUMN `notes` TEXT DEFAULT NULL AFTER `fk_semester`;

ALTER TABLE `student_courses` ADD COLUMN `status` VARCHAR(15) DEFAULT 'active' AFTER `semester`; -- will deferred or atcive
ALTER TABLE `student_courses` ADD INDEX `student_courses_status_idx1` (`status`);
ALTER TABLE `student_courses` ADD COLUMN `continues_assessments_score` DECIMAL(5,2) DEFAULT 0 AFTER `status`;
ALTER TABLE `student_courses` ADD INDEX `student_courses_continues_assessments_score_idx1` (`continues_assessments_score`);
ALTER TABLE `student_courses` ADD COLUMN `project_score` DECIMAL(5,2) DEFAULT 0 AFTER `continues_assessments_score`;
ALTER TABLE `student_courses` ADD INDEX `student_courses_project_score_idx1` (`project_score`);
ALTER TABLE `student_courses` ADD COLUMN `exam_score` DECIMAL(5,2) DEFAULT 0 AFTER `continues_assessments_score`;
ALTER TABLE `student_courses` ADD INDEX `student_courses_exam_idx1` (`exam_score`);
ALTER TABLE `student_courses` ADD COLUMN `final_score` DECIMAL(5,2) DEFAULT 0 AFTER `exam_score`;
ALTER TABLE `student_courses` ADD INDEX `student_courses_final_score_idx1` (`final_score`);
ALTER TABLE `student_courses` ADD COLUMN `grade` VARCHAR(5) DEFAULT NULL AFTER `final_score`;
ALTER TABLE `student_courses` ADD INDEX `student_courses_grade_idx1` (`grade`);
ALTER TABLE `student_courses` ADD COLUMN `gpa` DECIMAL(4,2) DEFAULT NULL AFTER `grade`;
ALTER TABLE `student_courses` ADD INDEX `student_courses_gpa_idx1` (`gpa`);

-- DROP PROCEDURE IF EXISTS recalc_results_and_gpa;
-- DELIMITER //
-- CREATE PROCEDURE recalc_results_and_gpa(IN in_semester_id INT)
-- BEGIN
--     -- First, recalc final_score and grade for each student result in this semester
--     UPDATE student_results sr
--     JOIN course c ON sr.fk_course = c.code
--     LEFT JOIN grade_points gp ON 1=0 -- placeholder for mapping later
--     SET 
--         sr.final_score = COALESCE(sr.continues_assessments_score, 0) +
--                          COALESCE(sr.project_score, 0) +
--                          COALESCE(sr.exam_score, 0),
--         sr.grade = CASE
--             WHEN (COALESCE(sr.continues_assessments_score, 0) +
--                   COALESCE(sr.project_score, 0) +
--                   COALESCE(sr.exam_score, 0)) >= 80 THEN 'A'
--             WHEN (COALESCE(sr.continues_assessments_score, 0) +
--                   COALESCE(sr.project_score, 0) +
--                   COALESCE(sr.exam_score, 0)) >= 75 THEN 'A-'
--             WHEN (COALESCE(sr.continues_assessments_score, 0) +
--                   COALESCE(sr.project_score, 0) +
--                   COALESCE(sr.exam_score, 0)) >= 70 THEN 'B+'
--             WHEN (COALESCE(sr.continues_assessments_score, 0) +
--                   COALESCE(sr.project_score, 0) +
--                   COALESCE(sr.exam_score, 0)) >= 65 THEN 'B'
--             WHEN (COALESCE(sr.continues_assessments_score, 0) +
--                   COALESCE(sr.project_score, 0) +
--                   COALESCE(sr.exam_score, 0)) >= 60 THEN 'C+'
--             WHEN (COALESCE(sr.continues_assessments_score, 0) +
--                   COALESCE(sr.project_score, 0) +
--                   COALESCE(sr.exam_score, 0)) >= 55 THEN 'C'
--             WHEN (COALESCE(sr.continues_assessments_score, 0) +
--                   COALESCE(sr.project_score, 0) +
--                   COALESCE(sr.exam_score, 0)) >= 50 THEN 'D'
--             WHEN (COALESCE(sr.continues_assessments_score, 0) +
--                   COALESCE(sr.project_score, 0) +
--                   COALESCE(sr.exam_score, 0)) >= 45 THEN 'E'
--             ELSE 'F'
--         END
--     WHERE sr.fk_semester = in_semester_id;

--     -- Next, update GPA points per course based on grade_points table
--     UPDATE student_results sr
--     JOIN grade_points gp ON sr.grade = gp.grade
--     SET sr.gpa = gp.point
--     WHERE sr.fk_semester = in_semester_id;

--     -- Finally, update GPA per semester for each student
--     UPDATE student_results sr
--     JOIN (
--         SELECT sr.fk_student, sr.fk_semester,
--                ROUND(SUM(gp.point * c.credit_hours) / NULLIF(SUM(c.credit_hours), 0), 2) AS gpa
--         FROM student_results sr
--         JOIN grade_points gp ON sr.grade = gp.grade
--         JOIN course c ON sr.fk_course = c.code
--         WHERE sr.fk_semester = in_semester_id
--         GROUP BY sr.fk_student, sr.fk_semester
--     ) gpa_calc
--     ON sr.fk_student = gpa_calc.fk_student
--    AND sr.fk_semester = gpa_calc.fk_semester
--     SET sr.gpa = gpa_calc.gpa;
-- END;
-- //
-- DELIMITER ;

DROP PROCEDURE IF EXISTS recalc_grades_by_semester;
DELIMITER //
CREATE PROCEDURE recalc_grades_by_semester(IN in_semester_id INT)
BEGIN
    -- Step 1: Update grade based on final_score
    UPDATE student_results sr
    SET sr.grade = CASE
        WHEN sr.final_score >= 80 THEN 'A'
        WHEN sr.final_score >= 75 THEN 'A-'
        WHEN sr.final_score >= 70 THEN 'B+'
        WHEN sr.final_score >= 65 THEN 'B'
        WHEN sr.final_score >= 60 THEN 'C+'
        WHEN sr.final_score >= 55 THEN 'C'
        WHEN sr.final_score >= 50 THEN 'D'
        WHEN sr.final_score >= 45 THEN 'E'
        ELSE 'F'
    END
    WHERE sr.fk_semester = in_semester_id;

    -- Step 2: Update GPA points
    UPDATE student_results sr
    JOIN grade_points gp ON sr.grade = gp.grade
    SET sr.gpa = gp.point
    WHERE sr.fk_semester = in_semester_id;

    -- Step 3: Calculate semester GPA using credit_hours from student_courses
    UPDATE student_results sr
    JOIN (
        SELECT sr.fk_student,
               sr.fk_semester,
               ROUND(SUM(sr.gpa * sc.credit_hours) / NULLIF(SUM(sc.credit_hours),0), 2) AS semester_gpa
        FROM student_results sr
        JOIN student_courses sc 
          ON sr.fk_student = sc.fk_student
         AND sr.fk_course  = sc.fk_course
         AND sr.fk_semester = sc.fk_semester
        WHERE sr.fk_semester = in_semester_id
        GROUP BY sr.fk_student, sr.fk_semester
    ) AS gpa_calc
      ON sr.fk_student = gpa_calc.fk_student
     AND sr.fk_semester = gpa_calc.fk_semester
    SET sr.gpa = gpa_calc.semester_gpa;
END;
//
DELIMITER ;

DROP PROCEDURE IF EXISTS recalc_grades_by_semester_course;
DELIMITER //
CREATE PROCEDURE recalc_grades_by_semester_course(
    IN in_semester_id INT,
    IN in_course_code VARCHAR(10)
)
BEGIN
    -- Step 1: Update grade based on final_score
    UPDATE student_results sr
    SET sr.grade = CASE
        WHEN sr.final_score >= 80 THEN 'A'
        WHEN sr.final_score >= 75 THEN 'A-'
        WHEN sr.final_score >= 70 THEN 'B+'
        WHEN sr.final_score >= 65 THEN 'B'
        WHEN sr.final_score >= 60 THEN 'C+'
        WHEN sr.final_score >= 55 THEN 'C'
        WHEN sr.final_score >= 50 THEN 'D'
        WHEN sr.final_score >= 45 THEN 'E'
        ELSE 'F'
    END
    WHERE sr.fk_semester = in_semester_id
      AND sr.fk_course = in_course_code;

    -- Step 2: Update GPA points
    UPDATE student_results sr
    JOIN grade_points gp ON sr.grade = gp.grade
    SET sr.gpa = gp.point
    WHERE sr.fk_semester = in_semester_id
      AND sr.fk_course = in_course_code;

    -- Step 3: Calculate semester GPA using credit_hours from student_courses
    UPDATE student_results sr
    JOIN (
        SELECT sr.fk_student,
               sr.fk_semester,
               ROUND(SUM(sr.gpa * sc.credit_hours) / NULLIF(SUM(sc.credit_hours),0), 2) AS semester_gpa
        FROM student_results sr
        JOIN student_courses sc 
          ON sr.fk_student = sc.fk_student
         AND sr.fk_course  = sc.fk_course
         AND sr.fk_semester = sc.fk_semester
        WHERE sr.fk_semester = in_semester_id
        GROUP BY sr.fk_student, sr.fk_semester
    ) AS gpa_calc
      ON sr.fk_student = gpa_calc.fk_student
     AND sr.fk_semester = gpa_calc.fk_semester
    SET sr.gpa = gpa_calc.semester_gpa;
END;
//
DELIMITER ;


ALTER TABLE `section` ADD COLUMN `fk_semester` INT AFTER `fk_course`;
ALTER TABLE `section` ADD CONSTRAINT `fk_section_semester` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `section` ADD COLUMN `notes` TEXT DEFAULT NULL AFTER `fk_semester`;

ALTER TABLE staff ADD COLUMN `avatar` VARCHAR(255) AFTER `password`;

-- Create a table to store grade points
CREATE TABLE `grade_points` (
    `grade` VARCHAR(2) PRIMARY KEY,
    `point` DECIMAL(3,2)
);
-- Insert grade points into the table
INSERT INTO `grade_points` VALUES
('A', 4.0), ('A-', 3.85), ('B+', 3.0), ('B', 2.85),
('C+', 2.5), ('C', 2.0), ('D', 1.5), ('E', 1.0), ('F', 0.0);

-- Calculate GPA for a specific student in a specific semester
-- This procedure will return the GPA for a specific student in a given semester
DROP PROCEDURE IF EXISTS calculate_gpa;
DELIMITER //
CREATE PROCEDURE calculate_gpa(IN in_student_id VARCHAR(10), IN in_semester_id INT)
BEGIN
    SELECT 
        ROUND(SUM(gp.point * sca.credit_hours) / NULLIF(SUM(sca.credit_hours), 0), 2) AS gpa
    FROM student_results AS sr
    JOIN student_courses AS sca 
        ON sr.fk_student = sca.fk_student 
       AND sr.fk_course = sca.fk_course 
       AND sr.fk_semester = sca.fk_semester
    JOIN grade_points AS gp ON sr.grade = gp.grade
    WHERE sr.fk_student = in_student_id 
      AND sr.fk_semester = in_semester_id;
END;
//
DELIMITER ;

-- Calculate CGPA for a specific student in a specific semester
-- This procedure will return the CGPA for a specific student in a given semester
DROP PROCEDURE IF EXISTS calculate_cgpa;
DELIMITER //
CREATE PROCEDURE calculate_cgpa(IN in_student_id VARCHAR(10))
BEGIN
    SELECT 
        ROUND(SUM(gp.point * sca.credit_hours) / NULLIF(SUM(sca.credit_hours), 0), 2) AS cgpa
    FROM student_results AS sr
    JOIN student_courses AS sca 
        ON sr.fk_student = sca.fk_student 
       AND sr.fk_course = sca.fk_course 
       AND sr.fk_semester = sca.fk_semester
    JOIN grade_points AS gp ON sr.grade = gp.grade
    WHERE sr.fk_student = in_student_id;
END;
//
DELIMITER ;

DROP PROCEDURE IF EXISTS calculate_gpa_cgpa;
DELIMITER //
CREATE PROCEDURE calculate_gpa_cgpa(IN in_student_id VARCHAR(10), IN in_semester_id INT)
BEGIN
    DECLARE gpa DECIMAL(4,2);
    DECLARE cgpa DECIMAL(4,2);
    DECLARE total_credits INT;
    DECLARE total_courses INT;

    -- GPA
    SELECT 
        ROUND(SUM(gp.point * sca.credit_hours) / NULLIF(SUM(sca.credit_hours), 0), 2)
    INTO gpa
    FROM student_results AS sr
    JOIN student_courses AS sca 
        ON sr.fk_student = sca.fk_student 
       AND sr.fk_course = sca.fk_course 
       AND sr.fk_semester = sca.fk_semester
    JOIN grade_points AS gp ON sr.grade = gp.grade
    WHERE sr.fk_student = in_student_id 
      AND sr.fk_semester = in_semester_id;

    -- CGPA
    SELECT 
        ROUND(SUM(gp.point * sca.credit_hours) / NULLIF(SUM(sca.credit_hours), 0), 2)
    INTO cgpa
    FROM student_results AS sr
    JOIN student_courses AS sca 
        ON sr.fk_student = sca.fk_student 
       AND sr.fk_course = sca.fk_course 
       AND sr.fk_semester = sca.fk_semester
    JOIN grade_points AS gp ON sr.grade = gp.grade
    WHERE sr.fk_student = in_student_id;

    -- Total credits
    SELECT SUM(credit_hours)
    INTO total_credits
    FROM student_courses
    WHERE fk_student = in_student_id;

    -- Total registered courses
    SELECT COUNT(*)
    INTO total_courses
    FROM student_courses
    WHERE fk_student = in_student_id;

    -- Final Output
    SELECT gpa, cgpa, total_credits, total_courses;
END;
//
DELIMITER ;

DROP PROCEDURE IF EXISTS calculate_students_gpa_cgpa;
DELIMITER //
CREATE PROCEDURE calculate_students_gpa_cgpa(IN in_semester_id INT)
BEGIN
    SELECT 
        s.index_number,
        ROUND(SUM(CASE WHEN sr.fk_semester = in_semester_id 
                       THEN gp.point * sca.credit_hours ELSE 0 END) /
              NULLIF(SUM(CASE WHEN sr.fk_semester = in_semester_id 
                              THEN sca.credit_hours ELSE 0 END), 0), 2) AS gpa,
        ROUND(SUM(gp.point * sca.credit_hours) / NULLIF(SUM(sca.credit_hours), 0), 2) AS cgpa
    FROM student AS s
    JOIN student_results AS sr ON s.index_number = sr.fk_student
    JOIN student_courses AS sca 
        ON sr.fk_student = sca.fk_student 
       AND sr.fk_course = sca.fk_course 
       AND sr.fk_semester = sca.fk_semester
    JOIN grade_points AS gp ON sr.grade = gp.grade
    WHERE s.archived = 0
    GROUP BY s.index_number;
END;
//
DELIMITER ;

DROP PROCEDURE IF EXISTS calculate_all_students_gpa_cgpa_in_department;
DELIMITER //
CREATE PROCEDURE calculate_all_students_gpa_cgpa_in_department(
    IN in_department_id INT,
    IN in_semester_id INT
)
BEGIN
    SELECT 
        s.index_number,
        -- GPA for this semester
        ROUND(
            SUM(CASE 
                WHEN sr.fk_semester = in_semester_id 
                THEN gp.point * sc.credit_hours 
                ELSE 0 
            END) / NULLIF(SUM(CASE 
                WHEN sr.fk_semester = in_semester_id 
                THEN sc.credit_hours 
                ELSE 0 
            END), 0), 
        2) AS gpa,
        -- CGPA overall
        ROUND(SUM(gp.point * sc.credit_hours) / NULLIF(SUM(sc.credit_hours), 0), 2) AS cgpa
    FROM student s
    JOIN student_results sr ON s.index_number = sr.fk_student
    JOIN student_courses sc 
        ON sr.fk_student = sc.fk_student 
       AND sr.fk_course = sc.fk_course 
       AND sr.fk_semester = sc.fk_semester
    JOIN grade_points gp ON sr.grade = gp.grade
    JOIN department d ON s.fk_department = d.id
    WHERE s.archived = 0 AND d.id = in_department_id
    GROUP BY s.index_number;
END;
//
DELIMITER ;

ALTER TABLE `class` ADD COLUMN `fk_staff` VARCHAR(10) AFTER `fk_program`;
ALTER TABLE `class` ADD CONSTRAINT `fk_class_staff` FOREIGN KEY (`fk_staff`) REFERENCES `staff` (`number`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `class` ADD COLUMN `archived` TINYINT(1) DEFAULT 0 AFTER `fk_staff`;
ALTER TABLE `class` ADD COLUMN `year` TEXT DEFAULT NULL AFTER `fk_staff`;
ALTER TABLE `class` ADD INDEX `class_archived_idx1` (`archived`);
ALTER TABLE `class` ADD INDEX `class_year_idx1` (`year`);

CREATE TABLE IF NOT EXISTS `faculty` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `archived` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE INDEX `faculty_name_idx1` ON `faculty` (`name`);
CREATE INDEX `faculty_created_at_idx1` ON `faculty` (`created_at`);
CREATE INDEX `faculty_updated_at_idx1` ON `faculty` (`updated_at`);
CREATE INDEX `faculty_archived_idx1` ON `faculty` (`archived`);

ALTER TABLE `department` ADD COLUMN `fk_faculty` INT(11) AFTER `name`;
ALTER TABLE `department` ADD CONSTRAINT `fk_department_faculty` FOREIGN KEY (`fk_faculty`) REFERENCES `faculty` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `department` ADD INDEX `department_fk_faculty_idx1` (`fk_faculty`);

INSERT INTO `faculty` (`name`, `description`, `created_at`, `updated_at`, `archived`) VALUES
('Faculty of Maritime Studies', 'Faculty of Science description', NOW(), NOW(), 0),
('Faculty of Engineering and Applied Sciences', 'Faculty of Engineering description', NOW(), NOW(), 0);

CREATE TABLE IF NOT EXISTS `class_advisor` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `fk_class` VARCHAR(10),
    `fk_staff` VARCHAR(10),
    `archived` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_class_advisor_class` FOREIGN KEY (`fk_class`) REFERENCES `class` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_class_advisor_staff` FOREIGN KEY (`fk_staff`) REFERENCES `staff` (`number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE INDEX `class_advisor_created_at_idx1` ON `class_advisor` (`created_at`);
CREATE INDEX `class_advisor_updated_at_idx1` ON `class_advisor` (`updated_at`);
ALTER TABLE `class_advisor` ADD INDEX `class_advisor_archived_idx1` (`archived`);

ALTER TABLE `class` ADD COLUMN `category` VARCHAR(50) DEFAULT 'regular' AFTER `year`;
ALTER TABLE `class` ADD INDEX `class_category_idx1` (`category`);

ALTER TABLE `staff` ADD COLUMN `phone_number` VARCHAR(15) DEFAULT NULL AFTER `email`;
ALTER TABLE `staff` ADD INDEX `staff_phone_number_idx1` (`phone_number`);

ALTER TABLE `staff` ADD COLUMN `availability` VARCHAR(50) DEFAULT 'available' AFTER `phone_number`;
ALTER TABLE `staff` ADD INDEX `staff_availability_idx1` (`availability`);

ALTER TABLE `lecturer_courses` ADD COLUMN `notes` VARCHAR(15) DEFAULT 'active' AFTER `fk_semester`;
ALTER TABLE `lecturer_courses` ADD INDEX `lecturer_courses_note_idx1` (`notes`);
ALTER TABLE `lecturer_courses` ADD COLUMN `status` VARCHAR(15) DEFAULT 'teaching' AFTER `notes`;
ALTER TABLE `lecturer_courses` ADD INDEX `lecturer_courses_status_idx1` (`status`);
-- ALTER TABLE `lecturer_courses` ADD COLUMN `submission_deadline` DATE DEFAULT NULL AFTER `notes`;
-- ALTER TABLE `lecturer_courses` ADD INDEX `lecturer_courses_submission_deadline_idx1` (`submission_deadline`);
-- ALTER TABLE `lecturer_courses` ADD COLUMN `deadline_note` TEXT DEFAULT NULL AFTER `submission_deadline`;
-- ALTER TABLE `lecturer_courses` ADD INDEX `lecturer_courses_deadline_note_idx1` (`deadline_note`);
-- ALTER TABLE `lecturer_courses` ADD COLUMN `deadline_status` VARCHAR(15) DEFAULT NULL AFTER `deadline_note`;
-- ALTER TABLE `lecturer_courses` ADD INDEX `lecturer_courses_deadline_status_idx1` (`deadline_status`);

DROP TABLE IF EXISTS `class_advisor`;
DROP TABLE IF EXISTS `departments`;

ALTER TABLE `student_courses` ADD COLUMN `registered` TINYINT(1) DEFAULT 0 AFTER `status`;
ALTER TABLE `student_courses` ADD INDEX `student_courses_registered_idx1` (`registered`);

DROP TABLE IF EXISTS `exam_results`;
CREATE TABLE IF NOT EXISTS `exam_results` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `fk_class` VARCHAR(10),
    `fk_semester` INT(11),
    `fk_staff` VARCHAR(10),
    `fk_course` VARCHAR(10),
    `project_based` TINYINT(1) DEFAULT 0,
    `exam_score_weight` INT DEFAULT 60, 
    `project_score_weight` INT DEFAULT 20, 
    `assessment_score_weight` INT DEFAULT 40,
    `notes` TEXT DEFAULT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `status` VARCHAR(15) DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME,
    CONSTRAINT `fk_exam_results_class` FOREIGN KEY (`fk_class`) REFERENCES `class` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_exam_results_semester` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_exam_results_staff` FOREIGN KEY (`fk_staff`) REFERENCES `staff` (`number`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_exam_results_course` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE INDEX `exam_results_created_at_idx1` ON `exam_results` (`created_at`);
CREATE INDEX `exam_results_updated_at_idx1` ON `exam_results` (`updated_at`);
CREATE INDEX `exam_results_status_idx1` ON `exam_results` (`status`);
CREATE INDEX `exam_results_file_name_idx1` ON `exam_results` (`file_name`);

ALTER TABLE `curriculum` ADD COLUMN `archived` TINYINT(1) DEFAULT 0;

DROP TABLE IF EXISTS `student_courses`;
CREATE TABLE IF NOT EXISTS `student_courses` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `fk_student` VARCHAR(10), -- FK
    `fk_course` VARCHAR(10), -- FK
    `fk_semester` INT, -- FK
    `notes` TEXT,
    `credit_hours` INT NOT NULL,
    `level` INT NOT NULL,
    `semester` INT NOT NULL,
    `registered` TINYINT(1) DEFAULT 0,
    `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
  CONSTRAINT `fk_student_courses_student1` FOREIGN KEY (`fk_student`) REFERENCES `student` (`index_number`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_student_courses_course1` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_student_courses_semester1` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
);
CREATE INDEX student_courses_credits_idx1 ON `student_courses` (`credits`);
CREATE INDEX student_courses_level_idx1 ON `student_courses` (`level`);
CREATE INDEX student_courses_semester_idx1 ON `student_courses` (`semester`);
CREATE INDEX student_courses_registered_idx1 ON `student_courses` (`registered`);
CREATE INDEX student_courses_added_at_idx1 ON `student_courses` (`added_at`);


DROP TABLE IF EXISTS `student_results`;
CREATE TABLE IF NOT EXISTS `student_results` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `fk_student` VARCHAR(10),
    `fk_course` VARCHAR(10),
    `fk_semester` INT(11),
    `continues_assessments_score` DECIMAL(5,2) DEFAULT 0,
    `project_score` DECIMAL(5,2) DEFAULT 0,
    `exam_score` DECIMAL(5,2) DEFAULT 0,
    `final_score` DECIMAL(5,2) DEFAULT 0,
    `grade` VARCHAR(5) DEFAULT NULL,
    `gpa` DECIMAL(4,2) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME,
    CONSTRAINT `fk_student_results_student` FOREIGN KEY (`fk_student`) REFERENCES `student` (`index_number`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_student_results_course` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_student_results_semester` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `student_results` ADD INDEX `student_results_continues_assessments_score_idx1` (`continues_assessments_score`);
ALTER TABLE `student_results` ADD INDEX `student_results_project_score_idx1` (`project_score`);
ALTER TABLE `student_results` ADD INDEX `student_results_exam_idx1` (`exam_score`);
ALTER TABLE `student_results` ADD INDEX `student_results_final_score_idx1` (`final_score`);
ALTER TABLE `student_results` ADD INDEX `student_results_gpa_idx1` (`gpa`);
ALTER TABLE `student_results` ADD INDEX `student_results_grade_idx1` (`grade`);


ALTER TABLE `student_courses` 
ADD COLUMN `fk_semester_registered` INT AFTER `registered`;
ALTER TABLE `student_courses` ADD 
CONSTRAINT `fk_student_courses_semester_registered` FOREIGN KEY (`fk_semester_registered`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE `course_resources` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `fk_course` VARCHAR(10),
    `fk_staff` VARCHAR(10),
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(50) NOT NULL,
    `file_size` INT NOT NULL,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- identify if the resource is by a lecturer or by department
    `type` VARCHAR(50) DEFAULT 'lecturer', -- lecturer or department
    -- identity if the resource is public or private
    `visibility` VARCHAR(50) DEFAULT 'private', -- public or private
    CONSTRAINT `fk_course_resources_course` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_course_resources_staff` FOREIGN KEY (`fk_staff`) REFERENCES `staff` (`number`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;






