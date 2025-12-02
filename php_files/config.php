<?php
// --- Database Credentials ---
	define('DB_HOST', getenv('db_host')); //server
    define('DB_USER', getenv('db_user')); //username
    define('DB_PASS', getenv('db_pass')); //password
    define('DB_NAME', 'book_rental_system'); //database  

/**
 * Establishes a MySQLi connection to the database.
 * @return mysqli The MySQLi connection object.
 */
function connectDB(): mysqli {
    // Suppress the default error warning (we handle it below)
    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if (!$conn) {
        // Log the detailed error internally
        error_log("MySQLi Connection Error: " . mysqli_connect_error());

        // Return a clean JSON error response to the frontend
        http_response_code(500);
        exit(json_encode([
            'status' => 'error',
            'message' => 'Database connection failed.',
            // DO NOT expose mysqli_connect_error() to the public frontend
        ]));
    }
    
    // Set the character set to UTF-8 for proper data handling
    if (!mysqli_set_charset($conn, "utf8mb4")) {
         error_log("Error loading character set utf8mb4: " . mysqli_error($conn));
    }
    
    return $conn;
}
?>