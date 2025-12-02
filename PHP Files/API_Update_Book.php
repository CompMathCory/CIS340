<?php
require_once 'config.php';

// Set response header to application/json
header('Content-Type: application/json');

// --- MODIFICATION START ---
// Change method check from 'PUT' to 'POST' to bypass local server restrictions.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    // Note: We still mention PUT/PATCH are standard, but POST is allowed here for testing.
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use POST for updates in this environment.']);
    exit;
}
// --- MODIFICATION END ---

// Get JSON data from request body (for POST requests now)
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

// Check if the required bookId is present
if (empty($data['bookId'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required field: bookId.']);
    exit;
}

$bookId = intval($data['bookId']);

// Dynamically build the SET clause for the UPDATE query
$set_parts = [];
$bind_types = "";
$bind_values = [];

// Fields that can be updated
$updatable_fields = [
    'title' => 's', 
    'ISBN' => 's', 
    'price' => 'd', 
    'inventoryCount' => 'i',
    'imageURL' => 's',
    'altText' => 's'
];

// Iterate through the input data to build the query dynamically
foreach ($updatable_fields as $field => $type) {
    if (isset($data[$field])) {
        // Only include fields present in the request body
        $set_parts[] = "`{$field}` = ?";
        $bind_types .= $type;
        
        // Handle value conversion based on type
        if ($type === 'i') {
            $bind_values[] = intval($data[$field]);
        } elseif ($type === 'd') {
            $bind_values[] = floatval($data[$field]);
        } else {
            $bind_values[] = $data[$field];
        }
    }
}

// Check if any fields were actually provided for update
if (empty($set_parts)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No valid fields provided for update (title, ISBN, price, inventoryCount, etc.).']);
    exit;
}

// Add the bookId to the bind values and 'i' type for the WHERE clause
$bind_types .= "i";
$bind_values[] = $bookId;

try {
    $conn = connectDB();

    // The UPDATE query structure
    $sql = "UPDATE BOOKS SET " . implode(', ', $set_parts) . " WHERE id = ?";
            
    $stmt = $conn->prepare($sql);

    // Bind parameters dynamically
    // The first argument to bind_param must be a string containing the types
    $stmt->bind_param($bind_types, ...$bind_values);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200); // OK
            echo json_encode([
                'status' => 'success', 
                'message' => "Book ID {$bookId} updated successfully."
            ]);
        } else {
            // Book not found or data was identical (0 affected rows)
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => "Book ID {$bookId} not found or no changes were made."]);
        }
    } else {
        http_response_code(500);
        error_log("Database Error: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Update Book Error: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>