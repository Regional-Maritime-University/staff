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

-- Trigger to calculate the final score and grade when a new record is inserted
DROP TRIGGER IF EXISTS `student_results_insert_trigger`;
DELIMITER //
CREATE TRIGGER `student_results_insert_trigger`
BEFORE INSERT ON `student_results`
FOR EACH ROW
BEGIN
    DECLARE final_score DECIMAL(5,2);
    DECLARE v_grade VARCHAR(5);
    DECLARE v_point DECIMAL(4,2);

    -- Handle missing grade point
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_point = 0.00;

    -- Calculate final score safely (handle NULLs)
    SET final_score = 
        COALESCE(NEW.continues_assessments_score, 0) +
        COALESCE(NEW.project_score, 0) +
        COALESCE(NEW.exam_score, 0);

    SET NEW.final_score = final_score;

    -- Determine grade
    IF final_score >= 80 THEN
        SET v_grade = 'A';
    ELSEIF final_score >= 75 THEN
        SET v_grade = 'A-';
    ELSEIF final_score >= 70 THEN
        SET v_grade = 'B+';
    ELSEIF final_score >= 65 THEN
        SET v_grade = 'B';
    ELSEIF final_score >= 60 THEN
        SET v_grade = 'C+';
    ELSEIF final_score >= 55 THEN
        SET v_grade = 'C';
    ELSEIF final_score >= 50 THEN
        SET v_grade = 'D';
    ELSEIF final_score >= 45 THEN
        SET v_grade = 'E';
    ELSE
        SET v_grade = 'F';
    END IF;

    SET NEW.grade = v_grade;

    -- Lookup GPA point from grade_points
    SELECT gp.point INTO v_point
    FROM grade_points gp
    WHERE gp.grade = v_grade
    LIMIT 1;

    SET NEW.gpa = v_point;
END;
//
DELIMITER ;

-- Trigger to update the final score and grade when assessment scores are updated
DROP TRIGGER IF EXISTS `student_results_update_trigger`;
DELIMITER //
CREATE TRIGGER `student_results_update_trigger`
BEFORE UPDATE ON `student_results`
FOR EACH ROW
BEGIN
    DECLARE final_score DECIMAL(5,2);
    DECLARE v_grade VARCHAR(5);
    DECLARE v_point DECIMAL(4,2);

    -- Handle missing grade point
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_point = 0.00;

    -- Only recalc if scores changed
    IF OLD.continues_assessments_score != NEW.continues_assessments_score 
       OR OLD.project_score != NEW.project_score 
       OR OLD.exam_score != NEW.exam_score THEN

        SET final_score = 
            COALESCE(NEW.continues_assessments_score, 0) +
            COALESCE(NEW.project_score, 0) +
            COALESCE(NEW.exam_score, 0);

        SET NEW.final_score = final_score;

        -- Determine grade
        IF final_score >= 80 THEN
            SET v_grade = 'A';
        ELSEIF final_score >= 75 THEN
            SET v_grade = 'A-';
        ELSEIF final_score >= 70 THEN
            SET v_grade = 'B+';
        ELSEIF final_score >= 65 THEN
            SET v_grade = 'B';
        ELSEIF final_score >= 60 THEN
            SET v_grade = 'C+';
        ELSEIF final_score >= 55 THEN
            SET v_grade = 'C';
        ELSEIF final_score >= 50 THEN
            SET v_grade = 'D';
        ELSEIF final_score >= 45 THEN
            SET v_grade = 'E';
        ELSE
            SET v_grade = 'F';
        END IF;

        SET NEW.grade = v_grade;

        SELECT gp.point INTO v_point
        FROM grade_points gp
        WHERE gp.grade = v_grade
        LIMIT 1;

        SET NEW.gpa = v_point;
    END IF;
END;
//
DELIMITER ;

-- Trigger to recalc GPA per semester after any update
DROP TRIGGER IF EXISTS `student_results_update_gpa_trigger`;
DELIMITER //
CREATE TRIGGER `student_results_update_gpa_trigger`
AFTER UPDATE ON `student_results`
FOR EACH ROW
BEGIN
    DECLARE gpa DECIMAL(4,2) DEFAULT 0.00;

    -- Calculate GPA for this student in the current semester
    SELECT 
        ROUND(SUM(gp.point * c.credit_hours) / NULLIF(SUM(c.credit_hours), 0), 2)
    INTO gpa
    FROM student_results sr
    JOIN grade_points gp ON sr.grade = gp.grade
    JOIN course c ON sr.fk_course = c.code
    WHERE sr.fk_student = NEW.fk_student 
      AND sr.fk_semester = NEW.fk_semester;

    -- Update all rows for this student in that semester with the GPA
    UPDATE student_results
    SET gpa = gpa
    WHERE fk_student = NEW.fk_student 
      AND fk_semester = NEW.fk_semester;
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
        ROUND(SUM(gp.point * sca.credit_hours) / NULLIF(SUM(sca.credit_hours), 0), 2)
    AS gpa
    FROM student_courses AS sca
    JOIN grade_points AS gp ON sca.grade = gp.grade
    WHERE sca.fk_student = in_student_id AND sca.fk_semester = in_semester_id;
END;
//
DELIMITER ;

-- Calculate CGPA for a specific student in a specific semester
-- This procedure will return the CGPA for a specific student in a given semester
DROP PROCEDURE IF EXISTS calculate_cgpa;
DELIMITER //
CREATE PROCEDURE calculate_cgpa (IN in_student_id VARCHAR(10))
BEGIN
    SELECT 
        ROUND(SUM(gp.point * sca.credit_hours) / SUM(sca.credit_hours), 2)
    AS cgpa 
    FROM student_courses AS sca 
    JOIN grade_points AS gp ON sca.grade = gp.grade 
    WHERE sca.fk_student = in_student_id;
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

    -- GPA for the given semester
    SELECT 
        ROUND(SUM(gp.point * sca.credit_hours) / NULLIF(SUM(sca.credit_hours), 0), 2)
    INTO gpa
    FROM student_courses AS sca
    JOIN grade_points AS gp ON sca.grade = gp.grade
    WHERE sca.fk_student = in_student_id AND sca.fk_semester = in_semester_id;

    -- CGPA across all semesters
    SELECT 
        ROUND(SUM(gp.point * sca.credit_hours) / NULLIF(SUM(sca.credit_hours), 0), 2)
    INTO cgpa 
    FROM student_courses AS sca 
    JOIN grade_points AS gp ON sca.grade = gp.grade 
    WHERE sca.fk_student = in_student_id;

    -- Total credits earned by student
    SELECT 
        SUM(credit_hours)
    INTO total_credits
    FROM student_courses
    WHERE fk_student = in_student_id;

    -- Total number of courses registered by student
    SELECT 
        COUNT(*)
    INTO total_courses
    FROM student_courses
    WHERE fk_student = in_student_id;

    -- Return all results
    SELECT 
        gpa AS gpa, 
        cgpa AS cgpa,
        total_credits AS total_credits,
        total_courses AS total_courses;
END;
//
DELIMITER ;


-- Calculate CGPA for a specific student in a specific semester
DROP PROCEDURE IF EXISTS calculate_student_semester_gpa;
DELIMITER //
CREATE PROCEDURE calculate_student_semester_gpa (
    IN in_student_id VARCHAR(10), 
    IN in_semester_id INT
)
BEGIN
    -- declare local variable
    DECLARE gpa DECIMAL(4,2);

    -- calculate GPA
    SELECT 
        ROUND(SUM(gp.point * sca.credit_hours) / NULLIF(SUM(sca.credit_hours), 0), 2)
    INTO gpa
    FROM student_courses AS sca
    JOIN grade_points AS gp ON sca.grade = gp.grade
    WHERE sca.fk_student = in_student_id 
      AND sca.fk_semester = in_semester_id;

    -- return result
    SELECT gpa AS semester_gpa;
END;
//
DELIMITER ;


-- Calculate CGPA for a specific student in a specific semester
-- This procedure will return the CGPA for a specific student in a given semester
DROP PROCEDURE IF EXISTS calculate_student_semester_cgpa;
DELIMITER //
CREATE PROCEDURE calculate_student_semester_cgpa (IN in_student_id VARCHAR(10), IN in_semester_id INT)
BEGIN
    DECLARE cumulative_gpa DECIMAL(4,2);

    SELECT 
        ROUND(SUM(gp.point * sca.credit_hours) / SUM(sca.credit_hours), 2)
    INTO cumulative_gpa 
    FROM student_courses AS sca 
    JOIN grade_points AS gp ON sca.grade = gp.grade 
    WHERE sca.fk_student = in_student_id;
    SELECT cumulative_gpa AS cgpa;
END;
//
DELIMITER ;

-- Calculate GPA and CGPA for all students in a specific semester
-- This procedure will return the GPA and CGPA for all students in a given semester

DROP PROCEDURE IF EXISTS calculate_students_gpa_cgpa;
DELIMITER //
CREATE PROCEDURE calculate_students_gpa_cgpa(IN in_semester_id INT)
BEGIN
    SELECT 
        s.index_number, 
        ROUND(SUM(CASE WHEN sca.fk_semester = in_semester_id THEN gp.point * sca.credit_hours ELSE 0 END) /
            NULLIF(SUM(CASE WHEN sca.fk_semester = in_semester_id THEN sca.credit_hours ELSE 0 END), 0), 2) AS gpa,
        ROUND(SUM(gp.point * sca.credit_hours) / NULLIF(SUM(sca.credit_hours), 0), 2) AS cgpa
    FROM student AS s 
    JOIN student_courses AS sca ON s.index_number = sca.fk_student 
    JOIN grade_points AS gp ON sca.grade = gp.grade 
    WHERE s.archived = 0 
    GROUP BY s.index_number;
END;
//
DELIMITER ;

-- Calculate GPA and CGPA for all students in a specific semester
-- This procedure will return the GPA and CGPA for all students in a given semester

DROP PROCEDURE IF EXISTS calculate_all_students_gpa_cgpa;
DELIMITER //
CREATE PROCEDURE calculate_all_students_gpa_cgpa(IN in_department_id INT, IN in_semester_id INT)
BEGIN
    SELECT 
        s.index_number, 
        ROUND(SUM(CASE WHEN sca.fk_semester = in_semester_id THEN gp.point * sca.credit_hours ELSE 0 END) /
            NULLIF(SUM(CASE WHEN sca.fk_semester = in_semester_id THEN sca.credit_hours ELSE 0 END), 0), 2) AS gpa,
        ROUND(SUM(gp.point * sca.credit_hours) / NULLIF(SUM(sca.credit_hours), 0), 2) AS cgpa
    FROM student AS s 
    JOIN student_courses AS sca ON s.index_number = sca.fk_student 
    JOIN grade_points AS gp ON sca.grade = gp.grade 
    JOIN department AS d ON s.fk_department = d.id 
    WHERE d.id = in_department_id AND s.archived = 0 
    GROUP BY s.index_number;
END;
//
DELIMITER ;


-- Calculate GPA and CGPA for all students in a specific semester and department

DROP PROCEDURE IF EXISTS calculate_all_students_gpa_cgpa_in_department;
DELIMITER //
CREATE PROCEDURE calculate_all_students_gpa_cgpa_in_department(
    IN in_department_id INT,
    IN in_semester_id INT
)
BEGIN
    SELECT 
        s.index_number, 
        -- GPA for specified semester
        ROUND(
            SUM(CASE 
                WHEN sca.fk_semester = in_semester_id THEN gp.point * sca.credit_hours 
                ELSE 0 
            END) / NULLIF(SUM(CASE 
                WHEN sca.fk_semester = in_semester_id THEN sca.credit_hours 
                ELSE 0 
            END), 0), 
        2) AS gpa,
        -- CGPA across all semesters
        ROUND(SUM(gp.point * sca.credit_hours) / NULLIF(SUM(sca.credit_hours), 0), 2) AS cgpa

    FROM students s
    JOIN student_courses sca ON s.index_number = sca.fk_student
    JOIN grade_points gp ON sca.grade = gp.grade
    JOIN department d ON s.fk_department = d.id
    WHERE s.archived = 0 AND d.id = in_department_id
    GROUP BY s.index_number, s.name, d.name;
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









