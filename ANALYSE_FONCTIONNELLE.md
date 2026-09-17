# Document d'Analyse Fonctionnelle : GESTION APPRENANTS

## 1. Acteurs
Pour la version 1 (V1), les rôles identifiés et justifiés sont les suivants :
- **Administrateur** : Personne en charge de la configuration globale de l'application (gestion des filières, niveaux, années de formation, etc.) et de la supervision.
- **Personnel chargé de la gestion des apprenants** : Utilisateurs principaux (ex. scolarité) habilités à ajouter, consulter, modifier, et supprimer les dossiers des apprenants.
*(Aucun autre rôle n'est créé dans la V1, conformément aux instructions).*

## 2. Cas d'utilisation
- **Gestion des apprenants** :
  - L'acteur (Personnel/Admin) peut ajouter un nouvel apprenant.
  - L'acteur peut rechercher un apprenant par critères.
  - L'acteur peut consulter la fiche détaillée d'un apprenant.
  - L'acteur peut modifier les informations d'un apprenant.
  - L'acteur peut supprimer (logiquement ou physiquement) un apprenant.
  - L'acteur peut filtrer la liste des apprenants (par filière, niveau, année, statut).
- **Gestion du référentiel (Administrateur principalement)** :
  - Ajouter, modifier, consulter, désactiver des filières.
  - Gérer les niveaux (CAP, BEP, Attestation, etc.).
  - Gérer les années de formation (ex. 2026-2027, 2027-2028).

## 3. Fonctionnalités (Périmètre V1)
- **Module Apprenants** : Formulaire de création, tableau de bord de liste, vue détaillée, formulaire d'édition, confirmation de suppression.
- **Module Filières** : Gestion (CRUD) des différentes filières proposées par le centre.
- **Module Niveaux** : Gestion du référentiel des niveaux associés.
- **Module Années de formation** : Gestion de la liste des années scolaires actives et passées.
- **Module de Recherche et Filtrage** : Moteur de recherche multicritères, dynamique via AJAX.

## 4. Règles métier
- **Matricule** : Doit être unique pour chaque apprenant. Il peut être auto-généré ou saisi, mais aucune duplication ne sera tolérée.
- **Champs obligatoires** : Filière, niveau et année de formation sont strictement obligatoires lors de la création et modification d'un apprenant.
- **Validation des données** : Tous les champs (ex. email, téléphone, dates) doivent faire l'objet d'une validation stricte côté serveur (et côté client pour l'UX).
- **Suppressions** : La gestion des suppressions doit éviter la perte de données orphelines. *À valider (voir section Ambiguïtés).*
- **Statuts** : Un apprenant doit avoir un statut (ex. Actif, Abandon, Diplômé) défini et géré dans le temps.

## 5. Données
Les données nécessaires par apprenant sont les suivantes :
- **Matricule** (Unique)
- **Nom**
- **Prénom**
- **Date de naissance**
- **Sexe**
- **Téléphone**
- **Adresse**
- **Filière** (Clé étrangère vers Filières)
- **Niveau** (Clé étrangère vers Niveaux)
- **Année de formation** (Clé étrangère vers Années)
- **Statut**

## 6. Contraintes
- **Technologiques** : PHP 8+, MySQL 8+, PDO.
- **Architecture** : Développement "from scratch" sans framework PHP ou JavaScript. Interface en HTML5, CSS3, jQuery, AJAX et JSON.
- **Navigateur** : Doit être compatible avec les navigateurs web modernes.

## 7. Exigences de sécurité
- **Injections SQL** : Utilisation stricte des requêtes préparées avec PDO pour toutes les interactions avec la base de données.
- **Failles XSS** : Echappement systématique de l'affichage des données saisies par les utilisateurs (ex. fonction `htmlspecialchars()` en PHP).
- **Validation** : Les données soumises via les formulaires (et AJAX) doivent impérativement être re-validées côté serveur pour éviter la corruption de la base ou toute exploitation malveillante.
- **Authentification V1** : Bien que l'authentification avancée soit hors périmètre V1, un contrôle d'accès basique (session PHP simple) doit protéger les interfaces de gestion.

## 8. Exigences de performance
- L'affichage de la liste des apprenants doit être rapide, même avec un grand nombre de dossiers. Les requêtes AJAX pour la recherche et les filtres doivent répondre quasi-instantanément pour assurer une bonne fluidité.
- En cas de volume de données important, la pagination de la liste d'apprenants devra être intégrée.

## 9. Exigences d'ergonomie
- **Interface Utilisateur (UI)** : Simple, intuitive et accessible. L'utilisation d'AJAX pour la recherche, la création, la modification et la suppression doit éviter les rechargements complets de page afin de fournir une expérience utilisateur réactive.
- **Retours visuels** : Les opérations de succès ou d'erreur doivent être signalées de manière claire à l'utilisateur (ex. notifications, messages flash).

## 10. Critères d'acceptation
- L'administrateur peut créer des filières, niveaux et années de formation sans erreurs.
- Le personnel peut ajouter, consulter, modifier, chercher, filtrer et supprimer un apprenant en utilisant l'interface.
- Les validations métier (unicité du matricule, champs obligatoires) bloquent correctement les données non conformes.
- Les requêtes de recherche/filtres s'effectuent sans recharger la page, grâce à AJAX/JSON.
- Le code source final respecte les contraintes techniques imposées (pas de framework).

---
## Ambiguïtés et décisions métier à valider
Avant de passer à la conception technique, les points suivants nécessitent une validation :
1. **Génération du Matricule** : Le matricule de l'apprenant doit-il être généré automatiquement par le système (selon un format spécifique, ex: "ANNEE-NUMERO") ou bien saisi manuellement par le personnel ?
2. **Type de Suppression** : S'agit-il d'une suppression physique (effacement total en base de données) ou d'une suppression logique (champ `deleted_at` ou changement de statut pour archiver l'apprenant sans perte de données historiques) ? La suppression logique est fortement recommandée pour la traçabilité.
3. **Statuts des Apprenants** : Quels sont les statuts exacts possibles à intégrer par défaut (ex: Inscrit, En cours, Abandon, Exclu, Diplômé) ?
4. **Authentification** : Bien que "l'authentification avancée" soit exclue, faut-il tout de même prévoir un écran de connexion (login/mot de passe simple) pour différencier les actions de l'Administrateur de celles du Personnel ? Ou bien l'application est-elle utilisée en réseau local sécurisé sans authentification pour la V1 ?
