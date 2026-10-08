-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 08, 2026 at 06:03 PM
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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_number`, `room_type_id`, `status`) VALUES
(1, '101', 1, 'occupied'),
(2, '102', 1, 'free'),
(3, '103', 2, 'occupied'),
(4, '104', 3, 'cleaning'),
(5, '201', 1, 'free');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `name`, `email`, `password_hash`, `phone_number`, `birth_date`, `nationality`, `address`, `created_at`, `is_deleted`) VALUES
(1, 'admin', '', 'admin@hotel.hu', '$2y$10$eImiTXuWVxfM37uY4JANjOL.88jjw18pG0q040R4g6O3y4WpP3WCe', '', '0000-00-00', '', '', '2026-10-08 18:00:00', 0),
(2, 'receptionist', '', 'recepcio@hotel.hu', '$2y$10$eImiTXuWVxfM37uY4JANjOL.88jjw18pG0q040R4g6O3y4WpP3WCe', '', '0000-00-00', '', '', '2026-10-08 18:00:00', 0),
(3, 'user', 'Nagy Anna', 'vendeg@hotel.hu', '$2y$10$eImiTXuWVxfM37uY4JANjOL.88jjw18pG0q040R4g6O3y4WpP3WCe', '+36201112233', '1998-10-12', 'Magyar', '1011 Budapest, Fő utca 5.', '2026-10-08 18:00:00', 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
