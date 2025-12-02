<?php
// Script to DROP ALL tables for the book rental application.
// Run this file when you need to completely reset the database schema.
// URL: http://localhost/drop_tables.php

// Include the configuration file where DB_HOST, DB_USER, etc., are defined.
require_once 'config.php';

// --- SQL DROP TABLE STATEMENTS ---
// These are listed in dependency order (junction tables first, core tables last)
$sql_drop_statements = [
    // Drop junction tables first to resolve foreign key constraints
    "DROP TABLE IF EXISTS ORDER_BOOKS;",
    "DROP TABLE IF EXISTS COURSE_BOOKS;",
    "DROP TABLE IF EXISTS PAYMENTS;",
    "DROP TABLE IF EXISTS ORDERS;",
    "DROP TABLE IF EXISTS CARTS;",
    // Drop core tables last
    "DROP TABLE IF EXISTS COURSES;",
    "DROP TABLE IF EXISTS BOOKS;",
    "DROP TABLE IF EXISTS USERS;"
];

// --- Execution Logic ---
function runDrop($sql_drop_statements) {
    if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
        die("Configuration error: DB constants not defined in config.php.");
    }
    
    // Connect to the specific database
    $conn_db = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn_db) {
        error_log("Database Connection Error: " . mysqli_connect_error());
        die("Failed to connect to the database: " . mysqli_connect_error());
    }

    $drop_count = 0;
    echo "--- Attempting to drop tables in database **" . DB_NAME . "**... ---<br><br>";

    try {
        // Run all drop statements
        foreach ($sql_drop_statements as $sql) {
            if (mysqli_query($conn_db, $sql)) {
                $tableName = strtoupper(preg_match('/DROP TABLE IF EXISTS (\w+)/', $sql, $matches) ? $matches[1] : 'Unknown Table');
                echo "Dropped: **{$tableName}**<br>";
                $drop_count++;
            } else {
                // Log non-fatal errors (e.g., table didn't exist)
                error_log("Non-fatal DROP error for SQL: {$sql} - " . mysqli_error($conn_db));
            }
        }
        
        mysqli_close($conn_db);
        echo "<br>✅ **Database Reset Complete!** Attempted to drop {$drop_count} table definitions.<br>";

    } catch (Exception $e) {
        if ($conn_db) {
             mysqli_close($conn_db);
        }
        error_log("Database Drop Fatal Error: " . $e->getMessage());
        die("Fatal Drop Error: " . $e->getMessage());
    }
}

runDrop($sql_drop_statements);
?>