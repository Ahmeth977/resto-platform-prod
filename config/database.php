<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
<<<<<<< Updated upstream
        // VALEURS FIXES - PAS de getenv() !
        $this->host = '34.52.242.229';
        $this->db_name = 'resto_platform';
        $this->username = 'root';
        $this->password = '781155609';
        $this->port = 3306;
=======
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'resto_platform';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
        $this->port = getenv('DB_PORT') ?: 3306;
>>>>>>> Stashed changes
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
<<<<<<< Updated upstream
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
=======
            // Forcer la connexion TCP/IP avec le port
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_PERSISTENT => false, // Éviter les sockets
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $exception) {
            error_log("MySQL Connection Error: " . $exception->getMessage());
            error_log("Connection details: host={$this->host}, port={$this->port}, db={$this->db_name}");
>>>>>>> Stashed changes
            throw new Exception("Database connection failed: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
?>
