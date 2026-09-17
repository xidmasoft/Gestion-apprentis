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
        $query = "SELECT id, libelle, date_debut, date_fin, actif FROM annees_formation ORDER BY libelle DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();

        $annees_arr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $annee_item = array(
                "id" => $id,
                "libelle" => htmlspecialchars_decode($libelle),
                "date_debut" => $date_debut,
                "date_fin" => $date_fin,
                "actif" => $actif
            );
            array_push($annees_arr, $annee_item);
        }

        http_response_code(200);
        echo json_encode($annees_arr);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Erreur lors de la récupération des années de formation."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Méthode non autorisée"]);
}
?>