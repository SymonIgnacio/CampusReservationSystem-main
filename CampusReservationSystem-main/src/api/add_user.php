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

// Extract data from request
$firstname = $data["firstname"];
$middlename = $data["middlename"];
$lastname = $data["lastname"];
$department = $data["department"];
$username = $data["username"];
$email = $data["email"];
$password = $data["password"];
$role = $data["role"];

// Ensure role is either student or faculty
if ($role !== "student" && $role !== "faculty") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid role. Role must be either 'student' or 'faculty'."
    ]);
    $conn->close();
    exit;
}

// Check if username already exists
$checkStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$checkStmt->bind_param("s", $username);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Username already exists"
    ]);
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

// Check if email already exists
$checkEmailStmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$checkEmailStmt->bind_param("s", $email);
$checkEmailStmt->execute();
$checkEmailResult = $checkEmailStmt->get_result();

if ($checkEmailResult->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Email already exists"
    ]);
    $checkEmailStmt->close();
    $conn->close();
    exit;
}
$checkEmailStmt->close();

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert the new user
$stmt = $conn->prepare("INSERT INTO users (firstname, middlename, lastname, department, username, email, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss", $firstname, $middlename, $lastname, $department, $username, $email, $hashedPassword, $role);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "User added successfully",
        "userId" => $conn->insert_id
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error adding user: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>