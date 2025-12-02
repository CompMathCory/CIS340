<?php
require_once 'config.php';

// Set response header to application/json
header('Content-Type: application/json');

// Check for POST request method (used as a workaround for blocked DELETE method)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use POST for delete operations in this environment.']);
    exit;
}

// Get JSON data from request body
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

// Check if the required bookId is present
if (empty($data['bookId'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required field: bookId.']);
    exit;
}

$bookId = intval($data['bookId']);

try {
    $conn = connectDB();

    // Prepare SQL statement for deletion
    $sql = "DELETE FROM BOOKS WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200); // OK
            echo json_encode([
                'status' => 'success', 
                'message' => "Book ID {$bookId} deleted successfully."
            ]);
        } else {
            // No row deleted (Book not found)
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => "Book ID {$bookId} not found."]);
        }
    } else {
        http_response_code(500);
        error_log("Database Error: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Delete Book Error: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>