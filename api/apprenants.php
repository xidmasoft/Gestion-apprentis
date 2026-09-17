<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($method) {
    case 'GET':
        // Lecture : Liste (avec filtres optionnels) ou Un seul (si ?id=)
        if (isset($_GET['id'])) {
            readSingle($db, $_GET['id']);
        } else {
            readList($db);
        }
        break;
    case 'POST':
        // Création
        create($db);
        break;
    case 'PUT':
        // Mise à jour
        update($db);
        break;
    case 'DELETE':
        // Suppression
        delete($db);
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Méthode non autorisée"]);
        break;
}

function readList($db) {
    try {
        $query = "SELECT a.id, a.matricule, a.nom, a.prenom, a.date_naissance, a.sexe, a.telephone, a.adresse, a.statut,
                         a.filiere_id, a.niveau_id, a.annee_id,
                         f.nom as filiere_nom, n.nom as niveau_nom, an.libelle as annee_libelle
                  FROM apprenants a
                  LEFT JOIN filieres f ON a.filiere_id = f.id
                  LEFT JOIN niveaux n ON a.niveau_id = n.id
                  LEFT JOIN annees_formation an ON a.annee_id = an.id
                  WHERE 1=1";

        $params = [];

        if (!empty($_GET['search'])) {
            $query .= " AND (a.nom LIKE :search1 OR a.prenom LIKE :search2 OR a.matricule LIKE :search3)";
            $params[':search1'] = '%' . $_GET['search'] . '%';
            $params[':search2'] = '%' . $_GET['search'] . '%';
            $params[':search3'] = '%' . $_GET['search'] . '%';
        }
        if (!empty($_GET['filiere_id'])) {
            $query .= " AND a.filiere_id = :filiere_id";
            $params[':filiere_id'] = $_GET['filiere_id'];
        }
        if (!empty($_GET['niveau_id'])) {
            $query .= " AND a.niveau_id = :niveau_id";
            $params[':niveau_id'] = $_GET['niveau_id'];
        }
        if (!empty($_GET['annee_id'])) {
            $query .= " AND a.annee_id = :annee_id";
            $params[':annee_id'] = $_GET['annee_id'];
        }

        $query .= " ORDER BY a.created_at DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);

        $apprenants_arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($apprenants_arr, $row);
        }

        http_response_code(200);
        echo json_encode($apprenants_arr);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Erreur lors de la récupération des apprenants: " . $e->getMessage()]);
    }
}

function readSingle($db, $id) {
    try {
        $query = "SELECT a.*, f.nom as filiere_nom, n.nom as niveau_nom, an.libelle as annee_libelle
                  FROM apprenants a
                  LEFT JOIN filieres f ON a.filiere_id = f.id
                  LEFT JOIN niveaux n ON a.niveau_id = n.id
                  LEFT JOIN annees_formation an ON a.annee_id = an.id
                  WHERE a.id = :id LIMIT 0,1";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            http_response_code(200);
            echo json_encode($row);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Apprenant non trouvé."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Erreur serveur."]);
    }
}

function create($db) {
    $data = json_decode(file_get_contents("php://input"));

    if (
        !empty($data->matricule) && !empty($data->nom) && !empty($data->prenom) &&
        !empty($data->date_naissance) && !empty($data->sexe) &&
        !empty($data->filiere_id) && !empty($data->niveau_id) && !empty($data->annee_id)
    ) {
        try {
            $query = "INSERT INTO apprenants
                      SET matricule=:matricule, nom=:nom, prenom=:prenom,
                          date_naissance=:date_naissance, sexe=:sexe, telephone=:telephone,
                          adresse=:adresse, filiere_id=:filiere_id, niveau_id=:niveau_id,
                          annee_id=:annee_id, statut=:statut";

            $stmt = $db->prepare($query);

            $matricule = strip_tags($data->matricule);
            $nom = strip_tags($data->nom);
            $prenom = strip_tags($data->prenom);
            $date_naissance = strip_tags($data->date_naissance);
            $sexe = strip_tags($data->sexe);
            $filiere_id = strip_tags($data->filiere_id);
            $niveau_id = strip_tags($data->niveau_id);
            $annee_id = strip_tags($data->annee_id);

            $stmt->bindParam(":matricule", $matricule);
            $stmt->bindParam(":nom", $nom);
            $stmt->bindParam(":prenom", $prenom);
            $stmt->bindParam(":date_naissance", $date_naissance);
            $stmt->bindParam(":sexe", $sexe);
            $stmt->bindValue(":telephone", isset($data->telephone) ? strip_tags($data->telephone) : null);
            $stmt->bindValue(":adresse", isset($data->adresse) ? strip_tags($data->adresse) : null);
            $stmt->bindParam(":filiere_id", $filiere_id);
            $stmt->bindParam(":niveau_id", $niveau_id);
            $stmt->bindParam(":annee_id", $annee_id);
            $statut = !empty($data->statut) ? strip_tags($data->statut) : 'Inscrit';
            $stmt->bindParam(":statut", $statut);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(["message" => "L'apprenant a été créé."]);
            }
        } catch (PDOException $e) {
            http_response_code(503);
            if ($e->getCode() == 23000) {
                if (strpos($e->getMessage(), 'fk_apprenant_filiere') !== false) {
                    echo json_encode(["message" => "Filière invalide."]);
                } else if (strpos($e->getMessage(), 'fk_apprenant_niveau') !== false) {
                    echo json_encode(["message" => "Niveau invalide."]);
                } else if (strpos($e->getMessage(), 'fk_apprenant_annee') !== false) {
                    echo json_encode(["message" => "Année invalide."]);
                } else {
                    echo json_encode(["message" => "Ce matricule existe déjà."]);
                }
            } else {
                echo json_encode(["message" => "Impossible de créer l'apprenant."]);
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Les données sont incomplètes."]);
    }
}

function update($db) {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id)) {
        try {
            $query = "UPDATE apprenants
                      SET matricule=:matricule, nom=:nom, prenom=:prenom,
                          date_naissance=:date_naissance, sexe=:sexe, telephone=:telephone,
                          adresse=:adresse, filiere_id=:filiere_id, niveau_id=:niveau_id,
                          annee_id=:annee_id, statut=:statut
                      WHERE id = :id";

            $stmt = $db->prepare($query);

            $matricule = strip_tags($data->matricule);
            $nom = strip_tags($data->nom);
            $prenom = strip_tags($data->prenom);
            $date_naissance = strip_tags($data->date_naissance);
            $sexe = strip_tags($data->sexe);
            $filiere_id = strip_tags($data->filiere_id);
            $niveau_id = strip_tags($data->niveau_id);
            $annee_id = strip_tags($data->annee_id);
            $statut = strip_tags($data->statut);
            $id = strip_tags($data->id);

            $stmt->bindParam(":matricule", $matricule);
            $stmt->bindParam(":nom", $nom);
            $stmt->bindParam(":prenom", $prenom);
            $stmt->bindParam(":date_naissance", $date_naissance);
            $stmt->bindParam(":sexe", $sexe);
            $stmt->bindValue(":telephone", isset($data->telephone) ? strip_tags($data->telephone) : null);
            $stmt->bindValue(":adresse", isset($data->adresse) ? strip_tags($data->adresse) : null);
            $stmt->bindParam(":filiere_id", $filiere_id);
            $stmt->bindParam(":niveau_id", $niveau_id);
            $stmt->bindParam(":annee_id", $annee_id);
            $stmt->bindParam(":statut", $statut);
            $stmt->bindParam(":id", $id);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(["message" => "L'apprenant a été mis à jour."]);
            }
        } catch (PDOException $e) {
            http_response_code(503);
            if ($e->getCode() == 23000) {
                if (strpos($e->getMessage(), 'fk_apprenant_filiere') !== false) {
                    echo json_encode(["message" => "Filière invalide."]);
                } else if (strpos($e->getMessage(), 'fk_apprenant_niveau') !== false) {
                    echo json_encode(["message" => "Niveau invalide."]);
                } else if (strpos($e->getMessage(), 'fk_apprenant_annee') !== false) {
                    echo json_encode(["message" => "Année invalide."]);
                } else {
                    echo json_encode(["message" => "Erreur : ce matricule existe déjà."]);
                }
            } else {
                echo json_encode(["message" => "Impossible de mettre à jour l'apprenant."]);
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "ID manquant."]);
    }
}

function delete($db) {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id)) {
        try {
            $query = "DELETE FROM apprenants WHERE id = :id";
            $stmt = $db->prepare($query);
            $id = strip_tags($data->id);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(["message" => "L'apprenant a été supprimé."]);
            }
        } catch (PDOException $e) {
            http_response_code(503);
            echo json_encode(["message" => "Impossible de supprimer l'apprenant."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "ID manquant."]);
    }
}
?>