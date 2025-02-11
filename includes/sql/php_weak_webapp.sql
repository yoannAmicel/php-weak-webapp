-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : lun. 10 fév. 2025 à 07:19
-- Version du serveur : 10.6.18-MariaDB-0ubuntu0.22.04.1
-- Version de PHP : 8.3.16

-- Création de la base de données
--
CREATE DATABASE IF NOT EXISTS `php_weak_webapp` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `php_weak_webapp`;


SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+02:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `php_weak_webapp`
--

-- --------------------------------------------------------

--
-- Structure de la table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `admin_id` tinyint(3) UNSIGNED NOT NULL,
  `user_id` tinyint(3) UNSIGNED NOT NULL,
  `action` varchar(50) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `contact_attempts`
--

CREATE TABLE `contact_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `published_date` date DEFAULT NULL,
  `comments_count` int(11) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `news_comment`
--

CREATE TABLE `news_comment` (
  `id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT '/uploads/account.png',
  `userID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `software`
--

CREATE TABLE `software` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `more_info_url` varchar(255) DEFAULT NULL,
  `title_color` varchar(7) NOT NULL DEFAULT '#000000'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `profile_picture` varchar(255) NOT NULL DEFAULT '/img/users/everyone.png',
  `role` enum('user','admin') DEFAULT 'user',
  `is_blocked` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





-- ----------------------------------------------------------------

--
-- Déclencheurs `news_comment`
--
DELIMITER $$
CREATE TRIGGER `update_comments_count_after_delete` AFTER DELETE ON `news_comment` FOR EACH ROW BEGIN
    UPDATE news
    SET comments_count = comments_count - 1
    WHERE id = OLD.news_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_comments_count_after_insert` AFTER INSERT ON `news_comment` FOR EACH ROW BEGIN
    UPDATE news
    SET comments_count = comments_count + 1
    WHERE id = NEW.news_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `user_id` (`user_id`);


--
-- Index pour la table `contact_attempts`
--
ALTER TABLE `contact_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `news_comment`
--
ALTER TABLE `news_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`);

--
-- Index pour la table `software`
--
ALTER TABLE `software`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

-- --------------------------------------------------------

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_admin_id` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `news_comment`
--
ALTER TABLE `news_comment`
  ADD CONSTRAINT `news_comment_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;
COMMIT;





-- ----------------------------------------------------------------

--
-- Déchargement des données de la table `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `image_url`, `source`, `published_date`, `comments_count`, `status`) VALUES
(1, '#WORKHUB at the International Football Summit', 'During the International Football Summit, #WORKHUB demonstrated its ability to streamline operations and manage schedules for large-scale events. Teams used WORKHUB to coordinate match schedules, optimize staff assignments, and ensure seamless communication across departments. The success of this event highlighted the platform’s capability to adapt to fast-paced, high-pressure environments in the sports industry.', 'img/compressed/sport-one.jpg', 'WORKHUB', '2024-12-30', 2, 'approved'),
(2, '#SHAREPRO at the Grand Slam Tennis Event', '#SHAREPRO successfully supported organizers at the Grand Slam Tennis Event, streamlining collaboration between teams and ensuring efficient communication across all departments. The event highlighted how innovative tools like SHAREPRO can enhance coordination in high-pressure environments, delivering exceptional results.', 'img/compressed/sport-two.jpg', 'SHAREPRO', '2024-05-31', 1, 'approved'),
(3, '#PLANIT at the Mountain Biking Championship', 'During the Mountain Biking Championship, #PLANIT proved invaluable in coordinating event schedules, managing logistics, and ensuring seamless collaboration between teams. This high-energy event demonstrated how #PLANIT helps streamline operations for sports events, enhancing the experience for participants and spectators alike.', 'img/compressed/sport-three.jpg', 'PLANIT', '2024-12-30', 1, 'approved'),
(4, 'Breaking: New Technology Announced', 'TechCorp unveiled a groundbreaking innovation that...', 'img/news/default.jpg', 'TECHCORP', '2024-08-28', 6, 'approved'),
(5, 'Sports Update: Championship Results', 'The final results of the international championship...', 'img/news/default.jpg', 'SPORTSNET', '2024-03-18', 4, 'approved'),
(6, 'Local News: City Renovation Plan', 'The city council approved a major renovation plan...', 'img/news/default.jpg', 'CITYNEWS', '2025-01-01', 8, 'pending'),
(7, 'Environment: New Conservation Efforts', 'Efforts to conserve endangered species have been...', 'img/news/default.jpg', 'GREENWORLD', '2024-04-20', 2, 'approved'),
(8, 'Finance: Market Trends for 2025', 'An analysis of market trends and predictions for...', 'img/news/default.jpg', 'FINANCEPRO', '2024-05-31', 0, 'pending');

-- --------------------------------------------------------

--
-- Déchargement des données de la table `news_comment`
--

INSERT INTO `news_comment` (`id`, `news_id`, `username`, `comment`, `created_at`, `profile_picture`, `userID`) VALUES
(1, 1, 'John Doe', 'Great news! Thanks for sharing.', '2025-02-06 10:06:50', '/uploads/account.png', NULL),
(2, 1, 'Jane Smith', 'Very informative.', '2025-02-06 10:06:50', '/uploads/account.png', NULL),
(3, 2, 'Alice Johnson', 'Loved reading this!', '2025-02-06 10:06:50', '/uploads/account.png', NULL),
(4, 3, 'Bob Brown', 'Keep up the great work!', '2025-02-06 10:06:50', '/uploads/account.png', NULL),
(6, 4, 'Michael Scott', 'This is a game-changer!', '2025-05-01 10:15:00', '/uploads/account.png', NULL),
(7, 4, 'Pam Beesly', 'I can’t wait to learn more about this.', '2025-05-01 10:30:00', '/uploads/account.png', NULL),
(8, 4, 'Jim Halpert', 'Amazing innovation!', '2025-05-01 10:45:00', '/uploads/account.png', NULL),
(9, 5, 'Angela Martin', 'Great match results!', '2025-05-03 07:00:00', '/uploads/account.png', NULL),
(10, 5, 'Dwight Schrute', 'This is why sports matter.', '2025-05-03 07:30:00', '/uploads/account.png', NULL),
(11, 6, 'Kevin Malone', 'The renovation plan looks promising.', '2025-05-05 08:00:00', '/uploads/account.png', NULL),
(12, 6, 'Oscar Martinez', 'Finally some improvements!', '2025-05-05 08:30:00', '/uploads/account.png', NULL),
(13, 6, 'Stanley Hudson', 'Let’s see if this actually happens.', '2025-05-05 09:00:00', '/uploads/account.png', NULL),
(14, 6, 'Ryan Howard', 'Interesting update.', '2025-05-05 09:15:00', '/uploads/account.png', NULL),
(15, 7, 'Toby Flenderson', 'Conservation efforts are so important.', '2025-05-07 12:00:00', '/uploads/account.png', NULL);

--
-- Déchargement des données de la table `software`
--

INSERT INTO `software` (`id`, `name`, `description`, `more_info_url`, `title_color`) VALUES
(1, '#PLANIT', '#PLANIT is an innovative planning and resource management tool designed for creative projects. It features advanced scheduling, team collaboration, and financial tracking to streamline your workflow.', '#', '#1D4ED8'),
(2, '#WORKHUB', '#WORKHUB is a personalized platform enabling employees to manage their schedules, track hours, and request time off seamlessly. Designed to enhance workplace efficiency.', '#', '#EA580C'),
(3, '#SHAREPRO', '#SHAREPRO is a collaborative sharing platform that empowers teams to exchange critical information, organize projects, and drive innovation effortlessly.', '#', '#16A34A'),
(4, '#ASSISTO', '#ASSISTO is your digital assistant for managing tasks, tracking assets, and generating reports. Simplify your operations with this versatile and user-friendly tool.', '#', '#1E3A8A');

-- --------------------------------------------------------

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `reset_token`, `reset_token_expiry`, `profile_picture`, `role`, `is_blocked`) VALUES
(4, 'admin', 'admin@gmail.com', '$2y$10$kSz81QAmA3lpjUZQ/h/uEOevFf3GoLqUculOt7zxWORvceSolQu2C', NULL, NULL, '/img/users/everyone.png', 'admin', 0),
(5, 'John Doe', 'john.doe@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'user', 0),
(6, 'Jane Smith', 'jane.smith@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'user', 0),
(7, 'Robert Brown', 'robert.brown@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'admin', 0),
(8, 'Emily Johnson', 'emily.johnson@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'user', 1),
(9, 'Michael Lee', 'michael.lee@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'user', 0),
(10, 'Sarah Wilson', 'sarah.wilson@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'admin', 0),
(11, 'David Martinez', 'david.martinez@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'user', 1),
(12, 'Sophia Garcia', 'sophia.garcia@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'user', 0),
(13, 'Daniel White', 'daniel.white@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'admin', 0),
(14, 'Olivia Harris', 'olivia.harris@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'user', 1),
(15, 'James Clark', 'james.clark@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'user', 0),
(16, 'Isabella Lewis', 'isabella.lewis@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'admin', 0),
(17, 'William Walker', 'william.walker@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'user', 0),
(18, 'Mia Young', 'mia.young@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'user', 1),
(19, 'Benjamin Hall', 'benjamin.hall@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'admin', 0),
(20, 'Ava Allen', 'ava.allen@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'user', 0),
(21, 'Ethan King', 'ethan.king@gmail.com', '$2y$10$eXaMpLeHaSh1234567890abcdefg', NULL, NULL, '/img/users/everyone.png', 'user', 0);





-- ----------------------------------------------------------------

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `contact_attempts`
--
ALTER TABLE `contact_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT pour la table `news_comment`
--
ALTER TABLE `news_comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT pour la table `software`
--
ALTER TABLE `software`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;