# Rapport de Tests - GESTION APPRENANTS V1

| Test | Résultat | Problème | Correction |
|---|---|---|---|
| **Création** - Apprenant valide | Succès | Aucun | |
| **Création** - Champs obligatoires (sans prénom) | Succès | Aucun (Rejeté comme attendu : HTTP 400) | |
| **Création** - Matricule dupliqué | Succès | Aucun (Rejeté avec message spécifique : HTTP 503) | |
| **Création** - Filière inexistante | Succès | Message générique de duplication levé au lieu de l'erreur d'intégrité (fk_apprenant_filiere) | Analyse de l'erreur PDO (Code 23000) améliorée pour vérifier la chaîne d'erreur spécifique (strpos) et renvoyer le bon message "Filière invalide". |
| **Création** - Niveau inexistant | Succès | Idem que filière | Idem (ajout de la condition `fk_apprenant_niveau`). |
| **Création** - Année inexistante | Succès | Idem que filière | Idem (ajout de la condition `fk_apprenant_annee`). |
| **Lecture** - Liste complète (Code 200) | Succès | Aucun | |
| **Lecture** - Recherche | Succès | Erreur `Invalid parameter number` lors de la requête préparée | Séparation du paramètre de recherche en trois paramètres distincts (`:search1`, `:search2`, `:search3`) pour gérer l'émulation désactivée de PDO. |
| **Lecture** - Filtre par filière/niveau/année | Succès | Aucun | |
| **Lecture** - Détails d'un apprenant | Succès | Aucun | |
| **Modification** - Modification valide | Succès | Aucun | |
| **Modification** - Identifiant inexistant | Succès | Aucun (La requête s'exécute sans affecter de ligne) | |
| **Modification** - Données invalides (Clé étrangère fausse) | Succès | Même problème que la création | Correction appliquée au bloc try/catch de la fonction update(). |
| **Suppression** - Suppression valide | Succès | Fatal Error en PHP causée par le passage direct du résultat d'une fonction à `bindParam()` (`Cannot pass parameter 2 by reference`) | Stockage du résultat dans une variable `$id` avant de la passer à `bindParam()`. |
| **Suppression** - Identifiant inexistant | Succès | Aucun | |
| **Suppression** - Confirmation avant suppression | Succès | Aucun (Géré par un `confirm()` en JavaScript dans `app.js`) | |
| **Base de Données** - Clés primaires, étrangères, UNIQUE, encodage | Succès | Le schéma initial comportait une erreur de syntaxe `IF NOT EXISTS` sur l'index, corrigée manuellement lors de l'initialisation. | Retrait de `IF NOT EXISTS` sur les instructions `CREATE INDEX`. |
| **Sécurité** - Injection SQL (Recherche) | Succès | Aucun (Bloqué par PDO préparé) | |
| **Sécurité** - XSS (Création et Affichage) | Succès | Vulnérabilité de double échappement ("Double-escaping") et persistance de données modifiées (stockage de htmlspecialchars en BDD). | Retrait de `htmlspecialchars()` avant insertion en base, et échappement réalisé au dernier moment lors de l'affichage DOM via la fonction JS `escapeHTML()`. |
| **Sécurité** - Paramètres manquants / HTTP Incorrectes | Succès | Aucun | |
| **Interface** - Tablette, Smartphone, Formulaire, Tableau | Succès | Aucun (Design responsive via `max-width`, `flex-wrap`, et `overflow-x: auto` pour la table) | |
| **API** - Vérification des endpoints (Code HTTP, JSON) | Succès | Le serveur de dev local retournait une erreur 404 lors du routage si le root directory (`-t`) pointait sur `/public`. | Modification des instructions du README.md pour démarrer le serveur de dev à la racine. |

**Statut Global :** V1 Validée. Tous les problèmes identifiés lors de l'étape de tests ont été corrigés, l'application est fonctionnelle et sécurisée selon le périmètre défini.