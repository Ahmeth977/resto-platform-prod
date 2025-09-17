<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        // VALEURS FIXES - PAS de getenv() !
        $this->host = '34.52.242.229';
        $this->db_name = 'resto_platform';
        $this->username = 'root';
        $this->password = '781155609';
        $this->port = 3306;
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            // Connexion TCP/IP forcée
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8";
            
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => false
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
