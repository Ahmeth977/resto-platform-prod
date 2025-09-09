<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        // Utilisez le socket Unix pour Google Cloud SQL
        $this->host = getenv('DB_HOST') ?: '/cloudsql/commandes:europe-west1:resto-db';
        $this->db_name = getenv('DB_NAME') ?: 'resto_platform';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: ''; // ← IMPORTANT!
        $this->port = null; // Null pour socket Unix
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            if (strpos($this->host, '/cloudsql/') === 0) {
                // Connexion socket Unix (Google Cloud)
                $dsn = "mysql:unix_socket={$this->host};dbname={$this->db_name}";
            } else {
                // Connexion TCP/IP standard
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
