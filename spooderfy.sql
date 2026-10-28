-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mer. 28 oct. 2026 à 10:22
-- Version du serveur : 8.4.3
-- Version de PHP : 8.4.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `spooderfy`
--

-- --------------------------------------------------------

--
-- Structure de la table `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `creator`
--

CREATE TABLE `creator` (
  `id` int NOT NULL,
  `name` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pivot_creator`
--

CREATE TABLE `pivot_creator` (
  `id` int NOT NULL,
  `song_id` int NOT NULL,
  `creator_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pivot_playlist`
--

CREATE TABLE `pivot_playlist` (
  `id` int NOT NULL,
  `playlist_id` int NOT NULL,
  `song_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `playlist`
--

CREATE TABLE `playlist` (
  `id` int NOT NULL,
  `name` varchar(250) NOT NULL,
  `user_id` int NOT NULL,
  `song_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `song`
--

CREATE TABLE `song` (
  `id` int NOT NULL,
  `name` varchar(250) NOT NULL,
  `creator_id` int NOT NULL DEFAULT '0',
  `audio` varchar(400) NOT NULL,
  `coverage` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `song`
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
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`) VALUES
(1, 'hugo'),
(2, 'joe');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `creator`
--
ALTER TABLE `creator`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `pivot_creator`
--
ALTER TABLE `pivot_creator`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `pivot_playlist`
--
ALTER TABLE `pivot_playlist`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `playlist`
--
ALTER TABLE `playlist`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `song`
--
ALTER TABLE `song`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `creator`
--
ALTER TABLE `creator`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `pivot_creator`
--
ALTER TABLE `pivot_creator`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `pivot_playlist`
--
ALTER TABLE `pivot_playlist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `playlist`
--
ALTER TABLE `playlist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `song`
--
ALTER TABLE `song`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
