-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20261014.d250d31a41
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 30, 2026 at 09:49 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spooderfy`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--
CREATE TABLE `comments` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `text` text NOT NULL,
  `song_id` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `text`, `song_id`) VALUES
(1, 1, 'clairement la meilleure version des 4 itérations de cette musique', 5),
(2, 3, 'ah', 2),
(3, 4, 'oui', 5),
(4, 4, 'test', 4),
(5, 4, 'la version rain possède moins d\'instruments et c\'est mieux comme ca', 5),
(6, 5, 'j\'approuve tout ce que j\'ai écrit sous d\'autres noms', 5),
(7, 4, 'Musique sympa', 6);

-- --------------------------------------------------------

--
-- Table structure for table `creator`
--
CREATE TABLE `creator` (
  `id` int NOT NULL,
  `name` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pivot_creator`
--
CREATE TABLE `pivot_creator` (
  `id` int NOT NULL,
  `song_id` int NOT NULL,
  `creator_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pivot_playlist`
--
CREATE TABLE `pivot_playlist` (
  `playlist_id` int NOT NULL,
  `song_id` int NOT NULL,
  `position` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pivot_playlist`
--

INSERT INTO `pivot_playlist` (`playlist_id`, `song_id`, `position`) VALUES
(1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `playlist`
--
CREATE TABLE `playlist` (
  `id` int NOT NULL,
  `name` varchar(250) NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `playlist`
--

INSERT INTO `playlist` (`id`, `name`, `user_id`) VALUES
(1, 'Deltarune', 4);

-- --------------------------------------------------------

--
-- Table structure for table `song`
--
CREATE TABLE `song` (
  `id` int NOT NULL,
  `name` varchar(250) NOT NULL,
  `creator_id` int NOT NULL DEFAULT '0',
  `audio` varchar(400) NOT NULL,
  `coverage` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `song`
--

INSERT INTO `song` (`id`, `name`, `creator_id`, `audio`, `coverage`) VALUES
(1, '34. Flower Man (DELTARUNE Chapter 5 Soundtrack) - Toby Fox', 0, 'song/all_song/34. Flower Man (DELTARUNE Chapter 5 Soundtrack) - Toby Fox.mp3', 'song/coverage/Deltarune song coverage.jpg'),
(2, '58. Hammer of Justice (DELTARUNE Chapter 3+4 Soundtrack) - Toby Fox', 0, 'song/all_song/58. Hammer of Justice (DELTARUNE Chapter 3+4 Soundtrack) - Toby Fox.mp3', 'song/coverage/Deltarune song coverage.jpg'),
(3, 'Adventures End - Mario & Luigi Dream Team Bros', 0, 'song/all_song/Adventures End - Mario & Luigi Dream Team Bros.mp3', 'song/coverage/Mario and luigi dream team bros song coverage.jpg'),
(4, 'Amusement Park (NieRAutomata Original Soundtrack)', 0, 'song/all_song/Amusement Park (NieRAutomata Original Soundtrack).mp3', 'song/coverage/Nier Automata song coverage.jpg'),
(5, 'Beneath the Mask - rain - Persona 5', 0, 'song/all_song/Beneath the Mask - rain - Persona 5.mp3', 'song/coverage/Persona 5 song coverage.jpg'),
(6, 'In the final - Mario & Luigi Bowsers Inside Story.mp3', 0, 'song/all_song/In the final - Mario & Luigi Bowsers Inside Story.mp3', 'song/coverage/Mario and luigi bowser inside story song coverage.jpg'),
(7, 'Revenge - Everhood OST 60', 0, 'song/all_song/Revenge - Everhood OST 60.mp3', 'song/coverage/Everhood song coverage.jpg'),
(8, 'Shade Dance Battle - Everhood 2', 0, 'song/all_song/Shade Dance Battle - Everhood 2.mp3', 'song/coverage/Everhood song coverage.jpg'),
(9, 'The Final Battle - Everhood OST 77', 0, 'song/all_song/The Final Battle - Everhood OST 77.mp3', 'song/coverage/Everhood song coverage.jpg'),
(10, 'Weight of the World English Version - J’Nique Nicole (NieRAutomata Original Soundtrack)', 0, 'song/all_song/Weight of the World English Version - J’Nique Nicole (NieRAutomata Original Soundtrack).mp3', 'song/coverage/Nier Automata song coverage.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--
CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`) VALUES
(1, 'hugo'),
(2, 'joe'),
(3, 'Doll'),
(4, 'Dummy'),
(5, 'Diel');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_comments_song` (`song_id`);

--
-- Indexes for table `creator`
--
ALTER TABLE `creator`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pivot_creator`
--
ALTER TABLE `pivot_creator`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `playlist`
--
ALTER TABLE `playlist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `song`
--
ALTER TABLE `song`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `creator`
--
ALTER TABLE `creator`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pivot_creator`
--
ALTER TABLE `pivot_creator`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `playlist`
--
ALTER TABLE `playlist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `song`
--
ALTER TABLE `song`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_song` FOREIGN KEY (`song_id`) REFERENCES `song` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
