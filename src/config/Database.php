<?php
class Database {
    private $host = "mysql";
    private $db_name = "api_db";
    private $username = "api_user";
    private $password = "api_password";
    public $conn;
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        if ($this->conn !== null) {
            return $this->conn;
        }
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            http_response_code(500);
            echo json_encode([
                "error" => "Database connection error",
                "message" => $exception->getMessage()
            ]);
            exit();
        }
        return $this->conn;
    }

    public function prepare($query) {
        $conn = $this->getConnection();
        if (!$conn) {
            http_response_code(500);
            echo json_encode(["error" => "No database connection"]);
            exit();
        }
        return $conn->prepare($query);
    }

    public function lastInsertId() {
        return $this->getConnection()->lastInsertId();
    }
}