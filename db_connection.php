
<?php
class Database {
    private $host = "lessons.dcie.teiemt.gr";
    private $username = "student_2410";
    private $password = "pass2410";
    private $database = "ZWOLOGIKOS_KHPOS";
    private $conn;

    public function __construct() {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        
        try {
            $this->conn = new mysqli(
                $this->host, 
                $this->username, 
                $this->password, 
                $this->database
            );
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }

            // Set character set to UTF-8 for full Unicode support
            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            die(json_encode([
                'status' => 'error',
                'message' => 'Database connection error'
            ]));
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    public function query($sql) {
        $result = $this->conn->query($sql);
        if (!$result) {
            error_log("Query error: " . $this->conn->error);
            throw new Exception("Σφάλμα στο ερώτημα της βάσης δεδομένων");
        }
        return $result;
    }

    public function escapeString($string) {
        return $this->conn->real_escape_string($string);
    }

    public function close() {
        $this->conn->close();
    }

    public function beginTransaction() {
        $this->conn->begin_transaction();
    }

    public function commit() {
        $this->conn->commit();
    }

    public function rollback() {
        $this->conn->rollback();
    }
}

// Singleton instance
function getDatabase() {
    static $db = null;
    if ($db === null) {
        $db = new Database();
    }
    return $db;
}
?>