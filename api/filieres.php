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
        $query = "SELECT id, nom, description, actif FROM filieres ORDER BY nom";
        $stmt = $db->prepare($query);
        $stmt->execute();

        $filieres_arr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $filiere_item = array(
                "id" => $id,
                "nom" => htmlspecialchars_decode($nom),
                "description" => htmlspecialchars_decode($description),
                "actif" => $actif
            );
            array_push($filieres_arr, $filiere_item);
        }

        http_response_code(200);
        echo json_encode($filieres_arr);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Erreur lors de la récupération des filières."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Méthode non autorisée"]);
}
?>