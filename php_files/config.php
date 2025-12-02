<?php
// --- Database Credentials ---
// Fix 1: Environment variables in Render/Railway are UPPERCASE (DB_HOST, DB_USER, etc.)
// Fix 2: Add DB_PORT constant, defaulting to 3306 to force a network connection.

	define('DB_HOST', getenv('DB_HOST')); // Server address
    define('DB_USER', getenv('DB_USER')); // Username
    define('DB_PASS', getenv('DB_PASS')); // Password
    define('DB_NAME', getenv('DB_NAME')); // Database Name (Using ENV variable for flexibility)
    define('DB_PORT', getenv('DB_PORT') ?: 3306); // Default to 3306 (MySQL standard port)

/**
 * Establishes a MySQLi connection to the database.
 * @return mysqli The MySQLi connection object.
 */
function connectDB(): mysqli {
    // Fix 3: Passed DB_PORT as the 5th argument to force TCP/IP connection.
    // Suppress the default error warning (we handle it below)
    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

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