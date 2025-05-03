<?php
// filepath: c:\xampp\htdocs\CampusReservationSystem-main\api\logout.php
<?php
session_start();
session_destroy();
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
echo json_encode(["success" => true, "message" => "Logged out successfully"]);
?>