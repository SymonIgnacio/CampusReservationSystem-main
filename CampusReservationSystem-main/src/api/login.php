<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit();
}

// Get the request body
$data = json_decode(file_get_contents("php://input"), true);

// Check if username and password are provided
if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Username and password are required"]);
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

// Log the request for debugging
error_log("Login attempt for username: " . $data['username']);

// Prepare statement to prevent SQL injection
// Adjust the table and column names to match your actual database structure
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Prepare statement failed: " . $conn->error]);
    exit();
}

$stmt->bind_param("s", $data['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Log the user data for debugging (remove in production)
    error_log("User found: " . json_encode($user));
    
    // Check if your database stores passwords as plain text or hashed
    // For plain text (not recommended for production):
    if ($user['password'] === $data['password']) {
        // Remove password from user data before sending to client
        unset($user['password']);
        
        // Start session and set session variables
        session_start();
        $_SESSION['user_id'] = $user['user_id']; // Adjust field name if needed
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "user" => $user
        ]);
    } else {
        error_log("Password mismatch for user: " . $data['username']);
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Invalid username or password"]);
    }
} else {
    error_log("No user found with username: " . $data['username']);
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid username or password"]);
}

$stmt->close();
$conn->close();
?>