<?php
// Ensure this path is correct based on where you save your files
require_once 'config.php'; 

echo "<h1>Database Connection Test</h1>";
echo "<p>Attempting to connect to the database...</p>";

// Call the connection function defined in config.php
$pdo = connectDB();

// If connectDB() runs successfully, it returns a PDO object.
// If it fails, the script will halt (die()) and output a JSON error (as designed in config.php).

if ($pdo instanceof PDO) {
    // If we reach this point, the connection was successful!
    echo "<h2>Success!</h2>";
    echo "<p>The connection was established successfully.</p>";
    
    // Optional: Test a simple query to ensure the database is fully accessible
    try {
        $stmt = $pdo->query('SELECT 1');
        if ($stmt->fetchColumn()) {
            echo "<p>SQL test query executed successfully.</p>";
        }
    } catch (Exception $e) {
        echo "<h3>Warning: Connection successful, but a simple SQL query failed.</h3>";
        echo "<p>This might indicate permission issues or an incorrect database name. Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

} else {
    // This part is unlikely to be reached because connectDB() uses die() on failure,
    // but it serves as a final safeguard.
    echo "<h2>Failure!</h2>";
    echo "<p>The script finished, but the connection object was not returned correctly.</p>";
}
?>