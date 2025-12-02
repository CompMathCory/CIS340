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

// Check for required courseId query parameter
if (!isset($_GET['courseId']) || !is_numeric($_GET['courseId'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid courseId parameter.']);
    exit;
}

// Sanitize and extract data
$courseId = (int)$_GET['courseId'];

try {
    $conn = connectDB();
    
    // SQL uses JOIN to link COURSES, COURSE_BOOKS, and BOOKS tables
    // We select all book columns for the given courseId
    $sql = "SELECT B.id, B.title, B.ISBN, B.price, B.rentalStatus, B.imageURL, B.altText, B.inventoryCount 
            FROM BOOKS B
            JOIN COURSE_BOOKS CB ON B.id = CB.bookID
            WHERE CB.courseID = ?";
            
    $stmt = $conn->prepare($sql);
    
    // Bind the courseId parameter
    $stmt->bind_param("i", $courseId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $books = [];
        
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }

        if (empty($books)) {
            // Course found but no books linked, or course not found
            // We can return a successful but empty list, or check course existence explicitly
            // For simplicity here, we return an empty list.
            http_response_code(200); 
            echo json_encode([
                'status' => 'success', 
                'message' => "No books found for Course ID $courseId.",
                'courseId' => $courseId,
                'books' => $books
            ]);
        } else {
            http_response_code(200);
            echo json_encode([
                'status' => 'success', 
                'message' => "Successfully retrieved books for Course ID $courseId.",
                'courseId' => $courseId,
                'books' => $books
            ]);
        }
    } else {
        http_response_code(500);
        error_log("Database Error: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Query execution failed: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>