<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "campus_db";

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Check if database exists
$dbExists = $conn->select_db($database);

if (!$dbExists) {
    echo json_encode([
        "success" => false,
        "message" => "Database '$database' does not exist"
    ]);
    exit();
}

// Check tables
$tables = [];
$tablesResult = $conn->query("SHOW TABLES");

if ($tablesResult) {
    while ($row = $tablesResult->fetch_array()) {
        $tables[] = $row[0];
    }
}

// Check users table structure
$usersColumns = [];
if (in_array('users', $tables)) {
    $columnsResult = $conn->query("DESCRIBE users");
    if ($columnsResult) {
        while ($row = $columnsResult->fetch_assoc()) {
            $usersColumns[] = $row['Field'];
        }
    }
}

// Check events table structure
$eventsColumns = [];
if (in_array('events', $tables)) {
    $columnsResult = $conn->query("DESCRIBE events");
    if ($columnsResult) {
        while ($row = $columnsResult->fetch_assoc()) {
            $eventsColumns[] = $row['Field'];
        }
    }
}

// Count records in tables
$counts = [];
foreach ($tables as $table) {
    $countResult = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($countResult) {
        $row = $countResult->fetch_assoc();
        $counts[$table] = $row['count'];
    } else {
        $counts[$table] = "Error counting";
    }
}

// Return database information
echo json_encode([
    "success" => true,
    "database" => $database,
    "tables" => $tables,
    "users_columns" => $usersColumns,
    "events_columns" => $eventsColumns,
    "record_counts" => $counts
]);

$conn->close();
?>