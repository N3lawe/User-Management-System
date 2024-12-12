<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $subject_id = $_POST['subject_id'];
    $mark = $_POST['mark'];

    $stmt = $conn->prepare("INSERT INTO user_subjects (user_id, subject_id, mark) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $subject_id, $mark);

    if ($stmt->execute()) {
        echo "Subject assigned successfully.";
        echo "<p><a href='home.php'>Back to Dashboard</a></p>";
    } else {
        echo "Error assigning subject: " . $stmt->error;
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Subject assigned</title>
    <link rel="stylesheet" href="css/styles.css">
</head>