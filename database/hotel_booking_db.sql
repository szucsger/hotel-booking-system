-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 11, 2026 at 05:50 PM
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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `room_id`, `check_in_date`, `check_out_date`, `guest_count`, `total_price`, `status`, `created_at`, `is_paid`) VALUES
(1, 1, 1, '2026-10-12', '2026-10-15', 2, 126000, 'confirmed', '2026-10-01 08:00:00', 1),
(2, 2, 2, '2026-10-10', '2026-10-14', 2, 272000, 'confirmed', '2026-10-02 09:30:00', 1),
(3, 3, 3, '2026-10-12', '2026-10-13', 2, 42000, 'confirmed', '2026-10-05 12:15:00', 0),
(4, 4, 4, '2026-10-11', '2026-10-18', 3, 378000, 'confirmed', '2026-10-06 07:45:00', 1),
(5, 5, 5, '2026-10-20', '2026-10-22', 2, 64000, 'pending', '2026-10-08 14:20:00', 0);

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
  `price_pet_night` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- Dumping data for table `room_types`
--

INSERT INTO `room_types` (`id`, `name`, `description`, `capacity`, `bed_type`, `is_extra_bed_allowed`, `price_pet_night`) VALUES
(1, 'Standard franciaágyas szoba', 'Légkondicionált szoba franciaággyal, zuhanyzós fürdőszobával és városra néző kilátással.', 2, 'Franciaágy', 0, 32000),
(2, 'Deluxe king szoba', 'Tágas prémium szoba king-size ággyal, saját erkélyes kilátással és munkaasztallal.', 2, 'King-size franciaágy', 1, 42000),
(3, 'Családi szoba', 'Két külön hálótérrel rendelkező szoba ideális családok számára, fürdőkáddal.', 4, 'Franciaágy + 2 különálló ágy', 1, 54000),
(4, 'Panorámás lakosztály', 'Exkluzív lakosztály külön nappalival, panorámás terasszal és jakuzzival.', 4, 'King-size franciaágy', 1, 68000),
(5, 'Elnöki lakosztály', 'A legmagasabb szintű luxus, tárgyalóval, privát szaunával és concierge szolgáltatással.', 2, 'Super King-size franciaágy', 0, 120000);

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
