<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        // CORRECTION: Utilisez le bon nom d'instance
        $this->host = getenv('DB_HOST') ?: '/cloudsql/sencommandes:europe-west1:resto-platform-db'; // ← CORRIGÉ
        $this->db_name = getenv('DB_NAME') ?: 'resto_platform';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '781155609'; // ← MOT DE PASSE AJOUTÉ
        $this->port = null;
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            if (strpos($this->host, '/cloudsql/') === 0) {
                $dsn = "mysql:unix_socket={$this->host};dbname={$this->db_name}";
            } else {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            }
            
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
        } catch(PDOException $exception) {
            error_log("MySQL Connection Error: " . $exception->getMessage());
            error_log("Trying to connect to: {$this->host}, db: {$this->db_name}");
            throw new Exception("Database connection failed: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
?>
