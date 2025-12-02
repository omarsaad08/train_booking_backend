<?php
// Start output buffering to catch any stray output
ob_start();

// Enable all error logging
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Origin, Accept");

// Error handling
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    http_response_code(500);
    error_log("PHP Error [$errno]: $errstr in $errfile:$errline");
    echo json_encode([
        "error" => "PHP Error",
        "message" => $errstr,
        "file" => basename($errfile),
        "line" => $errline
    ]);
    exit();
});

set_exception_handler(function($exception) {
    ob_clean();
    http_response_code(500);
    error_log("Exception: " . $exception->getMessage());
    echo json_encode([
        "error" => "Exception",
        "message" => $exception->getMessage()
    ]);
    exit();
});

try {
    error_log("=== Request Started ===");
    error_log("Method: " . $_SERVER['REQUEST_METHOD']);
    error_log("URI: " . $_SERVER['REQUEST_URI']);
    error_log("Path Info: " . ($_SERVER['PATH_INFO'] ?? 'N/A'));
    
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        ob_end_clean();
        http_response_code(200);
        exit();
    }

    include_once 'config/Database.php';
    include_once 'models/User.php';
    include_once 'models/Booking.php';
    include_once 'middleware/AuthMiddleware.php';
    include_once 'controllers/UserController.php';
    include_once 'controllers/BookingController.php';
    include_once 'controllers/ImageController.php';

    $request_method = $_SERVER["REQUEST_METHOD"];
    
    // Get the path - try REQUEST_URI first, then PATH_INFO
    $path = '';
    if (isset($_SERVER['REQUEST_URI'])) {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }
    if (empty($path) && isset($_SERVER['PATH_INFO'])) {
        $path = $_SERVER['PATH_INFO'];
    }
    
    $path_segments = explode('/', trim($path, '/'));

    // Simple Router
    if (isset($path_segments[0]) && !empty($path_segments[0])) {
        $resource = $path_segments[0];

        switch ($resource) {
            case 'signup':
                $controller = new UserController();
                if ($request_method === 'POST') {
                    $controller->signup();
                } else {
                    http_response_code(405);
                    echo json_encode(array("message" => "Method not allowed. Use POST."));
                }
                exit();
            case 'login':
                $controller = new UserController();
                if ($request_method === 'POST') {
                    $controller->login();
                } else {
                    http_response_code(405);
                    echo json_encode(array("message" => "Method not allowed. Use POST."));
                }
                exit();
            case 'users':
                // Authenticate for user endpoints
                $auth = new AuthMiddleware();
                $auth->authenticate();
                $controller = new UserController();
                break;
            case 'bookings':
                // Authenticate for booking endpoints
                $auth = new AuthMiddleware();
                $auth->authenticate();
                $controller = new BookingController();
                break;
            case 'images':
                // Authenticate for image endpoints
                $auth = new AuthMiddleware();
                $auth->authenticate();
                $controller = new ImageController();
                break;
            default:
                http_response_code(404);
                echo json_encode(array("message" => "Resource not found"));
                exit();
        }

        switch ($request_method) {
            case 'GET':
                $controller->handleGet();
                break;

            case 'POST':
                $controller->handlePost();
                break;

            case 'PUT':
                $controller->handlePut();
                break;

            case 'DELETE':
                $controller->handleDelete();
                break;

            default:
                http_response_code(405);
                echo json_encode(array("message" => "Method not allowed"));
                break;
        }
    } else {
        // Root path - display API documentation
        http_response_code(200);
        header("Content-Type: text/html; charset=UTF-8");

        // Read and display the markdown file as HTML
        $docsFile = __DIR__ . '/../API_DOCUMENTATION.md';
        if (file_exists($docsFile)) {
            $markdown = file_get_contents($docsFile);
            // Simple markdown to HTML conversion for display
            $html = '<html><head><title>Train Booking API Documentation</title>';
            $html .= '<style>body{font-family:Arial,sans-serif;max-width:1200px;margin:40px auto;padding:0 20px;line-height:1.6;}';
            $html .= 'h1{color:#333;border-bottom:2px solid #333;}h2{color:#555;margin-top:30px;}';
            $html .= 'table{border-collapse:collapse;width:100%;margin:20px 0;}';
            $html .= 'th,td{border:1px solid #ddd;padding:12px;text-align:left;}';
            $html .= 'th{background-color:#f4f4f4;font-weight:bold;}';
            $html .= 'code{background:#f4f4f4;padding:2px 6px;border-radius:3px;font-family:monospace;}';
            $html .= 'pre{background:#f4f4f4;padding:15px;border-radius:5px;overflow-x:auto;}';
            $html .= 'pre code{background:none;padding:0;}</style></head><body>';
            $html .= '<pre>' . htmlspecialchars($markdown) . '</pre>';
            $html .= '</body></html>';
            echo $html;
        } else {
            echo '<html><body><h1>Train Booking API</h1><p>Welcome to the Train Booking API. Documentation not found.</p></body></html>';
        }
    }
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        "error" => "Internal server error",
        "message" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
}

ob_end_flush();
?>