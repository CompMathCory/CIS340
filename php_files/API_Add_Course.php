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

// Check if required fields are present (Includes 'semester')
if (empty($data['name']) || empty($data['code']) || empty($data['instructor']) || empty($data['semester'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields: name, code, instructor, or semester.']);
    exit;
}

// Sanitize and extract data
$name = $data['name'];
$code = $data['code'];
$instructor = $data['instructor'];
$semester = $data['semester'];

try {
    $conn = connectDB();

    // Prepare SQL statement for insertion into COURSES table (Uses name, code, instructor, semester)
    $sql = "INSERT INTO COURSES (name, code, instructor, semester) 
            VALUES (?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    
    // Bind parameters: (name:s, code:s, instructor:s, semester:s)
    $stmt->bind_param("ssss", $name, $code, $instructor, $semester);

    if ($stmt->execute()) {
        // This relies on the COURSES table using AUTO_INCREMENT, which is confirmed in your setup_db.php
        $new_course_id = $conn->insert_id; 
        
        http_response_code(201); // Created
        echo json_encode([
            'status' => 'success', 
            'message' => 'Course added successfully.', 
            'courseId' => $new_course_id
        ]);
    } else {
        http_response_code(500);
        error_log("Database Error: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'Add Course Error: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>