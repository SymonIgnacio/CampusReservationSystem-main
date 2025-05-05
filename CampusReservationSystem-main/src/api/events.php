<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
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

// Get all tables in the database to find the events table
$tables_result = $conn->query("SHOW TABLES");
$tables = [];
while ($table = $tables_result->fetch_array()) {
    $tables[] = $table[0];
}

// Find a table that might contain events
$events_table = null;
$possible_tables = ['events', 'reservations', 'bookings', 'event', 'reservation', 'booking'];
foreach ($possible_tables as $table) {
    if (in_array($table, $tables)) {
        $events_table = $table;
        break;
    }
}

// If no events table found, return empty array
if (!$events_table) {
    echo json_encode([
        "success" => true,
        "events" => [],
        "message" => "No events table found in database"
    ]);
    exit;
}

// Get status filter if provided
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Get all events from the table
if (!empty($status)) {
    $sql = "SELECT * FROM $events_table WHERE status = '$status'";
} else {
    $sql = "SELECT * FROM $events_table";
}

$result = $conn->query($sql);

// Check for query error
if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Query error: " . $conn->error
    ]);
    exit;
}

// Get column names
$columns = [];
$columns_result = $conn->query("DESCRIBE $events_table");
while ($column = $columns_result->fetch_assoc()) {
    $columns[] = $column['Field'];
}

$events = [];
while ($row = $result->fetch_assoc()) {
    // Add date formatting if date columns exist
    if (in_array('start_time', $columns)) {
        $start_time = $row['start_time'];
        if ($start_time) {
            $row['date'] = date('Y-m-d', strtotime($start_time));
            $row['time'] = date('h:i A', strtotime($start_time));
        }
    }
    
    // Add compatibility fields
    if (in_array('title', $columns) && !in_array('name', $columns)) {
        $row['name'] = $row['title'];
    } else if (in_array('name', $columns) && !in_array('title', $columns)) {
        $row['title'] = $row['name'];
    }
    
    // Add place/location compatibility
    if (in_array('place', $columns) && !in_array('location', $columns)) {
        $row['location'] = $row['place'];
    } else if (in_array('location', $columns) && !in_array('place', $columns)) {
        $row['place'] = $row['location'];
    }
    
    $events[] = $row;
}

// Return results
echo json_encode([
    "success" => true,
    "events" => $events,
    "debug_info" => [
        "table_used" => $events_table,
        "available_tables" => $tables,
        "columns" => $columns
    ]
]);

$conn->close();
?>