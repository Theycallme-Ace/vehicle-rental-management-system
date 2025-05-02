<?php
require_once 'config.php';

try {
    // Create database directory if it doesn't exist
    if (!is_dir(dirname(DB_FILE))) {
        mkdir(dirname(DB_FILE), 0777, true);
    }
    
    $dsn = 'sqlite:' . DB_FILE;
    $pdo = new PDO($dsn, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Enable foreign key support for SQLite
    $pdo->exec('PRAGMA foreign_keys = ON');
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>
