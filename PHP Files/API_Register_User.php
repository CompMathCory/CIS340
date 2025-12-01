<?php
// Set headers for JSON response and CORS
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow POST requests
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include the configuration file for database connection
require_once 'config.php'; 

// --- 1. Get and Validate Input ---
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Only POST method is allowed.']);
    exit();
}

// Basic input validation
if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields: username, password, or email.']);
    exit();
}

// Sanitize and prepare variables
$username = htmlspecialchars($data['username']);
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$password = $data['password'];

// Hash the password securely
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// --- 2. Database Operation ---
try {
    $conn = connectDB(); 

    // SQL Injection Prevention: Use a Prepared Statement
    $sql = "INSERT INTO USERS (username, email, password_hash) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // Bind parameters: 's' (string), 's' (string), 's' (string)
    $stmt->bind_param("sss", $username, $email, $password_hash);
    
    // Execute the statement
    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        http_response_code(201); // Created
        echo json_encode([
            'status' => 'success',
            'message' => 'User registered successfully.',
            'userId' => $newId
        ]);
    } else {
        // Check for duplicate entry error (e.g., username or email already exists)
        if ($conn->errno == 1062) {
             http_response_code(409); // Conflict
             echo json_encode(['status' => 'error', 'message' => 'Username or email already exists.']);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to register user.',
                'dbError' => $stmt->error
            ]);
        }
    }

    // Clean up
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Catch connection errors or other exceptions
    http_response_code(500);
    error_log("Registration Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An internal server error occurred.',
        'exception' => $e->getMessage()
    ]);
}
?>