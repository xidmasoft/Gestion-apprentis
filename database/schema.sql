CREATE DATABASE IF NOT EXISTS gestion_apprenants CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_apprenants;

-- Table : filieres
CREATE TABLE IF NOT EXISTS `filieres` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `actif` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table : niveaux
CREATE TABLE IF NOT EXISTS `niveaux` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `actif` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table : annees_formation
CREATE TABLE IF NOT EXISTS `annees_formation` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `libelle` VARCHAR(20) NOT NULL,
  `date_debut` DATE,
  `date_fin` DATE,
  `actif` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table : apprenants
CREATE TABLE IF NOT EXISTS `apprenants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `matricule` VARCHAR(50) NOT NULL UNIQUE,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `date_naissance` DATE NOT NULL,
  `sexe` ENUM('M', 'F', 'Autre') NOT NULL,
  `telephone` VARCHAR(20),
  `adresse` TEXT,
  `filiere_id` INT NOT NULL,
  `niveau_id` INT NOT NULL,
  `annee_id` INT NOT NULL,
  `statut` VARCHAR(50) NOT NULL DEFAULT 'Inscrit',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT `fk_apprenant_filiere` FOREIGN KEY (`filiere_id`) REFERENCES `filieres`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_apprenant_niveau` FOREIGN KEY (`niveau_id`) REFERENCES `niveaux`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_apprenant_annee` FOREIGN KEY (`annee_id`) REFERENCES `annees_formation`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Index additionnels pour la recherche
CREATE INDEX `idx_apprenant_nom` ON `apprenants`(`nom`);
CREATE INDEX `idx_apprenant_matricule` ON `apprenants`(`matricule`);

-- Insertion de données initiales
INSERT INTO `filieres` (`nom`, `description`) VALUES
('Informatique', 'Développement web et mobile'),
('Réseaux', 'Administration systèmes et réseaux'),
('Gestion', 'Gestion et comptabilité');

INSERT INTO `niveaux` (`nom`, `description`) VALUES
('CAP', 'Certificat d''Aptitude Professionnelle'),
('BEP', 'Brevet d''Études Professionnelles'),
('Attestation', 'Attestation de formation');

INSERT INTO `annees_formation` (`libelle`, `date_debut`, `date_fin`) VALUES
('2023-2024', '2023-09-01', '2024-06-30'),
('2024-2025', '2024-09-01', '2025-06-30');

INSERT INTO `apprenants` (`matricule`, `nom`, `prenom`, `date_naissance`, `sexe`, `telephone`, `adresse`, `filiere_id`, `niveau_id`, `annee_id`, `statut`) VALUES
('MAT2024-001', 'Dupont', 'Jean', '2005-04-12', 'M', '0123456789', '12 rue des Fleurs', 1, 1, 2, 'Inscrit'),
('MAT2024-002', 'Martin', 'Sophie', '2006-08-25', 'F', '0987654321', '34 avenue du Soleil', 2, 2, 2, 'En cours');
