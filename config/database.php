<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        // Utilisez l'IP publique TEMPORAIREMENT
        $this->host = getenv('DB_HOST') ?: '34.52.242.229'; // IP publique
        $this->db_name = getenv('DB_NAME') ?: 'resto_platform';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '781155609';
        $this->port = 3306; // Port MySQL standard
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            // Connexion TCP/IP pour contourner le problème de socket
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
        } catch(PDOException $exception) {
            error_log("MySQL Connection Error: " . $exception->getMessage());
            error_log("Trying to connect to: {$this->host}:{$this->port}, db: {$this->db_name}");
            throw new Exception("Database connection failed: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
?>
