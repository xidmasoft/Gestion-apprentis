# Document de Conception Technique : GESTION APPRENANTS (V1)

## 1. Architecture Générale
L'application repose sur une architecture simple de type Client/Serveur, sans framework :
- **Client (Frontend)** : Interface utilisateur (SPA simplifiée ou vues multiples) en HTML5, CSS3. L'interaction et le dynamisme sont assurés par jQuery, qui communique avec le serveur via des requêtes AJAX (format JSON).
- **Serveur (Backend)** : Scripts PHP 8+ agissant comme une API RESTful simplifiée. Ils traitent les requêtes JSON, valident les données, interagissent avec la base de données et retournent des réponses au format JSON.
- **Base de Données** : MySQL 8+ (moteur InnoDB pour les transactions et l'intégrité référentielle, encodage `utf8mb4`). L'accès aux données se fait exclusivement via PDO.

## 2. Modèle Relationnel
L'entité centrale est `apprenants`. Elle est liée par des relations (1,n) aux référentiels :
- Une **filiere** contient de 0 à n apprenants. Un apprenant appartient à exactement 1 filière.
- Un **niveau** est attribué à de 0 à n apprenants. Un apprenant a exactement 1 niveau.
- Une **annee_formation** concerne de 0 à n apprenants. Un apprenant est inscrit pour exactement 1 année de formation (dans le contexte V1, l'historique d'inscription n'est pas une table de liaison, l'apprenant est lié à son année en cours).

## 3. Structure SQL (Schéma initial)
```sql
-- Création de la base
CREATE DATABASE IF NOT EXISTS gestion_apprenants CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_apprenants;

-- Table : filieres
CREATE TABLE `filieres` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `actif` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table : niveaux
CREATE TABLE `niveaux` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `actif` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table : annees_formation
CREATE TABLE `annees_formation` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `libelle` VARCHAR(20) NOT NULL, -- Ex: "2026-2027"
  `date_debut` DATE,
  `date_fin` DATE,
  `actif` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table : apprenants
CREATE TABLE `apprenants` (
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
  `statut` VARCHAR(50) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  -- Clés étrangères
  CONSTRAINT `fk_apprenant_filiere` FOREIGN KEY (`filiere_id`) REFERENCES `filieres`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_apprenant_niveau` FOREIGN KEY (`niveau_id`) REFERENCES `niveaux`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_apprenant_annee` FOREIGN KEY (`annee_id`) REFERENCES `annees_formation`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Index additionnels pour la recherche
CREATE INDEX `idx_apprenant_nom` ON `apprenants`(`nom`);
CREATE INDEX `idx_apprenant_matricule` ON `apprenants`(`matricule`);
```

## 4. Architecture des Fichiers
Proposition d'organisation améliorée :
```
/
├── config/
│   ├── database.php       # Connexion PDO
│   └── config.php         # Variables globales (URL, constantes)
├── api/                   # Endpoints backend (retournent du JSON)
│   ├── apprenants/
│   │   ├── create.php
│   │   ├── read.php       # Liste, recherche, filtres
│   │   ├── read_single.php# Détails
│   │   ├── update.php
│   │   └── delete.php
│   ├── referentiels/      # Pour filières, niveaux, années
│   │   └── read.php       # Retourne les listes pour les `<select>`
├── public/                # Accessible via le serveur web
│   ├── index.html         # Point d'entrée principal (SPA)
│   ├── css/
│   │   └── style.css      # Styles personnalisés
│   └── js/
│       ├── app.js         # Logique principale jQuery (événements, UI)
│       └── api.js         # Fonctions d'appel AJAX (séparation des requêtes)
├── database/
│   └── schema.sql         # Script SQL de création (Point 3)
└── README.md
```

## 5. Endpoints API
Toutes les requêtes (sauf GET) s'attendent à un `Content-Type: application/json`.
- `GET /api/apprenants/read.php` : Liste des apprenants. Paramètres optionnels dans l'URL pour filtres et recherche (ex: `?search=Dupont&filiere_id=2`).
- `GET /api/apprenants/read_single.php?id={id}` : Détails d'un apprenant.
- `POST /api/apprenants/create.php` : Création (Body JSON : matricule, nom, etc.).
- `PUT /api/apprenants/update.php` : Mise à jour (Body JSON incluant l'ID).
- `DELETE /api/apprenants/delete.php` : Suppression (Body JSON: `{"id": 123}`).
- `GET /api/referentiels/read.php?type={filieres|niveaux|annees}` : Retourne la liste des éléments actifs du référentiel demandé.

## 6. Flux Frontend/Backend
1. **Initialisation** : Au chargement de `index.html`, jQuery appelle `api/referentiels/read.php` pour peupler les listes déroulantes (filtres et formulaires). Ensuite, il appelle `api/apprenants/read.php` pour charger le tableau initial.
2. **Recherche/Filtre** : L'utilisateur change un filtre, jQuery envoie une requête `GET` à l'API. L'API retourne un tableau JSON. jQuery vide le `<tbody>` et reconstruit les lignes.
3. **Création/Modification** : L'utilisateur soumet le formulaire, le comportement par défaut est bloqué (`e.preventDefault()`). jQuery récupère les valeurs, forme un objet JSON, l'envoie via AJAX (`POST` ou `PUT`). À la réponse (`200 OK` ou `400 Bad Request`), l'UI affiche un message de succès/erreur et rafraîchit le tableau.

## 7. Maquettes Fonctionnelles Simples (Wireframes UI)
- **Tableau de bord** :
  - **En-tête** : Titre "Gestion des Apprenants".
  - **Barre d'outils** :
    - Champ de recherche textuelle (Nom, Matricule).
    - Listes déroulantes : Filtre par Filière, Filtre par Niveau.
    - Bouton [+ Nouvel Apprenant] (Ouvre le formulaire modal ou zone dédiée).
  - **Zone de messages** (cachée par défaut) : Alertes (succès/erreur).
  - **Tableau de données** : Colonnes (Matricule, Nom, Prénom, Filière, Statut, Actions).
  - **Actions (par ligne)** : Bouton 👁 (Détails), ✏️ (Modifier), 🗑 (Supprimer).
- **Formulaire (Modal ou Page intégrée)** :
  - Champs classiques (`input type="text"`, `date`, `select`).
  - Boutons [Enregistrer] [Annuler].

## 8. Règles de Sécurité
- **Base de données** : Utilisation exclusive de `PDO` avec `prepare()` et `execute()` pour prévenir toute injection SQL.
- **XSS (Cross-Site Scripting)** : Toutes les données sortantes dans `index.html` (gérées via jQuery `text()`) et côté serveur avant insertion JSON (fonction `htmlspecialchars()` si nécessaire, bien que `json_encode` + `text()` côté JS soit sûr).
- **Validation** :
  - *Frontend* : Attributs HTML5 (`required`, `type="date"`, `pattern`) + validations JS avant envoi.
  - *Backend* : **Validation stricte indispensable** (vérifier la présence des champs, les types, et l'existence des clés étrangères).
- **Gestion des erreurs** : Le mode PDO doit être sur `ERRMODE_EXCEPTION`. En cas d'erreur `catch`, l'API retourne une erreur HTTP `500` avec un message JSON générique (`{"message": "Erreur serveur"}`), sans fuiter de données SQL (`Stack Trace`).
- **Fichiers de configuration** : Le dossier `/config` contenant les identifiants BDD (`database.php`) ne doit en aucun cas être accessible publiquement (configurer un `.htaccess` ou placer le dossier hors de la racine web si possible).

## 9. Stratégie de Tests
Dans un environnement sans framework de tests complexes :
- **Tests Unitaires Backend (Manuels / Outils API)** : Utilisation d'un outil comme Postman ou cURL pour tester les endpoints de l'API indépendamment du front (tester les succès et surtout les cas d'erreur : requêtes incomplètes, matricule en doublon, etc.).
- **Tests d'Intégration Frontend** : Parcourir les scénarios d'utilisation dans le navigateur :
  - Création complète d'un apprenant et vérification dans le tableau.
  - Test des recherches textuelles partielles et des filtres combinés.
  - Tentative de suppression d'un apprenant.
- **Tests de Sécurité basiques** : Saisir des balises `<script>alert(1)</script>` dans le nom de l'apprenant pour s'assurer qu'elles ne sont pas exécutées à l'affichage.
