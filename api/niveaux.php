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
        read($db);
        break;
    case 'POST':
        create($db);
        break;
    case 'PUT':
        update($db);
        break;
    case 'DELETE':
        delete($db);
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Méthode non autorisée"]);
        break;
}

function read($db) {
    try {
        $query = "SELECT id, nom, description, actif FROM niveaux ORDER BY nom";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            array_push($arr, $row);
        }
        http_response_code(200);
        echo json_encode($arr);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Erreur serveur."]);
    }
}

function create($db) {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->nom)) {
        try {
            $query = "INSERT INTO niveaux SET nom=:nom, description=:description, actif=:actif";
            $stmt = $db->prepare($query);
            $nom = strip_tags($data->nom);
            $stmt->bindParam(":nom", $nom);
            $stmt->bindValue(":description", isset($data->description) ? strip_tags($data->description) : null);
            $stmt->bindValue(":actif", isset($data->actif) ? $data->actif : 1);
            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(["message" => "Niveau créé."]);
            }
        } catch (PDOException $e) {
            http_response_code(503);
            echo json_encode(["message" => "Impossible de créer le niveau."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Nom obligatoire."]);
    }
}

function update($db) {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->id) && !empty($data->nom)) {
        try {
            $query = "UPDATE niveaux SET nom=:nom, description=:description, actif=:actif WHERE id=:id";
            $stmt = $db->prepare($query);
            $nom = strip_tags($data->nom);
            $stmt->bindParam(":nom", $nom);
            $stmt->bindValue(":description", isset($data->description) ? strip_tags($data->description) : null);
            $stmt->bindValue(":actif", isset($data->actif) ? $data->actif : 1);
            $id = strip_tags($data->id);
            $stmt->bindParam(":id", $id);
            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(["message" => "Niveau mis à jour."]);
            }
        } catch (PDOException $e) {
            http_response_code(503);
            echo json_encode(["message" => "Impossible de mettre à jour le niveau."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "ID et Nom obligatoires."]);
    }
}

function delete($db) {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->id)) {
        try {
            $query = "DELETE FROM niveaux WHERE id=:id";
            $stmt = $db->prepare($query);
            $id = strip_tags($data->id);
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(["message" => "Niveau supprimé."]);
            }
        } catch (PDOException $e) {
            http_response_code(503);
            if ($e->getCode() == 23000) {
                // Contrainte de clé étrangère
                $query = "UPDATE niveaux SET actif=0 WHERE id=:id";
                $stmt = $db->prepare($query);
                $id = strip_tags($data->id);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                echo json_encode(["message" => "Impossible de supprimer ce niveau car des apprenants y sont liés. Il a été désactivé."]);
            } else {
                echo json_encode(["message" => "Erreur lors de la suppression."]);
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "ID manquant."]);
    }
}
?>
