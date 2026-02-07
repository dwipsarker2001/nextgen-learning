<?php
// Database configuration
$host = 'db';
$username = 'root';
$password = 'root';
$dbname = 'digital-shikkhok';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
