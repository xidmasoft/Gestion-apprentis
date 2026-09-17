<?php
/**
 * Simple API Gateway / Proxy for DocumentRoot /public
 * Routes requests from /public/api.php?route=xxx to ../api/xxx.php
 */
$route = $_GET['route'] ?? '';
$allowed_routes = ['apprenants', 'filieres', 'niveaux', 'annees-formation'];

if (in_array($route, $allowed_routes)) {
    // Pass execution to the actual API endpoint
    require_once __DIR__ . '/../api/' . $route . '.php';
} else {
    http_response_code(404);
    echo json_encode(["message" => "Endpoint introuvable."]);
}
?>
