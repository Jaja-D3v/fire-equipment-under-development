-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2026 at 03:30 AM
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
-- Database: `ems_db`
--
CREATE DATABASE IF NOT EXISTS `ems_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ems_db`;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs_tbl`
--

CREATE TABLE `activity_logs_tbl` (
  `log_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs_tbl`
--

INSERT INTO `activity_logs_tbl` (`log_id`, `user_name`, `action`, `description`, `created_at`) VALUES
(22, 'jared', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-007', '2026-09-25 05:41:44'),
(23, 'jared', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-007', '2026-09-25 05:41:59'),
(24, 'jared', 'Add Fire Extinguisher', 'Added fire extinguisher FE-007', '2026-09-25 05:44:17'),
(25, 'jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-001', '2026-07-25 05:45:16'),
(26, 'jared', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-007', '2026-09-25 05:50:15'),
(27, 'jared', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-006', '2026-09-25 05:50:17'),
(28, 'jared', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-005', '2026-09-25 07:08:16'),
(29, 'jared', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-002', '2026-09-25 07:35:44'),
(30, 'jared', 'Add Fire Extinguisher', 'Added fire extinguisher FE-005', '2026-09-25 07:36:26'),
(31, 'jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-001', '2026-09-25 07:50:01'),
(32, 'jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-002', '2026-09-25 08:17:31'),
(33, 'jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-002', '2026-09-25 08:34:27'),
(34, 'jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-002', '2026-09-25 08:49:27'),
(35, 'jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-004', '2026-09-25 08:52:11'),
(36, 'jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-004', '2026-09-25 09:03:06'),
(37, 'jared', 'Add Fire Extinguisher', 'Added fire extinguisher FE-006', '2026-09-28 01:03:00'),
(38, 'error while getting employee name', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-006', '2026-09-28 06:28:16'),
(39, 'NOEL VICTOR PAGATPAT', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-006', '2026-09-28 06:29:24'),
(40, 'NOEL VICTOR PAGATPAT', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-005', '2026-09-28 06:38:07'),
(41, 'NOEL VICTOR PAGATPAT', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-006', '2026-09-28 06:42:09'),
(42, 'Marivic Nagpala', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-001', '2026-09-28 07:56:00'),
(43, 'MARILYN LACANDAZO', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-001', '2026-09-28 08:23:37'),
(44, 'MARILYN LACANDAZO', 'Add Fire Extinguisher', 'Added fire extinguisher FE-006', '2026-09-28 08:30:14'),
(45, 'MARILYN LACANDAZO', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-006', '2026-09-28 08:31:01'),
(46, 'MARILYN LACANDAZO', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-006', '2026-09-28 08:33:17'),
(47, 'error while getting employee name', 'Add Fire Extinguisher', 'Added fire extinguisher FE-006', '2026-09-29 00:58:32'),
(48, 'error while getting employee name', 'Add Fire Extinguisher', 'Added fire extinguisher FE-007', '2026-09-29 01:04:13'),
(49, 'error while getting employee name', 'Add Fire Extinguisher', 'Added fire extinguisher FE-008', '2026-09-29 01:05:43'),
(50, 'error while getting employee name', 'Add Fire Extinguisher', 'Added fire extinguisher FE-009', '2026-09-29 01:52:09'),
(51, 'error while getting employee name', 'Add Fire Extinguisher', 'Added fire extinguisher FE-010', '2026-09-29 01:52:24'),
(52, 'error while getting employee name', 'Add Fire Extinguisher', 'Added fire extinguisher FE-011', '2026-09-29 01:52:40'),
(53, 'error while getting employee name', 'Add Fire Extinguisher', 'Added fire extinguisher FE-012', '2026-09-29 01:52:53'),
(54, 'error while getting employee name', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-001', '2026-09-29 02:20:44'),
(55, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-013', '2026-09-30 03:03:31'),
(56, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-014', '2026-09-30 03:08:23'),
(57, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-015', '2026-09-30 03:23:15'),
(58, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-006', '2026-09-30 06:09:27'),
(59, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-007', '2026-09-30 06:10:11'),
(60, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-008', '2026-09-30 06:14:38'),
(61, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-009', '2026-09-30 06:24:33'),
(62, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-010sdfsdfsfsd', '2026-09-30 08:00:28'),
(63, 'jaja | dev', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-010sdfsdfsfsd', '2026-09-30 08:00:47'),
(64, 'jaja | dev', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-009', '2026-10-01 05:45:51'),
(65, 'jaja | dev', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-009', '2026-10-01 06:49:04'),
(66, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-011', '2026-10-01 06:54:03'),
(67, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-012', '2026-10-02 00:59:06'),
(68, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-013', '2026-10-02 01:04:29'),
(69, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-014', '2026-10-02 01:09:58'),
(70, 'jaja | dev', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-014', '2026-10-02 03:58:05'),
(71, 'jaja | dev', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-013', '2026-10-02 03:58:10'),
(72, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-014', '2026-10-02 04:28:08'),
(73, 'jaja | dev', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-014', '2026-10-05 01:21:24'),
(74, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-015', '2026-10-05 02:25:41'),
(75, 'jaja | dev', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-015', '2026-10-05 02:25:59'),
(76, 'jaja | dev', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-015', '2026-10-05 02:27:24'),
(77, 'jaja | dev', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-014', '2026-10-06 02:21:58'),
(78, 'jaja | dev', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-014', '2026-10-06 02:22:08'),
(79, 'jaja | dev', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-014', '2026-10-06 02:22:21'),
(80, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-06 02:30:43'),
(81, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-001', '2026-09-30 00:01:00'),
(82, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-002', '2026-09-30 00:05:00'),
(83, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-003', '2026-09-30 00:12:00'),
(84, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-004', '2026-09-30 00:18:00'),
(85, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-005', '2026-09-30 00:25:00'),
(86, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-001', '2026-09-30 01:02:00'),
(87, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-002', '2026-09-30 01:10:00'),
(88, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-003', '2026-09-30 01:17:00'),
(89, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-004', '2026-09-30 01:24:00'),
(90, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-005', '2026-09-30 01:31:00'),
(91, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-006', '2026-09-30 02:05:00'),
(92, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-007', '2026-09-30 02:13:00'),
(93, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-008', '2026-09-30 02:21:00'),
(94, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-009', '2026-09-30 02:29:00'),
(95, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-010', '2026-09-30 02:37:00'),
(96, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-011', '2026-09-30 03:03:00'),
(97, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-012', '2026-09-30 03:10:00'),
(98, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-013', '2026-09-30 03:18:00'),
(99, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-014', '2026-09-30 03:26:00'),
(100, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-015', '2026-09-30 03:34:00'),
(101, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-011', '2026-09-30 05:02:00'),
(102, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-012', '2026-09-30 05:10:00'),
(103, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-013', '2026-09-30 05:18:00'),
(104, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-014', '2026-09-30 05:26:00'),
(105, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-015', '2026-09-30 05:34:00'),
(106, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-011', '2026-09-30 06:02:00'),
(107, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-012', '2026-09-30 06:10:00'),
(108, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-013', '2026-09-30 06:18:00'),
(109, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-014', '2026-09-30 06:26:00'),
(110, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-015', '2026-09-30 06:34:00'),
(111, 'jaja | dev', 'delete fire extinguisher', 'Deleted fire extinguisher FE-003', '2026-09-30 07:01:00'),
(112, 'jaja | dev', 'delete fire extinguisher', 'Deleted fire extinguisher FE-008', '2026-09-30 07:08:00'),
(113, 'jaja | dev', 'delete fire extinguisher', 'Deleted fire extinguisher FE-012', '2026-09-30 07:15:00'),
(114, 'jaja | dev', 'delete fire extinguisher', 'Deleted fire extinguisher FE-016', '2026-09-30 07:22:00'),
(115, 'jaja | dev', 'delete fire extinguisher', 'Deleted fire extinguisher FE-019', '2026-09-30 07:29:00'),
(116, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-016', '2026-10-01 00:05:00'),
(117, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-017', '2026-10-01 00:14:00'),
(118, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-018', '2026-10-01 00:23:00'),
(119, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-019', '2026-10-01 00:32:00'),
(120, 'jaja | dev', 'add fire extinguisher', 'Added fire extinguisher FE-020', '2026-10-01 00:41:00'),
(121, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-016', '2026-10-01 01:10:00'),
(122, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-017', '2026-10-01 01:18:00'),
(123, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-018', '2026-10-01 01:26:00'),
(124, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-019', '2026-10-01 01:34:00'),
(125, 'jaja | dev', 'update fire extinguisher', 'Updated fire extinguisher FE-020', '2026-10-01 01:42:00'),
(126, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-016', '2026-10-01 02:05:00'),
(127, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-017', '2026-10-01 02:13:00'),
(128, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-018', '2026-10-01 02:21:00'),
(129, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-019', '2026-10-01 02:29:00'),
(130, 'jaja | dev', 'inspect fire extinguisher', 'Completed inspection for fire extinguisher FE-020', '2026-10-01 02:37:00'),
(131, 'jaja | dev', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-06 04:15:03'),
(132, 'jaja | dev', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-06 04:41:07'),
(133, 'jaja | dev', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-002', '2026-10-06 08:31:43'),
(134, 'jaja | dev', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-012', '2026-10-06 09:14:54'),
(135, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-066', '2026-10-07 03:36:25'),
(136, 'jaja | dev', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-003', '2026-10-07 05:02:22'),
(137, 'jaja | dev', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-003', '2026-10-07 06:30:04'),
(138, 'jaja | dev', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-066', '2026-10-07 06:30:28'),
(139, 'jaja | dev', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-066', '2026-10-07 06:31:05'),
(140, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-001', '2026-10-07 08:09:53'),
(141, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-08 01:10:39'),
(142, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-08 01:21:36'),
(143, 'jaja | dev', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-001', '2026-10-08 05:39:42'),
(144, 'jaja | dev', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-001', '2026-10-08 05:46:07'),
(145, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-08 08:51:45'),
(146, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-08 08:53:34'),
(147, 'jaja | dev', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-066', '2026-10-08 08:55:45'),
(148, 'jaja | dev', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-066', '2026-10-08 09:04:40'),
(149, 'jaja | dev', 'Restored Fire Extinguisher', 'Restored fire extinguisher', '2026-10-08 09:04:46'),
(150, 'jaja | dev', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-066', '2026-10-08 09:10:10'),
(151, 'jaja | dev', 'Restored Fire Extinguisher', 'Restored fire extinguisher FE-066', '2026-10-08 09:17:00'),
(152, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-067', '2026-10-12 00:52:15'),
(153, 'jaja | dev', 'Restored Fire Extinguisher', 'Restored fire extinguisher FE-005', '2026-10-12 05:25:51'),
(154, 'jaja | dev', 'Restored Fire Extinguisher', 'Restored fire extinguisher FE-020', '2026-10-12 05:28:32'),
(155, 'jaja | dev', 'Restored Fire Extinguisher', 'Restored fire extinguisher FE-019', '2026-10-12 05:30:04'),
(156, 'jaja | dev', 'Restored Fire Extinguisher', 'Restored fire extinguisher FE-018', '2026-10-12 05:31:14'),
(157, 'jaja | dev', 'Add Fire Extinguisher', 'Added fire extinguisher FE-068', '2026-10-13 00:44:11'),
(158, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-004', '2026-10-13 06:37:38'),
(159, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-004', '2026-10-13 06:41:46'),
(160, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-004', '2026-10-13 06:43:46'),
(161, 'Jared', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-012', '2026-10-14 00:04:54'),
(162, 'Jared', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-016', '2026-10-14 00:04:59'),
(163, 'Jared', 'Delete Fire Extinguisher', 'Deleted fire extinguisher FE-018', '2026-10-14 00:05:04'),
(164, 'Jared', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-021', '2026-10-14 00:11:51'),
(165, 'Jared', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-020', '2026-10-14 00:12:11'),
(166, 'Jared', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-019', '2026-10-14 00:12:28'),
(167, 'Jared', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-017', '2026-10-14 00:12:44'),
(168, 'Jared', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-015', '2026-10-14 00:13:02'),
(169, 'Jared', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-014', '2026-10-14 00:13:21'),
(170, 'Jared', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-091', '2026-10-14 00:13:40'),
(171, 'Jared', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-090                                            ', '2026-10-14 00:13:51'),
(172, 'Jared', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-011', '2026-10-14 00:14:01'),
(173, 'Jared', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-013', '2026-10-14 00:14:16'),
(174, 'Jared', 'Update Fire Extinguisher', 'Updated fire extinguisher FE-013', '2026-10-14 00:14:24'),
(175, 'Jared', 'Add Fire Extinguisher', 'Added fire extinguisher FE-092', '2026-10-14 01:54:15'),
(176, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-14 02:01:18'),
(177, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-14 02:08:20'),
(178, 'Jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-14 06:32:34'),
(179, 'Jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-001', '2026-10-14 06:34:21'),
(180, 'Jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-004', '2026-10-14 06:45:06'),
(181, 'Jared', 'Add Fire Extinguisher', 'Added fire extinguisher FE-093', '2026-10-14 07:20:37'),
(182, 'Jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-093', '2026-10-14 07:21:30'),
(183, 'Jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-14 07:26:48'),
(184, 'Jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-14 07:34:44'),
(185, 'Jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-14 07:36:15'),
(186, 'Jared', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-14 07:39:11'),
(187, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-15 02:16:25'),
(188, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-15 03:27:26'),
(189, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-090', '2026-10-15 04:25:38'),
(190, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-045', '2026-10-15 04:26:14'),
(191, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-004', '2026-10-15 04:26:37'),
(192, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-076', '2026-10-15 04:27:02'),
(193, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-035', '2026-10-15 04:27:33'),
(194, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-008', '2026-10-15 04:28:25'),
(195, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-020', '2026-10-15 05:17:36'),
(196, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-004', '2026-10-15 05:20:04'),
(197, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-15 09:00:26'),
(198, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-15 09:47:01'),
(199, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-003', '2026-10-15 09:50:30'),
(200, 'Jared Abrera', 'Inspect Fire Extinguisher', 'Inspect fire extinguisher FE-001', '2026-10-15 09:51:37');

-- --------------------------------------------------------

--
-- Table structure for table `fire_extinguishers_tbl`
--

CREATE TABLE `fire_extinguishers_tbl` (
  `extinguisher_id` int(11) NOT NULL,
  `extinguisher_code` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `capacity` varchar(50) NOT NULL,
  `location` varchar(255) NOT NULL,
  `branch` varchar(100) NOT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `manufactured_date` date DEFAULT NULL,
  `class` varchar(50) NOT NULL,
  `placement` varchar(50) NOT NULL,
  `condition_status` enum('Good','Not Good') NOT NULL DEFAULT 'Good',
  `remarks` varchar(255) DEFAULT NULL,
  `expiration_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `archived` tinyint(1) NOT NULL DEFAULT 0,
  `refilled_date` date DEFAULT NULL,
  `added_by` varchar(100) DEFAULT NULL,
  `last_date_inspected` datetime DEFAULT NULL,
  `inspected_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fire_extinguishers_tbl`
--

INSERT INTO `fire_extinguishers_tbl` (`extinguisher_id`, `extinguisher_code`, `type`, `capacity`, `location`, `branch`, `provider`, `manufactured_date`, `class`, `placement`, `condition_status`, `remarks`, `expiration_date`, `created_at`, `updated_at`, `archived`, `refilled_date`, `added_by`, `last_date_inspected`, `inspected_by`) VALUES
(1, 'FE-001', 'HCFC', '10 lbs', 'Warehouse FG Area (near Roll up door)', 'Laguna', NULL, '2023-01-15', 'ABC', 'Wall Mounted', 'Good', '', '2026-11-01', '2026-09-22 00:58:02', '2026-10-15 09:51:37', 0, '2026-09-28', NULL, '2026-10-15 17:51:37', 'Jared Abrera'),
(2, 'FE-002', 'HCFC', '10 lbs', 'Reception Area', 'Lima', NULL, '2023-02-20', 'ABC', 'Wall Mounted', 'Not Good', 'sdfsdfsd', '2026-11-01', '2026-09-22 00:58:02', '2026-10-13 07:44:02', 0, '2026-09-25', NULL, NULL, 'jaja | dev'),
(3, 'FE-003', 'AFFF', '50 lbsdsfsdf', 'Server Room', 'Laguna', NULL, '2022-06-10', 'BC', 'Wall Mounted', 'Good', '', '2026-10-31', '2026-09-22 00:58:02', '2026-10-15 09:50:30', 0, NULL, NULL, '2026-10-15 17:50:30', 'Jared Abrera'),
(4, 'FE-004', 'AFFF', '20 lbs', 'storage', 'Laguna', NULL, '2023-03-05', 'ABC', 'Floor Stand', 'Not Good', '', '2029-10-07', '2026-09-22 00:58:02', '2026-10-15 05:21:20', 0, NULL, NULL, '2026-10-15 13:20:04', 'Jared Abrera'),
(117, 'FE-045', 'ABC Dry Powder', '10 lbs', 'Main Lobby', 'Laguna', NULL, '2023-01-15', 'ABC', 'Wall Mounted', 'Good', '', '2027-01-15', '2026-10-13 07:33:00', '2026-10-15 04:29:18', 0, '2025-01-15', 'admin', '2026-10-15 12:26:14', 'Jared Abrera'),
(118, 'FE-076', 'CO2', '5 lbs', 'Server Room', 'Laguna', NULL, '2022-02-10', 'BC', 'Floor Standing', 'Good', '', '2026-02-10', '2026-10-13 07:33:00', '2026-10-15 04:29:07', 0, '2024-12-10', 'admin', '2026-10-15 12:27:02', 'Jared Abrera'),
(119, 'FE-035', 'ABC Dry Powder', '20 lbs', 'Warehouse A', 'Laguna', NULL, '2023-03-12', 'ABC', 'Wall Mounted', 'Good', '', '2027-03-12', '2026-10-13 07:33:00', '2026-10-15 04:28:58', 0, '2025-02-12', 'admin', '2026-10-15 12:27:33', 'Jared Abrera'),
(120, 'FE-036', 'Foam', '10 lbs', 'Production Area', 'Laguna', NULL, '2022-04-05', 'A', 'Floor Standing', 'Good', 'Archived sample record', '2026-04-05', '2026-10-13 07:33:00', '2026-10-13 07:50:20', 0, '2024-11-05', 'admin', '2025-06-13 10:30:00', 'inspector2'),
(121, 'FE-037', 'CO2', '10 lbs', 'Electrical Room', 'Laguna', NULL, '2023-05-20', 'BC', 'Wall Mounted', 'Good', 'Archived sample record', '2027-05-20', '2026-10-13 07:33:00', '2026-10-13 07:50:27', 0, '2025-03-20', 'admin', '2025-06-14 11:00:00', 'inspector1'),
(122, 'FE-038', 'ABC Dry Powder', '10 lbs', 'Office Area', 'Laguna', NULL, '2022-06-18', 'ABC', 'Wall Mounted', 'Not Good', 'Archived sample record', '2026-06-18', '2026-10-13 07:33:00', '2026-10-13 07:50:36', 0, '2024-10-18', 'admin', '2025-06-15 11:30:00', 'inspector2'),
(123, 'FE-039', 'Foam', '20 lbs', 'Loading Bay', 'Laguna', NULL, '2023-07-22', 'A', 'Floor Standing', 'Good', 'Archived sample record', '2027-07-22', '2026-10-13 07:33:00', '2026-10-13 07:50:43', 0, '2025-04-22', 'admin', '2025-06-16 13:00:00', 'inspector1'),
(124, 'FE-008', 'CO2', '5 lbs', 'Generator Room', 'Laguna', NULL, '2022-08-14', 'BC', 'Wall Mounted', 'Good', '', '2026-08-14', '2026-10-13 07:33:00', '2026-10-15 04:28:50', 0, '2024-09-14', 'admin', '2026-10-15 12:28:25', 'Jared Abrera'),
(125, 'FE-091', 'Dry Chemical', '20lbs', 'Warehouse B', 'Laguna', NULL, '2023-09-09', 'ABC', 'Ground', 'Good', 'Archived sample record', '2027-09-09', '2026-10-13 07:33:00', '2026-10-14 00:13:40', 0, '2025-05-09', 'admin', '2025-06-18 14:00:00', 'inspector1'),
(126, 'FE-090', 'Foam', '50lbs', 'Cafeteria', 'Laguna', NULL, '2022-10-11', 'A', 'Hanged', 'Good', '', '2026-10-11', '2026-10-13 07:33:00', '2026-10-15 04:29:47', 0, '2024-08-11', 'admin', '2026-10-15 12:25:38', 'Jared Abrera'),
(127, 'FE-011', 'Dry Chemical', '20lbs', 'Reception', 'Laguna', NULL, '2023-01-17', 'ABC', 'Stand', 'Good', 'Archived sample record', '2027-01-17', '2026-10-13 07:33:00', '2026-10-14 00:14:01', 0, '2025-01-17', 'admin', '2025-06-20 09:00:00', 'inspector1'),
(128, 'FE-012', 'CO2', '5 lbs', 'IT Office', 'Laguna', NULL, '2022-02-19', 'BC', 'Floor Standing', 'Good', 'Archived sample record', '2026-02-19', '2026-10-13 07:33:00', '2026-10-14 00:04:54', 1, '2024-12-19', 'admin', '2025-06-21 09:30:00', 'inspector2'),
(129, 'FE-013', 'Foam', '20lbs', 'Storage Room 1', 'Laguna', NULL, '2023-03-21', 'ABC', 'Hanged', 'Not Good', 'Archived sample record', '2027-03-21', '2026-10-13 07:33:00', '2026-10-14 00:14:16', 0, '2025-02-21', 'admin', '2025-06-22 10:00:00', 'inspector1'),
(130, 'FE-014', 'Foam', '20lbs', 'Packing Area', 'Laguna', NULL, '2022-04-23', 'A', 'Stand', 'Good', 'Archived sample record', '2026-04-23', '2026-10-13 07:33:00', '2026-10-14 00:13:21', 0, '2024-11-23', 'admin', '2025-06-23 10:30:00', 'inspector2'),
(131, 'FE-015', 'CO2', '20lbs', 'Control Room', 'Laguna', NULL, '2023-05-25', 'BC', 'Hanged', 'Good', 'Archived sample record', '2027-05-25', '2026-10-13 07:33:00', '2026-10-14 00:13:02', 0, '2025-03-25', 'admin', '2025-06-24 11:00:00', 'inspector1'),
(132, 'FE-016', 'ABC Dry Powder', '10 lbs', 'Office Floor 2', 'Laguna', NULL, '2022-06-27', 'ABC', 'Wall Mounted', 'Not Good', 'Archived sample record', '2026-06-27', '2026-10-13 07:33:00', '2026-10-14 00:04:59', 1, '2024-10-27', 'admin', '2025-06-25 11:30:00', 'inspector2'),
(133, 'FE-017', 'Foam', '20lbs', 'Warehouse C', 'Laguna', NULL, '2023-07-29', 'A', 'Hanged', 'Good', 'Archived sample record', '2027-07-29', '2026-10-13 07:33:00', '2026-10-14 00:12:44', 0, '2025-04-29', 'admin', '2025-06-26 13:00:00', 'inspector1'),
(134, 'FE-018', 'CO2', '5 lbs', 'Electrical Panel Area', 'Laguna', NULL, '2022-08-31', 'BC', 'Storage', 'Good', 'Archived sample record', '2026-08-31', '2026-10-13 07:33:00', '2026-10-14 00:05:04', 1, '2024-09-30', 'admin', '2025-06-27 13:30:00', 'inspector2'),
(135, 'FE-019', 'Dry Chemical', '20lbs', 'Warehouse D', 'Laguna', NULL, '2023-09-13', 'ABC', 'Hanged', 'Good', 'Archived sample record', '2027-09-13', '2026-10-13 07:33:00', '2026-10-14 00:12:28', 0, '2025-05-13', 'admin', '2025-06-28 14:00:00', 'inspector1'),
(136, 'FE-020', 'Foam', '20lbs', 'Break Room', 'Laguna', NULL, '2022-10-15', 'A', 'Storage', 'Not Good', '', '2029-10-07', '2026-10-13 07:33:00', '2026-10-15 05:18:13', 0, '2024-08-15', 'admin', '2026-10-15 13:17:36', 'Jared Abrera'),
(137, 'FE-021', 'Dry Chemical', '20lbs', 'Main Entrance', 'Laguna', NULL, '2023-01-21', 'ABC', 'Hanged', 'Good', 'Archived sample record', '2027-01-21', '2026-10-13 07:33:00', '2026-10-14 00:11:51', 0, '2025-01-21', 'admin', '2025-07-01 09:00:00', 'inspector1'),
(167, 'FE-092', 'Dry Chemical', '20lbs', 'BUILDING A', 'Laguna', NULL, '2026-10-23', 'Abc', 'Storage', 'Good', '', '2029-10-23', '2026-10-14 01:54:15', '2026-10-14 01:54:15', 0, NULL, 'Jared', NULL, NULL),
(168, 'FE-093', 'Dry Chemical', '20lbs', 'Server Room', 'Laguna', NULL, '2026-10-22', 'Ab', 'Hanged', 'Good', '', '2029-10-22', '2026-10-14 07:20:37', '2026-10-14 07:21:30', 0, NULL, 'Jared', '2026-10-14 15:21:30', 'Jared');

-- --------------------------------------------------------

--
-- Table structure for table `inspection_checklist_tbl`
--

CREATE TABLE `inspection_checklist_tbl` (
  `inspect_id` int(11) NOT NULL,
  `extinguisher_code` varchar(50) NOT NULL,
  `branch` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `capacity` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `class` varchar(50) NOT NULL,
  `date_inspected` datetime NOT NULL,
  `inspected_by` varchar(100) NOT NULL,
  `verified_and_approved_by` varchar(100) NOT NULL,
  `evaluation_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `action_taken` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `target_date_of_implementation` date DEFAULT NULL,
  `is_seal_ok` tinyint(1) DEFAULT 0,
  `is_pin_ok` tinyint(1) DEFAULT 0,
  `is_pressure_ok` tinyint(1) DEFAULT 0,
  `is_hose_ok` tinyint(1) DEFAULT 0,
  `is_nozzle_ok` tinyint(1) DEFAULT 0,
  `is_belt_ok` tinyint(1) DEFAULT 0,
  `is_cylinder_body_ok` tinyint(1) DEFAULT 0,
  `is_demarcation_line_ok` tinyint(1) DEFAULT 0,
  `is_signage_ok` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 0,
  `rejected_by` varchar(50) DEFAULT NULL,
  `is_cleaning_of_unit_ok` tinyint(1) DEFAULT NULL,
  `reject_reason` text DEFAULT NULL,
  `rejection_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inspection_checklist_tbl`
--

INSERT INTO `inspection_checklist_tbl` (`inspect_id`, `extinguisher_code`, `branch`, `location`, `capacity`, `type`, `class`, `date_inspected`, `inspected_by`, `verified_and_approved_by`, `evaluation_status`, `action_taken`, `remarks`, `target_date_of_implementation`, `is_seal_ok`, `is_pin_ok`, `is_pressure_ok`, `is_hose_ok`, `is_nozzle_ok`, `is_belt_ok`, `is_cylinder_body_ok`, `is_demarcation_line_ok`, `is_signage_ok`, `status`, `rejected_by`, `is_cleaning_of_unit_ok`, `reject_reason`, `rejection_date`) VALUES
(169, 'FE-003', 'Laguna', 'Server Room', '50 lbsdsfsdf', 'AFFF', 'BC', '2026-10-15 11:27:26', 'Jared Abrera', 'Jared', 'Rejected', '', '', '0000-00-00', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'Jared', 1, 'need to replace the nozzle', NULL),
(170, 'FE-090', 'Laguna', 'Cafeteria', '50lbs', 'Foam', 'A', '2026-10-15 12:25:38', 'Jared Abrera', 'Jared', 'Approved', '', '', '0000-00-00', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 1, '', NULL),
(171, 'FE-045', 'Lima', 'Main Lobby', '10 lbs', 'ABC Dry Powder', 'ABC', '2026-10-15 12:26:14', 'Jared Abrera', 'Jared', 'Approved', '', '', '0000-00-00', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 1, '', NULL),
(172, 'FE-004', 'Laguna', 'storage', '20 lbs', 'AFFF', 'ABC', '2026-10-15 12:26:37', 'Jared Abrera', 'Jared', 'Approved', '', '', '0000-00-00', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 1, '', NULL),
(173, 'FE-076', 'Laguna', 'Server Room', '5 lbs', 'CO2', 'BC', '2026-10-15 12:27:02', 'Jared Abrera', 'Jared', 'Approved', '', '', '0000-00-00', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 1, '', NULL),
(174, 'FE-035', 'Laguna', 'Warehouse A', '20 lbs', 'ABC Dry Powder', 'ABC', '2026-10-15 12:27:33', 'Jared Abrera', 'Jared', 'Approved', '', '', '0000-00-00', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 1, '', NULL),
(175, 'FE-008', 'Laguna', 'Generator Room', '5 lbs', 'CO2', 'BC', '2026-10-15 12:28:25', 'Jared Abrera', 'Jared', 'Approved', '', '', '0000-00-00', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 1, '', NULL),
(176, 'FE-020', 'Laguna', 'Break Room', '20lbs', 'Foam', 'A', '2026-10-15 13:17:36', 'Jared Abrera', 'Jared', 'Approved', 'Refill', '', '2026-10-07', 1, 1, 0, 1, 1, 1, 1, 1, 1, 0, NULL, 1, '', NULL),
(177, 'FE-004', 'Laguna', 'storage', '20 lbs', 'AFFF', 'ABC', '2026-10-15 13:20:04', 'Jared Abrera', 'Jared', 'Approved', 'Refill', '', '2026-10-07', 1, 1, 0, 1, 1, 1, 1, 1, 1, 0, NULL, 1, '', NULL),
(178, 'FE-003', 'Laguna', 'Server Room', '50 lbsdsfsdf', 'AFFF', 'BC', '2026-10-15 17:00:26', 'Jared Abrera', 'N/A', 'Pending', '', '', '0000-00-00', 1, 1, 1, 1, 0, 1, 1, 1, 1, 0, NULL, 1, NULL, NULL),
(179, 'FE-003', 'Laguna', 'Server Room', '50 lbsdsfsdf', 'AFFF', 'BC', '2026-10-15 17:47:01', 'Jared Abrera', 'N/A', 'Pending', '', '', '0000-00-00', 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, NULL, 0, NULL, NULL),
(180, 'FE-003', 'Laguna', 'Server Room', '50 lbsdsfsdf', 'AFFF', 'BC', '2026-10-15 17:50:30', 'Jared Abrera', 'N/A', 'Pending', '', '', '0000-00-00', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, 1, NULL, NULL),
(181, 'FE-001', 'Laguna', 'Warehouse FG Area (near Roll up door)', '10 lbs', 'HCFC', 'ABC', '2026-10-15 17:51:37', 'Jared Abrera', 'N/A', 'Rejected', '', '', '0000-00-00', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'Jared', 1, 'sample reject', '2026-10-16 07:26:27');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_dropdown_tbl`
--

CREATE TABLE `maintenance_dropdown_tbl` (
  `id` int(10) UNSIGNED NOT NULL,
  `category` varchar(50) NOT NULL,
  `value` varchar(150) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_dropdown_tbl`
--

INSERT INTO `maintenance_dropdown_tbl` (`id`, `category`, `value`, `status`, `created_at`, `updated_at`) VALUES
(2, 'branch', 'Laguna', 'active', '2026-10-01 08:58:58', '2026-10-01 09:30:54'),
(3, 'branch', 'Cavite', 'active', '2026-10-01 08:58:58', '2026-10-01 09:30:43'),
(7, 'condition', 'Needs Refill', 'active', '2026-10-01 08:58:58', '2026-10-01 08:58:58'),
(10, 'type', 'Dry Chemical', 'active', '2026-10-01 08:58:58', '2026-10-01 08:58:58'),
(11, 'type', 'CO2', 'active', '2026-10-01 08:58:58', '2026-10-01 08:58:58'),
(12, 'type', 'Water', 'active', '2026-10-01 08:58:58', '2026-10-01 08:58:58'),
(13, 'type', 'Foam', 'active', '2026-10-01 08:58:58', '2026-10-01 08:58:58'),
(14, 'fire_class', 'Abc', 'active', '2026-10-01 08:58:58', '2026-10-08 05:20:18'),
(15, 'fire_class', 'Ab', 'active', '2026-10-01 08:58:58', '2026-10-08 05:20:39'),
(16, 'fire_class', 'C', 'active', '2026-10-01 08:58:58', '2026-10-08 05:21:23'),
(17, 'fire_class', 'Class D', 'inactive', '2026-10-01 08:58:58', '2026-10-05 09:31:25'),
(18, 'capacity', '10lbs', 'active', '2026-10-01 08:58:58', '2026-10-05 09:46:10'),
(19, 'capacity', '20lbs', 'active', '2026-10-01 08:58:58', '2026-10-05 09:46:23'),
(20, 'capacity', '50lbs', 'active', '2026-10-01 08:58:58', '2026-10-05 09:46:49'),
(21, 'capacity', '6 kg', 'inactive', '2026-10-01 08:58:58', '2026-10-05 09:46:55'),
(22, 'location', 'Production', 'active', '2026-10-01 08:58:58', '2026-10-01 08:58:58'),
(23, 'location', 'Warehouse', 'active', '2026-10-01 08:58:58', '2026-10-01 08:58:58'),
(24, 'location', 'Office', 'active', '2026-10-01 08:58:58', '2026-10-01 08:58:58'),
(25, 'location', 'Parking Area', 'active', '2026-10-01 08:58:58', '2026-10-01 08:58:58'),
(36, 'placement', 'Hanged', 'active', '2026-10-05 09:30:09', '2026-10-05 09:30:09'),
(37, 'placement', 'Stand', 'active', '2026-10-05 09:30:18', '2026-10-05 09:30:18'),
(38, 'placement', 'Fhc', 'active', '2026-10-05 09:30:28', '2026-10-05 09:30:28'),
(39, 'placement', 'Ceiling', 'active', '2026-10-05 09:30:37', '2026-10-05 09:30:37'),
(40, 'placement', 'Storage', 'active', '2026-10-05 09:30:45', '2026-10-05 09:30:45'),
(41, 'placement', 'Wheeled', 'active', '2026-10-05 09:30:53', '2026-10-05 09:30:53'),
(42, 'placement', 'Ground', 'active', '2026-10-05 09:31:01', '2026-10-05 09:31:01'),
(43, 'branch', 'Lima', 'active', '2026-10-06 04:25:26', '2026-10-06 04:25:26'),
(44, 'branch', 'Sdfsdfsdfsdfsdf', 'inactive', '2026-10-12 01:57:13', '2026-10-12 01:57:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs_tbl`
--
ALTER TABLE `activity_logs_tbl`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `fire_extinguishers_tbl`
--
ALTER TABLE `fire_extinguishers_tbl`
  ADD PRIMARY KEY (`extinguisher_id`);

--
-- Indexes for table `inspection_checklist_tbl`
--
ALTER TABLE `inspection_checklist_tbl`
  ADD PRIMARY KEY (`inspect_id`);

--
-- Indexes for table `maintenance_dropdown_tbl`
--
ALTER TABLE `maintenance_dropdown_tbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_category_value` (`category`,`value`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs_tbl`
--
ALTER TABLE `activity_logs_tbl`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT for table `fire_extinguishers_tbl`
--
ALTER TABLE `fire_extinguishers_tbl`
  MODIFY `extinguisher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT for table `inspection_checklist_tbl`
--
ALTER TABLE `inspection_checklist_tbl`
  MODIFY `inspect_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=182;

--
-- AUTO_INCREMENT for table `maintenance_dropdown_tbl`
--
ALTER TABLE `maintenance_dropdown_tbl`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
--
