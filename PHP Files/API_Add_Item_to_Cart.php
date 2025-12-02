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

// 1. Validate required input fields
// We use the ORDER_BOOKS table to hold cart items, using cartID in place of orderID initially.
if (empty($data['cartId']) || !is_numeric($data['cartId']) || 
    empty($data['bookId']) || !is_numeric($data['bookId']) || 
    empty($data['quantity']) || !is_numeric($data['quantity']) || (int)$data['quantity'] < 1) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid fields: cartId, bookId, or quantity (must be >= 1).']);
    exit;
}

// Sanitize and extract data
$cartId = (int)$data['cartId'];
$bookId = (int)$data['bookId'];
$quantity = (int)$data['quantity'];

try {
    $conn = connectDB();
    
    // Start transaction for consistency check and update
    $conn->begin_transaction();

    // Check inventory: Get current inventory and price
    $stmt_inv = $conn->prepare("SELECT price, inventoryCount FROM BOOKS WHERE id = ?");
    $stmt_inv->bind_param("i", $bookId);
    $stmt_inv->execute();
    $result_inv = $stmt_inv->get_result();
    
    if ($result_inv->num_rows === 0) {
        $conn->rollback();
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => "Book ID $bookId not found."]);
        $stmt_inv->close();
        $conn->close();
        exit;
    }
    
    $book = $result_inv->fetch_assoc();
    $currentInventory = (int)$book['inventoryCount'];
    $price = $book['price']; 
    $stmt_inv->close();

    // 2. Check if the requested quantity exceeds inventory
    if ($quantity > $currentInventory) {
        $conn->rollback();
        http_response_code(409); // Conflict
        echo json_encode(['status' => 'error', 'message' => "Insufficient inventory. Only $currentInventory copies of Book ID $bookId available."]);
        $conn->close();
        exit;
    }

    // 3. Check if the item already exists in the cart (using orderID column for cartID temporarily)
    $stmt_check = $conn->prepare("SELECT quantity FROM ORDER_BOOKS WHERE orderID = ? AND bookID = ?");
    $stmt_check->bind_param("ii", $cartId, $bookId);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $stmt_check->close();

    if ($result_check->num_rows > 0) {
        // Item exists, update quantity
        $row = $result_check->fetch_assoc();
        $newQuantity = (int)$row['quantity'] + $quantity;
        
        // Ensure updated quantity does not exceed inventory
        if ($newQuantity > $currentInventory) {
             $conn->rollback();
             http_response_code(409); // Conflict
             echo json_encode(['status' => 'error', 'message' => "Cannot add. Total quantity ($newQuantity) exceeds inventory ($currentInventory)."]);
             $conn->close();
             exit;
        }

        $stmt_update = $conn->prepare("UPDATE ORDER_BOOKS SET quantity = ? WHERE orderID = ? AND bookID = ?");
        $stmt_update->bind_param("iii", $newQuantity, $cartId, $bookId);
        $stmt_update->execute();
        $stmt_update->close();
        
        $actionMessage = "Updated quantity of Book ID $bookId in Cart ID $cartId to $newQuantity.";

    } else {
        // Item does not exist, insert new row
        $stmt_insert = $conn->prepare("INSERT INTO ORDER_BOOKS (orderID, bookID, quantity, priceAtTimeOfOrder) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("iids", $cartId, $bookId, $quantity, $price);
        $stmt_insert->execute();
        $stmt_insert->close();
        
        $actionMessage = "Added Book ID $bookId to Cart ID $cartId with quantity $quantity.";
    }

    // 4. Decrease the inventory count in the BOOKS table
    $stmt_dec = $conn->prepare("UPDATE BOOKS SET inventoryCount = inventoryCount - ? WHERE id = ?");
    $stmt_dec->bind_param("ii", $quantity, $bookId);
    $stmt_dec->execute();
    $stmt_dec->close();

    // Commit transaction
    $conn->commit();
    
    http_response_code(200); // OK
    echo json_encode([
        'status' => 'success', 
        'message' => $actionMessage,
        'cartId' => $cartId,
        'bookId' => $bookId
    ]);


} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>