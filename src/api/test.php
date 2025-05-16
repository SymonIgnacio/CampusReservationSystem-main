<?php
// Include CORS headers
require_once 'cors_fix.php';

// Return a simple success response
echo json_encode([
    "success" => true,
    "message" => "API is working",
    "timestamp" => date("Y-m-d H:i:s"),
    "session_active" => session_status() === PHP_SESSION_ACTIVE
]);
?>