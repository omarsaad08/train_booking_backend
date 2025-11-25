<?php
class UserController
{
    private $db;
    private $user;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    // GET - Get user by ID or Email
    public function handleGet()
    {
        if (isset($_GET['id'])) {
            $this->getById($_GET['id']);
        } else if (isset($_GET['email'])) {
            $this->getByEmail($_GET['email']);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Please provide id or email parameter."));
        }
    }

    // Get user by ID
    public function getById($id)
    {
        $this->user->id = $id;

        if ($this->user->readOne()) {
            $user_item = array(
                "id" => $this->user->id,
                "name" => $this->user->name,
                "email" => $this->user->email,
                "created_at" => $this->user->created_at
            );
            http_response_code(200);
            echo json_encode($user_item);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "User not found."));
        }
    }

    // Get user by Email
    public function getByEmail($email)
    {
        $this->user->email = $email;

        if ($this->user->readByEmail()) {
            $user_item = array(
                "id" => $this->user->id,
                "name" => $this->user->name,
                "email" => $this->user->email,
                "created_at" => $this->user->created_at
            );
            http_response_code(200);
            echo json_encode($user_item);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "User not found."));
        }
    }

    // Signup new user
    public function signup()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->name) && !empty($data->email) && !empty($data->password)) {
            // Check if user already exists
            $this->user->email = $data->email;
            if ($this->user->readByEmail()) {
                http_response_code(409);
                echo json_encode(array("message" => "User with this email already exists."));
                return;
            }

            $this->user->name = $data->name;
            $this->user->email = $data->email;
            $this->user->password = password_hash($data->password, PASSWORD_BCRYPT);

            if ($this->user->create()) {
                // Get the newly created user ID
                $this->user->id = $this->db->lastInsertId();

                // Generate and save token
                $token = $this->user->generateToken();
                $this->user->updateToken($token);

                http_response_code(201);
                echo json_encode(array("token" => $token, "user_id" => $this->user->id));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create user."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Signup failed. Name, email, and password are required."));
        }
    }

    // Login user
    public function login()
    {
        $rawInput = file_get_contents("php://input");
        $data = json_decode($rawInput);

        // Debug logging
        error_log("=== LOGIN DEBUG ===");
        error_log("Raw input: " . $rawInput);
        error_log("Email: " . (isset($data->email) ? $data->email : 'MISSING'));
        error_log("Password provided: " . (!empty($data->password) ? 'YES' : 'NO'));

        if (!empty($data->email) && !empty($data->password)) {
            $this->user->email = $data->email;

            if ($this->user->readByEmail()) {
                error_log("User found: " . $this->user->email);
                // Verify password
                if (password_verify($data->password, $this->user->password)) {
                    error_log("Password verified successfully");
                    // Generate and save new token
                    $token = $this->user->generateToken();
                    $this->user->updateToken($token);

                    http_response_code(200);
                    echo json_encode(array("token" => $token, "user_id" => $this->user->id));
                } else {
                    error_log("Password verification FAILED");
                    http_response_code(401);
                    echo json_encode(array("message" => "Invalid credentials."));
                }
            } else {
                error_log("User NOT found: " . $data->email);
                http_response_code(401);
                echo json_encode(array("message" => "Invalid credentials."));
            }
        } else {
            error_log("Missing email or password");
            http_response_code(400);
            echo json_encode(array("message" => "Login failed. Email and password are required."));
        }
    }

    // POST handler - no longer used for auth
    public function handlePost()
    {
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed. Use /signup or /login endpoints."));
    }

    // PUT and DELETE methods are removed as per requirements
    public function handlePut()
    {
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed for users endpoint."));
    }

    public function handleDelete()
    {
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed for users endpoint."));
    }
}
?>