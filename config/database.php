<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        // FORCER la connexion TCP/IP avec l'IP publique
        $this->host = getenv('DB_HOST') ?: '34.52.242.229'; // IP directe
        $this->db_name = getenv('DB_NAME') ?: 'resto_platform';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
        $this->port = 3306; // Port forcé
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            // Connexion TCP/IP explicite
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
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
