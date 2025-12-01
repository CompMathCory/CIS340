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
if (empty($data['title']) || empty($data['ISBN']) || !isset($data['price']) || !isset($data['inventoryCount'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields: title, ISBN, price, or inventoryCount.']);
    exit();
}

// Sanitize and prepare variables
$title = htmlspecialchars($data['title']);
$isbn = htmlspecialchars($data['ISBN']);
$price = (float)$data['price']; 
$inventoryCount = (int)$data['inventoryCount']; 
$imageURL = isset($data['imageURL']) ? htmlspecialchars($data['imageURL']) : 'https://placehold.co/150x200/cccccc/333333?text=No+Cover';
$altText = isset($data['altText']) ? htmlspecialchars($data['altText']) : 'Default book cover placeholder.';
$rentalStatus = 'available'; // New books are always available by default

// --- 2. Database Operation ---
try {
    $conn = connectDB(); 

    // SQL Injection Prevention: Use a Prepared Statement for insertion
    $sql = "INSERT INTO BOOKS (title, ISBN, price, rentalStatus, imageURL, altText, inventoryCount) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    
    // Bind parameters: 's' (string), 's' (string), 'd' (double/float), 's' (string), 's' (string), 's' (string), 'i' (integer)
    $stmt->bind_param("ssisssi", $title, $isbn, $price, $rentalStatus, $imageURL, $altText, $inventoryCount);
    
    // Execute the statement
    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        http_response_code(201); // Created
        echo json_encode([
            'status' => 'success',
            'message' => 'Book added successfully.',
            'bookId' => $newId
        ]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add book to the database.',
            'dbError' => $stmt->error
        ]);
    }

    // Clean up
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Catch connection errors or other exceptions
    http_response_code(500);
    error_log("Add Book Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An internal server error occurred.',
        'exception' => $e->getMessage()
    ]);
}
?>