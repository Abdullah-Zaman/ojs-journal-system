<?php
// ==========================================================
// Database connection settings
// Change these if your MySQL setup is different (XAMPP default
// is usually host=localhost, user=root, password="").
// ==========================================================
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'ojs_system';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error .
        '<br>Please import sql/schema.sql first and check the credentials in config/db.php.');
}

$conn->set_charset('utf8mb4');
