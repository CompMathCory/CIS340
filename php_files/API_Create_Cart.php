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

// 1. Validate required input field
if (empty($data['userId']) || !is_numeric($data['userId'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid userId.']);
    exit;
}

// Sanitize and extract data
$userId = (int)$data['userId'];

try {
    $conn = connectDB();
    
    // We use INSERT IGNORE to prevent an error if a cart already exists for this unique userId.
    // If a cart already exists, IGNORE prevents the insertion and returns a success status, 
    // but the row count affected will be 0.
    $sql = "INSERT IGNORE INTO CARTS (userID, status) VALUES (?, 'active')";
            
    $stmt = $conn->prepare($sql);
    
    // Bind parameter: userID (i)
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        $rowsAffected = $stmt->affected_rows;

        if ($rowsAffected > 0) {
            $newCartId = $conn->insert_id;
            http_response_code(201); // Created
            echo json_encode([
                'status' => 'success', 
                'message' => "New cart created successfully for User ID $userId.", 
                'cartId' => $newCartId
            ]);
        } else {
            // This case means a cart already exists (due to INSERT IGNORE)
            // We need to fetch the existing cart ID to return it to the client
            $sql_fetch = "SELECT id FROM CARTS WHERE userID = ?";
            $stmt_fetch = $conn->prepare($sql_fetch);
            $stmt_fetch->bind_param("i", $userId);
            $stmt_fetch->execute();
            $result_fetch = $stmt_fetch->get_result();
            
            $existingCartId = null;
            if ($row = $result_fetch->fetch_assoc()) {
                $existingCartId = $row['id'];
            }
            $stmt_fetch->close();
            
            http_response_code(200); // OK
            echo json_encode([
                'status' => 'success', 
                'message' => "Cart already exists for User ID $userId. Existing cart ID returned.", 
                'cartId' => $existingCartId
            ]);
        }
    } else {
        http_response_code(500);
        error_log("Database Error: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Cart Creation Error: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>