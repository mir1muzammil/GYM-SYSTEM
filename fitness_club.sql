-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2026 at 07:17 PM
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
-- Database: `fitness_club`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`, `full_name`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@fitnessclub.com', '2025-11-22 18:40:35'),
(2, 'just', '$2y$10$yulBUQ69T8ZH.K3Soja1h..yzPqlTwu7gjut3jq/MI0CJdJX.yJqi', 'Mir Muzammil', 'lolfarig@gmail.com', '2025-11-22 18:51:35'),
(3, 'mir', '$2y$10$Zdyd1JO65EY/8N9DS5IVfeGSYxI0D9rLXZXATXKOTSeq9hALddj9y', 'Mir Muzammil', 'mirmuzammil7861@gmail.com', '2025-11-22 19:28:58'),
(4, 'blah', '$2y$10$Ji.aULtGtFbZPe7rZAf80u9JcPzV//Lgukt9QEUCzyK4v8C20HBkm', 'blah', 'blah@gmail.com', '2026-03-06 18:19:38');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `AttendanceID` int(11) NOT NULL,
  `MemberID` int(11) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `CheckInTime` time DEFAULT NULL,
  `CheckOutTime` time DEFAULT NULL,
  `Status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`AttendanceID`, `MemberID`, `Date`, `CheckInTime`, `CheckOutTime`, `Status`) VALUES
(1, 1, '2024-11-20', '06:30:00', '08:00:00', 'Present'),
(2, 2, '2024-11-20', '07:00:00', '08:30:00', 'Present'),
(3, 3, '2024-11-20', '17:00:00', '18:30:00', 'Present'),
(4, 1, '2024-11-21', '06:45:00', '08:15:00', 'Present'),
(5, 4, '2024-11-21', '18:00:00', '19:00:00', 'Present'),
(9, 12, '2026-03-06', '21:10:16', '21:10:23', 'Present'),
(10, 13, '2026-03-13', '19:12:08', '19:12:16', 'Present');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `EquipmentID` int(11) NOT NULL,
  `EquipmentName` varchar(100) DEFAULT NULL,
  `PurchaseDate` date DEFAULT NULL,
  `Condition` varchar(50) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`EquipmentID`, `EquipmentName`, `PurchaseDate`, `Condition`, `Quantity`) VALUES
(2, 'Elliptical Machine', '2023-02-01', 'Good', 6),
(3, 'Stationary Bike', '2023-03-10', 'Excellent', 10),
(4, 'Dumbbells Set', '2022-06-15', 'Good', 20),
(5, 'Barbell Set', '2022-06-15', 'Excellent', 10),
(6, 'Weight Bench', '2023-04-01', 'Excellent', 8),
(7, 'Cable Machine', '2023-05-15', 'Good', 4),
(8, 'Rowing Machine', '2023-07-01', 'Excellent', 5),
(9, 'Yoga Mats', '2024-01-01', 'Excellent', 30);

-- --------------------------------------------------------

--
-- Table structure for table `exercise`
--

CREATE TABLE `exercise` (
  `ExerciseID` int(11) NOT NULL,
  `ExerciseName` varchar(100) DEFAULT NULL,
  `MuscleGroup` varchar(50) DEFAULT NULL,
  `EquipmentNeeded` tinyint(1) DEFAULT NULL,
  `Repetitions` int(11) DEFAULT NULL,
  `Sets` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exercise`
--

INSERT INTO `exercise` (`ExerciseID`, `ExerciseName`, `MuscleGroup`, `EquipmentNeeded`, `Repetitions`, `Sets`) VALUES
(2, 'Squats', 'Legs', 1, 15, 4),
(3, 'Deadlift', 'Back', 1, 10, 3),
(4, 'Pull-ups', 'Back', 1, 10, 3),
(5, 'Push-ups', 'Chest', 0, 20, 3),
(6, 'Plank', 'Core', 0, 60, 3),
(7, 'Lunges', 'Legs', 0, 12, 3),
(8, 'Bicep Curls', 'Arms', 1, 15, 3),
(9, 'Tricep Dips', 'Arms', 0, 12, 3),
(10, 'Shoulder Press', 'Shoulders', 1, 12, 4);

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `MemberID` int(11) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Age` int(11) DEFAULT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `ProfilePhoto` varchar(255) DEFAULT NULL,
  `MembershipTypeID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`MemberID`, `Name`, `Age`, `Gender`, `ContactNumber`, `Email`, `Address`, `JoinDate`, `Password`, `ProfilePhoto`, `MembershipTypeID`) VALUES
(1, 'Usman Malik', 28, 'Male', '0321-1111111', 'usman@email.com', 'Gulshan-e-Iqbal, Karachi', '2024-01-15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 3),
(2, 'Ayesha Siddiqui', 25, 'Female', '0322-2222222', 'ayesha@email.com', 'DHA Phase 5, Karachi', '2024-02-01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 4),
(3, 'Bilal Ahmed', 32, 'Male', '0323-3333333', 'bilal@email.com', 'Clifton, Karachi', '2024-02-15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 2),
(4, 'Zainab Khan', 22, 'Female', '0324-4444444', 'zainab@email.com', 'North Nazimabad, Karachi', '2024-03-01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 5),
(5, 'Imran Shah', 35, 'Male', '0325-5555555', 'imran@email.com', 'Saddar, Karachi', '2024-03-15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1),
(6, 'Hira Farooq', 27, 'Female', '0326-6666666', 'hira@email.com', 'Gulistan-e-Johar, Karachi', '2024-04-01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 3),
(7, 'Kamran Yousuf', 30, 'Male', '0327-7777777', 'kamran@email.com', 'PECHS, Karachi', '2024-04-15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 2),
(8, 'Sana Qadir', 24, 'Female', '0328-8888888', 'sana@email.com', 'Malir, Karachi', '2024-05-01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 4),
(12, 'muzammil', 23, 'Male', '03000000000', 'blah@gmail.com', 'sncjvbhjnx', '2026-03-06', '$2y$10$Pb7Hs.L/nH1dvyG0FU8onuEZiYBP/8io28VvLG7DjX/vxbPCCk/gS', NULL, 2),
(13, 'muzammmil', 21, 'Male', '03232178666', 'muzammmil@gmail.com', 'karachi', '2026-03-13', '$2y$10$y4IuSqqPetAtY5yp/dUQcuqfKpnH06ikq6CY6Zmrp4xgYZuPqKqOy', NULL, 2);

-- --------------------------------------------------------

--
-- Table structure for table `membership`
--

CREATE TABLE `membership` (
  `MembershipTypeID` int(11) NOT NULL,
  `TypeName` varchar(50) DEFAULT NULL,
  `DurationMonths` int(11) DEFAULT NULL,
  `Price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `membership`
--

INSERT INTO `membership` (`MembershipTypeID`, `TypeName`, `DurationMonths`, `Price`) VALUES
(1, 'Basic', 1, 2500.00),
(2, 'Standard', 3, 6500.00),
(3, 'Premium', 6, 12000.00),
(4, 'Annual', 12, 20000.00),
(5, 'Student', 1, 1800.00);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `token_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `token` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`token_id`, `admin_id`, `token`, `expires_at`, `used`, `created_at`) VALUES
(1, 2, '523570', '2025-11-23 00:24:00', 1, '2025-11-22 19:09:00'),
(2, 3, '444468', '2025-11-23 00:44:13', 1, '2025-11-22 19:29:14');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `MemberID` int(11) DEFAULT NULL,
  `Amount` decimal(10,2) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `PaymentMethod` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PaymentID`, `MemberID`, `Amount`, `Date`, `PaymentMethod`) VALUES
(2, 2, 20000.00, '2024-02-01', 'Bank Transfer'),
(3, 3, 6500.00, '2024-07-01', 'Cash'),
(4, 4, 1800.00, '2024-08-01', 'Cash'),
(5, 5, 2500.00, '2024-08-15', 'Debit Card'),
(6, 6, 12000.00, '2024-04-01', 'Credit Card'),
(7, 7, 6500.00, '2024-04-15', 'Cash'),
(11, 12, 6500.00, '2026-03-06', 'JazzCash');

-- --------------------------------------------------------

--
-- Table structure for table `payment_requests`
--

CREATE TABLE `payment_requests` (
  `RequestID` int(11) NOT NULL,
  `MemberID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `PaymentMethod` varchar(50) NOT NULL,
  `TransactionRef` varchar(100) DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `Status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `RequestedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `ReviewedAt` datetime DEFAULT NULL,
  `ReviewedBy` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_requests`
--

INSERT INTO `payment_requests` (`RequestID`, `MemberID`, `Amount`, `PaymentMethod`, `TransactionRef`, `Notes`, `Status`, `RequestedAt`, `ReviewedAt`, `ReviewedBy`) VALUES
(1, 12, 6500.00, 'JazzCash', 'l,kmbgkc x', '', 'Approved', '2026-03-06 20:09:56', '2026-03-06 21:12:57', 4);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `StaffID` int(11) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Role` varchar(50) DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Salary` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`StaffID`, `Name`, `Role`, `ContactNumber`, `Email`, `Salary`) VALUES
(1, 'Muhammad Asif', 'Manager', '0311-1111111', 'asif@fitnessclub.com', 80000.00),
(2, 'Nadia Jamil', 'Receptionist', '0312-2222222', 'nadia@fitnessclub.com', 35000.00),
(3, 'Tariq Mehmood', 'Cleaner', '0313-3333333', 'tariq@fitnessclub.com', 25000.00);

-- --------------------------------------------------------

--
-- Table structure for table `trainer`
--

CREATE TABLE `trainer` (
  `TrainerID` int(11) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Specialization` varchar(100) DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Salary` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trainer`
--

INSERT INTO `trainer` (`TrainerID`, `Name`, `Specialization`, `ContactNumber`, `Email`, `Salary`) VALUES
(1, 'Ahmed Khan', 'Weight Training', '0300-1234567', 'ahmed@fitnessclub.com', 45000.00),
(2, 'Sara Ali', 'Yoga & Pilates', '0301-2345678', 'sara@fitnessclub.com', 40000.00),
(3, 'Hassan Raza', 'Cardio & HIIT', '0302-3456789', 'hassan@fitnessclub.com', 42000.00),
(4, 'Fatima Noor', 'CrossFit', '0303-4567890', 'fatima@fitnessclub.com', 48000.00),
(5, 'Ali Hussain', 'Boxing & MMA', '0304-5678901', 'ali@fitnessclub.com', 50000.00);

-- --------------------------------------------------------

--
-- Table structure for table `workoutplan`
--

CREATE TABLE `workoutplan` (
  `PlanID` int(11) NOT NULL,
  `PlanName` varchar(100) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `DurationWeeks` int(11) DEFAULT NULL,
  `TrainerID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workoutplan`
--

INSERT INTO `workoutplan` (`PlanID`, `PlanName`, `Description`, `DurationWeeks`, `TrainerID`) VALUES
(1, 'Beginner Strength', 'Basic strength training for beginners', 8, 1),
(2, 'Advanced Muscle Building', 'Intense muscle building program', 12, 1),
(3, 'Yoga Flow', 'Relaxing yoga sessions for flexibility', 6, 2),
(4, 'HIIT Burn', 'High intensity interval training for fat loss', 4, 3),
(5, 'CrossFit Challenge', 'Complete CrossFit training program', 10, 4);

-- --------------------------------------------------------

--
-- Table structure for table `workoutschedule`
--

CREATE TABLE `workoutschedule` (
  `ScheduleID` int(11) NOT NULL,
  `MemberID` int(11) DEFAULT NULL,
  `PlanID` int(11) DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workoutschedule`
--

INSERT INTO `workoutschedule` (`ScheduleID`, `MemberID`, `PlanID`, `StartDate`, `EndDate`) VALUES
(2, 2, 3, '2024-06-15', '2024-07-27'),
(3, 3, 1, '2024-07-01', '2024-08-26'),
(4, 4, 4, '2024-07-15', '2024-08-12'),
(5, 5, 5, '2024-08-01', '2024-10-10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`AttendanceID`),
  ADD KEY `MemberID` (`MemberID`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`EquipmentID`);

--
-- Indexes for table `exercise`
--
ALTER TABLE `exercise`
  ADD PRIMARY KEY (`ExerciseID`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`MemberID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `MembershipTypeID` (`MembershipTypeID`);

--
-- Indexes for table `membership`
--
ALTER TABLE `membership`
  ADD PRIMARY KEY (`MembershipTypeID`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `MemberID` (`MemberID`);

--
-- Indexes for table `payment_requests`
--
ALTER TABLE `payment_requests`
  ADD PRIMARY KEY (`RequestID`),
  ADD KEY `MemberID` (`MemberID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`StaffID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `trainer`
--
ALTER TABLE `trainer`
  ADD PRIMARY KEY (`TrainerID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `workoutplan`
--
ALTER TABLE `workoutplan`
  ADD PRIMARY KEY (`PlanID`),
  ADD KEY `TrainerID` (`TrainerID`);

--
-- Indexes for table `workoutschedule`
--
ALTER TABLE `workoutschedule`
  ADD PRIMARY KEY (`ScheduleID`),
  ADD KEY `MemberID` (`MemberID`),
  ADD KEY `PlanID` (`PlanID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `AttendanceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `EquipmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `exercise`
--
ALTER TABLE `exercise`
  MODIFY `ExerciseID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `MemberID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `membership`
--
ALTER TABLE `membership`
  MODIFY `MembershipTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `payment_requests`
--
ALTER TABLE `payment_requests`
  MODIFY `RequestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `StaffID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `trainer`
--
ALTER TABLE `trainer`
  MODIFY `TrainerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `workoutplan`
--
ALTER TABLE `workoutplan`
  MODIFY `PlanID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `workoutschedule`
--
ALTER TABLE `workoutschedule`
  MODIFY `ScheduleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `member` (`MemberID`);

--
-- Constraints for table `member`
--
ALTER TABLE `member`
  ADD CONSTRAINT `member_ibfk_1` FOREIGN KEY (`MembershipTypeID`) REFERENCES `membership` (`MembershipTypeID`);

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `member` (`MemberID`);

--
-- Constraints for table `payment_requests`
--
ALTER TABLE `payment_requests`
  ADD CONSTRAINT `pr_member_fk` FOREIGN KEY (`MemberID`) REFERENCES `member` (`MemberID`);

--
-- Constraints for table `workoutplan`
--
ALTER TABLE `workoutplan`
  ADD CONSTRAINT `workoutplan_ibfk_1` FOREIGN KEY (`TrainerID`) REFERENCES `trainer` (`TrainerID`);

--
-- Constraints for table `workoutschedule`
--
ALTER TABLE `workoutschedule`
  ADD CONSTRAINT `workoutschedule_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `member` (`MemberID`),
  ADD CONSTRAINT `workoutschedule_ibfk_2` FOREIGN KEY (`PlanID`) REFERENCES `workoutplan` (`PlanID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
