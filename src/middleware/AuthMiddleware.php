<?php
class AuthMiddleware
{
    private $db;
    private $user;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    // Check if request is authenticated
    public function authenticate()
    {
        // Get Authorization header - try multiple methods for compatibility
        $authHeader = null;
        
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? null;
        }
        
        // Fallback to $_SERVER
        if (!$authHeader && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (!$authHeader) {
            http_response_code(401);
            echo json_encode(array("message" => "No authorization token provided."));
            exit();
        }

        //Extract token (format: "Bearer <token>" or just "<token>")
        $token = str_replace('Bearer ', '', $authHeader);

        // Verify token
        if (!$this->user->getUserByToken($token)) {
            http_response_code(401);
            echo json_encode(array("message" => "Invalid or expired token."));
            exit();
        }

        // Token is valid, user data is now loaded in $this->user
        return $this->user;
    }
}
?>