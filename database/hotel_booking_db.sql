-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 18, 2026 at 06:01 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotel_booking_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `room_id` int NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `guest_count` int NOT NULL,
  `total_price` int NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `room_id` (`room_id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `room_id`, `check_in_date`, `check_out_date`, `guest_count`, `total_price`, `status`, `created_at`, `is_paid`) VALUES
(1, 10, 6, '2026-09-21', '2026-09-26', 1, 225000, 'checked_out', '2026-09-14 22:00:00', 1),
(2, 32, 1, '2026-09-15', '2026-09-18', 3, 54000, 'pending', '2026-09-10 22:00:00', 0),
(3, 37, 2, '2026-09-11', '2026-09-17', 4, 144000, 'confirmed', '2026-08-28 22:00:00', 1),
(4, 35, 13, '2026-09-17', '2026-09-19', 4, 64000, 'checked_out', '2026-09-13 22:00:00', 1),
(5, 17, 9, '2026-11-17', '2026-11-21', 2, 300000, 'checked_out', '2026-11-03 23:00:00', 1),
(6, 47, 12, '2026-09-26', '2026-10-02', 3, 144000, 'checked_in', '2026-09-14 22:00:00', 1),
(7, 42, 6, '2026-10-27', '2026-11-01', 4, 225000, 'confirmed', '2026-10-22 22:00:00', 1),
(8, 15, 2, '2026-10-14', '2026-10-15', 2, 24000, 'checked_out', '2026-10-09 22:00:00', 1),
(9, 1, 2, '2026-11-30', '2026-12-06', 1, 144000, 'pending', '2026-11-28 23:00:00', 0),
(10, 22, 2, '2026-11-05', '2026-11-07', 3, 48000, 'checked_in', '2026-10-31 23:00:00', 1),
(11, 35, 3, '2026-11-13', '2026-11-18', 4, 160000, 'pending', '2026-10-30 23:00:00', 1),
(12, 27, 4, '2026-09-13', '2026-09-14', 4, 32000, 'cancelled', '2026-09-05 22:00:00', 1),
(13, 30, 14, '2026-09-07', '2026-09-13', 1, 192000, 'confirmed', '2026-08-30 22:00:00', 1),
(14, 47, 6, '2026-09-14', '2026-09-16', 2, 90000, 'pending', '2026-09-10 22:00:00', 1),
(15, 28, 3, '2026-10-06', '2026-10-10', 2, 128000, 'confirmed', '2026-09-27 22:00:00', 1),
(16, 36, 2, '2026-09-07', '2026-09-13', 1, 144000, 'confirmed', '2026-08-24 22:00:00', 1),
(17, 16, 3, '2026-10-23', '2026-10-27', 4, 128000, 'pending', '2026-10-21 22:00:00', 1),
(18, 11, 7, '2026-09-01', '2026-09-05', 3, 232000, 'checked_in', '2026-08-26 22:00:00', 1),
(19, 28, 12, '2026-11-11', '2026-11-17', 4, 144000, 'pending', '2026-11-05 23:00:00', 0),
(20, 14, 1, '2026-11-14', '2026-11-20', 1, 108000, 'cancelled', '2026-11-12 23:00:00', 0),
(21, 38, 8, '2026-11-04', '2026-11-11', 2, 378000, 'confirmed', '2026-10-25 23:00:00', 1),
(22, 6, 14, '2026-09-24', '2026-09-25', 1, 32000, 'pending', '2026-09-21 22:00:00', 1),
(23, 37, 4, '2026-11-14', '2026-11-19', 1, 160000, 'checked_out', '2026-11-11 23:00:00', 1),
(24, 27, 11, '2026-11-14', '2026-11-19', 3, 90000, 'cancelled', '2026-11-02 23:00:00', 0),
(25, 46, 6, '2026-10-01', '2026-10-04', 4, 135000, 'pending', '2026-09-22 22:00:00', 1),
(26, 21, 15, '2026-09-10', '2026-09-11', 4, 39000, 'checked_out', '2026-08-30 22:00:00', 1),
(27, 7, 2, '2026-11-08', '2026-11-10', 3, 48000, 'pending', '2026-11-05 23:00:00', 1),
(28, 16, 6, '2026-10-07', '2026-10-09', 4, 90000, 'checked_out', '2026-09-24 22:00:00', 1),
(29, 20, 10, '2026-11-23', '2026-11-28', 1, 650000, 'checked_out', '2026-11-17 23:00:00', 1),
(30, 43, 2, '2026-09-18', '2026-09-21', 1, 72000, 'confirmed', '2026-09-05 22:00:00', 1),
(31, 36, 3, '2026-10-05', '2026-10-08', 2, 96000, 'cancelled', '2026-09-23 22:00:00', 0),
(32, 41, 14, '2026-10-04', '2026-10-09', 4, 160000, 'cancelled', '2026-10-01 22:00:00', 0),
(33, 41, 7, '2026-10-06', '2026-10-07', 1, 58000, 'cancelled', '2026-09-24 22:00:00', 0),
(34, 17, 3, '2026-10-27', '2026-11-01', 4, 160000, 'checked_out', '2026-10-25 23:00:00', 1),
(35, 8, 2, '2026-11-28', '2026-11-30', 1, 48000, 'cancelled', '2026-11-20 23:00:00', 0),
(36, 9, 1, '2026-10-10', '2026-10-13', 1, 54000, 'cancelled', '2026-09-28 22:00:00', 0),
(37, 16, 11, '2026-09-14', '2026-09-17', 4, 54000, 'checked_out', '2026-09-01 22:00:00', 1),
(38, 10, 15, '2026-10-01', '2026-10-08', 2, 273000, 'pending', '2026-09-29 22:00:00', 1),
(39, 12, 12, '2026-10-13', '2026-10-20', 4, 168000, 'pending', '2026-10-09 22:00:00', 1),
(40, 45, 2, '2026-10-19', '2026-10-26', 1, 168000, 'checked_in', '2026-10-14 22:00:00', 1),
(41, 13, 14, '2026-10-29', '2026-11-01', 3, 96000, 'pending', '2026-10-27 23:00:00', 0),
(42, 43, 4, '2026-10-22', '2026-10-25', 3, 96000, 'confirmed', '2026-10-08 22:00:00', 1),
(43, 18, 6, '2026-11-22', '2026-11-27', 4, 225000, 'checked_out', '2026-11-15 23:00:00', 1),
(44, 2, 2, '2026-10-04', '2026-10-06', 3, 48000, 'confirmed', '2026-10-01 22:00:00', 1),
(45, 39, 7, '2026-10-15', '2026-10-21', 3, 348000, 'checked_in', '2026-10-04 22:00:00', 1),
(46, 33, 2, '2026-10-20', '2026-10-25', 2, 120000, 'cancelled', '2026-10-07 22:00:00', 0),
(47, 28, 1, '2026-11-06', '2026-11-13', 2, 126000, 'cancelled', '2026-11-03 23:00:00', 1),
(48, 43, 15, '2026-10-13', '2026-10-18', 3, 195000, 'confirmed', '2026-09-30 22:00:00', 1),
(49, 20, 9, '2026-10-10', '2026-10-16', 4, 450000, 'cancelled', '2026-09-27 22:00:00', 1),
(50, 19, 9, '2026-09-17', '2026-09-19', 4, 150000, 'checked_in', '2026-09-05 22:00:00', 1),
(51, 38, 14, '2027-01-24', '2027-01-29', 3, 160000, 'pending', '2027-01-21 04:28:00', 0),
(52, 39, 4, '2027-03-13', '2027-03-17', 2, 128000, 'confirmed', '2027-02-25 13:44:00', 1),
(53, 13, 3, '2026-12-01', '2026-12-05', 3, 128000, 'confirmed', '2026-11-14 02:40:00', 1),
(54, 16, 11, '2027-05-11', '2027-05-15', 2, 72000, 'confirmed', '2027-04-26 06:41:00', 1),
(55, 14, 2, '2027-04-03', '2027-04-09', 3, 144000, 'pending', '2027-03-07 08:46:00', 1),
(56, 5, 7, '2027-05-19', '2027-05-20', 4, 58000, 'confirmed', '2027-04-30 12:38:00', 1),
(57, 2, 6, '2027-04-19', '2027-04-20', 3, 45000, 'pending', '2027-03-22 12:58:00', 1),
(58, 12, 5, '2026-11-19', '2026-11-26', 2, 273000, 'pending', '2026-10-27 03:08:00', 1),
(59, 42, 10, '2027-06-17', '2027-06-19', 4, 260000, 'pending', '2027-06-06 11:16:00', 0),
(60, 6, 2, '2026-11-02', '2026-11-07', 3, 120000, 'pending', '2026-10-09 06:07:00', 0),
(61, 44, 12, '2027-06-09', '2027-06-16', 2, 168000, 'cancelled', '2027-06-02 01:48:00', 0),
(62, 48, 15, '2027-07-24', '2027-07-28', 2, 156000, 'confirmed', '2027-07-18 09:39:00', 1),
(63, 25, 2, '2026-11-16', '2026-11-21', 3, 120000, 'pending', '2026-11-13 11:43:00', 1),
(64, 6, 5, '2027-01-03', '2027-01-06', 4, 117000, 'confirmed', '2026-12-19 13:27:00', 1),
(65, 21, 4, '2026-12-04', '2026-12-07', 2, 96000, 'confirmed', '2026-12-02 04:35:00', 1),
(66, 40, 8, '2027-08-04', '2027-08-05', 3, 54000, 'confirmed', '2027-07-08 09:30:00', 1),
(67, 43, 4, '2027-07-26', '2027-07-31', 3, 160000, 'confirmed', '2027-07-24 11:42:00', 1),
(68, 23, 11, '2027-04-21', '2027-04-25', 4, 72000, 'cancelled', '2027-04-18 02:46:00', 0),
(69, 34, 2, '2027-02-04', '2027-02-06', 1, 48000, 'confirmed', '2027-01-23 13:37:00', 1),
(70, 2, 8, '2027-01-28', '2027-02-03', 3, 324000, 'pending', '2027-01-05 04:25:00', 0),
(71, 9, 8, '2027-07-23', '2027-07-28', 3, 270000, 'confirmed', '2027-06-30 09:13:00', 1),
(72, 17, 4, '2027-05-27', '2027-05-29', 4, 64000, 'confirmed', '2027-05-01 13:45:00', 1),
(73, 4, 10, '2027-04-10', '2027-04-16', 4, 780000, 'pending', '2027-03-18 14:43:00', 0),
(74, 3, 13, '2027-06-12', '2027-06-17', 1, 160000, 'confirmed', '2027-05-28 06:18:00', 1),
(75, 18, 13, '2026-12-29', '2027-01-02', 1, 128000, 'confirmed', '2026-12-05 10:22:00', 1),
(76, 41, 2, '2026-11-30', '2026-12-05', 4, 120000, 'confirmed', '2026-11-12 03:56:00', 1),
(77, 10, 8, '2027-05-18', '2027-05-19', 3, 54000, 'confirmed', '2027-05-09 08:47:00', 1),
(78, 42, 13, '2027-07-16', '2027-07-17', 2, 32000, 'cancelled', '2027-07-08 03:50:00', 0),
(79, 12, 12, '2027-01-23', '2027-01-27', 2, 96000, 'confirmed', '2027-01-15 06:15:00', 1),
(80, 44, 9, '2027-02-10', '2027-02-11', 1, 75000, 'confirmed', '2027-01-11 06:03:00', 1),
(81, 9, 10, '2027-06-20', '2027-06-22', 1, 260000, 'confirmed', '2027-06-17 06:21:00', 1),
(82, 37, 12, '2026-12-02', '2026-12-05', 4, 72000, 'confirmed', '2026-11-08 14:53:00', 1),
(83, 19, 11, '2027-07-15', '2027-07-16', 1, 18000, 'confirmed', '2027-07-04 10:50:00', 1),
(84, 47, 14, '2027-05-03', '2027-05-09', 4, 192000, 'confirmed', '2027-04-06 02:12:00', 1),
(85, 46, 15, '2027-04-05', '2027-04-11', 1, 234000, 'confirmed', '2027-03-25 08:03:00', 1),
(86, 16, 8, '2027-02-18', '2027-02-20', 4, 108000, 'confirmed', '2027-01-28 12:29:00', 1),
(87, 29, 8, '2027-01-24', '2027-01-27', 1, 162000, 'confirmed', '2027-01-12 02:58:00', 1),
(88, 15, 5, '2027-07-13', '2027-07-16', 1, 117000, 'pending', '2027-07-07 09:23:00', 1),
(89, 50, 13, '2027-06-08', '2027-06-13', 3, 160000, 'confirmed', '2027-05-23 03:41:00', 1),
(90, 6, 3, '2026-10-27', '2026-10-30', 4, 96000, 'confirmed', '2026-09-28 07:58:00', 1),
(91, 23, 1, '2027-02-12', '2027-02-13', 4, 18000, 'confirmed', '2027-01-26 08:07:00', 1),
(92, 7, 15, '2027-01-20', '2027-01-24', 4, 156000, 'confirmed', '2027-01-07 03:47:00', 1),
(93, 44, 10, '2027-03-11', '2027-03-18', 4, 910000, 'confirmed', '2027-02-13 12:27:00', 1),
(94, 43, 8, '2027-03-18', '2027-03-20', 4, 108000, 'pending', '2027-03-15 14:27:00', 0),
(95, 46, 12, '2026-10-22', '2026-10-29', 4, 168000, 'confirmed', '2026-10-06 03:37:00', 1),
(96, 40, 5, '2026-12-25', '2026-12-28', 4, 117000, 'confirmed', '2026-11-29 12:11:00', 1),
(97, 29, 12, '2026-11-18', '2026-11-22', 1, 96000, 'pending', '2026-10-23 06:54:00', 0),
(98, 44, 8, '2027-03-05', '2027-03-12', 3, 378000, 'pending', '2027-02-26 02:56:00', 0),
(99, 49, 14, '2027-07-19', '2027-07-24', 4, 160000, 'cancelled', '2027-07-04 01:49:00', 0),
(100, 45, 1, '2027-04-26', '2027-05-02', 1, 108000, 'confirmed', '2027-04-19 09:08:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `room_number` varchar(10) COLLATE utf8mb4_hungarian_ci NOT NULL,
  `room_type_id` int NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT 'free',
  PRIMARY KEY (`id`),
  KEY `INDEX` (`room_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_number`, `room_type_id`, `status`) VALUES
(1, '101', 1, 'occupied'),
(2, '102', 2, 'free'),
(3, '103', 3, 'cleaning'),
(4, '104', 4, 'cleaning'),
(5, '105', 5, 'occupied'),
(6, '201', 6, 'cleaning'),
(7, '202', 7, 'free'),
(8, '203', 8, 'cleaning'),
(9, '204', 9, 'free'),
(10, '205', 10, 'cleaning'),
(11, '301', 1, 'occupied'),
(12, '302', 2, 'cleaning'),
(13, '303', 3, 'cleaning'),
(14, '304', 4, 'cleaning'),
(15, '305', 5, 'free');

-- --------------------------------------------------------

--
-- Table structure for table `room_types`
--

DROP TABLE IF EXISTS `room_types`;
CREATE TABLE IF NOT EXISTS `room_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_hungarian_ci NOT NULL,
  `description` text COLLATE utf8mb4_hungarian_ci,
  `capacity` int NOT NULL,
  `bed_type` varchar(50) COLLATE utf8mb4_hungarian_ci NOT NULL,
  `is_extra_bed_allowed` tinyint(1) NOT NULL,
  `price_per_night` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- Dumping data for table `room_types`
--

INSERT INTO `room_types` (`id`, `name`, `description`, `capacity`, `bed_type`, `is_extra_bed_allowed`, `price_per_night`) VALUES
(1, 'Single Standard', 'Egyfős alapszoba, kényelmes egyszemélyes ággyal, zuhanyzós fürdőszobával.', 1, 'Egyszemélyes ágy', 0, 18000),
(2, 'Single Deluxe', 'Tágasabb egyfős szoba dolgozósarokkal és erkéllyel.', 1, 'Egyszemélyes ágy', 0, 24000),
(3, 'Double Standard', 'Légkondicionált szoba franciaággyal, városra néző kilátással.', 2, 'Franciaágy', 0, 32000),
(4, 'Twin Standard', 'Két különálló ággyal rendelkező kényelmes szoba.', 2, '2 különálló ágy', 0, 32000),
(5, 'Double Superior', 'Nagyobb alapterületű franciaágyas szoba ülősarokkal.', 2, 'Franciaágy', 1, 39000),
(6, 'Deluxe King', 'Tágas prémium szoba king-size ággyal és munkaasztallal.', 2, 'King-size franciaágy', 1, 45000),
(7, 'Executive Suite', 'Exkluzív lakosztály külön nappalival és panorámás terasszal.', 3, 'King-size franciaágy + Kanapé', 1, 58000),
(8, 'Családi Szoba', 'Két hálótérrel rendelkező szoba családoknak, fürdőkáddal.', 4, 'Franciaágy + 2 különálló ágy', 1, 54000),
(9, 'Panorámás Lakosztály', 'Luxus lakosztály saját jakuzzival és erkélyes kilátással.', 2, 'Super King-size franciaágy', 1, 75000),
(10, 'Elnöki Lakosztály', 'A legmagasabb szintű luxus tárgyalóval és szaunával.', 4, 'Super King-size + King-size', 0, 130000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role` varchar(20) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT 'user',
  `name` varchar(100) COLLATE utf8mb4_hungarian_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_hungarian_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_hungarian_ci NOT NULL,
  `phone_number` varchar(30) COLLATE utf8mb4_hungarian_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `nationality` varchar(50) COLLATE utf8mb4_hungarian_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_hungarian_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `name`, `email`, `password_hash`, `phone_number`, `birth_date`, `nationality`, `address`, `created_at`, `is_deleted`) VALUES
(1, 'admin', 'Horváth Anna', 'user1@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36301419610', '1977-04-08', 'Magyar', 'Győr, Kossuth Lajos utca 76.', '2025-08-05 06:01:00', 0),
(2, 'receptionist', 'Molnár László', 'user2@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36304903402', '1992-10-01', 'Szlovák', 'Sopron, Béke tér 29.', '2025-08-18 15:17:00', 0),
(3, 'receptionist', 'Kovács Viktória', 'user3@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36303678638', '2004-07-11', 'Magyar', 'Szombathely, Ady Endre út 98.', '2025-06-22 07:05:00', 0),
(4, 'user', 'Horváth Sándor', 'user4@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36307022674', '1982-10-09', 'Magyar', 'Miskolc, Kossuth Lajos utca 119.', '2025-07-13 07:35:00', 0),
(5, 'user', 'Török Mária', 'user5@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36307067228', '1996-04-23', 'Magyar', 'Budapest, Ady Endre út 99.', '2025-05-29 07:54:00', 0),
(6, 'user', 'Horváth József', 'user6@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36307377459', '1977-08-21', 'Magyar', 'Szombathely, Dózsa György út 46.', '2025-04-18 16:17:00', 0),
(7, 'user', 'Szabó Dóra', 'user7@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36303871230', '1994-12-08', 'Magyar', 'Miskolc, Béke tér 35.', '2026-04-19 16:44:00', 0),
(8, 'user', 'Balogh Attila', 'user8@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36306440561', '1963-04-27', 'Magyar', 'Debrecen, Béke tér 35.', '2025-02-03 10:58:00', 0),
(9, 'user', 'Takács Eszter', 'user9@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36304567281', '2001-08-13', 'Osztrák', 'Szombathely, Rákóczi út 18.', '2025-05-07 17:35:00', 0),
(10, 'user', 'Farkas Attila', 'user10@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36308187926', '1997-07-12', 'Magyar', 'Szombathely, Arany János utca 12.', '2026-01-22 07:55:00', 0),
(11, 'user', 'Varga Éva', 'user11@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36303684052', '2003-07-20', 'Magyar', 'Szeged, Béke tér 77.', '2025-08-28 14:16:00', 0),
(12, 'user', 'Kovács Attila', 'user12@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36302921859', '2003-09-25', 'Magyar', 'Debrecen, Kossuth Lajos utca 38.', '2025-08-11 08:29:00', 0),
(13, 'user', 'Farkas Katalin', 'user13@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36309398441', '1971-09-04', 'Magyar', 'Veszprém, Ady Endre út 20.', '2025-07-11 18:10:00', 0),
(14, 'user', 'Rácz Attila', 'user14@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36301009594', '1998-06-16', 'Magyar', 'Győr, Dózsa György út 113.', '2026-03-02 19:19:00', 0),
(15, 'user', 'Tóth Anikó', 'user15@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36305041154', '1996-02-03', 'Osztrák', 'Győr, Petőfi Sándor utca 17.', '2025-12-04 14:35:00', 0),
(16, 'user', 'Farkas István', 'user16@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36309852897', '1998-07-07', 'Szlovák', 'Sopron, Rákóczi út 52.', '2025-12-10 17:23:00', 0),
(17, 'user', 'Rácz Judit', 'user17@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36308574680', '1967-04-08', 'Magyar', 'Debrecen, Fő utca 76.', '2025-10-11 09:37:00', 0),
(18, 'user', 'Kovács József', 'user18@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36302191066', '2005-11-02', 'Magyar', 'Győr, Fő utca 111.', '2025-06-19 07:32:00', 0),
(19, 'user', 'Farkas Anikó', 'user19@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36309143903', '1973-09-05', 'Osztrák', 'Sopron, Arany János utca 104.', '2025-07-28 09:06:00', 0),
(20, 'user', 'Mészáros László', 'user20@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36306944126', '1987-07-15', 'Magyar', 'Győr, Fő utca 52.', '2026-01-08 12:51:00', 0),
(21, 'user', 'Horváth Boglárka', 'user21@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36305171761', '1972-04-18', 'Osztrák', 'Szombathely, Béke tér 24.', '2025-05-23 13:15:00', 0),
(22, 'user', 'Szabó Bence', 'user22@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36308434500', '1995-02-02', 'Szlovák', 'Budapest, Kossuth Lajos utca 119.', '2026-01-21 10:10:00', 0),
(23, 'user', 'Simon Andrea', 'user23@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36309075854', '1973-07-02', 'Magyar', 'Szeged, Fő utca 50.', '2025-05-16 18:50:00', 0),
(24, 'user', 'Papp Gábor', 'user24@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36308096887', '2004-12-26', 'Szlovák', 'Miskolc, Petőfi Sándor utca 25.', '2025-06-01 09:03:00', 0),
(25, 'user', 'Fekete Eszter', 'user25@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36302022698', '1980-01-02', 'Osztrák', 'Veszprém, Petőfi Sándor utca 8.', '2026-05-07 14:05:00', 0),
(26, 'user', 'Nagy Bence', 'user26@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36302149597', '1998-02-22', 'Magyar', 'Szeged, Kossuth Lajos utca 114.', '2025-10-19 09:37:00', 0),
(27, 'user', 'Tóth Eszter', 'user27@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36302375453', '1986-11-19', 'Szlovák', 'Debrecen, Rákóczi út 27.', '2025-12-09 18:20:00', 0),
(28, 'user', 'Farkas József', 'user28@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36307640184', '1968-11-21', 'Magyar', 'Miskolc, Dózsa György út 119.', '2026-01-20 08:00:00', 0),
(29, 'user', 'Török Judit', 'user29@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36302677407', '1964-09-07', 'Szlovák', 'Pécs, Petőfi Sándor utca 120.', '2025-06-28 07:56:00', 0),
(30, 'user', 'Juhász József', 'user30@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36305781295', '1970-08-27', 'Szlovák', 'Pécs, Fő utca 86.', '2026-02-23 15:19:00', 0),
(31, 'user', 'Horváth Anna', 'user31@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36303253031', '1976-02-04', 'Szlovák', 'Szombathely, Rákóczi út 37.', '2025-11-06 10:45:00', 0),
(32, 'user', 'Molnár Zoltán', 'user32@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36305428914', '1992-08-09', 'Magyar', 'Győr, Béke tér 107.', '2025-05-22 06:00:00', 0),
(33, 'user', 'Varga Zsuzsanna', 'user33@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36305394880', '1970-12-15', 'Szlovák', 'Szeged, Fő utca 15.', '2025-02-08 18:57:00', 0),
(34, 'user', 'Fekete István', 'user34@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36301604451', '1983-10-18', 'Magyar', 'Szeged, Petőfi Sándor utca 6.', '2025-06-07 11:57:00', 0),
(35, 'user', 'Tóth Viktória', 'user35@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36307003042', '1973-11-08', 'Magyar', 'Debrecen, Béke tér 80.', '2026-01-19 09:59:00', 0),
(36, 'user', 'Balogh Máté', 'user36@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36303726327', '1971-07-01', 'Magyar', 'Debrecen, Béke tér 103.', '2025-12-09 18:51:00', 0),
(37, 'user', 'Farkas Anikó', 'user37@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36303670896', '2004-02-13', 'Magyar', 'Miskolc, Ady Endre út 26.', '2026-02-23 14:22:00', 0),
(38, 'user', 'Balogh János', 'user38@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36304740036', '1961-11-07', 'Német', 'Debrecen, Rákóczi út 111.', '2025-02-05 19:17:00', 0),
(39, 'user', 'Rácz Zsuzsanna', 'user39@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36307705490', '2003-09-11', 'Magyar', 'Győr, Rákóczi út 23.', '2025-10-25 10:02:00', 0),
(40, 'user', 'Török László', 'user40@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36308290770', '1982-12-26', 'Magyar', 'Szeged, Kossuth Lajos utca 50.', '2026-04-06 15:12:00', 0),
(41, 'user', 'Tóth Mária', 'user41@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36308315831', '1960-09-26', 'Szlovák', 'Sopron, Dózsa György út 56.', '2025-02-05 17:58:00', 0),
(42, 'user', 'Török Zoltán', 'user42@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36306266630', '2002-02-24', 'Magyar', 'Veszprém, Rákóczi út 86.', '2025-07-29 11:25:00', 0),
(43, 'user', 'Papp Dóra', 'user43@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36303135534', '1972-07-22', 'Német', 'Szombathely, Rákóczi út 52.', '2025-10-08 06:19:00', 0),
(44, 'user', 'Molnár János', 'user44@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36308212326', '1997-10-21', 'Magyar', 'Miskolc, Arany János utca 57.', '2025-12-12 10:32:00', 0),
(45, 'user', 'Nagy Judit', 'user45@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36302422633', '1978-09-22', 'Magyar', 'Győr, Ady Endre út 87.', '2025-06-08 09:51:00', 0),
(46, 'user', 'Varga József', 'user46@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36301409790', '1962-04-16', 'Magyar', 'Miskolc, Béke tér 114.', '2025-11-19 16:12:00', 0),
(47, 'user', 'Lakatos Dóra', 'user47@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36309294272', '1985-04-05', 'Magyar', 'Győr, Béke tér 29.', '2025-04-01 18:44:00', 0),
(48, 'user', 'Oláh Attila', 'user48@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36301842524', '1995-04-28', 'Magyar', 'Miskolc, Petőfi Sándor utca 103.', '2025-08-26 16:33:00', 0),
(49, 'user', 'Török Krisztina', 'user49@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36306323376', '1988-10-27', 'Szlovák', 'Szeged, Arany János utca 115.', '2025-03-23 18:55:00', 0),
(50, 'user', 'Oláh Gábor', 'user50@example.hu', '$2y$10$e8T3n7N7z.9V...placeholderhash', '+36305348512', '1975-11-09', 'Szlovák', 'Miskolc, Ady Endre út 36.', '2025-08-14 07:45:00', 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
