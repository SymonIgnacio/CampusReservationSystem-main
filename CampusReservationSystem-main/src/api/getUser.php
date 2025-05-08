<?php
// getUser.php - Get current logged in user from session
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in via session
if (isset($_SESSION['user_id'])) {
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

    // Get user data from database
    $stmt = $conn->prepare("SELECT id, username, firstname, lastname, email, department, role FROM users WHERE id = ?");
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
} else if (isset($_SESSION['user'])) {
    // For backward compatibility with old session format
    echo json_encode([
        "success" => true,
        "user" => $_SESSION['user']
    ]);
} else {
    // Check if user info is stored in localStorage via cookie
    $user = null;
    if (isset($_COOKIE['user'])) {
        $user = json_decode($_COOKIE['user'], true);
    }

    if ($user) {
        echo json_encode([
            "success" => true,
            "user" => $user,
            "source" => "cookie"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No user logged in"
        ]);
    }
}
?>