<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

// Get filter parameter if provided
$role = isset($_GET['role']) ? $_GET['role'] : '';

// Prepare SQL query based on filter
if (!empty($role)) {
    // Only show non-admin users with the specified role
    $sql = "SELECT user_id, username, firstname, lastname, department, role FROM users WHERE role = ? AND role != 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $role);
} else {
    // Show all users except admins
    $sql = "SELECT user_id, username, firstname, lastname, department, role FROM users WHERE role != 'admin'";
    $stmt = $conn->prepare($sql);
}

// Execute query
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Return results
echo json_encode([
    "success" => true,
    "users" => $users
]);

$stmt->close();
$conn->close();
?>