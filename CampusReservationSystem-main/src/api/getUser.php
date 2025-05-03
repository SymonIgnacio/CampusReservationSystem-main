<?php
// filepath: c:\xampp\htdocs\CampusReservationSystem-main\api\getUser.php
<?php
session_start();
header("Access-Control-Allow-Origin: http://localhost:3000"); // Allow requests from React app
header("Access-Control-Allow-Credentials: true"); // Allow cookies/session handling
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allow specific HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers

if (isset($_SESSION['user'])) {
    echo json_encode(["success" => true, "user" => $_SESSION['user']]);
} else {
    echo json_encode(["success" => false, "message" => "No user logged in"]);
}
?>