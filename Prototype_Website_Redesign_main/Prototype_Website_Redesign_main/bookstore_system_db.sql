-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 04:56 AM
-- Server version: 9.5.0
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookstore_system_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `course` varchar(20) DEFAULT NULL,
  `professor` varchar(100) DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT '1',
  `purchase_price` decimal(10,2) NOT NULL,
  `rental_price` decimal(10,2) NOT NULL,
  `condition_type` enum('New','Used') DEFAULT 'New',
  `inventory` int DEFAULT '0',
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `isbn`, `course`, `professor`, `is_required`, `purchase_price`, `rental_price`, `condition_type`, `inventory`, `image_url`, `created_at`) VALUES
(3, 'Secrets of Math', 'Dr. A', '123-1234-123', 'MA410', 'Prof. B', 1, 100.00, 25.00, 'New', 10, NULL, '2025-12-03 03:20:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('student','staff') DEFAULT 'student',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@mssu.edu', '$2y$10$abcdefg...', NULL, 'staff', '2025-12-03 02:24:09'),
(2, 'michael', 'michael@mssu.edu', '$2y$10$Jmnb.frUVk8W4GRkO46bIeILmCO/ZQNMnOXOFYjt7F4nGLf/fCgGu', NULL, 'student', '2025-12-03 02:54:52'),
(3, 'McTeacherson', 'McTeacherson@mssu.edu', '$2y$10$8BZe9ooshI/XKmJdifXu8u4ABgcU5v7J8FcGCmQy/jY3z7OjUJKKS', NULL, 'staff', '2025-12-03 03:01:03'),
(4, 'NewTeacher', 'newteacher@mssu.edu', '$2y$10$YBZ7lIbt68bjz1f/HK203eUA4mbDpaeHK9SLD.9yAmEQ59LEdK2i2', NULL, 'staff', '2025-12-03 03:18:18'),
(5, 'teststudent', 'student@yahoo.edu', '$2y$10$6D7XJVyk1W9VdDQvkNC5GuXNCnbYosURA6HIMQPqe2oUEOWjP432C', NULL, 'student', '2025-12-03 03:22:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
