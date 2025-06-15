-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 15, 2025 at 03:19 PM
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
-- Database: `bandcafe_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `practice_records`
--

CREATE TABLE `practice_records` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `attended` tinyint(1) NOT NULL DEFAULT 0,
  `points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `practice_requests`
--

CREATE TABLE `practice_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `transport_to_venue` tinyint(1) NOT NULL DEFAULT 0,
  `transport_to_home` tinyint(1) NOT NULL DEFAULT 0,
  `pickup_time` time DEFAULT NULL,
  `pickup_address` varchar(255) DEFAULT NULL,
  `dropoff_time` time DEFAULT NULL,
  `dropoff_address` varchar(255) DEFAULT NULL,
  `target_goal` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `instrument` varchar(50) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `instrument`, `section`) VALUES
(1, 'admin', '$2y$10$93wdTcpXQNPTFDJbhTQCGODITi3vK9r3bgpBzs0CwGT0ZQiU64qwi', 'admin', 'Conductor', 'Admin'),
(2, 'testuser', '$2y$10$93wdTcpXQNPTFDJbhTQCGODITi3vK9r3bgpBzs0CwGT0ZQiU64qwi', 'user', 'Trumpet', 'Brass'),
(3, 'jane_smith', '$2y$10$93wdTcpXQNPTFDJbhTQCGODITi3vK9r3bgpBzs0CwGT0ZQiU64qwi', 'user', 'Clarinet', 'Woodwind'),
(8, 'hon2838', '$2y$10$93wdTcpXQNPTFDJbhTQCGODITi3vK9r3bgpBzs0CwGT0ZQiU64qwi', 'user', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `practice_records`
--
ALTER TABLE `practice_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `practice_requests`
--
ALTER TABLE `practice_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `practice_records`
--
ALTER TABLE `practice_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `practice_requests`
--
ALTER TABLE `practice_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `practice_records`
--
ALTER TABLE `practice_records`
  ADD CONSTRAINT `practice_records_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `practice_requests` (`id`),
  ADD CONSTRAINT `practice_records_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `practice_requests`
--
ALTER TABLE `practice_requests`
  ADD CONSTRAINT `practice_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
