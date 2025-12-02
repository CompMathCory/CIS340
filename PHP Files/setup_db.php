<?php
// Script to set up ALL necessary tables for the book rental application (8 tables total).
// Run this file ONCE in your browser: http://localhost/setup_db.php

// Include the configuration file where DB_HOST, DB_USER, etc., are defined.
require_once 'config.php';

// --- SQL DROP TABLE STATEMENTS ---
// These are listed in dependency order to avoid foreign key errors during drop.
$sql_drop_statements = [
    "DROP TABLE IF EXISTS ORDER_BOOKS;",
    "DROP TABLE IF EXISTS COURSE_BOOKS;",
    "DROP TABLE IF EXISTS PAYMENTS;",
    "DROP TABLE IF EXISTS ORDERS;",
    "DROP TABLE IF EXISTS CARTS;",
    "DROP TABLE IF EXISTS COURSES;",
    "DROP TABLE IF EXISTS BOOKS;",
    "DROP TABLE IF EXISTS USERS;" // Drop USERS last
];


// --- SQL CREATE TABLE STATEMENTS (ALL PRIMARY KEYS NOW INT AUTO_INCREMENT) ---
$sql_create_statements = [
    // 1. USERS Table
    "CREATE TABLE IF NOT EXISTS USERS (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        username VARCHAR(100) NOT NULL UNIQUE,
        password_hash CHAR(60) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        role VARCHAR(50) NOT NULL DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;",
    
    // 2. BOOKS Table
    "CREATE TABLE IF NOT EXISTS BOOKS (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        title VARCHAR(255) NOT NULL,
        ISBN CHAR(13) UNIQUE,
        price DECIMAL(10, 2) NOT NULL,
        rentalStatus VARCHAR(50) DEFAULT 'available',
        imageURL VARCHAR(255),
        altText VARCHAR(255),
        inventoryCount INT DEFAULT 0,
        managedBy CHAR(36) -- This field remains CHAR(36) for external tracking purposes
    ) ENGINE=InnoDB;",
    
    // 3. ORDERS Table (ID changed to INT AUTO_INCREMENT)
    "CREATE TABLE IF NOT EXISTS ORDERS (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        userID INT(11) UNSIGNED NOT NULL, 
        orderDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        totalAmount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        status VARCHAR(50) NOT NULL DEFAULT 'pending',
        FOREIGN KEY (userID) REFERENCES USERS(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;",
    
    // 4. CARTS Table (ID changed to INT AUTO_INCREMENT)
    "CREATE TABLE IF NOT EXISTS CARTS (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        userID INT(11) UNSIGNED NOT NULL UNIQUE,
        status VARCHAR(50) NOT NULL DEFAULT 'active',
        FOREIGN KEY (userID) REFERENCES USERS(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;",
    
    // 5. COURSES Table (ID changed to INT AUTO_INCREMENT)
    "CREATE TABLE IF NOT EXISTS COURSES (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,             
        code VARCHAR(50) NOT NULL UNIQUE,       
        instructor VARCHAR(100),                
        semester VARCHAR(50)
    ) ENGINE=InnoDB;",
    
    // 6. PAYMENTS Table (ID changed to INT AUTO_INCREMENT)
    "CREATE TABLE IF NOT EXISTS PAYMENTS (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        orderID INT(11) UNSIGNED NOT NULL,
        type VARCHAR(50) NOT NULL,
        transactionData TEXT,
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (orderID) REFERENCES ORDERS(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;",
    
    // 7. ORDER_BOOKS (Composite Key, no separate ID)
    "CREATE TABLE IF NOT EXISTS ORDER_BOOKS (
        orderID INT(11) UNSIGNED NOT NULL,
        bookID INT(11) UNSIGNED NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        PRIMARY KEY (orderID, bookID),
        FOREIGN KEY (orderID) REFERENCES ORDERS(id) ON DELETE CASCADE,
        FOREIGN KEY (bookID) REFERENCES BOOKS(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB;",
    
    // 8. COURSE_BOOKS (Composite Key, no separate ID)
    "CREATE TABLE IF NOT EXISTS COURSE_BOOKS (
        courseID INT(11) UNSIGNED NOT NULL,
        bookID INT(11) UNSIGNED NOT NULL,
        PRIMARY KEY (courseID, bookID),
        FOREIGN KEY (courseID) REFERENCES COURSES(id) ON DELETE CASCADE,
        FOREIGN KEY (bookID) REFERENCES BOOKS(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB;"
];

// --- Execution Logic (Updated to handle DROP and CREATE) ---
function runSetup($sql_drop_statements, $sql_create_statements) {
    // Check if DB constants are defined
    if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
        die("Configuration error: DB constants not defined in config.php.");
    }
    
    // 1. Connect to the MySQL server (WITHOUT specifying a database name)
    $conn_server = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);
    if (!$conn_server) {
        error_log("Setup Connection Error: " . mysqli_connect_error());
        die("Failed to connect to MySQL Server for setup: " . mysqli_connect_error());
    }

    $db_name = DB_NAME;
    $conn_db = null;

    try {
        // Create the target database (if needed)
        $create_db_sql = "CREATE DATABASE IF NOT EXISTS " . $db_name . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        if (mysqli_query($conn_server, $create_db_sql)) {
            echo "Database **{$db_name}** checked/created successfully.<br>";
        } else {
            throw new Exception("Error creating database: " . mysqli_error($conn_server));
        }

        mysqli_close($conn_server);

        // 2. Re-connect, specifying the target database
        $conn_db = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, $db_name);
        if (!$conn_db) {
            throw new Exception("Failed to connect to the database: " . mysqli_connect_error());
        }

        // 3. Drop all tables for clean schema build
        echo "<br>--- Dropping existing tables... ---<br>";
        foreach ($sql_drop_statements as $sql) {
            if (mysqli_query($conn_db, $sql)) {
                $tableName = strtoupper(preg_match('/DROP TABLE IF EXISTS (\w+)/', $sql, $matches) ? $matches[1] : 'Unknown Table');
                echo "Dropped: **{$tableName}**<br>";
            } else {
                // Log non-fatal errors (e.g., table didn't exist)
                error_log("Non-fatal DROP error for SQL: {$sql} - " . mysqli_error($conn_db));
            }
        }
        
        // 4. Run all table creation statements
        echo "<br>--- Creating new tables... ---<br>";
        $success_count = 0;
        foreach ($sql_create_statements as $sql) {
            if (mysqli_query($conn_db, $sql)) {
                $tableName = strtoupper(preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/', $sql, $matches) ? $matches[1] : 'Unknown Table');
                echo "Table created: **{$tableName}**<br>";
                $success_count++;
            } else {
                throw new Exception("Fatal Error creating table: " . mysqli_error($conn_db) . "\nSQL: " . $sql);
            }
        }
        
        mysqli_close($conn_db);
        echo "<br>🎉 **Database setup complete!** All {$success_count} tables were rebuilt with the consistent integer ID schema.<br>";

    } catch (Exception $e) {
        if ($conn_db) {
             mysqli_close($conn_db);
        }
        error_log("Database Setup Fatal Error: " . $e->getMessage());
        die("Fatal Setup Error: " . $e->getMessage());
    }
}

runSetup($sql_drop_statements, $sql_create_statements);
?>