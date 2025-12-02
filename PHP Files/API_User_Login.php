<?php
require_once 'config.php';

// Set response header to application/json
header('Content-Type: application/json');

// Check for POST request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

// Get JSON data from request body
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

// Check if required fields are present (username or email, and password)
if (empty($data['identifier']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields: identifier (username or email) and password.']);
    exit;
}

$identifier = $data['identifier']; // Could be username or email
$password = $data['password'];

try {
    $conn = connectDB();

    // Prepare SQL statement to select the user by username OR email
    // This allows the user to log in using either field.
    $sql = "SELECT id, username, email, password_hash, role FROM USERS WHERE username = ? OR email = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // User found, now verify the password
        if (password_verify($password, $user['password_hash'])) {
            // Password is correct
            
            // NOTE: In a real app, you would generate a JWT token here.
            // For now, we return basic user info for the client to store.
            
            http_response_code(200); // OK
            echo json_encode([
                'status' => 'success', 
                'message' => 'Login successful.',
                'user' => [
                    'userId' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            // Password verification failed
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Invalid username/email or password.']);
        }
    } else {
        // User not found
        http_response_code(401); // Unauthorized
        echo json_encode(['status' => 'error', 'message' => 'Invalid username/email or password.']);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>