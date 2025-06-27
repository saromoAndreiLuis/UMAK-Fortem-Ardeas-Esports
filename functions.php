<?php
include 'ghubdbconn.php';

// Function to register a new user
function registerUser($name, $email, $password) {
    global $conn;
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO Accounts (name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $passwordHash);
    return $stmt->execute();
}

// Function to validate user login
function validateUser($email, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT password_hash FROM Accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($passwordHash);
    $stmt->fetch();
    return password_verify($password, $passwordHash);
}

// Function to add a new event (admin use)
function addEvent($title, $dateTime, $coverPhoto) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO Events (title, date_time, cover_photo) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $dateTime, $coverPhoto);
    return $stmt->execute();
}

// Function to fetch events
function fetchEvents() {
    global $conn;
    $result = $conn->query("SELECT * FROM Events");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch quick stats
function fetchQuickStats() {
    global $conn;
    $result = $conn->query("SELECT * FROM QuickStats LIMIT 1");
    return $result->fetch_assoc();
}

?>
