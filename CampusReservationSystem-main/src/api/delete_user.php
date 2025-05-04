<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

// Get the request body
$data = json_decode(file_get_contents("php://input"), true);

// Check if userId is provided
if (!isset($data['userId'])) {
    echo json_encode([
        "success" => false,
        "message" => "User ID is required"
    ]);
    exit;
}

$userId = $data['userId'];

// Connect to DB
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]);
    exit;
}

// Check if the user exists and is not an admin
$checkStmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$checkStmt->bind_param("i", $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);
    $checkStmt->close();
    $conn->close();
    exit;
}

$user = $checkResult->fetch_assoc();
if ($user['role'] === 'admin') {
    echo json_encode([
        "success" => false,
        "message" => "Cannot delete admin users"
    ]);
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

// Delete the user
$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "User deleted successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error deleting user: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>