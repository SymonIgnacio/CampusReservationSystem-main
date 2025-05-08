<?php
// Simple test endpoint to verify API connectivity
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Return a simple success response
echo json_encode([
    "success" => true,
    "message" => "API is working",
    "timestamp" => date("Y-m-d H:i:s")
]);
?>