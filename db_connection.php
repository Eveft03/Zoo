<?php
class Database {
    private $host = "localhost";
    private $username = "student_2410";
    private $password = "pass2410";
    private $database = "student_2410"; // Βεβαιώσου ότι υπάρχει η βάση με αυτό το ακριβές όνομα
    private $conn;

    public function __construct() {
        try {
            $this->conn = mysqli_connect($this->host, $this->username, $this->password, $this->database);
            
            if (!$this->conn) {
                throw new Exception(mysqli_connect_error());
            }

            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            // Μπορείς να αλλάξεις σε json_encode για να βλέπεις σε JSON μορφή το σφάλμα αν χρειαστεί
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "status" => "error",
                "message" => "Database connection error: " . $e->getMessage()
            ]);
            exit;
        }
    }

    public function getConnection() {
        if (!$this->conn) {
            throw new Exception("No database connection");
        }
        return $this->conn;
    }

    public function prepare($sql) {
        $stmt = $this->conn->prepare($sql);
        if(!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        return $stmt;
    }

    public function query($sql) {
        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("Query error: " . $this->conn->error);
        }
        return $result;
    }

    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
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

function getDatabase() {
    static $db = null;
    if ($db === null) {
        $db = new Database();
    }
    return $db->getConnection();
}
