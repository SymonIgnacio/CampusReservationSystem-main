<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Return a simple success response
echo json_encode([
    "success" => true,
    "message" => "API connection successful",
    "timestamp" => date("Y-m-d H:i:s")
]);
?>