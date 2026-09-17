<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $query = "SELECT id, nom, description, actif FROM niveaux ORDER BY nom";
        $stmt = $db->prepare($query);
        $stmt->execute();

        $niveaux_arr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $niveau_item = array(
                "id" => $id,
                "nom" => htmlspecialchars_decode($nom),
                "description" => htmlspecialchars_decode($description),
                "actif" => $actif
            );
            array_push($niveaux_arr, $niveau_item);
        }

        http_response_code(200);
        echo json_encode($niveaux_arr);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Erreur lors de la récupération des niveaux."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Méthode non autorisée"]);
}
?>