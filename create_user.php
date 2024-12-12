<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, is_active) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $username, $email, $hashed_password, $role, $is_active);

    if ($stmt->execute()) {
        echo "<p>User created successfully!</p>";
        echo "<p><a href='home.php'>Back to Dashboard</a></p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Create New User</title>
    <link rel="stylesheet" href="css/styles.css">
</head>