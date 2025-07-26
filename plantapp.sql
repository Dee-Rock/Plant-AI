-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 26, 2025 at 02:57 PM
-- Server version: 5.7.24-log
-- PHP Version: 7.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `plantapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `identification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `identifications`
--

CREATE TABLE `identifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `plant_name` varchar(255) NOT NULL,
  `scientific_name` varchar(255) DEFAULT NULL,
  `result_json` text,
  `identified_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `identifications`
--

INSERT INTO `identifications` (`id`, `user_id`, `image_path`, `plant_name`, `scientific_name`, `result_json`, `identified_at`) VALUES
(1, NULL, 'uploads/plant_687fc747c2520.jpeg', 'Mangifera indica', 'Mangifera indica', '{\"id\":105002674900,\"plant_name\":\"Mangifera indica\",\"probability\":0.9776000000000000245137243837234564125537872314453125,\"confirmed\":false,\"plant_details\":{\"language\":\"en\",\"scientific_name\":\"Mangifera indica\",\"structured_name\":{\"genus\":\"mangifera\",\"species\":\"indica\"}}}', '2025-07-22 17:15:55'),
(2, NULL, 'uploads/plant_687fc79a7e9b0.jpeg', 'Carica papaya', 'Carica papaya', '{\"id\":105002686900,\"plant_name\":\"Carica papaya\",\"probability\":0.9899999999999999911182158029987476766109466552734375,\"confirmed\":false,\"plant_details\":{\"language\":\"en\",\"scientific_name\":\"Carica papaya\",\"structured_name\":{\"genus\":\"carica\",\"species\":\"papaya\"}}}', '2025-07-22 17:17:16'),
(3, NULL, 'uploads/plant_687fc85cce678.jpeg', 'Carica papaya', 'Carica papaya', '{\"id\":105002716300,\"plant_name\":\"Carica papaya\",\"probability\":0.9899999999999999911182158029987476766109466552734375,\"confirmed\":false,\"plant_details\":{\"language\":\"en\",\"scientific_name\":\"Carica papaya\",\"structured_name\":{\"genus\":\"carica\",\"species\":\"papaya\"}}}', '2025-07-22 17:20:43'),
(4, NULL, 'uploads/plant_687fc889eba00.jpeg', 'Mangifera indica', 'Mangifera indica', '{\"id\":105002719900,\"plant_name\":\"Mangifera indica\",\"probability\":0.9776000000000000245137243837234564125537872314453125,\"confirmed\":false,\"plant_details\":{\"language\":\"en\",\"scientific_name\":\"Mangifera indica\",\"structured_name\":{\"genus\":\"mangifera\",\"species\":\"indica\"}}}', '2025-07-22 17:21:16'),
(5, NULL, 'uploads/plant_687fc90621437.jpeg', 'Carica papaya', 'Carica papaya', '{\"id\":105002737900,\"plant_name\":\"Carica papaya\",\"probability\":0.9899999999999999911182158029987476766109466552734375,\"confirmed\":false,\"plant_details\":{\"language\":\"en\",\"scientific_name\":\"Carica papaya\",\"structured_name\":{\"genus\":\"carica\",\"species\":\"papaya\"}}}', '2025-07-22 17:23:21'),
(6, NULL, 'uploads/plant_6884ec5832281.jpeg', 'Carica papaya', 'Carica papaya', '{\"id\":105030717400,\"plant_name\":\"Carica papaya\",\"probability\":0.9899999999999999911182158029987476766109466552734375,\"confirmed\":false,\"plant_details\":{\"language\":\"en\",\"scientific_name\":\"Carica papaya\",\"structured_name\":{\"genus\":\"carica\",\"species\":\"papaya\"}}}', '2025-07-26 14:55:22');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `created_at`, `profile_photo`) VALUES
(1, 'Hannah', 'delalirock5@gmail.com', '$2y$10$hl0wAWHwfFwohqMHNWe4TOSD..OtpdSEbTs5LMb6Kihjm746OOPXq', '2025-07-24 19:34:25', NULL),
(2, 'nora', 'no@gmail.com', '$2y$10$czOY1jTZSW/DkjXTgLqX.OQHSIo7aUohr1eLD2txpOleSyg.l7Mhu', '2025-07-26 14:50:02', 'uploads/profile_2_6884ec432041e.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `identification_id` (`identification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `identifications`
--
ALTER TABLE `identifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `identifications`
--
ALTER TABLE `identifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`identification_id`) REFERENCES `identifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `identifications`
--
ALTER TABLE `identifications`
  ADD CONSTRAINT `identifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
