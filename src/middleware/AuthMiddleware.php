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
        // Get Authorization header
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

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