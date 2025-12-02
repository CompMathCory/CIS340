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
if (empty($data['courseId']) || empty($data['bookId'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields: courseId and bookId.']);
    exit;
}

// Sanitize and extract data
$courseId = (int)$data['courseId'];
$bookId = (int)$data['bookId'];

try {
    $conn = connectDB();
    
    // Start transaction
    $conn->begin_transaction();

    // 2. Check if the course and book IDs exist (Optional but recommended for robustness)
    // Check Course Existence
    $stmt_course = $conn->prepare("SELECT id FROM COURSES WHERE id = ?");
    $stmt_course->bind_param("i", $courseId);
    $stmt_course->execute();
    $result_course = $stmt_course->get_result();
    if ($result_course->num_rows === 0) {
        $conn->rollback();
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => "Course ID $courseId not found."]);
        $stmt_course->close();
        $conn->close();
        exit;
    }
    $stmt_course->close();

    // Check Book Existence
    $stmt_book = $conn->prepare("SELECT id FROM BOOKS WHERE id = ?");
    $stmt_book->bind_param("i", $bookId);
    $stmt_book->execute();
    $result_book = $stmt_book->get_result();
    if ($result_book->num_rows === 0) {
        $conn->rollback();
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => "Book ID $bookId not found."]);
        $stmt_book->close();
        $conn->close();
        exit;
    }
    $stmt_book->close();

    // 3. Prepare SQL statement for insertion into COURSE_BOOKS
    // Note: The primary key for COURSE_BOOKS is the composite key (courseID, bookID).
    // If the combination already exists, the database will raise an error.
    $sql = "INSERT INTO COURSE_BOOKS (courseID, bookID) VALUES (?, ?)";
            
    $stmt = $conn->prepare($sql);
    
    // Bind parameters: courseID (i), bookID (i)
    $stmt->bind_param("ii", $courseId, $bookId);

    if ($stmt->execute()) {
        $conn->commit();
        
        http_response_code(201); // Created
        echo json_encode([
            'status' => 'success', 
            'message' => "Book ID $bookId successfully linked to Course ID $courseId."
        ]);
    } else {
        $conn->rollback();
        // Check for duplicate key error (1062) if the link already exists
        if ($conn->errno == 1062) {
             http_response_code(409); // Conflict
             echo json_encode(['status' => 'error', 'message' => "Link already exists: Book ID $bookId is already linked to Course ID $courseId."]);
        } else {
            http_response_code(500);
            error_log("Database Error: " . $conn->error);
            echo json_encode(['status' => 'error', 'message' => 'Link Course/Book Error: ' . $conn->error]);
        }
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>