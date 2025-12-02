<?php
require_once 'config.php';

// Set response header to application/json
header('Content-Type: application/json');

// Function to generate a simple UUID-like string (CHAR(36) compliant)
function generate_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

// Check for POST request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

// Get JSON data from request body
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

// Check if required fields are present
if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields: username, password, or email.']);
    exit;
}

$username = $data['username'];
$password = $data['password'];
$email = $data['email'];

// --- Database Logic Starts Here ---
try {
    $conn = connectDB();

    // 1. Generate the unique ID (Necessary for CHAR(36) Primary Key)
    $uuid = generate_uuid();
    
    // 2. Hash the password for security
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'customer'; // Default role

    // Check if user or email already exists (Security Check)
    $stmt = $conn->prepare("SELECT id FROM USERS WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        http_response_code(409); // Conflict
        echo json_encode(['status' => 'error', 'message' => 'Username or email already exists.']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Prepare SQL statement for insertion (Core Logic)
    // NOTE: We MUST explicitly list the ID here because it's not AUTO_INCREMENT.
    $sql = "INSERT INTO USERS (id, username, email, password_hash, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // Bind parameters: (id, username, email, password_hash, role)
    $stmt->bind_param("sssss", $uuid, $username, $email, $password_hash, $role);

    if ($stmt->execute()) {
        http_response_code(201); // Created
        echo json_encode([
            'status' => 'success', 
            'message' => 'User registered successfully.', 
            'userId' => $uuid
        ]);
    } else {
        http_response_code(500);
        error_log("Database Error: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Registration Error: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>