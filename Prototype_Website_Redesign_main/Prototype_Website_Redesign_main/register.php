<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include_once 'db_connection.php';

$data = json_decode(file_get_contents("php://input"));

// 1. Validate Input
if (
    !isset($data->username) || 
    !isset($data->email) || 
    !isset($data->password)
) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Incomplete data"]);
    exit();
}

$username = htmlspecialchars(strip_tags($data->username));
$email = htmlspecialchars(strip_tags($data->email));
$password = $data->password;
$role = isset($data->role) ? $data->role : 'student';

try {
    // 2. Check if user already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $checkStmt->execute([$email, $username]);
    
    if ($checkStmt->rowCount() > 0) {
        http_response_code(409); // 409 Conflict
        echo json_encode(["success" => false, "message" => "Email or Username already exists"]);
        exit();
    }

    // 3. Hash the password (NEVER store plain text passwords)
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // 4. Insert User
    $sql = "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt->execute([$username, $email, $password_hash, $role])) {
        $newUserId = $conn->lastInsertId();
        
        // Return success matching api.ts RegisterResponse interface
        echo json_encode([
            "success" => true, 
            "message" => "Account created successfully",
            "user" => [
                "id" => $newUserId,
                "username" => $username,
                "email" => $email,
                "role" => $role
            ]
        ]);
    } else {
        throw new Exception("Execute failed");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>