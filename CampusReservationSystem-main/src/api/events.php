<?php
// events.php - Simple version that just fetches data from the database
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header("Content-Type: application/json");

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

// Check which table exists
$reservationsTableExists = $conn->query("SHOW TABLES LIKE 'reservations'")->num_rows > 0;
$eventsTableExists = $conn->query("SHOW TABLES LIKE 'events'")->num_rows > 0;

$tableName = $reservationsTableExists ? 'reservations' : ($eventsTableExists ? 'events' : null);

if (!$tableName) {
    echo json_encode([
        "success" => false,
        "events" => [],
        "message" => "No events or reservations table found in database"
    ]);
    exit;
}

// Check if the table has a status column
$hasStatusColumn = false;
$columnsResult = $conn->query("DESCRIBE $tableName");
while ($column = $columnsResult->fetch_assoc()) {
    if ($column['Field'] === 'status') {
        $hasStatusColumn = true;
        break;
    }
}

// Add status column if it doesn't exist
if (!$hasStatusColumn) {
    $alterSql = "ALTER TABLE $tableName ADD COLUMN status VARCHAR(50) DEFAULT 'approved'";
    if (!$conn->query($alterSql)) {
        echo json_encode([
            "success" => false,
            "message" => "Failed to add status column: " . $conn->error
        ]);
        exit;
    }
}

// Get all events from the table
$sql = "SELECT * FROM $tableName";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Query failed: " . $conn->error
    ]);
    exit;
}

$events = [];
while ($row = $result->fetch_assoc()) {
    // Add some basic formatting
    if (isset($row['date_from'])) {
        $row['date'] = $row['date_from'];
    }
    
    if (isset($row['time_start']) && isset($row['time_end'])) {
        $row['time'] = $row['time_start'] . ' - ' . $row['time_end'];
    }
    
    if (isset($row['activity'])) {
        $row['name'] = $row['activity'];
        $row['title'] = $row['activity'];
    }
    
    if (isset($row['venue'])) {
        $row['location'] = $row['venue'];
        $row['place'] = $row['venue'];
    }
    
    if (isset($row['requestor_name'])) {
        $row['organizer'] = $row['requestor_name'];
        $row['requestedBy'] = $row['requestor_name'];
    }
    
    // Ensure status field exists
    if (!isset($row['status'])) {
        // Default all events to 'approved' if status is not present
        $row['status'] = 'approved';
    }
    
    // Ensure ID field exists
    if (!isset($row['id']) && isset($row['reservation_id'])) {
        $row['id'] = $row['reservation_id'];
    } else if (!isset($row['id']) && isset($row['event_id'])) {
        $row['id'] = $row['event_id'];
    } else if (!isset($row['id'])) {
        // Generate a unique ID if none exists
        $row['id'] = uniqid();
    }
    
    $events[] = $row;
}

// Return results
echo json_encode([
    "success" => true,
    "events" => $events
]);

$conn->close();
?>