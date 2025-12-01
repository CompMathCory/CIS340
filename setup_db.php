<?php
// --- Database Setup Credentials (Must be a user with CREATE permissions) ---
	define('DB_HOST', getenv('db_host')); //server
    define('DB_USER', getenv('db_user')); //username
    define('DB_PASS', getenv('db_pass'); //password
    define('DB_NAME', getenv('db_name'); //database  
    define('DB_CHARSET', 'utf8mb4';

// --- SQL CREATE TABLE STATEMENTS ---
$sql_statements = [
    // 1. USERS Table
    "CREATE TABLE IF NOT EXISTS USERS (
        id VARCHAR(36) PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password_encrypted CHAR(60) NOT NULL, -- Use CHAR(60) for bcrypt hashes
        email VARCHAR(255) NOT NULL UNIQUE,
        role VARCHAR(50) NOT NULL DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;",
    
    // 2. BOOKS Table
    "CREATE TABLE IF NOT EXISTS BOOKS (
        id VARCHAR(36) PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        ISBN CHAR(13) UNIQUE,
        price DECIMAL(10, 2) NOT NULL,
        rentalStatus VARCHAR(50) DEFAULT 'available',
        imageURL VARCHAR(255),
        altText VARCHAR(255),
        inventoryCount INT DEFAULT 0,
        managedBy VARCHAR(36) -- Can be linked to a staff user's ID
    ) ENGINE=InnoDB;",
    
    // 3. ORDERS Table
    "CREATE TABLE IF NOT EXISTS ORDERS (
        id VARCHAR(36) PRIMARY KEY,
        userID VARCHAR(36) NOT NULL,
        orderDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        totalAmount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        status VARCHAR(50) NOT NULL DEFAULT 'pending',
        -- Foreign Key: Links to the USERS table
        FOREIGN KEY (userID) REFERENCES USERS(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;",
    
    // 4. CARTS Table
    "CREATE TABLE IF NOT EXISTS CARTS (
        id VARCHAR(36) PRIMARY KEY,
        userID VARCHAR(36) NOT NULL UNIQUE, -- Only one active cart per user
        status VARCHAR(50) NOT NULL DEFAULT 'active',
        FOREIGN KEY (userID) REFERENCES USERS(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;",
    
    // 5. COURSES Table
    "CREATE TABLE IF NOT EXISTS COURSES (
        id VARCHAR(36) PRIMARY KEY,
        courseName VARCHAR(255) NOT NULL UNIQUE,
        professor VARCHAR(100),
        semester VARCHAR(50)
    ) ENGINE=InnoDB;",
    
    // 6. PAYMENTS Table
    "CREATE TABLE IF NOT EXISTS PAYMENTS (
        id VARCHAR(36) PRIMARY KEY,
        orderID VARCHAR(36) NOT NULL,
        type VARCHAR(50) NOT NULL,
        transactionData TEXT, -- Store non-sensitive transaction details
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (orderID) REFERENCES ORDERS(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;",
    
    // 7. ORDER_BOOKS (Junction/Detail Table for Many-to-Many: Orders <-> Books)
    "CREATE TABLE IF NOT EXISTS ORDER_BOOKS (
        id VARCHAR(36) PRIMARY KEY,
        orderID VARCHAR(36) NOT NULL,
        bookID VARCHAR(36) NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        FOREIGN KEY (orderID) REFERENCES ORDERS(id) ON DELETE CASCADE,
        FOREIGN KEY (bookID) REFERENCES BOOKS(id) ON DELETE RESTRICT,
        UNIQUE KEY (orderID, bookID) -- Prevent duplicate entries for the same book in the same order
    ) ENGINE=InnoDB;",
    
    // 8. COURSE_BOOKS (Junction/Detail Table for Many-to-Many: Courses <-> Books)
    "CREATE TABLE IF NOT EXISTS COURSE_BOOKS (
        id VARCHAR(36) PRIMARY KEY,
        courseID VARCHAR(36) NOT NULL,
        bookID VARCHAR(36) NOT NULL,
        FOREIGN KEY (courseID) REFERENCES COURSES(id) ON DELETE CASCADE,
        FOREIGN KEY (bookID) REFERENCES BOOKS(id) ON DELETE RESTRICT,
        UNIQUE KEY (courseID, bookID)
    ) ENGINE=InnoDB;"
];

// --- Execution Logic (No need to edit below here) ---
try {
    // 1. Connect to the MySQL server (without specifying a database)
    $pdo = new PDO("mysql:host=" . SETUP_DB_HOST, SETUP_DB_USER, SETUP_DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create the target database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . TARGET_DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Database **" . TARGET_DB_NAME . "** checked/created successfully.<br>";

    // 3. Re-connect to the newly created database
    $pdo = new PDO("mysql:host=" . SETUP_DB_HOST . ";dbname=" . TARGET_DB_NAME, SETUP_DB_USER, SETUP_DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 4. Run all table creation statements
    foreach ($sql_statements as $sql) {
        // Simple extraction of table name for logging
        $tableName = strtoupper(preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/', $sql, $matches) ? $matches[1] : 'Unknown Table');
        $pdo->exec($sql);
        echo "Table created/checked: **{$tableName}**<br>";
    }

    echo "<br>🎉 **Database setup complete!** You can now use `config.php` in your main application.<br>";

} catch (\PDOException $e) {
    http_response_code(500);
    die("Database Setup Error: " . $e->getMessage());
}
?>