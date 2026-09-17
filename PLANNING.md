# Document de Planification : GESTION APPRENANTS

## Objectif général
Créer une application web permettant au personnel autorisé d'un Centre de Formation Professionnelle et Technique de gérer efficacement les dossiers des apprenants.

## Périmètre
Le projet, dans sa première version (V1), se concentre exclusivement sur les fonctionnalités de base de la gestion administrative des apprenants et de leur parcours de formation (filières, niveaux, années de formation).
Sont exclus du périmètre de la V1 : les paiements, la gestion des absences, les notes, les examens, l'authentification avancée, les notifications et toute application mobile native.

## Fonctionnalités V1
La V1 doit gérer :
1. Les apprenants (enregistrement, consultation, modification, suppression).
2. Les filières.
3. Les niveaux.
4. Les années de formation.
5. Les statuts des apprenants.
6. La recherche d'apprenants.
7. Les filtres pour affiner les recherches.
8. Les opérations CRUD (Create, Read, Update, Delete) sur l'ensemble de ces entités.

## Fonctionnalités futures (Hors périmètre V1)
- Gestion des paiements.
- Gestion des absences.
- Gestion des notes et examens.
- Système d'authentification avancée (rôles, permissions étendues, 2FA, etc.).
- Système de notifications (email, SMS, etc.).
- Application mobile native.

## Contraintes techniques
- **Langage Backend :** PHP 8+
- **Base de données :** MySQL 8+
- **Interface Base de données :** PDO (PHP Data Objects)
- **Frontend :** HTML5, CSS3
- **JavaScript :** jQuery, AJAX, JSON
- **Restriction :** Ne pas utiliser de framework PHP (ex: Laravel, Symfony) ou JavaScript (ex: React, Vue, Angular) pour la première version. Le développement doit se faire en PHP natif / JS (avec jQuery).

## Risques
- **Sécurité :** L'absence de framework nécessite une attention particulière à la sécurité des requêtes SQL (injection SQL, d'où l'utilisation impérative de requêtes préparées avec PDO) et à la validation/nettoyage des données (XSS, CSRF).
- **Maintenabilité :** Le code "from scratch" sans architecture imposée par un framework peut rapidement devenir difficile à maintenir si des règles strictes de structuration (par exemple MVC) ne sont pas mises en place.
- **Performances :** Si les recherches et les filtres ne sont pas optimisés, l'expérience utilisateur pourrait se dégrader avec un grand volume de données.

## Dépendances
- Un environnement serveur compatible PHP 8+ (ex: Apache, Nginx).
- Un serveur de base de données MySQL 8+.
- La bibliothèque jQuery (inclusion via CDN ou fichier local).
- Un navigateur web moderne supportant HTML5, CSS3, et les requêtes AJAX.

## Livrables
- Le code source complet de l'application (backend PHP, frontend HTML/CSS/JS).
- Le script de création de la base de données (fichier `.sql`).
- La documentation d'installation, de configuration et d'utilisation (ex: `README.md`).

## Critères d'achèvement de la V1
La V1 sera considérée comme terminée lorsque :
- L'interface utilisateur permet d'effectuer correctement toutes les opérations CRUD sur les apprenants, filières, niveaux et années de formation.
- Le système de filtrage et de recherche d'apprenants est opérationnel (utilisant AJAX/JSON pour l'asynchronisme).
- Le code est robuste, gère les erreurs courantes et prévient les injections SQL via PDO.
- Les contraintes technologiques imposées ont été scrupuleusement respectées (aucun framework utilisé).
- L'application peut être déployée de manière fluide avec les scripts SQL fournis.
