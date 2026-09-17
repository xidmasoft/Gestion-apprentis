# Plan de Maintenance et d'Évolution : GESTION APPRENANTS

Ce document détaille les procédures de maintenance de l'application en production et propose une feuille de route pour son évolution, incluant les futurs modules fonctionnels et d'éventuelles refontes de la structure de base de données.

---

## 1. Maintenance Corrective
Procédure à suivre en cas de bug signalé en production :

1. **Identification & Réception** : Création d'un ticket (ex: GitLab, Jira ou Trello) avec description du problème, environnement concerné, et informations de l'utilisateur.
2. **Reproduction** : Tenter de reproduire le bug de manière isolée sur un environnement de développement local ou de *staging* copie de la production.
3. **Correction** : Développement de la correction sur une branche Git dédiée (ex: `fix/nom-du-bug`).
4. **Tests** :
   - Tests de non-régression locaux.
   - Validation de la correction par un pair si possible.
5. **Déploiement** : Application du correctif en production (idéalement aux heures creuses) avec notification aux utilisateurs impactés, suivant la procédure définie dans `DEPLOIEMENT.md`.

---

## 2. Maintenance Préventive
Les actions suivantes doivent être planifiées et exécutées régulièrement :

- **Sauvegardes (Quotidiennes)** : Automatiser un *dump* de la base de données MySQL via une tâche cron, sauvegardé sur un support physique ou cloud distinct du serveur web.
- **Vérification des Logs (Hebdomadaire)** : Analyser les journaux d'erreurs Apache/Nginx et PHP (`/var/log/php_errors.log`) pour détecter de potentielles failles ou erreurs récurrentes.
- **Mises à jour (Mensuelle)** : Vérifier et appliquer les mises à jour de sécurité de l'OS (Debian/Ubuntu), de PHP et de MySQL. Tester ces mises à jour en *staging* avant la production.
- **Surveillance de l'espace disque & Performances** : Mettre en place des alertes simples (ex: via un script bash et *mailutils*) en cas de saturation de l'espace disque du serveur ou du processeur.

---

## 3. Maintenance Évolutive et Architecture

### 3.1. Analyse Architecturale : Séparation Apprenants / Inscriptions
*Problème actuel* : L'architecture de la V1 lie directement un apprenant à un ID d'année (`annee_id`), de niveau et de filière dans la table `apprenants`. Cela empêche de suivre l'historique d'un apprenant sur plusieurs années (ex: passage de 1ère année à 2ème année).

*Solution ciblée* : Séparer l'entité `apprenants` de l'entité `inscriptions`.
- **Table `apprenants`** : Données d'identité pures (matricule, nom, prénom, date de naissance, sexe, téléphone, adresse).
- **Table `inscriptions`** : Ligne d'inscription associant `apprenant_id`, `annee_id`, `filiere_id`, `niveau_id` et un `statut` (En cours, Réussi, Redouble).

**Action requise** : Avant tout développement des fonctionnalités de la Phase 2, un script de migration des données SQL existantes devra être créé pour transférer les données des colonnes vers la nouvelle table `inscriptions` sans perte.

### 3.2. Feuille de Route d'Évolution (Roadmap)
Les améliorations futures doivent être implémentées dans l'ordre logique suivant, basé sur les dépendances techniques :

**PHASE 2 - Restructuration et Accès**
1. **Migration structurelle Apprenants / Inscriptions** : Permettre les réinscriptions et la gestion de l'historique (comme analysé au 3.1).
2. **Utilisateurs et Rôles (Authentification)** : Création d'un système de connexion (login/mot de passe hashé) et de permissions (Admin, Personnel, Professeurs). Prérequis indispensable pour les modules suivants.

**PHASE 3 - Suivi Pédagogique**
3. **Absences** : Possibilité d'associer des retards/absences justifiées ou non à une `inscription_id`.
4. **Évaluations et Examens** : Création d'une nomenclature des matières et examens.
5. **Notes** : Saisie des notes liées aux examens et aux inscriptions (bulletins).

**PHASE 4 - Administratif et Financier**
6. **Paiements** : Suivi des frais de scolarité associés aux inscriptions (intégration possible de reçus au format PDF).
7. **Documents** : Module permettant de générer des attestations ou de stocker les pièces jointes d'un dossier.

**PHASE 5 - Optimisation**
8. **Statistiques et Tableaux de bord** : Agrégation des données (taux de réussite, suivi financier, absentéisme).
9. **Notifications** : Alertes automatiques (email/SMS) pour absences ou retards de paiement.

---

## 4. Stratégie de Documentation
Pour assurer la pérennité du projet, les documents suivants seront maintenus à jour à chaque cycle de développement :

- **`README.md`** : Aperçu général du projet et quick-start.
- **Documentation de la base de données** : Le fichier `schema.sql` (ou un dictionnaire de données) devra toujours refléter la structure en production.
- **Documentation API** : Maintenir un fichier (ex: format OpenAPI/Swagger ou un simple Markdown) listant les routes, paramètres et exemples de réponses.
- **`DEPLOIEMENT.md`** : Ajusté si les prérequis changent (ex: ajout d'une librairie PDF pour PHP).
- **Historique des versions (Changelog)** : Historisation des correctifs et des nouvelles fonctionnalités par version de l'application.
