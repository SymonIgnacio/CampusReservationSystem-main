<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

// Prevent PHP from showing errors in the output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "campus_db";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Get all reservations (instead of events)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM reservations";
    $result = $conn->query($sql);
    
    if ($result) {
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        echo json_encode(["success" => true, "events" => $events]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error fetching reservations: " . $conn->error]);
    }
}

$conn->close();
?>