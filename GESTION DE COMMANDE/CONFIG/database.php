<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'gestion_commandes';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $exception) {
            
            die("Erreur de connexion à la base de données: " . $exception->getMessage() . 
                "<br>Vérifiez que:<br>" .
                "- La base de données '" . $this->db_name . "' existe<br>" .
                "- L'utilisateur '" . $this->username . "' a les droits d'accès<br>" .
                "- Le serveur MySQL est démarré");
        }
        return $this->conn;
    }
}
?>