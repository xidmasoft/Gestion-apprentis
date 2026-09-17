$(document).ready(function () {
    // URL de base de l'API (à adapter si nécessaire)
    const API_URL = '../api/';

    // Initialisation
    loadReferentiels();
    loadApprenants();

    // -------------------------------------------------------------------------
    // Événements
    // -------------------------------------------------------------------------

    // Recherche et Filtres
    $('#search-input').on('keyup', debounce(loadApprenants, 500));
    $('#filter-filiere, #filter-niveau, #filter-annee').on('change', loadApprenants);
    $('#btn-reset-filters').on('click', function() {
        $('#search-input').val('');
        $('#filter-filiere').val('');
        $('#filter-niveau').val('');
        $('#filter-annee').val('');
        loadApprenants();
    });

    // Afficher formulaire d'ajout
    $('#btn-show-form').on('click', function() {
        resetForm();
        $('#modal-title').text('Ajouter un apprenant');
        $('#modal-form').removeClass('hidden');
    });

    // Fermer les modales
    $('.close-modal, #btn-cancel-form').on('click', function() {
        $('.modal').addClass('hidden');
    });

    // Soumission du formulaire (Ajout/Modification)
    $('#apprenant-form').on('submit', function(e) {
        e.preventDefault();
        saveApprenant();
    });

    // Délégation d'événements pour les boutons du tableau
    $('#apprenants-table tbody').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        editApprenant(id);
    });

    $('#apprenants-table tbody').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        deleteApprenant(id);
    });

    $('#apprenants-table tbody').on('click', '.btn-view', function() {
        const id = $(this).data('id');
        viewApprenant(id);
    });


    // -------------------------------------------------------------------------
    // Fonctions AJAX & Métier
    // -------------------------------------------------------------------------

    // Charger les référentiels (listes déroulantes)
    function loadReferentiels() {
        $.getJSON(API_URL + 'filieres.php', function(data) {
            populateSelect('#filter-filiere', data, 'Toutes les filières');
            populateSelect('#form-filiere', data, 'Sélectionner');
        });
        $.getJSON(API_URL + 'niveaux.php', function(data) {
            populateSelect('#filter-niveau', data, 'Tous les niveaux');
            populateSelect('#form-niveau', data, 'Sélectionner');
        });
        $.getJSON(API_URL + 'annees-formation.php', function(data) {
            populateSelect('#filter-annee', data, 'Toutes les années', 'libelle');
            populateSelect('#form-annee', data, 'Sélectionner', 'libelle');
        });
    }

    // Charger la liste des apprenants
    function loadApprenants() {
        const search = $('#search-input').val();
        const filiere = $('#filter-filiere').val();
        const niveau = $('#filter-niveau').val();
        const annee = $('#filter-annee').val();

        let url = API_URL + 'apprenants.php?';
        if (search) url += 'search=' + encodeURIComponent(search) + '&';
        if (filiere) url += 'filiere_id=' + filiere + '&';
        if (niveau) url += 'niveau_id=' + niveau + '&';
        if (annee) url += 'annee_id=' + annee;

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                renderTable(data);
                $('#total-count').text(data.length);
            },
            error: function() {
                showMessage('Erreur lors du chargement des apprenants.', 'error');
            }
        });
    }

    // Sauvegarder (POST ou PUT)
    function saveApprenant() {
        const id = $('#form-id').val();
        const method = id ? 'PUT' : 'POST';

        const formData = {
            id: id,
            matricule: $('#form-matricule').val(),
            nom: $('#form-nom').val(),
            prenom: $('#form-prenom').val(),
            date_naissance: $('#form-date-naissance').val(),
            sexe: $('#form-sexe').val(),
            telephone: $('#form-telephone').val(),
            adresse: $('#form-adresse').val(),
            filiere_id: $('#form-filiere').val(),
            niveau_id: $('#form-niveau').val(),
            annee_id: $('#form-annee').val(),
            statut: $('#form-statut').val()
        };

        $.ajax({
            url: API_URL + 'apprenants.php',
            type: method,
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(res) {
                $('#modal-form').addClass('hidden');
                showMessage(res.message, 'success');
                loadApprenants();
            },
            error: function(xhr) {
                let msg = 'Erreur lors de l\'enregistrement.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showMessage(msg, 'error');
            }
        });
    }

    // Récupérer pour édition
    function editApprenant(id) {
        $.getJSON(API_URL + 'apprenants.php?id=' + id, function(data) {
            $('#form-id').val(data.id);
            $('#form-matricule').val(data.matricule);
            $('#form-nom').val(data.nom);
            $('#form-prenom').val(data.prenom);
            $('#form-date-naissance').val(data.date_naissance);
            $('#form-sexe').val(data.sexe);
            $('#form-telephone').val(data.telephone);
            $('#form-adresse').val(data.adresse);
            $('#form-filiere').val(data.filiere_id);
            $('#form-niveau').val(data.niveau_id);
            $('#form-annee').val(data.annee_id);
            $('#form-statut').val(data.statut);

            $('#modal-title').text('Modifier l\'apprenant');
            $('#modal-form').removeClass('hidden');
        }).fail(function() {
            showMessage('Erreur lors de la récupération des données.', 'error');
        });
    }

    // Supprimer
    function deleteApprenant(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer cet apprenant ?")) {
            $.ajax({
                url: API_URL + 'apprenants.php',
                type: 'DELETE',
                contentType: 'application/json',
                data: JSON.stringify({ id: id }),
                success: function(res) {
                    showMessage(res.message, 'success');
                    loadApprenants();
                },
                error: function(xhr) {
                    showMessage('Erreur lors de la suppression.', 'error');
                }
            });
        }
    }

    // Afficher détails
    function viewApprenant(id) {
        $.getJSON(API_URL + 'apprenants.php?id=' + id, function(data) {
            const html = `
                <div class="details-grid">
                    <p><strong>Matricule</strong> ${escapeHTML(data.matricule)}</p>
                    <p><strong>Statut</strong> <span class="badge ${formatBadgeClass(escapeHTML(data.statut))}">${escapeHTML(data.statut)}</span></p>
                    <p><strong>Nom</strong> ${escapeHTML(data.nom)}</p>
                    <p><strong>Prénom</strong> ${escapeHTML(data.prenom)}</p>
                    <p><strong>Date de Naissance</strong> ${formatDate(escapeHTML(data.date_naissance))}</p>
                    <p><strong>Sexe</strong> ${escapeHTML(data.sexe)}</p>
                    <p><strong>Filière</strong> ${escapeHTML(data.filiere_nom)}</p>
                    <p><strong>Niveau</strong> ${escapeHTML(data.niveau_nom)}</p>
                    <p><strong>Année</strong> ${escapeHTML(data.annee_libelle)}</p>
                    <p><strong>Téléphone</strong> ${escapeHTML(data.telephone || '-')}</p>
                    <p style="grid-column: 1 / -1;"><strong>Adresse</strong> ${escapeHTML(data.adresse || '-')}</p>
                </div>
            `;
            $('#details-content').html(html);
            $('#modal-details').removeClass('hidden');
        }).fail(function() {
            showMessage('Erreur lors de la récupération des détails.', 'error');
        });
    }


    // -------------------------------------------------------------------------
    // Fonctions utilitaires
    // -------------------------------------------------------------------------

    function escapeHTML(str) {
        if (!str) return '';
        return str.toString().replace(/[&<>'"]/g,
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    }

    function renderTable(data) {
        const tbody = $('#apprenants-table tbody');
        tbody.empty();

        if (data.length === 0) {
            tbody.append('<tr><td colspan="7" class="text-center">Aucun apprenant trouvé.</td></tr>');
            return;
        }

        data.forEach(item => {
            const tr = `
                <tr>
                    <td>${escapeHTML(item.matricule)}</td>
                    <td><strong>${escapeHTML(item.nom)}</strong> ${escapeHTML(item.prenom)}</td>
                    <td>${escapeHTML(item.filiere_nom)}</td>
                    <td>${escapeHTML(item.niveau_nom)}</td>
                    <td>${escapeHTML(item.annee_libelle)}</td>
                    <td><span class="badge ${formatBadgeClass(escapeHTML(item.statut))}">${escapeHTML(item.statut)}</span></td>
                    <td class="actions-cell">
                        <button class="btn-secondary btn-small btn-view" data-id="${escapeHTML(item.id)}" title="Voir">👁</button>
                        <button class="btn-primary btn-small btn-edit" data-id="${escapeHTML(item.id)}" title="Modifier">✏️</button>
                        <button class="btn-danger btn-small btn-delete" data-id="${escapeHTML(item.id)}" title="Supprimer">🗑</button>
                    </td>
                </tr>
            `;
            tbody.append(tr);
        });
    }

    function populateSelect(selector, data, defaultOption, displayField = 'nom') {
        const select = $(selector);
        select.empty();
        select.append(`<option value="">${defaultOption}</option>`);
        data.forEach(item => {
            select.append(`<option value="${item.id}">${item[displayField]}</option>`);
        });
    }

    function resetForm() {
        $('#form-id').val('');
        $('#apprenant-form')[0].reset();
    }

    function showMessage(message, type) {
        const box = $('#message-box');
        box.text(message)
           .removeClass('hidden msg-success msg-error')
           .addClass(type === 'success' ? 'msg-success' : 'msg-error');

        // Cacher après 3 secondes
        setTimeout(() => {
            box.addClass('hidden');
        }, 3000);
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const [year, month, day] = dateStr.split('-');
        return `${day}/${month}/${year}`;
    }

    function formatBadgeClass(statut) {
        return statut.toLowerCase().replace(' ', '-').replace('ô', 'o');
    }
});