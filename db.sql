ALTER TABLE staff ADD COLUMN `designation` VARCHAR(100) AFTER `gender`;

CREATE TABLE IF NOT EXISTS `lecture_course_assignments` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,

    `department_id` INT(11),
    `lecturer_id` VARCHAR(10),
    `course_code` VARCHAR(10),
    `semester_id` INT(11),

    `lecture_day` VARCHAR(15),
    `lecture_period` VARCHAR(15),
    `room_number` VARCHAR(10),
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_lecturer_course_assignments_department_id` FOREIGN KEY (`department_id`) REFERENCES `department` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_lecturer_course_assignments_lecturer_id` FOREIGN KEY (`lecturer_id`) REFERENCES `staff` (`number`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_lecturer_course_assignments_course_code` FOREIGN KEY (`course_code`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_lecturer_course_assignments_semester_id` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE INDEX `lecture_day_idx1` ON `lecture_course_assignments` (`lecture_day`);
CREATE INDEX `lecture_period_idx1` ON `lecture_course_assignments` (`lecture_period`);
CREATE INDEX `room_number_idx1` ON `lecture_course_assignments` (`room_number`);
CREATE INDEX `created_at_idx1` ON `lecture_course_assignments` (`created_at`);
CREATE INDEX `updated_at_idx1` ON `lecture_course_assignments` (`updated_at`);

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
    `lecture_course_assignment_id` INT,
    `lecturer_id` VARCHAR(10),
    `date` DATE,
    `note` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_deadlines_lecture_course_assignment_id` FOREIGN KEY (`lecture_course_assignment_id`) REFERENCES `lecture_course_assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_deadlines_lecturer_id` FOREIGN KEY (`lecturer_id`) REFERENCES `staff` (`number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE INDEX `deadlines_date_idx1` ON `deadlines` (`date`);
CREATE INDEX `deadlines_created_at_idx1` ON `deadlines` (`created_at`);
CREATE INDEX `deadlines_updated_at_idx1` ON `deadlines` (`updated_at`);




