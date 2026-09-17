# GESTION APPRENANTS - V1

Application web de gestion des apprenants d'un Centre de Formation Professionnelle et Technique.
Développée "from scratch" en PHP, MySQL, HTML, CSS et jQuery.

## Installation

1. Cloner le dépôt.
2. Importer la base de données : `mysql -u root -p < database/schema.sql`
3. Configurer les variables d'environnement pour la connexion à la base de données.
   Exemple (si vous utilisez un serveur web ou php-fpm) :
   `DB_USER=root`
   `DB_PASS=votre_mot_de_passe`
   Par défaut, si elles ne sont pas définies, le système essaiera avec `root` et un mot de passe vide.
4. Lancer l'application : pointez votre serveur web (ex: Apache) vers la racine du projet ou utilisez le serveur de développement PHP :
   `php -S localhost:8000`
5. Accéder à l'application dans votre navigateur : `http://localhost:8000/public/index.php`

## Structure

- `database/` : Schéma SQL
- `config/` : Configuration (PDO)
- `api/` : Endpoints backend (PHP)
- `public/` : Interface utilisateur (HTML/CSS/JS)
