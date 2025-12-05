<?php
// login.php
include_once 'db_connection.php';

// Handle CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $data->email ?? '';
    $password = $data->password ?? '';

    // 1. Find user by email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Verify Password
    if ($user && password_verify($password, $user['password_hash'])) {
        // Remove sensitive hash from output
        unset($user['password_hash']);
        
        // CRITICAL FIX: Generate a fake token for the frontend
        $token = bin2hex(random_bytes(16));
        
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "user" => $user,
            "token" => $token  // <--- React needs this!
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "success" => false, 
            "message" => "Invalid email or password"
        ]);
    }
}
?>