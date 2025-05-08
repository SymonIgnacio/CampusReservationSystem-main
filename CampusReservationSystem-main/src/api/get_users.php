<?php
// get_users.php - Get a list of all users (for admin purposes)
header("Access-Control-Allow-Origin: *");
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

// Check if users table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
if ($tableCheck->num_rows == 0) {
    echo json_encode([
        "success" => false,
        "message" => "Users table does not exist"
    ]);
    exit;
}

// Get filter parameters if provided
$role = isset($_GET['role']) ? $_GET['role'] : '';
$excludeAdmin = isset($_GET['excludeAdmin']) ? filter_var($_GET['excludeAdmin'], FILTER_VALIDATE_BOOLEAN) : true;

// Prepare SQL query based on filter
if (!empty($role) && $excludeAdmin) {
    // Only show non-admin users with the specified role
    $sql = "SELECT id, username, firstname, lastname, email, department, role FROM users WHERE role = ? AND role != 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $role);
} else if (!empty($role)) {
    // Show users with the specified role including admins
    $sql = "SELECT id, username, firstname, lastname, email, department, role FROM users WHERE role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $role);
} else if ($excludeAdmin) {
    // Show all users except admins
    $sql = "SELECT id, username, firstname, lastname, email, department, role FROM users WHERE role != 'admin'";
    $stmt = $conn->prepare($sql);
} else {
    // Show all users including admins
    $sql = "SELECT id, username, firstname, lastname, email, department, role FROM users";
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
    "users" => $users,
    "count" => count($users)
]);

$stmt->close();
$conn->close();
?>