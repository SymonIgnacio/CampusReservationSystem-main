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
$data = json_decode(file_get_contents("php://input"), true);

$firstName = $data["firstName"];
$middleName = $data["middleName"];
$lastName = $data["lastName"];
$email = $data["email"];
$username = $data["username"];
$password = $data["password"];

// Hash password before storing
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Connect to DB
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "INSERT INTO users (first_name, middle_name, last_name, email, username, password)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $firstName, $middleName, $lastName, $email, $username, $hashedPassword);

if ($stmt->execute()) {
  echo "Registration successful!";
} else {
  echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
