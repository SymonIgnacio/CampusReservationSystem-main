<?php
// Enable error reporting but don't display errors in output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Function to return error response
function returnError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode([
        "success" => false,
        "message" => $message
    ]);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    returnError("Method not allowed", 405);
}

// Get the request body
$requestBody = file_get_contents("php://input");
if (empty($requestBody)) {
    returnError("Empty request body");
}

try {
    $data = json_decode($requestBody, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        returnError("Invalid JSON: " . json_last_error_msg());
    }
} catch (Exception $e) {
    returnError("Error parsing request: " . $e->getMessage());
}

// Check if required fields are provided
if (!isset($data['id']) || !isset($data['status'])) {
    returnError("ID and status are required");
}

// Validate status
$allowedStatuses = ['pending', 'approved', 'declined', 'completed'];
if (!in_array($data['status'], $allowedStatuses)) {
    returnError("Invalid status. Must be one of: " . implode(', ', $allowedStatuses));
}

// Connect to DB
try {
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        returnError("Connection failed: " . $conn->connect_error, 500);
    }
} catch (Exception $e) {
    returnError("Database connection error: " . $e->getMessage(), 500);
}

try {
    // Check if events table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'events'");
    if ($tableCheck->num_rows == 0) {
        returnError("Events table does not exist", 500);
    }
    
    // Check if the ID exists
    $checkStmt = $conn->prepare("SELECT id FROM events WHERE id = ?");
    if (!$checkStmt) {
        returnError("Prepare check failed: " . $conn->error, 500);
    }
    
    $checkStmt->bind_param("i", $data['id']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        returnError("No event found with ID: " . $data['id'], 404);
    }
    
    $checkStmt->close();
    
    // Update the event status
    $updateStmt = $conn->prepare("UPDATE events SET status = ? WHERE id = ?");
    if (!$updateStmt) {
        returnError("Prepare update failed: " . $conn->error, 500);
    }
    
    $updateStmt->bind_param("si", $data['status'], $data['id']);
    
    if (!$updateStmt->execute()) {
        returnError("Error updating event status: " . $updateStmt->error, 500);
    }
    
    if ($updateStmt->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Event status updated successfully"
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "message" => "No changes made to event status"
        ]);
    }
    
    $updateStmt->close();
    $conn->close();
} catch (Exception $e) {
    returnError("Error processing request: " . $e->getMessage(), 500);
}
?>