<?php
/**
 * SportZone - Database Connection (mysqli)
 * Using mysqli + prepared statements throughout the project
 * to prevent SQL Injection.
 */
require_once __DIR__ . '/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
