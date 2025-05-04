<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Get the request body
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

// Debug: Check if input is empty
if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "No data received"
    ]);
    exit;
}

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

// Extract data from request
$reservation_id = $data["referenceNumber"];
$user_id = $data["userId"]; // This should be passed from the frontend
$resource_id = $data["venue"];
$event_name = $data["eventName"];
$start_time = $data["dateFrom"] . " " . $data["timeStart"] . ":00";
$end_time = $data["dateTo"] . " " . $data["timeEnd"] . ":00";
$status = "approved"; // Since this is created by admin, it's auto-approved
$purpose = $data["purpose"];
$approved_by = $user_id; // Admin is approving their own event

// Insert into database
$sql = "INSERT INTO reservations (reservation_id, user_id, resource_id, event_name, start_time, end_time, status, purpose, approved_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("siisssssi", $reservation_id, $user_id, $resource_id, $event_name, $start_time, $end_time, $status, $purpose, $approved_by);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Event created successfully",
        "reservation_id" => $reservation_id
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>