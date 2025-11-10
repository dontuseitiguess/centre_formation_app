-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : lun. 10 nov. 2025 à 09:23
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `centre_formation`
--

-- --------------------------------------------------------

--
-- Structure de la table `affectation`
--

CREATE TABLE `affectation` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `formateur_id` int(11) NOT NULL,
  `role` enum('FORMATEUR_PRINCIPAL','INTERVENANT') NOT NULL DEFAULT 'FORMATEUR_PRINCIPAL'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `affectation`
--

INSERT INTO `affectation` (`id`, `session_id`, `formateur_id`, `role`) VALUES
(1, 1, 1, 'FORMATEUR_PRINCIPAL'),
(2, 2, 2, 'FORMATEUR_PRINCIPAL');

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

CREATE TABLE `etudiant` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `etudiant`
--

INSERT INTO `etudiant` (`id`, `nom`, `prenom`, `email`, `telephone`) VALUES
(1, 'Leroy', 'Emma', 'emma.leroy@example.com', '0700000001'),
(2, 'Moreau', 'Noah', 'noah.moreau@example.com', '0700000002'),
(3, 'Bernard', 'Lina', 'lina.bernard@example.com', '0700000003');

-- --------------------------------------------------------

--
-- Structure de la table `formateur`
--

CREATE TABLE `formateur` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `formateur`
--

INSERT INTO `formateur` (`id`, `nom`, `prenom`, `email`, `telephone`) VALUES
(1, 'Martin', 'Sophie', 'sophie.martin@centre.com', '0600000001'),
(2, 'Dupont', 'Lucas', 'lucas.dupont@centre.com', '0600000002');

-- --------------------------------------------------------

--
-- Structure de la table `formation`
--

CREATE TABLE `formation` (
  `id` int(11) NOT NULL,
  `titre` varchar(150) NOT NULL,
  `domaine` varchar(100) NOT NULL,
  `niveau` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `formation`
--

INSERT INTO `formation` (`id`, `titre`, `domaine`, `niveau`, `description`, `created_at`) VALUES
(1, 'Excel Débutant', 'Bureautique', 'Débutant', 'Maîtriser les bases d’Excel : cellules, formules simples, mise en forme.', '2025-11-03 13:14:52'),
(2, 'Initiation à Python', 'Développement', 'Débutant', 'Découvrir la programmation avec Python pour l’analyse de données.', '2025-11-03 13:14:52');

-- --------------------------------------------------------

--
-- Structure de la table `inscription`
--

CREATE TABLE `inscription` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `etudiant_id` int(11) NOT NULL,
  `statut` enum('PREINSCRIT','CONFIRME','ANNULE') NOT NULL DEFAULT 'PREINSCRIT',
  `date_inscription` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `inscription`
--

INSERT INTO `inscription` (`id`, `session_id`, `etudiant_id`, `statut`, `date_inscription`) VALUES
(1, 1, 1, 'CONFIRME', '2025-11-03 13:14:52'),
(2, 1, 2, 'PREINSCRIT', '2025-11-03 13:14:52'),
(3, 2, 3, 'PREINSCRIT', '2025-11-03 13:14:52');

--
-- Déclencheurs `inscription`
--
DELIMITER $$
CREATE TRIGGER `trg_inscription_check_capacity` BEFORE INSERT ON `inscription` FOR EACH ROW BEGIN
    DECLARE nb_inscrits INT;
    DECLARE max_cap INT;
    SELECT COUNT(*) INTO nb_inscrits FROM inscription WHERE session_id = NEW.session_id AND statut <> 'ANNULE';
    SELECT capacite INTO max_cap FROM session WHERE id = NEW.session_id;
    IF nb_inscrits >= max_cap THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Capacité maximale atteinte pour cette session';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `presence`
--

CREATE TABLE `presence` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `etudiant_id` int(11) NOT NULL,
  `present` tinyint(1) NOT NULL DEFAULT 0,
  `remarque` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `presence`
--

INSERT INTO `presence` (`id`, `session_id`, `etudiant_id`, `present`, `remarque`) VALUES
(1, 1, 1, 1, 'Arrivée à l’heure'),
(2, 1, 2, 0, 'Absent jour 1');

-- --------------------------------------------------------

--
-- Structure de la table `session`
--

CREATE TABLE `session` (
  `id` int(11) NOT NULL,
  `formation_id` int(11) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `salle` varchar(100) NOT NULL,
  `capacite` int(11) NOT NULL CHECK (`capacite` >= 1),
  `statut` enum('PLANIFIEE','OUVERTE','CLOTUREE','ANNULEE') NOT NULL DEFAULT 'PLANIFIEE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `session`
--

INSERT INTO `session` (`id`, `formation_id`, `date_debut`, `date_fin`, `salle`, `capacite`, `statut`) VALUES
(1, 1, '2025-11-20', '2025-11-21', 'Salle A', 2, 'OUVERTE'),
(2, 2, '2025-12-05', '2025-12-06', 'Salle B', 3, 'PLANIFIEE');

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_places_disponibles`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_places_disponibles` (
`session_id` int(11)
,`capacite` int(11)
,`inscrits` decimal(22,0)
,`places_disponibles` decimal(23,0)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_session_occupation`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_session_occupation` (
`session_id` int(11)
,`formation` varchar(150)
,`date_debut` date
,`date_fin` date
,`salle` varchar(100)
,`capacite` int(11)
,`inscrits` decimal(22,0)
,`taux_occupation` decimal(27,1)
);

-- --------------------------------------------------------

--
-- Structure de la vue `v_places_disponibles`
--
DROP TABLE IF EXISTS `v_places_disponibles`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_places_disponibles`  AS SELECT `s`.`id` AS `session_id`, `s`.`capacite` AS `capacite`, coalesce(sum(case when `i`.`statut` <> 'ANNULE' then 1 else 0 end),0) AS `inscrits`, `s`.`capacite`- coalesce(sum(case when `i`.`statut` <> 'ANNULE' then 1 else 0 end),0) AS `places_disponibles` FROM (`session` `s` left join `inscription` `i` on(`i`.`session_id` = `s`.`id`)) GROUP BY `s`.`id` ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_session_occupation`
--
DROP TABLE IF EXISTS `v_session_occupation`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_session_occupation`  AS SELECT `s`.`id` AS `session_id`, `f`.`titre` AS `formation`, `s`.`date_debut` AS `date_debut`, `s`.`date_fin` AS `date_fin`, `s`.`salle` AS `salle`, `s`.`capacite` AS `capacite`, sum(case when `i`.`statut` <> 'ANNULE' then 1 else 0 end) AS `inscrits`, round(100 * sum(case when `i`.`statut` <> 'ANNULE' then 1 else 0 end) / `s`.`capacite`,1) AS `taux_occupation` FROM ((`session` `s` join `formation` `f` on(`f`.`id` = `s`.`formation_id`)) left join `inscription` `i` on(`i`.`session_id` = `s`.`id`)) GROUP BY `s`.`id` ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `affectation`
--
ALTER TABLE `affectation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_affectation_unique` (`session_id`,`formateur_id`),
  ADD KEY `fk_affectation_formateur` (`formateur_id`);

--
-- Index pour la table `etudiant`
--
ALTER TABLE `etudiant`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_etudiant_email` (`email`);

--
-- Index pour la table `formateur`
--
ALTER TABLE `formateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_formateur_email` (`email`);

--
-- Index pour la table `formation`
--
ALTER TABLE `formation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_formation_titre` (`titre`);

--
-- Index pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_inscription_unique` (`session_id`,`etudiant_id`),
  ADD KEY `idx_inscription_session` (`session_id`),
  ADD KEY `idx_inscription_etudiant` (`etudiant_id`);

--
-- Index pour la table `presence`
--
ALTER TABLE `presence`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_presence_unique` (`session_id`,`etudiant_id`),
  ADD KEY `idx_presence_session` (`session_id`),
  ADD KEY `idx_presence_etudiant` (`etudiant_id`);

--
-- Index pour la table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session_dates` (`date_debut`,`date_fin`),
  ADD KEY `idx_session_formation` (`formation_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `affectation`
--
ALTER TABLE `affectation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `etudiant`
--
ALTER TABLE `etudiant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `formateur`
--
ALTER TABLE `formateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `formation`
--
ALTER TABLE `formation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `inscription`
--
ALTER TABLE `inscription`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `presence`
--
ALTER TABLE `presence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `session`
--
ALTER TABLE `session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `affectation`
--
ALTER TABLE `affectation`
  ADD CONSTRAINT `fk_affectation_formateur` FOREIGN KEY (`formateur_id`) REFERENCES `formateur` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_affectation_session` FOREIGN KEY (`session_id`) REFERENCES `session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD CONSTRAINT `fk_inscription_etudiant` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscription_session` FOREIGN KEY (`session_id`) REFERENCES `session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `presence`
--
ALTER TABLE `presence`
  ADD CONSTRAINT `fk_presence_etudiant` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_presence_session` FOREIGN KEY (`session_id`) REFERENCES `session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `session`
--
ALTER TABLE `session`
  ADD CONSTRAINT `fk_session_formation` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
