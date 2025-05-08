<?php
// getUser.php - Get current logged in user from session
header("Access-Control-Allow-Origin: http://localhost:3000");  // Adjust this to your React app's URL
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Prevent PHP from showing errors in the output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log for debugging
error_log("getUser.php called. Session data: " . json_encode($_SESSION));

// Check if user is logged in via session
if (isset($_SESSION['user_id'])) {
    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        echo json_encode([
            "success" => false,
            "message" => "Connection failed"
        ]);
        exit;
    }

    // Get user data from database using the correct column name (user_id)
    $stmt = $conn->prepare("SELECT user_id, username, firstname, lastname, email, role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        echo json_encode([
            "success" => true,
            "user" => $user
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "User not found"
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        "success" => false,
        "message" => "No user logged in"
    ]);
}
?>