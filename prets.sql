-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 01 août 2025 à 09:24
-- Version du serveur : 8.0.31
-- Version de PHP : 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gmah`
--

-- --------------------------------------------------------

--
-- Structure de la table `prets`
--

DROP TABLE IF EXISTS `prets`;
CREATE TABLE IF NOT EXISTS `prets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article` int NOT NULL,
  `emprunteur` varchar(50) NOT NULL,
  `date_debut` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_fin` datetime NOT NULL,
  `etat` int DEFAULT NULL,
  `message` text,
  `montant_demande` int DEFAULT NULL,
  `photos_dommage` longblob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `prets`
--

INSERT INTO `prets` (`id`, `article`, `emprunteur`, `date_debut`, `date_fin`, `etat`, `message`, `montant_demande`, `photos_dommage`) VALUES
(1, 1, 'Efrat_Freoua', '2025-08-01 09:07:08', '2026-01-27 00:00:00', 1, NULL, 100, 0x5b22646f6d6d6167655f315f363838633837376635383634635f7461626c65322e77656270222c22646f6d6d6167655f315f363838633837376635383838365f7461626c652e77656270225d),
(2, 1, 'Efrat_Freoua', '2025-08-01 09:17:57', '2025-08-23 00:00:00', 1, NULL, 0, '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
