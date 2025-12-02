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

// Basic input validation: Check if book ID, status, and user ID are provided
if (empty($data['bookId']) || empty($data['rentalStatus']) || empty($data['userId'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields: bookId, rentalStatus, or userId.']);
    exit();
}

// Sanitize and prepare variables
$bookId = (int)$data['bookId'];
$rentalStatus = htmlspecialchars($data['rentalStatus']); // e.g., 'rented' or 'available'
$userId = htmlspecialchars($data['userId']); // The user performing the action

// --- 2. Database Operation using Transactions for Safety ---
// We use a transaction to ensure both status and inventory are updated safely.
try {
    $conn = connectDB(); 
    $conn->begin_transaction(); // Start transaction

    // --- A. Fetch current book data and lock the row ---
    // SELECT FOR UPDATE locks the row to prevent simultaneous updates (race conditions)
    $sql_select = "SELECT inventoryCount FROM BOOKS WHERE id = ? FOR UPDATE";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $bookId);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $book = $result->fetch_assoc();
    $stmt_select->close();

    if (!$book) {
        $conn->rollback();
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Book not found.']);
        exit();
    }

    $currentCount = $book['inventoryCount'];

    // --- B. Determine Update Action and Perform Inventory Change ---
    $inventory_delta = 0; 
    
    if ($rentalStatus === 'rented') {
        if ($currentCount <= 0) {
            $conn->rollback();
            http_response_code(409); // Conflict: Cannot rent out-of-stock book
            echo json_encode(['status' => 'error', 'message' => 'Book is out of stock. Cannot rent.']);
            exit();
        }
        $inventory_delta = -1; // Decrement inventory
    } elseif ($rentalStatus === 'available') { 
        $inventory_delta = 1; // Increment inventory (return)
    }

    // --- C. Update the Book Record ---
    $sql_update = "UPDATE BOOKS 
                   SET inventoryCount = inventoryCount + ?, rentalStatus = ? 
                   WHERE id = ?";
            
    $stmt_update = $conn->prepare($sql_update);
    
    // Bind parameters: 'i' (integer), 's' (string), 'i' (integer)
    $stmt_update->bind_param("isi", $inventory_delta, $rentalStatus, $bookId);
    
    if ($stmt_update->execute()) {
        $conn->commit(); // Commit the transaction only if the update was successful
        http_response_code(200); // OK
        echo json_encode([
            'status' => 'success',
            'message' => "Book ID {$bookId} updated. Inventory changed by {$inventory_delta}.",
            'newStatus' => $rentalStatus
        ]);
    } else {
        $conn->rollback(); // Rollback on failure
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update book status and inventory.',
            'dbError' => $stmt_update->error
        ]);
    }

    // Clean up
    $stmt_update->close();
    $conn->close();

} catch (Exception $e) {
    // Catch connection errors or other exceptions
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback(); // Ensure rollback if an exception occurs mid-transaction
    }
    http_response_code(500);
    error_log("Rent/Return Book Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An internal server error occurred during transaction.',
        'exception' => $e->getMessage()
    ]);
}
?>