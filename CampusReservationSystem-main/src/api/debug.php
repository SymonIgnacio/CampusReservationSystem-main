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

// Output PHP version and loaded extensions
$debug_info = [
    'php_version' => PHP_VERSION,
    'extensions' => get_loaded_extensions(),
    'server' => $_SERVER['SERVER_SOFTWARE'],
    'document_root' => $_SERVER['DOCUMENT_ROOT'],
    'script_filename' => $_SERVER['SCRIPT_FILENAME']
];

// Try database connection
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

try {
    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);
    
    if ($conn->connect_error) {
        $debug_info['db_connection'] = "Failed: " . $conn->connect_error;
    } else {
        $debug_info['db_connection'] = "Success";
        
        // Check if events table exists
        $result = $conn->query("SHOW TABLES LIKE 'events'");
        $debug_info['events_table_exists'] = $result->num_rows > 0;
        
        if ($debug_info['events_table_exists']) {
            // Get table structure
            $result = $conn->query("DESCRIBE events");
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row;
            }
            $debug_info['events_table_structure'] = $columns;
            
            // Count records
            $result = $conn->query("SELECT COUNT(*) as count FROM events");
            $row = $result->fetch_assoc();
            $debug_info['events_count'] = $row['count'];
            
            // Get a sample record if available
            if ($row['count'] > 0) {
                $result = $conn->query("SELECT * FROM events LIMIT 1");
                $debug_info['sample_record'] = $result->fetch_assoc();
            }
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    $debug_info['db_error'] = $e->getMessage();
}

// Return debug info
echo json_encode($debug_info, JSON_PRETTY_PRINT);
?>