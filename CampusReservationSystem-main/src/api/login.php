<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the request body
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

// Debug: Check if input is empty
if (!$data) {
    echo json_encode([
        "success" => false, 
        "message" => "No JSON received or invalid format", 
        "debug" => $rawInput
    ]);
    exit();
}

// Check if username and password are provided
if (!isset($data['username']) || !isset($data['password'])) {
    echo json_encode([
        "success" => false, 
        "message" => "Username and password are required"
    ]);
    exit();
}

// Database connection
$host = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "campus_db";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        "success" => false, 
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
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
    
    if ($conn->query($createTableSQL)) {
        // Create a default admin user
        $adminFirstName = "Admin";
        $adminMiddleName = "";
        $adminLastName = "User";
        $adminDepartment = "Administration";
        $adminEmail = "admin@example.com";
        $adminUsername = "admin";
        $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
        $adminRole = "admin";
        
        $sql = "INSERT INTO users (firstname, middlename, lastname, department, email, username, password, role)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $adminFirstName, $adminMiddleName, $adminLastName, $adminDepartment, $adminEmail, $adminUsername, $adminPassword, $adminRole);
        
        if (!$stmt->execute()) {
            echo json_encode([
                "success" => false, 
                "message" => "Error creating admin user: " . $stmt->error
            ]);
            exit();
        }
        $stmt->close();
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "Error creating users table: " . $conn->error
        ]);
        exit();
    }
}

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
if (!$stmt) {
    echo json_encode([
        "success" => false, 
        "message" => "Prepare statement failed: " . $conn->error
    ]);
    exit();
}

$stmt->bind_param("s", $data['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Check if password matches the hashed password in the database
    if (password_verify($data['password'], $user['password'])) {
        // Remove password from user data before sending to client
        unset($user['password']);
        
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "user" => $user
        ]);
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "Invalid username or password"
        ]);
    }
} else {
    echo json_encode([
        "success" => false, 
        "message" => "Invalid username or password"
    ]);
}

$stmt->close();
$conn->close();
?>