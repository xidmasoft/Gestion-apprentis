<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Apprenants</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Inclusion jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <header>
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; gap: 20px; align-items: center;">
                <h1>Gestion des Apprenants</h1>
                <nav>
                    <button class="btn-secondary btn-small" onclick="switchView('apprenants')">Apprenants</button>
                    <button class="btn-secondary btn-small" onclick="switchView('filieres')">Filières</button>
                    <button class="btn-secondary btn-small" onclick="switchView('niveaux')">Niveaux</button>
                    <button class="btn-secondary btn-small" onclick="switchView('annees')">Années</button>
                </nav>
            </div>
            <div class="stats" id="stats-container">
                Total : <span id="total-count">0</span>
            </div>
        </div>
    </header>

    <main class="container">
        <!-- Messages d'état -->
        <div id="message-box" class="hidden"></div>

        <div id="view-apprenants" class="view-section">

            <!-- Outils : Recherche, Filtres et Ajout -->
            <div class="toolbar">
            <div class="search-filters">
                <input type="text" id="search-input" placeholder="Rechercher par nom, prénom ou matricule...">
                <select id="filter-filiere">
                    <option value="">Toutes les filières</option>
                </select>
                <select id="filter-niveau">
                    <option value="">Tous les niveaux</option>
                </select>
                <select id="filter-annee">
                    <option value="">Toutes les années</option>
                </select>
                <button id="btn-reset-filters">Réinitialiser</button>
            </div>
            <button id="btn-show-form" class="btn-primary">+ Nouvel Apprenant</button>
        </div>

            <!-- Tableau des apprenants -->
            <div class="table-responsive">
                <table id="apprenants-table">
                    <thead>
                        <tr>
                            <th>Matricule</th>
                            <th>Nom & Prénom</th>
                            <th>Filière</th>
                            <th>Niveau</th>
                            <th>Année</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Chargement via AJAX -->
                        <tr><td colspan="7" class="text-center">Chargement des données...</td></tr>
                    </tbody>
                </table>
            </div>
        </div> <!-- end view apprenants -->

        <!-- VUE REFERENTIELS -->
        <div id="view-referentiels" class="view-section hidden">
            <div class="toolbar">
                <h2 id="ref-title" style="margin:0;">Référentiels</h2>
                <button id="btn-show-ref-form" class="btn-primary">+ Ajouter</button>
            </div>
            <div class="table-responsive">
                <table id="referentiels-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th id="th-nom-libelle">Nom</th>
                            <th>Description / Période</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="5" class="text-center">Chargement...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modale Apprenant -->
        <div id="modal-form" class="modal hidden">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2 id="modal-title">Ajouter un apprenant</h2>
                <form id="apprenant-form">
                    <input type="hidden" id="form-id" name="id">

                    <div class="form-group">
                        <label for="form-matricule">Matricule *</label>
                        <input type="text" id="form-matricule" name="matricule" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <label for="form-nom">Nom *</label>
                            <input type="text" id="form-nom" name="nom" required>
                        </div>
                        <div class="form-group half">
                            <label for="form-prenom">Prénom *</label>
                            <input type="text" id="form-prenom" name="prenom" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <label for="form-date-naissance">Date de naissance *</label>
                            <input type="date" id="form-date-naissance" name="date_naissance" required>
                        </div>
                        <div class="form-group half">
                            <label for="form-sexe">Sexe *</label>
                            <select id="form-sexe" name="sexe" required>
                                <option value="">Sélectionner</option>
                                <option value="M">Masculin</option>
                                <option value="F">Féminin</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form-telephone">Téléphone</label>
                        <input type="text" id="form-telephone" name="telephone">
                    </div>
                    <div class="form-group">
                        <label for="form-adresse">Adresse</label>
                        <textarea id="form-adresse" name="adresse" rows="2"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group third">
                            <label for="form-filiere">Filière *</label>
                            <select id="form-filiere" name="filiere_id" required></select>
                        </div>
                        <div class="form-group third">
                            <label for="form-niveau">Niveau *</label>
                            <select id="form-niveau" name="niveau_id" required></select>
                        </div>
                        <div class="form-group third">
                            <label for="form-annee">Année *</label>
                            <select id="form-annee" name="annee_id" required></select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form-statut">Statut *</label>
                        <select id="form-statut" name="statut" required>
                            <option value="Inscrit">Inscrit</option>
                            <option value="En cours">En cours</option>
                            <option value="Abandon">Abandon</option>
                            <option value="Exclu">Exclu</option>
                            <option value="Diplômé">Diplômé</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary" id="btn-cancel-form">Annuler</button>
                        <button type="submit" class="btn-primary" id="btn-save-form">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modale Référentiel -->
        <div id="modal-ref-form" class="modal hidden">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2 id="modal-ref-title">Ajouter</h2>
                <form id="ref-form">
                    <input type="hidden" id="ref-form-id" name="id">
                    <input type="hidden" id="ref-form-type" name="type">

                    <div class="form-group" id="fg-nom">
                        <label id="lbl-nom" for="ref-form-nom">Nom *</label>
                        <input type="text" id="ref-form-nom" name="nom">
                    </div>

                    <div class="form-group" id="fg-desc">
                        <label for="ref-form-desc">Description</label>
                        <textarea id="ref-form-desc" name="description" rows="2"></textarea>
                    </div>

                    <div class="form-row" id="fg-dates" class="hidden">
                        <div class="form-group half">
                            <label for="ref-form-debut">Date début</label>
                            <input type="date" id="ref-form-debut" name="date_debut">
                        </div>
                        <div class="form-group half">
                            <label for="ref-form-fin">Date fin</label>
                            <input type="date" id="ref-form-fin" name="date_fin">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ref-form-actif">Statut *</label>
                        <select id="ref-form-actif" name="actif" required>
                            <option value="1">Actif</option>
                            <option value="0">Inactif</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary" id="btn-cancel-ref-form">Annuler</button>
                        <button type="submit" class="btn-primary" id="btn-save-ref-form">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Détails Apprenant -->
        <div id="modal-details" class="modal hidden">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2>Détails de l'apprenant</h2>
                <div id="details-content">
                    <!-- Chargé via AJAX -->
                </div>
            </div>
        </div>

    </main>
    <script src="js/app.js"></script>
</body>
</html>