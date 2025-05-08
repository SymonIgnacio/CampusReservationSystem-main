<?php
// get_users.php - Get a list of all users (for admin purposes)

// Disable error display in output
error_reporting(0);
ini_set('display_errors', 0);

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

try {
    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        returnError("Connection failed: " . $conn->connect_error, 500);
    }

    // Check if database exists
    $dbExists = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    if ($dbExists->num_rows == 0) {
        // Create database if it doesn't exist
        if (!$conn->query("CREATE DATABASE IF NOT EXISTS $dbname")) {
            returnError("Failed to create database: " . $conn->error, 500);
        }
        $conn->select_db($dbname);
    }

    // Check if users table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
    if ($tableCheck->num_rows == 0) {
        // Create users table
        $createTableSQL = "CREATE TABLE users (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            firstname VARCHAR(255) NOT NULL,
            middlename VARCHAR(255),
            lastname VARCHAR(255) NOT NULL,
            department VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'student',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($createTableSQL)) {
            returnError("Failed to create users table: " . $conn->error, 500);
        }
        
        // Create sample users
        $sampleUsers = [
            [
                'firstname' => 'Admin',
                'middlename' => '',
                'lastname' => 'User',
                'department' => 'IT Department',
                'email' => 'admin@example.com',
                'username' => 'admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin'
            ],
            [
                'firstname' => 'John',
                'middlename' => 'A',
                'lastname' => 'Doe',
                'department' => 'Computer Science',
                'email' => 'john.doe@example.com',
                'username' => 'johndoe',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'student'
            ],
            [
                'firstname' => 'Jane',
                'middlename' => 'B',
                'lastname' => 'Smith',
                'department' => 'Engineering',
                'email' => 'jane.smith@example.com',
                'username' => 'janesmith',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'student'
            ],
            [
                'firstname' => 'Robert',
                'middlename' => 'C',
                'lastname' => 'Johnson',
                'department' => 'Mathematics',
                'email' => 'robert.johnson@example.com',
                'username' => 'robertj',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'faculty'
            ]
        ];
        
        foreach ($sampleUsers as $user) {
            $sql = "INSERT INTO users (firstname, middlename, lastname, department, email, username, password, role) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssss", 
                $user['firstname'], 
                $user['middlename'], 
                $user['lastname'], 
                $user['department'], 
                $user['email'], 
                $user['username'], 
                $user['password'], 
                $user['role']
            );
            
            $stmt->execute();
            $stmt->close();
        }
    }

    // Get filter parameters if provided
    $role = isset($_GET['role']) ? $_GET['role'] : '';
    $excludeAdmin = isset($_GET['excludeAdmin']) ? filter_var($_GET['excludeAdmin'], FILTER_VALIDATE_BOOLEAN) : false;

    // Prepare SQL query based on filter
    if (!empty($role) && $excludeAdmin) {
        // Only show non-admin users with the specified role
        $sql = "SELECT id, username, firstname, lastname, email, department, role FROM users WHERE role = ? AND role != 'admin'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $role);
    } else if (!empty($role)) {
        // Show users with the specified role including admins
        $sql = "SELECT id, username, firstname, lastname, email, department, role FROM users WHERE role = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $role);
    } else if ($excludeAdmin) {
        // Show all users except admins
        $sql = "SELECT id, username, firstname, lastname, email, department, role FROM users WHERE role != 'admin'";
        $stmt = $conn->prepare($sql);
    } else {
        // Show all users including admins
        $sql = "SELECT id, username, firstname, lastname, email, department, role FROM users";
        $stmt = $conn->prepare($sql);
    }

    if (!$stmt) {
        returnError("Prepare statement failed: " . $conn->error, 500);
    }

    // Execute query
    if (!$stmt->execute()) {
        returnError("Query execution failed: " . $stmt->error, 500);
    }
    
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    // Return results
    echo json_encode([
        "success" => true,
        "users" => $users,
        "count" => count($users)
    ]);

    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    returnError("Server error: " . $e->getMessage(), 500);
}
?>
?>