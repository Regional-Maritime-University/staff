ALTER TABLE staff ADD COLUMN `designation` VARCHAR(100) AFTER `gender`;

CREATE TABLE IF NOT EXISTS `lecture_course_assignments` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `staff_number` VARCHAR(10) NOT NULL,
    `course_code` VARCHAR(10) NOT NULL,
    `lecture_day` VARCHAR(15) NOT NULL,
    `lecture_period` VARCHAR(15) NOT NULL,
    `room_number` VARCHAR(10)
);
CREATE INDEX `staff_number_idx1` ON `lecture_course_assignments` (`staff_number`);
CREATE INDEX `course_code_idx1` ON `lecture_course_assignments` (`course_code`);
CREATE INDEX `lecture_day_idx1` ON `lecture_course_assignments` (`lecture_day`);
CREATE INDEX `lecture_period_idx1` ON `lecture_course_assignments` (`lecture_period`);
CREATE INDEX `room_number_idx1` ON `lecture_course_assignments` (`room_number`);

ALTER TABLE `lecture_course_assignments` ADD COLUMN `semester_id` INT(11) NOT NULL AFTER `course_code`;
ALTER TABLE `lecture_course_assignments` ADD COLUMN `notes` INT(11) NOT NULL AFTER `room_number`;
-- add department_id column to lecture_course_assignments table
ALTER TABLE `lecture_course_assignments` ADD COLUMN `department_id` INT(11) NOT NULL AFTER `notes`;
 -- 
ALTER TABLE `lecture_course_assignments` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `notes`;
ALTER TABLE `lecture_course_assignments` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

ALTER TABLE `lecture_course_assignments` ADD FOREIGN KEY (`staff_number`) REFERENCES `staff` (`number`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `lecture_course_assignments` ADD FOREIGN KEY (`course_code`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `lecture_course_assignments` ADD FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `lecture_course_assignments` ADD INDEX `created_at_idx1` (`created_at`);
ALTER TABLE `lecture_course_assignments` ADD INDEX `updated_at_idx1` (`updated_at`);



