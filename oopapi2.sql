-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 13, 2024 at 09:21 AM
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
-- Database: `oopapi2`
--

-- --------------------------------------------------------

--
-- Table structure for table `accountstable`
--

CREATE TABLE `accountstable` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `user_email` varchar(50) DEFAULT NULL,
  `user_password` varchar(100) DEFAULT NULL,
  `token` text DEFAULT NULL,
  `vip_points` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vip_access` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accountstable`
--

INSERT INTO `accountstable` (`id`, `userID`, `user_email`, `user_password`, `token`, `vip_points`, `vip_access`) VALUES
(1, 1, 'jeraldpangan123', '$2y$10$YzMzOGYyNmU2OWM5MTI2MenmJriDwilav4gqSFsLNMKvWHl6lmX0G', 'NzhkNjM4Y2Q5MTg3M2Q2OGUwN2I0MWI4ZmM3NmUzMmY5MTllNWJkOGU1YTliODc4Yzg4YzBlNThlNWJiZDA4Nw==', 0.00, 0),
(2, 7, 'godwindg69', '$2y$10$MzU5NzE0NDQ3M2VlYjhkYOKtkQwH.uKjz0goqCYBJncBhzxftWNGC', 'YWQxOGQ5YTVmMzM1ZDE4Yzk4ODU1MWU4ZDc0NGNlYzZiYzYxNzQ3YzQ3M2M1YTQwMjc3Y2M4OTNkNzMwNGJjOA==', 0.00, 0),
(3, 8, 'jaja', '$2y$10$NWU4MWY1ZTA1Y2IzN2I1YOP6y0yXx9UE9ItnfNVchI6eDriexoUQi', 'ZWQ3NTBkZTkxOGQxNmUzZTNiNWJmYTY1ZWVlMjZhNjFjYTk2Mzg3Y2NlZjY5ZTY5YTc2NWQ4MGJjM2I2ZDM2MA==', 3000.00, 0),
(4, 9, 'buboy1000', '$2y$10$OTgyZmRlZjFhYzdhODZlNu7aGRbJ4Fn23VBigu4.l9TloRGDAQzPS', 'NjRjMGYwOWU2ZGQxOGE5YmM2NTI2MzVmNTA5NTY3ZWE5MDFlN2FmZTRlN2MxNzUxNmJhZTdhMzgxYTExMWE5Ng==', 1633500.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `billingtable`
--

CREATE TABLE `billingtable` (
  `billingID` int(11) NOT NULL,
  `bookingID` int(11) DEFAULT NULL,
  `carID` int(11) DEFAULT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `amount_paid` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billingtable`
--

INSERT INTO `billingtable` (`billingID`, `bookingID`, `carID`, `daily_rate`, `total_cost`, `amount_paid`) VALUES
(23, 12, NULL, 0.00, NULL, 3000.00),
(24, 14, NULL, 0.00, NULL, 1633500.00);

-- --------------------------------------------------------

--
-- Table structure for table `bookingtable`
--

CREATE TABLE `bookingtable` (
  `bookingID` int(11) NOT NULL,
  `carID` int(11) DEFAULT NULL,
  `userID` int(11) DEFAULT NULL,
  `daily_rate` decimal(10,2) DEFAULT NULL,
  `book_date` datetime(6) NOT NULL,
  `return_date` datetime(6) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookingtable`
--

INSERT INTO `bookingtable` (`bookingID`, `carID`, `userID`, `daily_rate`, `book_date`, `return_date`, `total_cost`) VALUES
(11, 1, 7, NULL, '2024-12-18 00:00:00.000000', '2023-12-19 00:00:00.000000', 1800.00),
(12, 2, 8, NULL, '2025-01-01 00:00:00.000000', '2025-01-03 00:00:00.000000', 3000.00),
(13, 9, 9, NULL, '2024-12-25 00:00:00.000000', '2024-12-26 00:00:00.000000', 4500.00),
(14, 9, 9, NULL, '2024-12-28 00:00:00.000000', '2025-12-26 00:00:00.000000', 1633500.00);

-- --------------------------------------------------------

--
-- Table structure for table `carstable`
--

CREATE TABLE `carstable` (
  `carID` int(11) NOT NULL,
  `car_brand` varchar(50) DEFAULT NULL,
  `car_model` varchar(50) DEFAULT NULL,
  `manu_year` varchar(10) DEFAULT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `AC` tinyint(1) DEFAULT NULL,
  `seating_capacity` int(10) DEFAULT NULL,
  `plate_no` varchar(10) DEFAULT NULL,
  `isdeleted` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carstable`
--

INSERT INTO `carstable` (`carID`, `car_brand`, `car_model`, `manu_year`, `daily_rate`, `AC`, `seating_capacity`, `plate_no`, `isdeleted`) VALUES
(1, 'Toyota', 'Vios', '2020', 1800.00, 1, 5, 'ABC1234', 0),
(2, 'Mitsubishi', 'Mirage', '2021', 1500.00, 1, 5, 'DEF5678', 0),
(3, 'Honda', 'Civic', '2019', 2500.00, 1, 5, 'GHI9101', 0),
(4, 'Hyundai', 'Accent', '2020', 1700.00, 1, 5, 'JKL2345', 0),
(5, 'Ford', 'Everest', '2022', 3500.00, 1, 7, 'MNO6789', 0),
(6, 'Toyota', 'Fortuner', '2021', 4000.00, 1, 7, 'PQR3456', 0),
(7, 'Suzuki', 'Celerio', '2022', 1200.00, 1, 5, 'STU7890', 0),
(8, 'Nissan', 'Almera', '2020', 1600.00, 1, 5, 'VWX4567', 0),
(9, 'Chevrolet', 'Trailblazer', '2021', 4500.00, 1, 7, 'YZA8901', 0),
(10, 'Kia', 'Sportage', '2019', 3000.00, 1, 5, 'BCD2345', 0),
(11, 'Rolls-Royce', 'Phantom', NULL, 250000.00, NULL, 4, NULL, 0),
(12, 'Lamborghini', 'Aventador', NULL, 300000.00, NULL, 2, NULL, 0),
(13, 'Ferrari', 'LaFerrari', NULL, 500000.00, NULL, 2, NULL, 0),
(14, 'Bentley', 'Continental GT', NULL, 200000.00, NULL, 4, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `userstable`
--

CREATE TABLE `userstable` (
  `userID` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `isdeleted` tinyint(1) DEFAULT NULL,
  `drivers_license` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userstable`
--

INSERT INTO `userstable` (`userID`, `name`, `contact_no`, `isdeleted`, `drivers_license`) VALUES
(1, 'Jerald Pangan', '09123456789', 0, ''),
(7, 'Godwin De Guzman', '099101101', 0, '123456678'),
(8, 'Justine Balagasay', '09953035437', 0, '11111000'),
(9, 'Cendo Bolofer', '091088888', 0, '00000000');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accountstable`
--
ALTER TABLE `accountstable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_userID` (`userID`);

--
-- Indexes for table `billingtable`
--
ALTER TABLE `billingtable`
  ADD PRIMARY KEY (`billingID`),
  ADD KEY `bookingID` (`bookingID`),
  ADD KEY `billingtable_ibfk_2` (`carID`);

--
-- Indexes for table `bookingtable`
--
ALTER TABLE `bookingtable`
  ADD PRIMARY KEY (`bookingID`),
  ADD KEY `carID` (`carID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `carstable`
--
ALTER TABLE `carstable`
  ADD PRIMARY KEY (`carID`);

--
-- Indexes for table `userstable`
--
ALTER TABLE `userstable`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `unique_drivers_license` (`drivers_license`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accountstable`
--
ALTER TABLE `accountstable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `billingtable`
--
ALTER TABLE `billingtable`
  MODIFY `billingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `bookingtable`
--
ALTER TABLE `bookingtable`
  MODIFY `bookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `carstable`
--
ALTER TABLE `carstable`
  MODIFY `carID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `userstable`
--
ALTER TABLE `userstable`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accountstable`
--
ALTER TABLE `accountstable`
  ADD CONSTRAINT `accountstable_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `userstable` (`userID`);

--
-- Constraints for table `billingtable`
--
ALTER TABLE `billingtable`
  ADD CONSTRAINT `billingtable_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `bookingtable` (`bookingID`),
  ADD CONSTRAINT `billingtable_ibfk_2` FOREIGN KEY (`carID`) REFERENCES `bookingtable` (`carID`);

--
-- Constraints for table `bookingtable`
--
ALTER TABLE `bookingtable`
  ADD CONSTRAINT `bookingtable_ibfk_1` FOREIGN KEY (`carID`) REFERENCES `carstable` (`carID`),
  ADD CONSTRAINT `bookingtable_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `userstable` (`userID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
