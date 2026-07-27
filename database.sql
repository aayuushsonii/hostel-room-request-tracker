-- Hostel Room Requirement & Allocation Tracker Database Schema
-- Database: hostel_db

CREATE DATABASE IF NOT EXISTS `hostel_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hostel_db`;

-- 1. Table for Admin Users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('admin', 'warden') DEFAULT 'admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Default Admin (Username: admin, Password: admin123)
INSERT INTO `users` (`username`, `password`, `full_name`, `role`) VALUES
('admin', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1zY8G7n3/j5J5VnS1gQW/1E11223344', 'Hostel Warden', 'admin')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- 2. Table for Registered Students
CREATE TABLE IF NOT EXISTS `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `roll_number` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Default Demo Student (Email: student@college.edu, Password: student123)
INSERT INTO `students` (`full_name`, `email`, `roll_number`, `password`) VALUES
('Rahul Sharma', 'student@college.edu', '2023CS105', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1zY8G7n3/j5J5VnS1gQW/1E11223344'),
('Priya Verma', 'priya@college.edu', '2024EC210', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1zY8G7n3/j5J5VnS1gQW/1E11223344')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- 3. Table for Hostel Rooms
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_number` VARCHAR(20) NOT NULL UNIQUE,
  `block_name` VARCHAR(50) NOT NULL,
  `floor` INT NOT NULL,
  `room_type` ENUM('Single', 'Double', 'Triple') NOT NULL,
  `air_conditioned` TINYINT(1) DEFAULT 0,
  `total_beds` INT NOT NULL,
  `occupied_beds` INT DEFAULT 0,
  `monthly_rent` DECIMAL(10,2) NOT NULL,
  `status` ENUM('Available', 'Full', 'Maintenance') DEFAULT 'Available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Sample Rooms
INSERT INTO `rooms` (`room_number`, `block_name`, `floor`, `room_type`, `air_conditioned`, `total_beds`, `occupied_beds`, `monthly_rent`, `status`) VALUES
('A-101', 'Block A (Boys)', 1, 'Single', 1, 1, 0, 8500.00, 'Available'),
('A-102', 'Block A (Boys)', 1, 'Double', 1, 2, 1, 6500.00, 'Available'),
('A-201', 'Block A (Boys)', 2, 'Triple', 0, 3, 3, 4500.00, 'Full'),
('B-101', 'Block B (Girls)', 1, 'Single', 1, 1, 0, 9000.00, 'Available'),
('B-102', 'Block B (Girls)', 1, 'Double', 0, 2, 1, 6000.00, 'Available'),
('B-205', 'Block B (Girls)', 2, 'Double', 1, 2, 0, 7000.00, 'Maintenance')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- 4. Table for Room Allocation / Requirement Requests
CREATE TABLE IF NOT EXISTS `room_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `request_code` VARCHAR(20) NOT NULL UNIQUE,
  `student_name` VARCHAR(100) NOT NULL,
  `roll_number` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `course` VARCHAR(50) NOT NULL,
  `year_of_study` INT NOT NULL,
  `preferred_room_type` ENUM('Single', 'Double', 'Triple') NOT NULL,
  `ac_preference` TINYINT(1) DEFAULT 0,
  `special_requirements` TEXT,
  `status` ENUM('Pending', 'Approved', 'Rejected', 'Allocated') DEFAULT 'Pending',
  `allocated_room_id` INT NULL,
  `admin_remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`allocated_room_id`) REFERENCES `rooms`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Sample Requests
INSERT INTO `room_requests` (`request_code`, `student_name`, `roll_number`, `email`, `phone`, `gender`, `course`, `year_of_study`, `preferred_room_type`, `ac_preference`, `special_requirements`, `status`, `allocated_room_id`, `admin_remarks`) VALUES
('REQ-2026-101', 'Rahul Sharma', '2023CS105', 'student@college.edu', '9876543210', 'Male', 'B.Tech CSE', 2, 'Single', 1, 'Ground floor preferred if possible', 'Allocated', 1, 'Allocated Room A-101'),
('REQ-2026-102', 'Priya Verma', '2024EC210', 'priya@college.edu', '9876543211', 'Female', 'B.Tech ECE', 1, 'Double', 1, 'Quiet room for studying', 'Pending', NULL, NULL);

-- 5. Table for Maintenance Complaints
CREATE TABLE IF NOT EXISTS `complaints` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` VARCHAR(20) NOT NULL UNIQUE,
  `student_name` VARCHAR(100) NOT NULL,
  `roll_number` VARCHAR(50) NOT NULL,
  `room_number` VARCHAR(20) NOT NULL,
  `category` ENUM('Plumbing', 'Electrical', 'Wi-Fi', 'Furniture', 'Cleaning', 'Other') NOT NULL,
  `description` TEXT NOT NULL,
  `status` ENUM('Open', 'In Progress', 'Resolved') DEFAULT 'Open',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Sample Complaints
INSERT INTO `complaints` (`ticket_id`, `student_name`, `roll_number`, `room_number`, `category`, `description`, `status`) VALUES
('TKT-901', 'Rahul Sharma', '2023CS105', 'A-101', 'Electrical', 'AC remote fan speed control button not functioning.', 'In Progress');
