<?php

class ImageController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function handlePost()
    {
        error_log("=== ImageController::handlePost START ===");
        
        try {
            error_log("Getting auth token...");
            $token = $this->getAuthToken();
            
            if (!$token) {
                error_log("No token found");
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                return;
            }
            
            error_log("Token: " . substr($token, 0, 20) . "...");

            // Get user from token
            error_log("Preparing user query...");
            $userQuery = $this->db->prepare("SELECT id FROM users WHERE api_token = ?");
            if (!$userQuery) {
                throw new Exception("Failed to prepare user query");
            }
            
            error_log("Executing user query...");
            $userQuery->execute([$token]);
            $user = $userQuery->fetch();

            if (!$user) {
                error_log("User not found for token");
                http_response_code(401);
                echo json_encode(['error' => 'User not found']);
                return;
            }

            $userId = $user['id'];
            error_log("User ID: $userId");
            
            // Parse JSON request body
            error_log("Reading request body...");
            $rawInput = file_get_contents('php://input');
            error_log("Raw input length: " . strlen($rawInput));
            
            $input = json_decode($rawInput, true);
            error_log("Decoded input: " . json_encode(array_keys($input ?? [])));
            
            $bookingId = $input['booking_id'] ?? null;
            $imageName = $input['image_name'] ?? 'image_' . time();
            $imageData = $input['image_data'] ?? null;

            error_log("Booking ID: $bookingId");
            error_log("Image Name: $imageName");
            error_log("Image Data length: " . strlen($imageData ?? ''));

            if (!$bookingId) {
                error_log("booking_id is required");
                http_response_code(400);
                echo json_encode(['error' => 'booking_id is required']);
                return;
            }

            // Verify booking belongs to user
            error_log("Verifying booking...");
            $bookingQuery = $this->db->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ?");
            if (!$bookingQuery) {
                throw new Exception("Failed to prepare booking query");
            }
            $bookingQuery->execute([$bookingId, $userId]);
            $booking = $bookingQuery->fetch();

            if (!$booking) {
                error_log("Booking not found or unauthorized");
                http_response_code(403);
                echo json_encode(['error' => 'Booking not found or unauthorized']);
                return;
            }

            // Check if image data is provided
            if (!$imageData) {
                error_log("image_data is required");
                http_response_code(400);
                echo json_encode(['error' => 'image_data is required']);
                return;
            }

            // Decode base64 image
            error_log("Decoding base64 image...");
            $decodedImage = base64_decode($imageData, true);
            if ($decodedImage === false) {
                error_log("Invalid base64 data");
                http_response_code(400);
                echo json_encode(['error' => 'Invalid image data']);
                return;
            }
            
            error_log("Decoded image size: " . strlen($decodedImage) . " bytes");

            // Insert image into database
            error_log("Inserting image into database...");
            $insertQuery = $this->db->prepare(
                "INSERT INTO booking_images (booking_id, user_id, image_data, image_name) 
                 VALUES (?, ?, ?, ?)"
            );
            if (!$insertQuery) {
                throw new Exception("Failed to prepare insert query");
            }
            $insertQuery->execute([$bookingId, $userId, $decodedImage, $imageName]);

            error_log("Image inserted successfully");
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'image_id' => $this->db->lastInsertId()
            ]);
        } catch (Exception $e) {
            error_log("Exception in handlePost: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
        
        error_log("=== ImageController::handlePost END ===");
    }

    public function handleGet()
    {
        error_log("=== ImageController::handleGet START ===");
        
        try {
            $token = $this->getAuthToken();
            if (!$token) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                return;
            }

            // Get user from token
            $userQuery = $this->db->prepare("SELECT id FROM users WHERE api_token = ?");
            if (!$userQuery) {
                throw new Exception("Failed to prepare user query");
            }
            $userQuery->execute([$token]);
            $user = $userQuery->fetch();

            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'User not found']);
                return;
            }

            $userId = $user['id'];
            error_log("Authenticated user: $userId");
            
            // Check if requesting a specific image by ID
            $imageId = $_GET['image_id'] ?? null;
            error_log("Checking for image_id parameter: " . ($imageId ? $imageId : 'not set'));
            
            if ($imageId) {
                error_log("=== Fetching specific image: $imageId ===");
                // Get specific image data
                $query = $this->db->prepare(
                    "SELECT image_data 
                     FROM booking_images 
                     WHERE id = ? AND user_id = ?"
                );
                if (!$query) {
                    throw new Exception("Failed to prepare query");
                }
                $query->execute([$imageId, $userId]);
                $image = $query->fetch();

                if (!$image) {
                    error_log("Image not found for id: $imageId");
                    http_response_code(404);
                    echo json_encode(['error' => 'Image not found']);
                    return;
                }

                error_log("Found image data, size: " . strlen($image['image_data']));
                
                // Clear output buffers and send binary image data
                if (ob_get_level()) {
                    ob_end_clean();
                }
                
                http_response_code(200);
                header('Content-Type: image/jpeg');
                header('Content-Length: ' . strlen($image['image_data']));
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                
                error_log("Sending image data to client");
                // Send the binary data
                echo $image['image_data'];
                exit();
            }

            $bookingId = $_GET['booking_id'] ?? null;

            if ($bookingId) {
                // Get images for specific booking
                $query = $this->db->prepare(
                    "SELECT id, booking_id, image_name, uploaded_at 
                     FROM booking_images 
                     WHERE booking_id = ? AND user_id = ? 
                     ORDER BY uploaded_at DESC"
                );
                if (!$query) {
                    throw new Exception("Failed to prepare query");
                }
                $query->execute([$bookingId, $userId]);
            } else {
                // Get all images for user
                $query = $this->db->prepare(
                    "SELECT id, booking_id, image_name, uploaded_at 
                     FROM booking_images 
                     WHERE user_id = ? 
                     ORDER BY uploaded_at DESC"
                );
                if (!$query) {
                    throw new Exception("Failed to prepare query");
                }
                $query->execute([$userId]);
            }

            $images = $query->fetchAll();

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $images
            ]);
        } catch (Exception $e) {
            error_log("Exception in handleGet: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
        
        error_log("=== ImageController::handleGet END ===");
    }

    public function handleDelete()
    {
        error_log("=== ImageController::handleDelete START ===");
        
        try {
            $token = $this->getAuthToken();
            if (!$token) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                return;
            }

            // Get user from token
            $userQuery = $this->db->prepare("SELECT id FROM users WHERE api_token = ?");
            if (!$userQuery) {
                throw new Exception("Failed to prepare user query");
            }
            $userQuery->execute([$token]);
            $user = $userQuery->fetch();

            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'User not found']);
                return;
            }

            $userId = $user['id'];
            
            // Parse JSON request body
            $input = json_decode(file_get_contents('php://input'), true);
            $imageId = $input['image_id'] ?? null;

            if (!$imageId) {
                http_response_code(400);
                echo json_encode(['error' => 'image_id is required']);
                return;
            }

            // Verify image belongs to user
            $imageQuery = $this->db->prepare("SELECT id FROM booking_images WHERE id = ? AND user_id = ?");
            if (!$imageQuery) {
                throw new Exception("Failed to prepare image query");
            }
            $imageQuery->execute([$imageId, $userId]);
            $image = $imageQuery->fetch();

            if (!$image) {
                http_response_code(403);
                echo json_encode(['error' => 'Image not found or unauthorized']);
                return;
            }

            // Delete image
            error_log("Deleting image $imageId");
            $deleteQuery = $this->db->prepare("DELETE FROM booking_images WHERE id = ?");
            if (!$deleteQuery) {
                throw new Exception("Failed to prepare delete query");
            }
            $deleteQuery->execute([$imageId]);

            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
        } catch (Exception $e) {
            error_log("Exception in handleDelete: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
        
        error_log("=== ImageController::handleDelete END ===");
    }

    private function getAuthToken()
    {
        // Try getallheaders() first (works on Apache)
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
        }
        
        // Fallback to $_SERVER (works on nginx/other servers)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }
        
        return null;
    }
}
