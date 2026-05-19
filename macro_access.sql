-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2026
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `macro_access`
--

-- --------------------------------------------------------

--
-- Table structure for table `test_records`
--

CREATE TABLE `test_records` (
  `id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `age` int(3) DEFAULT NULL,
  `sex` char(1) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `meth_result` enum('NEGATIVE','POSITIVE','INVALID') DEFAULT 'NEGATIVE',
  `thc_result` enum('NEGATIVE','POSITIVE','INVALID') DEFAULT 'NEGATIVE',
  `photo_path` varchar(255) DEFAULT NULL,
  `date_tested` datetime DEFAULT current_timestamp(),
  `added_by` int(11) DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `position` enum('ADMIN','CHIEF TECHNOLOGIST','STAFF') DEFAULT 'STAFF',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_requested` timestamp NULL DEFAULT NULL,
  `reset_approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `password`, `position`, `created_at`) VALUES
(1, 'admin', 'Administrator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', '2026-05-08 16:08:55');

-- --------------------------------------------------------

--
-- Sample data for `test_records`
--

INSERT INTO `test_records` (`client_name`, `age`, `sex`, `birth_date`, `company_name`, `meth_result`, `thc_result`, `date_tested`) VALUES
('Jepoy Dimagiba', 25, 'M', '1999-01-15', 'Jollibee Foods Corp', 'NEGATIVE', 'NEGATIVE', NOW()),
('Marites Dela Cruz', 42, 'F', '1984-03-22', 'SM Supermarket', 'POSITIVE', 'NEGATIVE', NOW()),
('Ramon Tolentino', 35, 'M', '1989-07-08', 'Meralco', 'NEGATIVE', 'POSITIVE', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Teresita Macapagal', 28, 'F', '1996-08-14', 'Philippine Airlines', 'POSITIVE', 'POSITIVE', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Roberto Samonte', 50, 'M', '1974-01-20', 'San Miguel Corp', 'NEGATIVE', 'NEGATIVE', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Crisanta Reyes', 31, 'F', '1995-05-12', 'Globe Telecom', 'POSITIVE', 'POSITIVE', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Andres Bonifacio', 45, 'M', '1979-11-30', 'LBC Express', 'NEGATIVE', 'NEGATIVE', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('Luzviminda Hernandez', 38, 'F', '1986-09-25', 'Puregold', 'POSITIVE', 'NEGATIVE', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('Gregorio Fernandez', 29, 'M', '1997-02-18', 'BDO Unibank', 'NEGATIVE', 'POSITIVE', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('Herminia Santos', 52, 'F', '1972-07-04', 'Mercury Drug', 'POSITIVE', 'POSITIVE', DATE_SUB(NOW(), INTERVAL 4 DAY));

--
-- Indexes for dumped tables
--

--
-- Indexes for table `test_records`
--
ALTER TABLE `test_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `test_records`
--
ALTER TABLE `test_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `test_records`
--
ALTER TABLE `test_records`
  ADD CONSTRAINT `test_records_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;