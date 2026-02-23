-- ============================================
-- RMU ADMISSIONS SYSTEM DATABASE SCHEMA
-- ============================================
-- Database: rmu_admissions_test
-- Engine: InnoDB
-- Charset: utf8/utf8mb4
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================
-- INDEPENDENT TABLES (No Foreign Keys)
-- ============================================

-- Academic Year Table
CREATE TABLE `academic_year` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `active` TINYINT(1) DEFAULT 1,
  `start_month` VARCHAR(5) NOT NULL,
  `end_month` VARCHAR(5) NOT NULL,
  `start_year` YEAR(4) NOT NULL,
  `end_year` YEAR(4) NOT NULL,
  `archived` TINYINT(1) DEFAULT 0,
  `name` VARCHAR(15) GENERATED ALWAYS AS (CONCAT(`start_year`, '-', `end_year`)) VIRTUAL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Admission Letter Data Table
CREATE TABLE `admission_letter_data` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `year_of_admission` VARCHAR(255) NOT NULL,
  `commencement_date` VARCHAR(255) NOT NULL,
  `initial_fees_in_words` VARCHAR(255) NOT NULL,
  `initial_fees_in_figures` VARCHAR(255) NOT NULL,
  `tel_number_1` VARCHAR(20) NOT NULL,
  `tel_number_2` VARCHAR(20) NOT NULL,
  `closing_date` VARCHAR(20) NOT NULL,
  `orientation_date` VARCHAR(255) NOT NULL,
  `orientation_end_date` DATE DEFAULT NULL,
  `deadline_date` VARCHAR(255) NOT NULL,
  `registration_fees_in_words` VARCHAR(255) NOT NULL,
  `registration_fees_in_figures` VARCHAR(255) NOT NULL,
  `university_registrar` VARCHAR(255) NOT NULL,
  `in_use` TINYINT(1) DEFAULT 0,
  `current_dollar_rate` VARCHAR(10) DEFAULT '10.00',
  `new_dollar_rate` VARCHAR(10) DEFAULT '10.00',
  `new_dollar_rate_effective_date` DATE DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Course Category Table
CREATE TABLE `course_category` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(25) NOT NULL,
  `archived` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Departments Table (for admissions)
CREATE TABLE `departments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `archived` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Downloaded Awaiting Results Table
CREATE TABLE `downloaded_awaiting_results` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `admission_number` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Enrolled Applicants Table
CREATE TABLE `enrolled_applicants` (
  `index_number` VARCHAR(10) NOT NULL,
  `app_number` VARCHAR(10) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `phone_number` VARCHAR(15) NOT NULL,
  `prefix` VARCHAR(10) DEFAULT NULL,
  `first_name` VARCHAR(255) NOT NULL,
  `middle_name` VARCHAR(255) DEFAULT NULL,
  `last_name` VARCHAR(255) NOT NULL,
  `suffix` VARCHAR(10) DEFAULT NULL,
  `gender` VARCHAR(1) DEFAULT 'F',
  `dob` DATE NOT NULL,
  `nationality` VARCHAR(25) NOT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `marital_status` VARCHAR(255) DEFAULT NULL,
  `disability` VARCHAR(255) DEFAULT NULL,
  `date_admitted` DATE NOT NULL,
  `term_admitted` VARCHAR(15) NOT NULL,
  `stream_admitted` VARCHAR(15) NOT NULL,
  `academic_year_admitted` VARCHAR(15) NOT NULL,
  `department` VARCHAR(255) NOT NULL,
  `program` VARCHAR(255) NOT NULL,
  `class` VARCHAR(10) DEFAULT NULL,
  `archived` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`index_number`),
  UNIQUE KEY `app_number` (`app_number`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Faculty Table
CREATE TABLE `faculty` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Fail Payment Logs Table
CREATE TABLE `fail_payment_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `log_data` TEXT DEFAULT NULL,
  `error_message` TEXT DEFAULT NULL,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Fee Item Table
CREATE TABLE `fee_item` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `value` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Fee Structure Category Table
CREATE TABLE `fee_structure_category` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Fee Structure Type Table
CREATE TABLE `fee_structure_type` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Form Categories Table
CREATE TABLE `form_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Grade Points Table
CREATE TABLE `grade_points` (
  `grade` VARCHAR(2) NOT NULL,
  `point` DECIMAL(3,2) DEFAULT NULL,
  PRIMARY KEY (`grade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Grades Table
CREATE TABLE `grades` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `grade` VARCHAR(2) NOT NULL,
  `type` VARCHAR(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Halls Table
CREATE TABLE `halls` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- High School Subjects Table
CREATE TABLE `high_sch_subjects` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(10) NOT NULL,
  `subject` VARCHAR(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- High School Courses Table
CREATE TABLE `high_shcool_courses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(10) DEFAULT NULL,
  `course` VARCHAR(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Notifications Table
CREATE TABLE `notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `type` ENUM('general', 'applicant') DEFAULT 'applicant',
  `to` INT(11) DEFAULT NULL,
  `read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Payment Method Table
CREATE TABLE `payment_method` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(15) NOT NULL,
  PRIMARY KEY (`name`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Role Table
CREATE TABLE `role` (
  `name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Settings Table
CREATE TABLE `settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `value` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Success Payment Logs Table
CREATE TABLE `success_payment_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category` VARCHAR(50) NOT NULL,
  `log_data` TEXT DEFAULT NULL,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- System Users Table
CREATE TABLE `sys_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(30) NOT NULL,
  `last_name` VARCHAR(30) NOT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) NOT NULL,
  `type` VARCHAR(5) DEFAULT 'user',
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- USSD Activity Logs Table
CREATE TABLE `ussd_activity_logs` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(255) DEFAULT NULL,
  `service_code` VARCHAR(255) DEFAULT NULL,
  `msisdn` VARCHAR(15) DEFAULT NULL,
  `msg_type` INT(11) DEFAULT NULL,
  `ussd_body` VARCHAR(255) DEFAULT NULL,
  `nw_code` VARCHAR(2) DEFAULT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- USSD Request Logs Table
CREATE TABLE `ussd_request_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `where` VARCHAR(255) DEFAULT 'forms',
  `request` TEXT DEFAULT NULL,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Years Table
CREATE TABLE `years` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `year` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ============================================
-- DEPENDENT TABLES (Level 1 - Basic Dependencies)
-- ============================================

-- Admission Period Table
CREATE TABLE `admission_period` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `academic_year` VARCHAR(9) DEFAULT NULL,
  `semester` VARCHAR(9) DEFAULT NULL,
  `info` TEXT DEFAULT NULL,
  `intake` VARCHAR(15) DEFAULT NULL,
  `active` TINYINT(4) DEFAULT 0,
  `closed` TINYINT(4) DEFAULT 0,
  `fk_academic_year` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_admissions_period_acad_year1` (`fk_academic_year`),
  CONSTRAINT `fk_admission_period_academic_year` FOREIGN KEY (`fk_academic_year`) REFERENCES `academic_year` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Department Table (academic)
CREATE TABLE `department` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(15) DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `fk_faculty` INT(11) DEFAULT NULL,
  `hod` VARCHAR(10) DEFAULT NULL,
  `archived` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `department_fk_faculty_idx1` (`fk_faculty`),
  KEY `fk_department_hod` (`hod`),
  CONSTRAINT `fk_department_faculty` FOREIGN KEY (`fk_faculty`) REFERENCES `faculty` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Forms Table
CREATE TABLE `forms` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `form_category` INT(11) NOT NULL,
  `name` VARCHAR(120) DEFAULT NULL,
  `amount` DECIMAL(6,2) NOT NULL,
  `non_member_amount` DECIMAL(5,2) DEFAULT 50.00,
  `dollar_cedis_rate` DECIMAL(5,2) DEFAULT 10.00,
  `member_amount` DECIMAL(5,2) GENERATED ALWAYS AS (`amount` / `dollar_cedis_rate`) VIRTUAL,
  PRIMARY KEY (`id`),
  KEY `fk_form_category` (`form_category`),
  CONSTRAINT `fk_form_category` FOREIGN KEY (`form_category`) REFERENCES `form_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Semester Table
CREATE TABLE `semester` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `active` TINYINT(1) DEFAULT 1,
  `name` INT(11) NOT NULL,
  `type` VARCHAR(30) DEFAULT 'continuing students',
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `exam_registration_start_date` DATE DEFAULT NULL,
  `exam_registration_end_date` DATE DEFAULT NULL,
  `exam_start_date` DATE DEFAULT NULL,
  `exam_end_date` DATE DEFAULT NULL,
  `resit_exam_start_date` DATE DEFAULT NULL,
  `resit_exam_end_date` DATE DEFAULT NULL,
  `resit_exam_registration_start_date` DATE DEFAULT NULL,
  `resit_exam_registration_end_date` DATE DEFAULT NULL,
  `resit_exam_results_uploaded` DATE DEFAULT NULL,
  `course_registration_opened` TINYINT(1) DEFAULT 0,
  `registration_end` DATE DEFAULT NULL,
  `exam_results_uploaded` TINYINT(1) DEFAULT 0,
  `archived` TINYINT(1) DEFAULT 0,
  `fk_academic_year` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_semester_academic_year1` (`fk_academic_year`),
  CONSTRAINT `fk_semester_academic_year1` FOREIGN KEY (`fk_academic_year`) REFERENCES `academic_year` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- System Users Privileges Table
CREATE TABLE `sys_users_privileges` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `select` TINYINT(1) NOT NULL DEFAULT 0,
  `insert` TINYINT(1) NOT NULL DEFAULT 0,
  `update` TINYINT(1) NOT NULL DEFAULT 0,
  `delete` TINYINT(1) NOT NULL DEFAULT 0,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_sys_users_id` (`user_id`),
  CONSTRAINT `fk_sys_users_id` FOREIGN KEY (`user_id`) REFERENCES `sys_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Vendor Details Table
CREATE TABLE `vendor_details` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(10) NOT NULL,
  `company` VARCHAR(100) DEFAULT NULL,
  `company_code` VARCHAR(3) DEFAULT NULL,
  `phone_number` VARCHAR(13) NOT NULL,
  `branch` VARCHAR(100) DEFAULT 'MAIN',
  `role` VARCHAR(50) DEFAULT NULL,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` INT(11) DEFAULT NULL,
  `api_user` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ============================================
-- DEPENDENT TABLES (Level 2)
-- ============================================

-- Activity Logs Table
CREATE TABLE `activity_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fk_department` INT(11) DEFAULT 1,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `operation` ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
  `type` VARCHAR(50) DEFAULT 'admin',
  `action` VARCHAR(100) DEFAULT 'login',
  `description` TEXT NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_activities_logs_department` (`fk_department`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_activity_logs_department` FOREIGN KEY (`fk_department`) REFERENCES `department` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- API Users Table
CREATE TABLE `api_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `vendor_id` INT(11) NOT NULL,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_vendors_api_users` (`vendor_id`),
  CONSTRAINT `fk_api_users_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendor_details` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Course Table
CREATE TABLE `course` (
  `code` VARCHAR(10) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `credit_hours` INT(11) NOT NULL,
  `contact_hours` INT(11) DEFAULT NULL,
  `semester` INT(11) NOT NULL,
  `level` INT(11) NOT NULL,
  `archived` TINYINT(1) DEFAULT 0,
  `fk_category` INT(11) DEFAULT NULL,
  `fk_department` INT(11) DEFAULT NULL,
  PRIMARY KEY (`code`),
  KEY `fk_course_category1` (`fk_category`),
  KEY `fk_course_department1` (`fk_department`),
  CONSTRAINT `fk_course_category` FOREIGN KEY (`fk_category`) REFERENCES `course_category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_course_department` FOREIGN KEY (`fk_department`) REFERENCES `department` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Foreign Form Purchase Requests Table
CREATE TABLE `foreign_form_purchase_requests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `reference_number` VARCHAR(12) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email_address` VARCHAR(255) NOT NULL,
  `p_country_name` VARCHAR(100) NOT NULL,
  `p_country_code` VARCHAR(10) NOT NULL,
  `phone_number` VARCHAR(15) NOT NULL,
  `s_country_name` VARCHAR(100) NOT NULL,
  `s_country_code` VARCHAR(10) NOT NULL,
  `support_number` VARCHAR(15) NOT NULL,
  `app_number` VARCHAR(12) DEFAULT NULL,
  `form` INT(11) DEFAULT NULL,
  `admission_period` INT(11) DEFAULT NULL,
  `status` VARCHAR(10) DEFAULT 'pending',
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_number` (`reference_number`),
  KEY `fk_ffpr_form` (`form`),
  KEY `fk_ffpr_admission_period` (`admission_period`),
  CONSTRAINT `fk_ffpr_admission_period` FOREIGN KEY (`admission_period`) REFERENCES `admission_period` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ffpr_form` FOREIGN KEY (`form`) REFERENCES `forms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Programs Table
CREATE TABLE `programs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `merit` VARCHAR(255) DEFAULT NULL,
  `department` INT(11) DEFAULT NULL,
  `regulation` VARCHAR(50) DEFAULT NULL,
  `category` VARCHAR(25) DEFAULT 'DEGREE',
  `code` VARCHAR(20) DEFAULT NULL,
  `index_code` VARCHAR(5) DEFAULT NULL,
  `faculty` VARCHAR(100) DEFAULT NULL,
  `duration` VARCHAR(50) DEFAULT NULL,
  `dur_format` VARCHAR(50) DEFAULT NULL,
  `num_of_semesters` INT(11) DEFAULT 8,
  `type` INT(11) NOT NULL,
  `regular` TINYINT(1) DEFAULT 1,
  `weekend` TINYINT(4) DEFAULT 0,
  `group` CHAR(1) DEFAULT NULL,
  `archived` TINYINT(1) DEFAULT 0,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_prog_form_type` (`type`),
  KEY `fk_program_department` (`department`),
  CONSTRAINT `fk_prog_form_type` FOREIGN KEY (`type`) REFERENCES `forms` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_program_department` FOREIGN KEY (`department`) REFERENCES `departments` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Room Table
CREATE TABLE `room` (
  `number` VARCHAR(10) NOT NULL,
  `capacity` INT(11) NOT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `longitude` VARCHAR(255) DEFAULT NULL,
  `latitude` VARCHAR(255) DEFAULT NULL,
  `archived` TINYINT(1) DEFAULT 0,
  `fk_department` INT(11) DEFAULT NULL,
  PRIMARY KEY (`number`),
  KEY `fk_room_department1` (`fk_department`),
  CONSTRAINT `fk_room_department1` FOREIGN KEY (`fk_department`) REFERENCES `department` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Staff Table
CREATE TABLE `staff` (
  `number` VARCHAR(10) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone_number` VARCHAR(15) DEFAULT NULL,
  `availability` VARCHAR(50) DEFAULT 'available',
  `password` VARCHAR(255) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `first_name` VARCHAR(255) NOT NULL,
  `middle_name` VARCHAR(255) DEFAULT NULL,
  `last_name` VARCHAR(255) NOT NULL,
  `prefix` VARCHAR(10) DEFAULT NULL,
  `gender` VARCHAR(1) DEFAULT 'F',
  `designation` VARCHAR(100) DEFAULT NULL,
  `role` VARCHAR(50) NOT NULL,
  `archived` TINYINT(1) DEFAULT 0,
  `fk_department` INT(11) DEFAULT NULL,
  PRIMARY KEY (`number`),
  KEY `fk_staff_department1` (`fk_department`),
  CONSTRAINT `fk_staff_department1` FOREIGN KEY (`fk_department`) REFERENCES `department` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ============================================
-- DEPENDENT TABLES (Level 3)
-- ============================================

-- API Request Logs Table
CREATE TABLE `api_request_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `request` TEXT DEFAULT NULL,
  `route` VARCHAR(255) DEFAULT NULL,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `api_user` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_api_user_req_logs` (`api_user`),
  CONSTRAINT `fk_api_request_logs_api_user` FOREIGN KEY (`api_user`) REFERENCES `api_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Class Table
CREATE TABLE `class` (
  `code` VARCHAR(10) NOT NULL,
  `fk_program` INT(11) NOT NULL,
  `fk_staff` VARCHAR(10) DEFAULT NULL,
  `year` TEXT DEFAULT NULL,
  `category` VARCHAR(50) DEFAULT 'regular',
  `archived` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`code`),
  KEY `fk_class_program1` (`fk_program`),
  KEY `fk_class_staff` (`fk_staff`),
  CONSTRAINT `fk_class_program` FOREIGN KEY (`fk_program`) REFERENCES `programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_class_staff` FOREIGN KEY (`fk_staff`) REFERENCES `staff` (`number`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Class Advisor Table
CREATE TABLE `class_advisor` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fk_class` VARCHAR(10) DEFAULT NULL,
  `fk_staff` VARCHAR(10) DEFAULT NULL,
  `archived` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_class_advisor_class` (`fk_class`),
  KEY `fk_class_advisor_staff` (`fk_staff`),
  CONSTRAINT `fk_class_advisor_class` FOREIGN KEY (`fk_class`) REFERENCES `class` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_class_advisor_staff` FOREIGN KEY (`fk_staff`) REFERENCES `staff` (`number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Course Resources Table
CREATE TABLE `course_resources` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fk_course` VARCHAR(10) DEFAULT NULL,
  `fk_staff` VARCHAR(10) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(50) NOT NULL,
  `file_size` INT(11) NOT NULL,
  `uploaded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` VARCHAR(50) DEFAULT 'lecturer',
  `visibility` VARCHAR(50) DEFAULT 'private',
  PRIMARY KEY (`id`),
  KEY `fk_course_resources_course` (`fk_course`),
  KEY `fk_course_resources_staff` (`fk_staff`),
  CONSTRAINT `fk_course_resources_course` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_course_resources_staff` FOREIGN KEY (`fk_staff`) REFERENCES `staff` (`number`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Curriculum Table
CREATE TABLE `curriculum` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fk_program` INT(11) DEFAULT NULL,
  `fk_course` VARCHAR(10) DEFAULT NULL,
  `archived` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_curriculum_program1` (`fk_program`),
  KEY `fk_curriculum_course1` (`fk_course`),
  CONSTRAINT `fk_curriculum_course` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_curriculum_program` FOREIGN KEY (`fk_program`) REFERENCES `programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Deadlines Table
CREATE TABLE `deadlines` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fk_department` INT(11) DEFAULT NULL,
  `fk_semester` INT(11) DEFAULT NULL,
  `fk_course` VARCHAR(10) DEFAULT NULL,
  `fk_class` VARCHAR(10) DEFAULT NULL,
  `fk_staff` VARCHAR(10) DEFAULT NULL,
  `due_date` DATE DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `status` VARCHAR(15) DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_deadlines_department` (`fk_department`),
  KEY `fk_deadlines_semester` (`fk_semester`),
  KEY `fk_deadlines_course` (`fk_course`),
  KEY `fk_deadlines_class` (`fk_class`),
  KEY `fk_deadlines_staff` (`fk_staff`),
  CONSTRAINT `fk_deadlines_class` FOREIGN KEY (`fk_class`) REFERENCES `class` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_deadlines_course` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_deadlines_department` FOREIGN KEY (`fk_department`) REFERENCES `department` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_deadlines_semester` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_deadlines_staff` FOREIGN KEY (`fk_staff`) REFERENCES `staff` (`number`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Fee Structure Table
CREATE TABLE `fee_structure` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `currency` VARCHAR(5) DEFAULT 'USD',
  `fk_program_id` INT(11) NOT NULL,
  `type` VARCHAR(15) NOT NULL,
  `category` VARCHAR(15) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `member_amount` DECIMAL(10,2) DEFAULT NULL,
  `non_member_amount` DECIMAL(10,2) DEFAULT NULL,
  `file` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_program_id` (`fk_program_id`),
  CONSTRAINT `fee_structure_ibfk_1` FOREIGN KEY (`fk_program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Fee Structure Item Table
CREATE TABLE `fee_structure_item` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `currency` VARCHAR(5) DEFAULT 'USD',
  `fk_fee_structure` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `member_amount` DECIMAL(10,2) NOT NULL,
  `non_member_amount` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fee_structure_item_ibfk_1` (`fk_fee_structure`),
  CONSTRAINT `fee_structure_item_ibfk_1` FOREIGN KEY (`fk_fee_structure`) REFERENCES `fee_structure` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Lecturer Courses Table
CREATE TABLE `lecturer_courses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fk_department` INT(11) DEFAULT NULL,
  `fk_staff` VARCHAR(10) DEFAULT NULL,
  `fk_course` VARCHAR(10) DEFAULT NULL,
  `fk_semester` INT(11) DEFAULT NULL,
  `notes` VARCHAR(15) DEFAULT 'active',
  `status` VARCHAR(15) DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_lecturer_course_assignments_department` (`fk_department`),
  KEY `fk_lecturer_course_assignments_staff` (`fk_staff`),
  KEY `fk_lecturer_course_assignments_course` (`fk_course`),
  KEY `fk_lecturer_course_assignments_semester` (`fk_semester`),
  CONSTRAINT `fk_lecturer_course_assignments_course` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lecturer_course_assignments_department` FOREIGN KEY (`fk_department`) REFERENCES `department` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lecturer_course_assignments_semester` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lecturer_course_assignments_staff` FOREIGN KEY (`fk_staff`) REFERENCES `staff` (`number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Purchase Detail Table
CREATE TABLE `purchase_detail` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `sold_by` VARCHAR(200) DEFAULT NULL,
  `ext_trans_id` VARCHAR(255) DEFAULT NULL,
  `ext_trans_datetime` DATETIME DEFAULT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email_address` VARCHAR(100) DEFAULT NULL,
  `country_name` VARCHAR(30) NOT NULL,
  `country_code` VARCHAR(30) NOT NULL,
  `phone_number` VARCHAR(15) NOT NULL,
  `amount` DECIMAL(6,2) NOT NULL,
  `app_number` VARCHAR(10) NOT NULL,
  `pin_number` VARCHAR(10) NOT NULL,
  `status` VARCHAR(10) DEFAULT 'PENDING',
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vendor` INT(11) NOT NULL,
  `form_id` INT(11) NOT NULL,
  `admission_period` INT(11) NOT NULL,
  `payment_method` VARCHAR(20) DEFAULT NULL,
  `deleted` TINYINT(1) DEFAULT 0,
  `sms_sent` TINYINT(1) DEFAULT 0,
  `email_sent` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_purchase_vendor_details` (`vendor`),
  KEY `fk_purchase_admission_period` (`admission_period`),
  KEY `form_id` (`form_id`),
  CONSTRAINT `fk_purchase_admission_period` FOREIGN KEY (`admission_period`) REFERENCES `admission_period` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_purchase_vendor_details` FOREIGN KEY (`vendor`) REFERENCES `vendor_details` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_detail_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Section Table
CREATE TABLE `section` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fk_class` VARCHAR(10) DEFAULT NULL,
  `fk_course` VARCHAR(10) DEFAULT NULL,
  `fk_semester` INT(11) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `credit_hours` INT(11) NOT NULL,
  `level` INT(11) NOT NULL,
  `semester` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_section_class1` (`fk_class`),
  KEY `fk_section_course1` (`fk_course`),
  KEY `fk_section_semester` (`fk_semester`),
  CONSTRAINT `fk_section_class1` FOREIGN KEY (`fk_class`) REFERENCES `class` (`code`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_section_course1` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_section_semester` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Shortlisted Applications Table
CREATE TABLE `shortlisted_applications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `app_login` INT(11) NOT NULL,
  `program_id` INT(11) NOT NULL,
  `program_duration` INT(11) DEFAULT NULL,
  `admission_period` INT(11) DEFAULT NULL,
  `stream` VARCHAR(100) NOT NULL,
  `level` VARCHAR(50) NOT NULL,
  `send_email` TINYINT(1) NOT NULL DEFAULT 0,
  `send_sms` TINYINT(1) NOT NULL DEFAULT 0,
  `status` VARCHAR(15) NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_applicant_login` (`app_login`),
  KEY `fk_shortlisted_applications_admission_period` (`admission_period`),
  CONSTRAINT `fk_shortlisted_applications_admission_period` FOREIGN KEY (`admission_period`) REFERENCES `admission_period` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ============================================
-- DEPENDENT TABLES (Level 4 - Applicants)
-- ============================================

-- Applicants Login Table
CREATE TABLE `applicants_login` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `app_number` VARCHAR(255) NOT NULL,
  `pin` VARCHAR(255) NOT NULL,
  `deleted` TINYINT(1) DEFAULT 1,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `purchase_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_number` (`app_number`),
  KEY `fk_purchase_id` (`purchase_id`),
  CONSTRAINT `fk_applicants_login_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchase_detail` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Exam Results Table
CREATE TABLE `exam_results` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fk_class` VARCHAR(10) DEFAULT NULL,
  `fk_semester` INT(11) DEFAULT NULL,
  `fk_staff` VARCHAR(10) DEFAULT NULL,
  `fk_course` VARCHAR(10) DEFAULT NULL,
  `project_based` TINYINT(1) DEFAULT 0,
  `exam_score_weight` INT(11) DEFAULT 60,
  `project_score_weight` INT(11) DEFAULT 20,
  `assessment_score_weight` INT(11) DEFAULT 40,
  `notes` TEXT DEFAULT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `status` VARCHAR(15) DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_exam_results_class` (`fk_class`),
  KEY `fk_exam_results_semester` (`fk_semester`),
  KEY `fk_exam_results_staff` (`fk_staff`),
  KEY `fk_exam_results_course` (`fk_course`),
  CONSTRAINT `fk_exam_results_class` FOREIGN KEY (`fk_class`) REFERENCES `class` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_exam_results_course` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_exam_results_semester` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_exam_results_staff` FOREIGN KEY (`fk_staff`) REFERENCES `staff` (`number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Lecture Table
CREATE TABLE `lecture` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `archived` TINYINT(1) DEFAULT 0,
  `fk_staff` VARCHAR(20) DEFAULT NULL,
  `fk_section` INT(11) DEFAULT NULL,
  `fk_semester` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_lecture_staff1` (`fk_staff`),
  KEY `fk_lecture_section1` (`fk_section`),
  KEY `lecture_semester1` (`fk_semester`),
  CONSTRAINT `fk_lecture_section1` FOREIGN KEY (`fk_section`) REFERENCES `section` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_lecture_staff1` FOREIGN KEY (`fk_staff`) REFERENCES `staff` (`number`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `lecture_semester1` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Schedule Table
CREATE TABLE `schedule` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `day_of_week` VARCHAR(10) NOT NULL,
  `course_crdt_hrs` INT(11) DEFAULT 0,
  `start_time` TIME NOT NULL,
  `minutes` INT(11) DEFAULT 50,
  `end_time` TIME GENERATED ALWAYS AS (`start_time` + `course_crdt_hrs` * `minutes`) VIRTUAL,
  `archived` TINYINT(1) DEFAULT 0,
  `fk_section` INT(11) DEFAULT NULL,
  `fk_room` VARCHAR(10) DEFAULT NULL,
  `fk_semester` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_schedule_section1` (`fk_section`),
  KEY `fk_schedule_room1` (`fk_room`),
  KEY `fk_schedule_semester1` (`fk_semester`),
  CONSTRAINT `fk_schedule_room1` FOREIGN KEY (`fk_room`) REFERENCES `room` (`number`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_schedule_section1` FOREIGN KEY (`fk_section`) REFERENCES `section` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_schedule_semester1` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ============================================
-- DEPENDENT TABLES (Level 5 - Applicant Related)
-- ============================================

-- Academic Background Table
CREATE TABLE `academic_background` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `s_number` INT(11) NOT NULL,
  `school_name` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `region` VARCHAR(100) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `cert_type` VARCHAR(20) DEFAULT NULL,
  `other_cert_type` VARCHAR(100) DEFAULT NULL,
  `index_number` VARCHAR(20) DEFAULT NULL,
  `month_started` VARCHAR(3) DEFAULT NULL,
  `year_started` VARCHAR(4) DEFAULT NULL,
  `month_completed` VARCHAR(3) DEFAULT NULL,
  `year_completed` VARCHAR(4) DEFAULT NULL,
  `course_of_study` VARCHAR(100) DEFAULT NULL,
  `other_course_studied` VARCHAR(100) DEFAULT NULL,
  `awaiting_result` TINYINT(4) DEFAULT 0,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `app_login` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `s_number` (`s_number`),
  KEY `fk_app_aca_bac` (`app_login`),
  CONSTRAINT `fk_academic_background_app_login` FOREIGN KEY (`app_login`) REFERENCES `applicants_login` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Acceptance Receipts Table
CREATE TABLE `acceptance_receipts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `bank_name` VARCHAR(255) NOT NULL,
  `bank_branch` VARCHAR(255) NOT NULL,
  `payment_date` DATE NOT NULL,
  `transaction_identifier` VARCHAR(255) NOT NULL,
  `receipt_image` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `app_login` INT(11) DEFAULT NULL,
  `admission_period` INT(11) DEFAULT NULL,
  `status` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_applicant_acceptance` (`app_login`),
  KEY `fk_acceptance_receipts_admission_period` (`admission_period`),
  CONSTRAINT `fk_acceptance_receipts_admission_period` FOREIGN KEY (`admission_period`) REFERENCES `admission_period` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_acceptance_receipts_app_login` FOREIGN KEY (`app_login`) REFERENCES `applicants_login` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Applicant Uploads Table
CREATE TABLE `applicant_uploads` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(25) DEFAULT NULL,
  `edu_code` INT(11) DEFAULT NULL,
  `file_name` VARCHAR(50) DEFAULT NULL,
  `linked_to` INT(11) DEFAULT NULL,
  `app_login` INT(11) NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_uploaded_files` (`app_login`),
  CONSTRAINT `fk_applicant_uploads_app_login` FOREIGN KEY (`app_login`) REFERENCES `applicants_login` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Broadsheets Table
CREATE TABLE `broadsheets` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `admin_period` INT(11) NOT NULL,
  `app_login` INT(11) NOT NULL,
  `program_id` INT(11) NOT NULL,
  `mode` VARCHAR(20) DEFAULT NULL,
  `required_core_passed` INT(11) NOT NULL,
  `required_core_subjects` TEXT DEFAULT NULL,
  `required_elective_passed` INT(11) DEFAULT NULL,
  `required_elective_subjects` TEXT DEFAULT NULL,
  `any_elective_subjects` TEXT DEFAULT NULL,
  `total_core_score` INT(11) DEFAULT NULL,
  `total_elective_score` INT(11) NOT NULL,
  `total_score` INT(11) NOT NULL,
  `program_choice` VARCHAR(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_admin_broadsheets` (`admin_period`),
  KEY `fk_app_broadsheets` (`app_login`),
  KEY `fk_program_broadsheets` (`program_id`),
  CONSTRAINT `fk_broadsheets_admin_period` FOREIGN KEY (`admin_period`) REFERENCES `admission_period` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_broadsheets_app_login` FOREIGN KEY (`app_login`) REFERENCES `applicants_login` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_broadsheets_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Form Sections Check Table
CREATE TABLE `form_sections_chek` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `personal` TINYINT(4) DEFAULT 0,
  `education` TINYINT(4) DEFAULT 0,
  `programme` TINYINT(4) DEFAULT 0,
  `uploads` TINYINT(4) DEFAULT 0,
  `declaration` TINYINT(4) DEFAULT 0,
  `reviewed` TINYINT(1) DEFAULT 0,
  `admitted` TINYINT(4) DEFAULT 0,
  `declined` TINYINT(1) NOT NULL DEFAULT 0,
  `enrolled` TINYINT(1) DEFAULT 0,
  `printed` TINYINT(1) DEFAULT 0,
  `notified_sms` TINYINT(1) DEFAULT 0,
  `emailed_letter` TINYINT(1) DEFAULT 0,
  `programme_awarded` INT(11) DEFAULT NULL,
  `programme_duration` INT(11) DEFAULT NULL,
  `level_admitted` INT(11) DEFAULT NULL,
  `shortlisted` TINYINT(1) DEFAULT 0,
  `stream_admitted` VARCHAR(30) DEFAULT NULL,
  `letter_path` TEXT DEFAULT NULL,
  `app_login` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_app_form_sec_check` (`app_login`),
  CONSTRAINT `fk_app_form_sec_check` FOREIGN KEY (`app_login`) REFERENCES `applicants_login` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Heard About Us Table
CREATE TABLE `heard_about_us` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `medium` VARCHAR(50) NOT NULL,
  `description` VARCHAR(50) DEFAULT NULL,
  `app_login` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_heard_abt_us` (`app_login`),
  CONSTRAINT `fk_heard_abt_us` FOREIGN KEY (`app_login`) REFERENCES `applicants_login` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Personal Information Table
CREATE TABLE `personal_information` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `prefix` VARCHAR(10) DEFAULT NULL,
  `first_name` VARCHAR(100) DEFAULT NULL,
  `middle_name` VARCHAR(100) DEFAULT NULL,
  `last_name` VARCHAR(100) DEFAULT NULL,
  `suffix` VARCHAR(10) DEFAULT NULL,
  `gender` VARCHAR(7) DEFAULT NULL,
  `dob` DATE DEFAULT NULL,
  `marital_status` VARCHAR(25) DEFAULT NULL,
  `nationality` VARCHAR(25) DEFAULT NULL,
  `country_res` VARCHAR(25) DEFAULT NULL,
  `disability` TINYINT(4) DEFAULT NULL,
  `disability_descript` VARCHAR(25) DEFAULT NULL,
  `photo` VARCHAR(25) DEFAULT NULL,
  `country_birth` VARCHAR(25) DEFAULT NULL,
  `spr_birth` VARCHAR(25) DEFAULT NULL,
  `city_birth` VARCHAR(25) DEFAULT NULL,
  `english_native` TINYINT(4) DEFAULT NULL,
  `speaks_english` TINYINT(4) DEFAULT NULL,
  `other_language` VARCHAR(25) DEFAULT NULL,
  `postal_addr` VARCHAR(255) DEFAULT NULL,
  `postal_town` VARCHAR(50) DEFAULT NULL,
  `postal_spr` VARCHAR(50) DEFAULT NULL,
  `postal_country` VARCHAR(50) DEFAULT NULL,
  `phone_no1_code` VARCHAR(5) DEFAULT NULL,
  `phone_no1` VARCHAR(13) DEFAULT NULL,
  `phone_no2_code` VARCHAR(5) DEFAULT NULL,
  `phone_no2` VARCHAR(13) DEFAULT NULL,
  `email_addr` VARCHAR(50) DEFAULT NULL,
  `p_prefix` VARCHAR(10) DEFAULT NULL,
  `p_first_name` VARCHAR(100) DEFAULT NULL,
  `p_last_name` VARCHAR(100) DEFAULT NULL,
  `p_occupation` VARCHAR(50) DEFAULT NULL,
  `p_phone_no_code` VARCHAR(5) DEFAULT NULL,
  `p_phone_no` VARCHAR(13) DEFAULT NULL,
  `p_email_addr` VARCHAR(50) DEFAULT NULL,
  `e_contact_name` VARCHAR(200) DEFAULT NULL,
  `e_contact_code` VARCHAR(10) DEFAULT NULL,
  `e_contact_phone` VARCHAR(200) DEFAULT NULL,
  `e_contact_email` VARCHAR(200) DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `app_login` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_app_pf` (`app_login`),
  CONSTRAINT `fk_app_pf` FOREIGN KEY (`app_login`) REFERENCES `applicants_login` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Previous University Records Table
CREATE TABLE `previous_uni_records` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pre_uni_rec` TINYINT(4) DEFAULT 0,
  `name_of_uni` VARCHAR(150) DEFAULT NULL,
  `program` VARCHAR(150) DEFAULT NULL,
  `month_enrolled` VARCHAR(3) DEFAULT NULL,
  `year_enrolled` VARCHAR(4) DEFAULT NULL,
  `completed` TINYINT(4) DEFAULT 0,
  `month_completed` VARCHAR(3) DEFAULT NULL,
  `year_completed` VARCHAR(4) DEFAULT NULL,
  `state` VARCHAR(25) DEFAULT NULL,
  `reasons` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `app_login` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_app_prev_uni` (`app_login`),
  CONSTRAINT `fk_app_prev_uni` FOREIGN KEY (`app_login`) REFERENCES `applicants_login` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Program Info Table
CREATE TABLE `program_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category` VARCHAR(20) DEFAULT NULL,
  `first_prog` VARCHAR(100) DEFAULT NULL,
  `second_prog` VARCHAR(100) DEFAULT NULL,
  `application_term` VARCHAR(15) DEFAULT NULL,
  `study_stream` VARCHAR(15) DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `app_login` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_app_prog_info` (`app_login`),
  CONSTRAINT `fk_app_prog_info` FOREIGN KEY (`app_login`) REFERENCES `applicants_login` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ============================================
-- DEPENDENT TABLES (Level 6 - Students)
-- ============================================

-- Student Table
CREATE TABLE `student` (
  `index_number` VARCHAR(10) NOT NULL,
  `app_number` VARCHAR(10) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `phone_number` VARCHAR(15) NOT NULL,
  `prefix` VARCHAR(10) DEFAULT NULL,
  `first_name` VARCHAR(255) NOT NULL,
  `middle_name` VARCHAR(255) DEFAULT NULL,
  `last_name` VARCHAR(255) NOT NULL,
  `suffix` VARCHAR(10) DEFAULT NULL,
  `gender` VARCHAR(1) DEFAULT 'F',
  `dob` DATE NOT NULL,
  `nationality` VARCHAR(25) NOT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `marital_status` VARCHAR(25) DEFAULT NULL,
  `disability` VARCHAR(25) DEFAULT NULL,
  `date_admitted` DATE NOT NULL,
  `term_admitted` VARCHAR(15) NOT NULL,
  `stream_admitted` VARCHAR(15) NOT NULL,
  `level_admitted` INT(11) DEFAULT 100,
  `programme_duration` INT(11) DEFAULT 4,
  `default_password` TINYINT(1) DEFAULT 1,
  `semester_setup` TINYINT(1) DEFAULT 0,
  `archived` TINYINT(1) DEFAULT 0,
  `fk_academic_year` INT(11) DEFAULT NULL,
  `fk_applicant` INT(11) DEFAULT NULL,
  `fk_department` INT(11) DEFAULT NULL,
  `fk_program` INT(11) DEFAULT NULL,
  `fk_class` VARCHAR(10) DEFAULT NULL,
  PRIMARY KEY (`index_number`),
  UNIQUE KEY `app_number` (`app_number`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_student_academic_year1` (`fk_academic_year`),
  KEY `fk_student_applicant1` (`fk_applicant`),
  KEY `fk_student_department1` (`fk_department`),
  KEY `fk_student_program1` (`fk_program`),
  KEY `fk_student_class1` (`fk_class`),
  CONSTRAINT `fk_student_academic_year1` FOREIGN KEY (`fk_academic_year`) REFERENCES `academic_year` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_student_applicant1` FOREIGN KEY (`fk_applicant`) REFERENCES `applicants_login` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_student_class1` FOREIGN KEY (`fk_class`) REFERENCES `class` (`code`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_student_department1` FOREIGN KEY (`fk_department`) REFERENCES `department` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_student_program1` FOREIGN KEY (`fk_program`) REFERENCES `programs` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- High School Results Table
CREATE TABLE `high_school_results` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(10) DEFAULT 'core',
  `subject` VARCHAR(100) NOT NULL,
  `grade` VARCHAR(2) NOT NULL,
  `acad_back_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_grades_aca_bac` (`acad_back_id`),
  CONSTRAINT `fk_grades_aca_bac` FOREIGN KEY (`acad_back_id`) REFERENCES `academic_background` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ============================================
-- DEPENDENT TABLES (Level 7 - Student Related)
-- ============================================

-- Course Registration Table
CREATE TABLE `course_registration` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fk_course` VARCHAR(10) DEFAULT NULL,
  `fk_student` VARCHAR(10) DEFAULT NULL,
  `fk_semester` INT(11) DEFAULT NULL,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_course_registration_course` (`fk_course`),
  KEY `fk_course_registration_student` (`fk_student`),
  KEY `fk_course_registration_semester` (`fk_semester`),
  CONSTRAINT `fk_course_registration_course` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_course_registration_semester` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_course_registration_student` FOREIGN KEY (`fk_student`) REFERENCES `student` (`index_number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Level Table
CREATE TABLE `level` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `level` INT(11) NOT NULL,
  `semester` INT(11) NOT NULL,
  `deferred` TINYINT(1) DEFAULT 0,
  `completed` TINYINT(1) DEFAULT 0,
  `active` TINYINT(1) DEFAULT 0,
  `archived` TINYINT(1) DEFAULT 0,
  `fk_student` VARCHAR(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_course_registration_student1` (`fk_student`),
  CONSTRAINT `fk_course_registration_student1` FOREIGN KEY (`fk_student`) REFERENCES `student` (`index_number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Student Courses Table
CREATE TABLE `student_courses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fk_student` VARCHAR(10) DEFAULT NULL,
  `fk_course` VARCHAR(10) DEFAULT NULL,
  `fk_semester` INT(11) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `credit_hours` INT(11) NOT NULL,
  `level` INT(11) NOT NULL,
  `semester` INT(11) NOT NULL,
  `status` VARCHAR(15) DEFAULT 'active',
  `registered` TINYINT(1) DEFAULT 0,
  `fk_semester_registered` INT(11) DEFAULT NULL,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_assigned_courses_student1` (`fk_student`),
  KEY `fk_assigned_courses_course1` (`fk_course`),
  KEY `fk_student_course_assignments_semester` (`fk_semester`),
  KEY `fk_student_courses_semester_registered` (`fk_semester_registered`),
  CONSTRAINT `fk_assigned_courses_course1` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_assigned_courses_student1` FOREIGN KEY (`fk_student`) REFERENCES `student` (`index_number`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_student_course_assignments_semester` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_student_courses_semester_registered` FOREIGN KEY (`fk_semester_registered`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Student Results Table
CREATE TABLE `student_results` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fk_student` VARCHAR(10) DEFAULT NULL,
  `fk_course` VARCHAR(10) DEFAULT NULL,
  `fk_semester` INT(11) DEFAULT NULL,
  `continues_assessments_score` DECIMAL(5,2) DEFAULT 0.00,
  `project_score` DECIMAL(5,2) DEFAULT 0.00,
  `exam_score` DECIMAL(5,2) DEFAULT 0.00,
  `final_score` DECIMAL(5,2) DEFAULT 0.00,
  `grade` VARCHAR(5) DEFAULT NULL,
  `gpa` DECIMAL(4,2) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_student_results_student` (`fk_student`),
  KEY `fk_student_results_course` (`fk_course`),
  KEY `fk_student_results_semester` (`fk_semester`),
  CONSTRAINT `fk_student_results_course` FOREIGN KEY (`fk_course`) REFERENCES `course` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_student_results_semester` FOREIGN KEY (`fk_semester`) REFERENCES `semester` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_student_results_student` FOREIGN KEY (`fk_student`) REFERENCES `student` (`index_number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ============================================
-- STORED PROCEDURES
-- ============================================

DELIMITER $$

-- Calculate all students GPA/CGPA in department
CREATE PROCEDURE `calculate_all_students_gpa_cgpa_in_department` (IN `in_department_id` INT, IN `in_semester_id` INT)
BEGIN
    SELECT 
        s.index_number,
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
END$$

-- Calculate CGPA for a student
CREATE PROCEDURE `calculate_cgpa` (IN `in_student_id` VARCHAR(10))
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
END$$

-- Calculate GPA for a student in a semester
CREATE PROCEDURE `calculate_gpa` (IN `in_student_id` VARCHAR(10), IN `in_semester_id` INT)
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
END$$

-- Calculate GPA and CGPA with totals
CREATE PROCEDURE `calculate_gpa_cgpa` (IN `in_student_id` VARCHAR(10), IN `in_semester_id` INT)
BEGIN
    DECLARE gpa DECIMAL(4,2);
    DECLARE cgpa DECIMAL(4,2);
    DECLARE total_credits INT;
    DECLARE total_courses INT;

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

    SELECT SUM(credit_hours) INTO total_credits FROM student_courses WHERE fk_student = in_student_id;
    SELECT COUNT(*) INTO total_courses FROM student_courses WHERE fk_student = in_student_id;

    SELECT gpa, cgpa, total_credits, total_courses;
END$$

-- Calculate students GPA/CGPA for a semester
CREATE PROCEDURE `calculate_students_gpa_cgpa` (IN `in_semester_id` INT)
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
END$$

-- Recalculate grades by semester
CREATE PROCEDURE `recalc_grades_by_semester` (IN `in_semester_id` INT)
BEGIN
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

    UPDATE student_results sr
    JOIN grade_points gp ON sr.grade = gp.grade
    SET sr.gpa = gp.point
    WHERE sr.fk_semester = in_semester_id;

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
END$$

-- Recalculate grades by semester and course
CREATE PROCEDURE `recalc_grades_by_semester_course` (IN `in_semester_id` INT, IN `in_course_code` VARCHAR(10))
BEGIN
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

    UPDATE student_results sr
    JOIN grade_points gp ON sr.grade = gp.grade
    SET sr.gpa = gp.point
    WHERE sr.fk_semester = in_semester_id
      AND sr.fk_course = in_course_code;

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
END$$

DELIMITER ;
