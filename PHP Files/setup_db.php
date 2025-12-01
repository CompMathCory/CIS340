<?php
// --- Database Setup Credentials (Must be a user with CREATE permissions) ---
/*	define('DB_HOST', getenv('db_host')); //server
    define('DB_USER', getenv('db_user')); //username
    define('DB_PASS', getenv('db_pass')); //password
    define('DB_NAME', 'book_rental_system'); //database  
    define('DB_CHARSET', 'utf8mb4');                     
*/

// Include the configuration file where DB_HOST, DB_USER, etc., are defined.
// NOTE: Ensure these constants/defines are accessible in the global scope!
require_once 'config.php';

// --- SQL CREATE TABLE STATEMENTS ---
// These statements define the structure of your database.
$sql_statements = [
    // 1. USERS Table
    "CREATE TABLE IF NOT EXISTS USERS (
        id CHAR(36) PRIMARY KEY, -- Using CHAR(36) for UUIDs
        username VARCHAR(100) NOT NULL UNIQUE,
        password_encrypted CHAR(60) NOT NULL, -- Recommended for bcrypt hashes
        email VARCHAR(255) NOT NULL UNIQUE,
        role VARCHAR(50) NOT NULL DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;",
    
    // 2. BOOKS Table
    "CREATE TABLE IF NOT EXISTS BOOKS (
        id CHAR(36) PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        ISBN CHAR(13) UNIQUE,
        price DECIMAL(10, 2) NOT NULL,
        rentalStatus VARCHAR(50) DEFAULT 'available',
        imageURL VARCHAR(255),
        altText VARCHAR(255),
        inventoryCount INT DEFAULT 0,
        managedBy CHAR(36)
    ) ENGINE=InnoDB;",
    
    // 3. ORDERS Table
    "CREATE TABLE IF NOT EXISTS ORDERS (
        id CHAR(36) PRIMARY KEY,
        userID CHAR(36) NOT NULL,
        orderDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        totalAmount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        status VARCHAR(50) NOT NULL DEFAULT 'pending',
        -- Foreign Key: Links to the USERS table
        FOREIGN KEY (userID) REFERENCES USERS(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;",
    
    // 4. CARTS Table
    "CREATE TABLE IF NOT EXISTS CARTS (
        id CHAR(36) PRIMARY KEY,
        userID CHAR(36) NOT NULL UNIQUE, -- Only one active cart per user
        status VARCHAR(50) NOT NULL DEFAULT 'active',
        FOREIGN KEY (userID) REFERENCES USERS(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;",
    
    // 5. COURSES Table
    "CREATE TABLE IF NOT EXISTS COURSES (
        id CHAR(36) PRIMARY KEY,
        courseName VARCHAR(255) NOT NULL UNIQUE,
        professor VARCHAR(100),
        semester VARCHAR(50)
    ) ENGINE=InnoDB;",
    
    // 6. PAYMENTS Table
    "CREATE TABLE IF NOT EXISTS PAYMENTS (
        id CHAR(36) PRIMARY KEY,
        orderID CHAR(36) NOT NULL,
        type VARCHAR(50) NOT NULL,
        transactionData TEXT,
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (orderID) REFERENCES ORDERS(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;",
    
    // 7. ORDER_BOOKS (Junction/Detail Table for Many-to-Many: Orders <-> Books)
    "CREATE TABLE IF NOT EXISTS ORDER_BOOKS (
        id CHAR(36) PRIMARY KEY,
        orderID CHAR(36) NOT NULL,
        bookID CHAR(36) NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        FOREIGN KEY (orderID) REFERENCES ORDERS(id) ON DELETE CASCADE,
        FOREIGN KEY (bookID) REFERENCES BOOKS(id) ON DELETE RESTRICT,
        UNIQUE KEY (orderID, bookID) 
    ) ENGINE=InnoDB;",
    
    // 8. COURSE_BOOKS (Junction/Detail Table for Many-to-Many: Courses <-> Books)
    "CREATE TABLE IF NOT EXISTS COURSE_BOOKS (
        id CHAR(36) PRIMARY KEY,
        courseID CHAR(36) NOT NULL,
        bookID CHAR(36) NOT NULL,
        FOREIGN KEY (courseID) REFERENCES COURSES(id) ON DELETE CASCADE,
        FOREIGN KEY (bookID) REFERENCES BOOKS(id) ON DELETE RESTRICT,
        UNIQUE KEY (courseID, bookID)
    ) ENGINE=InnoDB;"
];

// --- Execution Logic (No need to edit below here) ---
function runSetup($sql_statements) {
    // 1. Connect to the MySQL server (WITHOUT specifying a database name)
    // NOTE: DB_USER must have CREATE DATABASE permissions.
    $conn_server = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);

    if (!$conn_server) {
        error_log("Setup Connection Error: " . mysqli_connect_error());
        die("Failed to connect to MySQL Server for setup: " . mysqli_connect_error());
    }

    $db_name = DB_NAME;

    try {
        // 2. Create the target database
        $create_db_sql = "CREATE DATABASE IF NOT EXISTS " . $db_name . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        if (mysqli_query($conn_server, $create_db_sql)) {
            echo "Database **{$db_name}** checked/created successfully.<br>";
        } else {
            throw new Exception("Error creating database: " . mysqli_error($conn_server));
        }

        // Close the server connection
        mysqli_close($conn_server);

        // 3. Re-connect, specifying the newly created database
        $conn_db = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, $db_name);
        if (!$conn_db) {
            throw new Exception("Failed to connect to the new database: " . mysqli_connect_error());
        }

        // 4. Run all table creation statements
        $success_count = 0;
        foreach ($sql_statements as $sql) {
            if (mysqli_query($conn_db, $sql)) {
                $tableName = strtoupper(preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/', $sql, $matches) ? $matches[1] : 'Unknown Table');
                echo "Table created/checked: **{$tableName}**<br>";
                $success_count++;
            } else {
                throw new Exception("Error creating table: " . mysqli_error($conn_db) . "\nSQL: " . $sql);
            }
        }
        
        mysqli_close($conn_db);
        echo "<br>🎉 **Database setup complete!** {$success_count} tables were created/checked.<br>";

    } catch (Exception $e) {
        error_log("Database Setup Fatal Error: " . $e->getMessage());
        die("Fatal Setup Error: " . $e->getMessage());
    }
}

runSetup($sql_statements);
?>