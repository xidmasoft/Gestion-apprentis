<?php

class Database {
    private $host = "localhost";
    private $db_name = "gestion_apprenants";
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $exception) {
            // Dans un environnement de production, on éviterait d'afficher l'erreur brute
            // mais on la mettrait dans un fichier de log.
            // Pour le moment on renvoie un JSON en cas d'échec pour être compatible avec l'API.
            http_response_code(500);
            echo json_encode(["message" => "Erreur de connexion à la base de données."]);
            exit;
        }

        return $this->conn;
    }
}
?>