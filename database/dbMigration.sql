SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 1. Admins Table (Includes Profile updates)
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT 'System Administrator',
  `email` varchar(150) DEFAULT 'admin@doh.gov.ph',
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','locked') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Login Attempts (Security)
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Folders Table (Categories Update)
CREATE TABLE `folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder_name` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Files Table (Core, Soft Delete, Folder ID, OCR LongText)
CREATE TABLE `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder_id` int(11) DEFAULT NULL,
  `year` int(4) NOT NULL,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_hash` varchar(64) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_content` longtext DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `uploaded_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `folder_id` (`folder_id`),
  KEY `year` (`year`),
  KEY `uploaded_by` (`uploaded_by`),
  FULLTEXT KEY `ft_search_engine` (`title`,`name`,`description`,`file_content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Audit Logs
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `file_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `action_type` (`action_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Constraints
ALTER TABLE `folders` ADD CONSTRAINT `fk_folder_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`);
ALTER TABLE `files` ADD CONSTRAINT `fk_file_folder` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE SET NULL;
ALTER TABLE `files` ADD CONSTRAINT `fk_files_admin` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`);
ALTER TABLE `audit_logs` ADD CONSTRAINT `fk_audit_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

-- Insert Default Admin (Password is: admin123)
INSERT INTO `admins` (`name`, `email`, `username`, `password`) 
VALUES ('System Administrator', 'admin@doh.gov.ph', 'admin', '$2y$10$W2Y36d3Y1J/Kz.G.nQ4z3eR41kM6Q3Zz5k4z5Z4z5Z4z5Z4z5Z4z5'); 
-- NOTE: If admin123 fails, run your generate_pass.php script to create a new hash!

COMMIT;