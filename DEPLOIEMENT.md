# Guide de Déploiement en Production : GESTION APPRENANTS V1

Ce document décrit la procédure étape par étape pour déployer l'application "Gestion Apprenants" dans un environnement de production sécurisé.

> **⚠️ AVERTISSEMENT SÉCURITÉ** :
> - **NE JAMAIS** placer ou "commiter" les mots de passe ou identifiants de production dans le dépôt Git (GitHub/GitLab).
> - Les messages d'erreur détaillés de PHP (`display_errors`) doivent être impérativement désactivés en production.

---

## 1. Prérequis Serveur (Environnement cible)
- **Serveur Web** : Apache ou Nginx.
- **PHP** : Version 8.0 ou supérieure avec les extensions `pdo` et `pdo_mysql` activées.
- **Base de données** : MySQL 8.0 ou supérieur (ou MariaDB équivalent) supportant l'encodage `utf8mb4` et le moteur `InnoDB`.
- **Sécurité** : Certificat SSL/TLS (HTTPS) obligatoire.
- **Domaine** : Un nom de domaine ou sous-domaine pointant vers le serveur.

> *Note à l'administrateur système* : Les informations exactes d'hébergement (IP, type de panel comme cPanel/Plesk, accès SSH) étant inconnues à ce stade, ce guide suppose un accès Shell (SSH) standard à un serveur Linux. Si vous utilisez un hébergement mutualisé spécifique, merci de nous communiquer les détails pour adapter cette procédure.

---

## 2. Préparation de la Base de Données

Connectez-vous au serveur de base de données (via SSH ou un outil comme phpMyAdmin) en tant qu'administrateur (ex: `root`).

1. **Création de la base de données :**
   ```sql
   CREATE DATABASE gestion_apprenants_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. **Création de l'utilisateur dédié et attribution des droits :**
   ```sql
   CREATE USER 'user_prod'@'localhost' IDENTIFIED BY 'VOTRE_MOT_DE_PASSE_TRES_FORT';
   GRANT ALL PRIVILEGES ON gestion_apprenants_prod.* TO 'user_prod'@'localhost';
   FLUSH PRIVILEGES;
   ```
3. **Importation de la structure :**
   Récupérez le fichier `database/schema.sql` depuis le dépôt et importez-le :
   ```bash
   mysql -u user_prod -p gestion_apprenants_prod < schema.sql
   ```
   *(Attention : Assurez-vous que le script SQL n'écrase pas une base existante par erreur si vous ré-importez).*

---

## 3. Déploiement de l'Application

### 3.1. Transfert des fichiers
Transférez les fichiers de l'application vers votre serveur web (ex. via `rsync`, `scp`, ou git clone **sans le dossier `.git`**).

Structure attendue sur le serveur (ex. dans `/var/www/gestion-apprenants`) :
```text
/var/www/gestion-apprenants/
├── api/
├── config/
│   └── database.php
└── public/      <-- C'est ce dossier qui doit être exposé sur le web !
```

### 3.2. Configuration du Serveur Web (Apache/Nginx)
Pour des raisons de sécurité, le "Document Root" (la racine publique du serveur web) **doit** pointer sur le dossier `/public`. Les dossiers `/api` et `/config` ne doivent pas être accessibles directement par le navigateur.

*Exemple de VirtualHost Apache :*
```apache
<VirtualHost *:80>
    ServerName apprenants.votre-domaine.com
    DocumentRoot /var/www/gestion-apprenants/public

    <Directory /var/www/gestion-apprenants/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Configuration optionnelle pour l'API si elle n'est pas dans public
    Alias /api /var/www/gestion-apprenants/api
</VirtualHost>
```

### 3.3. Configuration des Variables d'Environnement
L'application utilise les variables d'environnement pour se connecter à la base de données. Configurez-les sur votre serveur.

- **Via Apache (`.htaccess` ou `VirtualHost`) :**
  ```apache
  SetEnv DB_USER "user_prod"
  SetEnv DB_PASS "VOTRE_MOT_DE_PASSE_TRES_FORT"
  ```
- **Via PHP-FPM (`www.conf`) :**
  ```ini
  env[DB_USER] = user_prod
  env[DB_PASS] = VOTRE_MOT_DE_PASSE_TRES_FORT
  ```

### 3.4. Configuration PHP (Sécurité)
Vérifiez le fichier `php.ini` de production :
```ini
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
```

### 3.5. Activation HTTPS
Générez et installez un certificat SSL (par exemple via Let's Encrypt / Certbot) :
```bash
sudo certbot --apache -d apprenants.votre-domaine.com
```
Forcez la redirection de HTTP vers HTTPS.

### 3.6. Nettoyage
Supprimez tous les fichiers temporaires, journaux de développement (ex: `php_server.log`), ou scripts de tests (ex: `run_tests.sh`) présents sur le serveur de production.

---

## 4. Vérification Post-Déploiement

Une fois l'application en ligne, effectuez immédiatement la batterie de tests manuels suivante dans votre navigateur web :

1. **Connexion & Lecture :** Accédez au tableau de bord. La liste des apprenants initiaux doit s'afficher sans erreur (vérifier la console (F12) pour s'assurer qu'aucune erreur AJAX/CORS n'est présente).
2. **Création :** Ajoutez un nouvel apprenant de test en remplissant tous les champs obligatoires. Vérifiez qu'il apparaît dans le tableau.
3. **Modification :** Modifiez le nom de l'apprenant créé. Vérifiez l'enregistrement.
4. **Recherche & Filtres :** Cherchez l'apprenant modifié via la barre de recherche. Testez un filtre (Filière ou Niveau).
5. **Détails :** Cliquez sur l'icône 👁 pour afficher la modale de détails.
6. **Suppression :** Supprimez l'apprenant de test créé à l'étape 2.
7. **Affichage Mobile :** Redimensionnez la fenêtre du navigateur (ou utilisez un smartphone) pour vérifier que le tableau est bien défilable horizontalement et que la mise en page s'adapte correctement.

---

## 5. Stratégie de Sauvegarde (Backup)
Configurez une tâche automatisée (Cron job) sur le serveur pour sauvegarder la base de données de production quotidiennement :
```bash
# Exemple de script cron (à lancer à 2h du matin)
0 2 * * * mysqldump -u user_prod -pVOTRE_MOT_DE_PASSE_TRES_FORT gestion_apprenants_prod | gzip > /chemin/vers/sauvegardes/backup_$(date +\%Y\%m\%d).sql.gz
```