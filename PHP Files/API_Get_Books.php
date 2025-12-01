<?php
// Set headers for JSON response and CORS
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, OPTIONS"); // Allow GET requests
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include the configuration file for database connection
require_once 'config.php'; 

// Check if it is a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Only GET method is allowed.']);
    exit();
}

// --- 1. Database Operation ---
try {
    $conn = connectDB(); 

    // SQL Query: Select all books that have an inventoryCount greater than 0
    $sql = "SELECT id, title, ISBN, price, rentalStatus, imageURL, altText, inventoryCount FROM BOOKS WHERE inventoryCount > 0 ORDER BY title ASC";
            
    $result = $conn->query($sql);
    
    $books = [];
    if ($result->num_rows > 0) {
        // Fetch all rows as an associative array
        while($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }

    // --- 2. Return Response ---
    http_response_code(200); // OK
    echo json_encode([
        'status' => 'success',
        'books' => $books,
        'count' => count($books)
    ]);

    // Clean up
    $conn->close();

} catch (Exception $e) {
    // Catch connection errors or other exceptions
    http_response_code(500);
    error_log("Get Books Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An internal server error occurred.',
        'exception' => $e->getMessage()
    ]);
}
?>