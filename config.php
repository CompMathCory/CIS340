<?php
// --- Database Credentials ---
	define('DB_HOST', getenv('db_host')); //server
    define('DB_USER', getenv('db_user')); //username
    define('DB_PASS', getenv('db_pass'); //password
    define('DB_NAME', getenv('db_name'); //database  
    define('DB_CHARSET', 'utf8mb4';
5w
/**
 * Establishes a PDO connection to the database.
 * @return PDO The PDO connection object.
 */
function connectDB(): PDO {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        // Throw an exception when a database error occurs
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Fetch results as associative arrays by default
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
        // Use real prepared statements for better security
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (\PDOException $e) {
        // Log the error (e.g., to a file) and die gracefully.
        error_log("Database Connection Error: " . $e->getMessage());
        // Return a JSON error since your frontend expects an API response
        http_response_code(500);
        die(json_encode(['error' => 'Database connection failed.']));
    }
}
?>