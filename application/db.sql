-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2025 at 06:31 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ganttchart`
--

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `status` int(2) NOT NULL DEFAULT 0 COMMENT '0 = in progress || 1 = completed || 2 = on hold',
  `completed_at` date DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `created_by`, `status`, `completed_at`, `created_at`) VALUES
(1, 'Avecon', '', 0, NULL, '2025-04-23 13:02:58'),
(3, 'Go Green Technology - Mobile App', '', 0, NULL, '2025-04-24 09:47:00'),
(4, 'Suryagarh', '', 0, NULL, '2025-04-25 04:48:03'),
(5, 'Verodirect', '', 0, NULL, '2025-04-25 04:48:58'),
(6, 'Kaam-chor Job portal', '', 0, NULL, '2025-04-25 04:54:23'),
(7, 'The Unifly Collective - Barefoot Junoon ', '', 0, NULL, '2025-04-25 05:01:55'),
(9, 'SAAS project', '', 0, NULL, '2025-04-25 05:32:44'),
(10, 'Bina hand blocks', '', 0, NULL, '2025-04-25 05:32:58'),
(11, 'Go Green Website', '', 0, NULL, '2025-04-25 05:33:44'),
(12, 'Home Connect', '', 0, NULL, '2025-04-25 05:34:51'),
(13, 'Tell Demm', '', 0, NULL, '2025-04-25 05:35:35'),
(14, 'Plabcoach', '', 0, NULL, '2025-04-25 05:35:51'),
(15, 'Sony World - Mobile App', '', 0, NULL, '2025-04-25 06:04:58'),
(17, 'Cohere', '', 0, NULL, '2025-04-25 12:24:16'),
(18, 'Wealth advice', 'shivam', 0, NULL, '2025-04-25 12:27:27'),
(19, 'Testing-Project-19-05-25', 'Shivam', 1, '2025-05-20', '2025-05-19 07:11:22');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `task_name` varchar(255) NOT NULL,
  `assigned_to` varchar(255) DEFAULT NULL,
  `is_note_task` int(2) NOT NULL DEFAULT 0,
  `start_date` date NOT NULL,
  `expected_end_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `progress` int(3) DEFAULT 0,
  `status` int(11) NOT NULL COMMENT '0 = in progress || 1 = completed || 2 = hold',
  `modified_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `task_name`, `assigned_to`, `is_note_task`, `start_date`, `expected_end_date`, `end_date`, `progress`, `status`, `modified_at`) VALUES
(1, 1, 'Ui design', 'Gautam', 0, '2025-04-23', '2025-05-19', '2025-06-10', 55, 2, '2025-05-19'),
(2, 1, 'Ui designnn', 'Gautam', 0, '2025-04-23', '2025-05-19', '2025-04-30', 30, 2, '2025-05-19'),
(3, 1, 'web design', 'Gautam', 0, '2025-04-24', NULL, '2025-04-30', 20, 0, '2025-05-19'),
(4, 1, 'backend developement', 'Gautam', 0, '2025-04-23', NULL, '2025-04-30', 10, 0, '2025-05-19'),
(5, 1, 'live website', 'Gautam', 0, '2025-04-25', NULL, '2025-05-29', 0, 0, '2025-05-19'),
(6, 1, 'new ui design', 'shivam', 0, '2025-04-30', NULL, '2025-05-30', 20, 0, '2025-05-19'),
(7, 1, 'new backend task', 'gautam', 0, '2025-04-30', NULL, '2025-05-30', 50, 0, '2025-05-19'),
(8, 1, 'new ui design', 'shivam', 0, '2025-04-30', NULL, '2025-05-30', 20, 0, '2025-05-19'),
(9, 1, 'new backend task', 'gautam', 0, '2025-04-30', NULL, '2025-05-30', 50, 0, '2025-05-19'),
(13, 3, 'Kick-off & Requirements Workshop', 'Ankur', 0, '2025-05-01', NULL, '2025-05-02', 2, 0, '2025-05-19'),
(14, 3, 'Requirements Sign-off / Scope Freeze', 'Sahil', 0, '2025-05-03', NULL, '2025-05-05', 3, 0, '2025-05-19'),
(15, 3, 'Information Architecture & User Stories', 'Aman', 0, '2025-05-06', NULL, '2025-05-10', 5, 0, '2025-05-19'),
(16, 3, 'UX Wireframes (All key flows)', 'Deepti', 0, '2025-05-12', NULL, '2025-05-19', 8, 0, '2025-05-19'),
(17, 3, 'UI Visual Design (Style-guide, screens)', 'Deepti', 0, '2025-05-20', NULL, '2025-05-30', 11, 0, '2025-05-19'),
(18, 3, 'Backend Architecture & Dev-Env Setup', 'Ashutosh', 0, '2025-05-13', NULL, '2025-05-20', 8, 0, '2025-05-19'),
(19, 3, 'API Contract Design & Documentation', 'Ashutosh', 0, '2025-05-21', NULL, '2025-05-27', 7, 0, '2025-05-19'),
(20, 3, 'Front-end Sprint 1 – Core Screens', 'Sameer', 0, '2025-05-22', NULL, '2025-06-05', 15, 0, '2025-05-19'),
(21, 3, 'Backend Sprint 1 – Core Services', 'Ashutosh', 0, '2025-05-28', NULL, '2025-06-10', 14, 0, '2025-05-19'),
(22, 3, 'Integration & Sprint 1 Review', 'Ankur', 0, '2025-06-11', NULL, '2025-06-13', 3, 0, '2025-05-19'),
(23, 3, 'Front-end Sprint 2 – Advanced Features', 'Sameer', 0, '2025-06-14', NULL, '2025-06-28', 15, 0, '2025-05-19'),
(24, 3, 'Backend Sprint 2 – Advanced APIs', 'Ashutosh', 0, '2025-06-14', NULL, '2025-06-28', 15, 0, '2025-05-19'),
(25, 3, 'QA & Bug-fix Cycle 1', 'Deepti', 0, '2025-06-29', NULL, '2025-07-05', 7, 0, '2025-05-19'),
(26, 3, 'Security / Performance Testing', 'Deepti', 0, '2025-07-06', NULL, '2025-07-10', 5, 0, '2025-05-19'),
(27, 3, 'UAT with Product Owner', 'Sahil', 0, '2025-07-06', NULL, '2025-07-10', 5, 0, '2025-05-19'),
(28, 3, 'Release Candidate Build & Store Submission', 'Aman', 0, '2025-07-11', NULL, '2025-07-14', 4, 0, '2025-05-19'),
(29, 3, 'Go-Live (Production Release)', 'Team', 0, '2025-07-15', NULL, '2025-07-15', 1, 0, '2025-05-19'),
(30, 3, 'Post-launch Support / Hot-fix Window', 'Team', 0, '2025-07-16', NULL, '2025-07-30', 15, 0, '2025-05-19'),
(31, 4, 'Article Submission. Classified ads Submission, and Profile Creation', '', 0, '2025-04-25', NULL, '2025-04-25', 0, 0, '2025-05-19'),
(32, 17, 'Code optimization', 'sahil', 0, '2025-04-15', NULL, '2025-04-18', 100, 0, '2025-05-19'),
(33, 17, 'print final quiz result ', 'sahil', 0, '2025-04-25', NULL, '2025-04-25', 100, 0, '2025-05-19'),
(34, 17, 'Remove unwanted users', 'sahil', 0, '2025-04-25', NULL, '2025-04-25', 100, 0, '2025-05-19'),
(35, 3, 'development', 'gautam', 0, '2025-04-24', NULL, '2025-05-01', 40, 0, '2025-05-19'),
(36, 3, 'live', 'shivam', 0, '2025-04-26', NULL, '2025-05-02', 60, 0, '2025-05-19'),
(37, 19, 'Testing', 'Gautam', 0, '2025-05-19', '2025-05-26', NULL, 100, 3, '2025-05-23'),
(38, 19, 'Ui design', 'shivam', 0, '2025-05-19', '2025-05-26', '2025-05-20', 40, 1, '2025-05-20'),
(39, 19, 'web design', 'shivam', 0, '2025-05-19', '2025-05-22', NULL, 100, 0, '2025-05-19'),
(40, 19, 'backend developement', 'shivam', 0, '2025-05-19', '2025-05-21', NULL, 100, 0, '2025-05-19'),
(41, 19, 'Testing', 'Gautam', 1, '2025-05-23', '2025-05-26', NULL, 30, 0, '2025-05-23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_task_project` (`project_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_task_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
