<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

// Debug: Check if input is empty
if (!$data) {
    echo "❌ No JSON received. Raw input: " . $rawInput;
    exit;
}

$firstName = $data["firstName"];
$middleName = $data["middleName"];
$lastName = $data["lastName"];
$email = $data["email"];
$username = $data["username"];
$password = $data["password"];

// Hash password before storing
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Set default role for new users
$role = "student";

// Connect to DB
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Use the correct column names that match your database
$sql = "INSERT INTO users (firstname, middlename, lastname, email, username, password, role)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $firstName, $middleName, $lastName, $email, $username, $hashedPassword, $role);

if ($stmt->execute()) {
  echo "Registration successful!";
} else {
  echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();