<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Include session configuration
require_once 'session_config.php';

// Destroy the session
session_unset();
session_destroy();

echo json_encode([
    "success" => true,
    "message" => "Logged out successfully"
]);
?>