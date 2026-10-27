-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2026 at 01:45 AM
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


-- --------------------------------------------------------

--
-- Table structure for table `chemical_spill_kit_current_inventory_materials_tbl`
--

CREATE TABLE `chemical_spill_kit_current_inventory_materials_tbl` (
  `current_material_id` int(11) NOT NULL,
  `chemical_spill_kit_id` int(11) NOT NULL,
  `material_name` varchar(100) NOT NULL,
  `status` enum('Good','No Good') DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `current_stocks` varchar(50) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chemical_spill_kit_inspection_tbl`
--

CREATE TABLE `chemical_spill_kit_inspection_tbl` (
  `inspection_id` int(11) NOT NULL,
  `chemical_spill_kit_id` int(11) NOT NULL,
  `chemical_spill_kit_code` varchar(50) NOT NULL,
  `location` varchar(150) NOT NULL,
  `branch` varchar(100) NOT NULL,
  `inspected_by` varchar(50) NOT NULL,
  `inspection_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chemical_spill_kit_inspection_tbl`
--

INSERT INTO `chemical_spill_kit_inspection_tbl` (`inspection_id`, `chemical_spill_kit_id`, `chemical_spill_kit_code`, `location`, `branch`, `inspected_by`, `inspection_date`) VALUES
(6, 7, 'CSK-002', 'Chemical Storage Room', 'Laguna', 'Jared Abrera', '2026-10-26 08:53:12'),
(7, 7, 'CSK-002', 'Chemical Storage Room', 'Laguna', 'Jared Abrera', '2026-10-26 09:03:15');

-- --------------------------------------------------------

--
-- Table structure for table `chemical_spill_kit_materials_checklist_tbl`
--

CREATE TABLE `chemical_spill_kit_materials_checklist_tbl` (
  `checklist_id` int(11) NOT NULL,
  `inspection_id` int(11) NOT NULL,
  `material_name` varchar(100) NOT NULL,
  `status` enum('Good','No Good') NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `current_stocks` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chemical_spill_kit_materials_checklist_tbl`
--
-- --------------------------------------------------------

--
-- Table structure for table `chemical_spill_kit_materials_inventory_tbl`
--

CREATE TABLE `chemical_spill_kit_materials_inventory_tbl` (
  `chemical_spill_kit_id` int(11) NOT NULL,
  `chemical_spill_kit_code` varchar(50) NOT NULL,
  `location` varchar(150) NOT NULL,
  `branch` varchar(100) NOT NULL,
  `pads` varchar(20) DEFAULT NULL,
  `boom_pillow` varchar(20) DEFAULT NULL,
  `socks` varchar(20) DEFAULT NULL,
  `disposal_bag` varchar(20) DEFAULT NULL,
  `goggles` varchar(20) DEFAULT NULL,
  `face_mask` varchar(20) DEFAULT NULL,
  `rubber_gloves` varchar(20) DEFAULT NULL,
  `nitrile_gloves` varchar(20) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `is_inspected_this_month` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chemical_spill_kit_materials_inventory_tbl`
--
-- --------------------------------------------------------

--
-- Table structure for table `chemical_spill_kit_materials_tbl`
--

CREATE TABLE `chemical_spill_kit_materials_tbl` (
  `material_id` int(10) UNSIGNED NOT NULL,
  `material_name` varchar(100) NOT NULL,
  `required_stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `branch` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


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
  `condition_status` enum('Good','No Good') NOT NULL DEFAULT 'Good',
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


CREATE TABLE `transactions_tbl` (
  `transaction_id` int(10) UNSIGNED NOT NULL,
  `category` varchar(100) NOT NULL,
  `branch` varchar(100) NOT NULL,
  `inspection_id` int(11) NOT NULL,
  `transaction_type` varchar(50) NOT NULL DEFAULT 'inspection',
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `submitted_by` varchar(50) DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `activity_logs_tbl`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `chemical_spill_kit_current_inventory_materials_tbl`
--
ALTER TABLE `chemical_spill_kit_current_inventory_materials_tbl`
  ADD PRIMARY KEY (`current_material_id`),
  ADD UNIQUE KEY `uq_kit_material` (`chemical_spill_kit_id`,`material_name`),
  ADD KEY `idx_current_material_kit` (`chemical_spill_kit_id`);

--
-- Indexes for table `chemical_spill_kit_inspection_tbl`
--
ALTER TABLE `chemical_spill_kit_inspection_tbl`
  ADD PRIMARY KEY (`inspection_id`),
  ADD KEY `idx_csk_inspection_kit` (`chemical_spill_kit_id`),
  ADD KEY `idx_csk_inspection_branch` (`branch`),
  ADD KEY `idx_csk_inspection_date` (`inspection_date`),
  ADD KEY `idx_csk_inspection_inspector` (`inspected_by`),
  ADD KEY `idx_csk_inspection_kit_branch` (`chemical_spill_kit_id`,`branch`);

--
-- Indexes for table `chemical_spill_kit_materials_checklist_tbl`
--
ALTER TABLE `chemical_spill_kit_materials_checklist_tbl`
  ADD PRIMARY KEY (`checklist_id`),
  ADD KEY `idx_csk_checklist_inspection` (`inspection_id`),
  ADD KEY `idx_csk_checklist_material` (`material_name`),
  ADD KEY `idx_csk_checklist_status` (`status`),
  ADD KEY `idx_csk_checklist_inspection_material` (`inspection_id`,`material_name`);

--
-- Indexes for table `chemical_spill_kit_materials_inventory_tbl`
--
ALTER TABLE `chemical_spill_kit_materials_inventory_tbl`
  ADD PRIMARY KEY (`chemical_spill_kit_id`),
  ADD KEY `idx_csk_code_branch` (`chemical_spill_kit_code`,`branch`),
  ADD KEY `idx_csk_branch` (`branch`),
  ADD KEY `idx_csk_monthly_inspection` (`is_inspected_this_month`);

--
-- Indexes for table `chemical_spill_kit_materials_tbl`
--
ALTER TABLE `chemical_spill_kit_materials_tbl`
  ADD PRIMARY KEY (`material_id`),
  ADD UNIQUE KEY `uq_spill_material_branch` (`branch`,`material_name`),
  ADD KEY `idx_spill_material_branch` (`branch`);

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
-- Indexes for table `transactions_tbl`
--
ALTER TABLE `transactions_tbl`
  ADD PRIMARY KEY (`transaction_id`),
  ADD UNIQUE KEY `uq_transaction_inspection` (`category`,`branch`,`inspection_id`),
  ADD KEY `idx_transaction_category_status` (`category`,`status`),
  ADD KEY `idx_transaction_branch` (`branch`),
  ADD KEY `idx_transaction_submitted_at` (`submitted_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs_tbl`
--
ALTER TABLE `activity_logs_tbl`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=254;

--
-- AUTO_INCREMENT for table `chemical_spill_kit_current_inventory_materials_tbl`
--
ALTER TABLE `chemical_spill_kit_current_inventory_materials_tbl`
  MODIFY `current_material_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chemical_spill_kit_inspection_tbl`
--
ALTER TABLE `chemical_spill_kit_inspection_tbl`
  MODIFY `inspection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `chemical_spill_kit_materials_checklist_tbl`
--
ALTER TABLE `chemical_spill_kit_materials_checklist_tbl`
  MODIFY `checklist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `chemical_spill_kit_materials_inventory_tbl`
--
ALTER TABLE `chemical_spill_kit_materials_inventory_tbl`
  MODIFY `chemical_spill_kit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `chemical_spill_kit_materials_tbl`
--
ALTER TABLE `chemical_spill_kit_materials_tbl`
  MODIFY `material_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `fire_extinguishers_tbl`
--
ALTER TABLE `fire_extinguishers_tbl`
  MODIFY `extinguisher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=387;

--
-- AUTO_INCREMENT for table `inspection_checklist_tbl`
--
ALTER TABLE `inspection_checklist_tbl`
  MODIFY `inspect_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `maintenance_dropdown_tbl`
--
ALTER TABLE `maintenance_dropdown_tbl`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `transactions_tbl`
--
ALTER TABLE `transactions_tbl`
  MODIFY `transaction_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;