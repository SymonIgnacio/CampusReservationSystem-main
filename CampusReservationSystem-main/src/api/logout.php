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

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log for debugging
error_log("logout.php called. Session before logout: " . json_encode($_SESSION));

// Clear session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Return success response
echo json_encode([
    "success" => true,
    "message" => "Logged out successfully"
]);
?>