-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 17 mars 2026 à 17:20
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `vite_gourmand`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `valide` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`id`, `user_id`, `note`, `commentaire`, `valide`) VALUES
(1, 1, 5, 'Parfait ! ', 0);

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `nb_personnes` int(11) DEFAULT NULL,
  `date_livraison` date DEFAULT NULL,
  `heure_livraison` time DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `prix_total` decimal(10,2) DEFAULT NULL,
  `date_commande` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`id`, `user_id`, `menu_id`, `nb_personnes`, `date_livraison`, `heure_livraison`, `adresse`, `statut`, `prix_total`, `date_commande`) VALUES
(10, 1, 4, 6, '2026-03-19', '18:00:00', 'rue 22', 'en attente', 480.00, '2026-03-17 04:05:09'),
(11, 1, 2, NULL, NULL, NULL, NULL, 'en attente', NULL, '2026-03-17 04:09:54');

-- --------------------------------------------------------

--
-- Structure de la table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `theme` varchar(100) DEFAULT NULL,
  `regime` varchar(100) DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `min_personnes` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `menus`
--

INSERT INTO `menus` (`id`, `titre`, `description`, `theme`, `regime`, `prix`, `min_personnes`, `stock`) VALUES
(1, 'Menu Noël', 'Menu festif pour les fêtes de fin d’année avec entrée, plat et dessert.', 'Noel', 'classique', 120.00, 10, 5),
(2, 'Menu Vegan', 'Menu végétal composé uniquement de produits d’origine végétale.', 'Vegan', 'vegan', 90.00, 8, 10),
(3, 'Menu Mariage', 'Menu premium spécialement conçu pour les mariages.', 'Mariage', 'classique', 150.00, 20, 3),
(4, 'Menu Anniversaire', 'Menu convivial parfait pour célébrer un anniversaire.', 'Anniversaire', 'classique', 80.00, 6, 12),
(5, 'Menu Pâques', 'Menu spécial Pâques avec des produits de saison.', 'Paques', 'classique', 110.00, 10, 6);

-- --------------------------------------------------------

--
-- Structure de la table `menu_plats`
--

CREATE TABLE `menu_plats` (
  `menu_id` int(11) NOT NULL,
  `plat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `plats`
--

CREATE TABLE `plats` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `type` enum('entree','plat','dessert') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `gsm` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('utilisateur','employe','admin') DEFAULT 'utilisateur',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `gsm`, `adresse`, `password`, `role`, `created_at`) VALUES
(1, 'DOE', 'JOHN', 'test@test.com', '123456789', '', '$2y$10$fN.FbxWpjl/KyVL/bPefY.rI09VP1GUrSU6p3xDxl4mTUby9l8KWS', 'utilisateur', '2026-03-06 17:12:19'),
(2, 'Test', 'Admin', 'testadmin@test.com', '123456789', '', '$2y$10$0.qcUaF7bdmBeqm7eRC61O.wzGu0Zhy7Z/Oret1BeFGrDvTlknRWS', 'admin', '2026-03-13 12:04:09');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `menu_plats`
--
ALTER TABLE `menu_plats`
  ADD PRIMARY KEY (`menu_id`,`plat_id`);

--
-- Index pour la table `plats`
--
ALTER TABLE `plats`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `plats`
--
ALTER TABLE `plats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
