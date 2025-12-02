<?php
require_once 'config.php';

// Set response header to application/json
header('Content-Type: application/json');

// Check for GET request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

try {
    $conn = connectDB();

    // Select all fields from the BOOKS table
    // We expect the ID to be an integer now.
    $sql = "SELECT id, title, ISBN, price, rentalStatus, imageURL, altText, inventoryCount FROM BOOKS ORDER BY title ASC";
    
    $stmt = $conn->prepare($sql);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $books = [];

        // Fetch all rows into an array
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }

        http_response_code(200); // OK
        echo json_encode([
            'status' => 'success', 
            'count' => count($books),
            'books' => $books
        ]);
        
    } else {
        http_response_code(500);
        error_log("Database Error: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch books: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>