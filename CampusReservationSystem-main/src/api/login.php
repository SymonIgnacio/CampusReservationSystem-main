<?php
// Enable CORS
header("Access-Control-Allow-Origin: http://localhost:3000");  // Adjust this to your React app's URL
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Prevent PHP from showing errors in the output
ini_set('display_errors', 0);
error_reporting(E_ALL);

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

// Log the request for debugging
error_log("Login attempt with username: " . $data['username']);

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "campus_db";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
if (!$stmt) {
    error_log("Prepare statement failed: " . $conn->error);
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error"]);
    exit();
}

$stmt->bind_param("s", $data['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Log the user data for debugging
    error_log("User found: " . json_encode($user));
    
    // Check if password matches using password_verify for bcrypt hashes
    if (password_verify($data['password'], $user['password'])) {
        // Remove password from user data before sending to client
        unset($user['password']);
        
        // Start session and set session variables
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        error_log("Login successful. Session data: " . json_encode($_SESSION));
        
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "user" => $user
        ]);
    } else {
        error_log("Password verification failed for user: " . $data['username']);
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
