SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 1. Admins Table
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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

-- 3. Files Table (Core)
CREATE TABLE `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_hash` varchar(64) NOT NULL, -- SHA256
  `file_size` int(11) NOT NULL,
  `file_content` longtext DEFAULT NULL, -- Extracted PDF text
  `uploaded_by` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `uploaded_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `year` (`year`),
  KEY `uploaded_by` (`uploaded_by`),
  FULLTEXT KEY `ft_search` (`title`,`name`,`description`,`file_content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Audit Logs
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
ALTER TABLE `files` ADD CONSTRAINT `fk_files_admin` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`);
ALTER TABLE `audit_logs` ADD CONSTRAINT `fk_audit_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`);

-- Default Admin (Password: admin123)
INSERT INTO `admins` (`username`, `password`) VALUES ('admin', '$2y$10$8.Kk.exampleHashPlaceholderForAdmin123');
COMMIT;