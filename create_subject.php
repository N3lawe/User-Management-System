<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_name = $_POST['subject_name'];
    $pass_mark = $_POST['pass_mark'];

    if (!empty($subject_name) && !empty($pass_mark)) {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_name, pass_mark) VALUES (?, ?)");
        $stmt->bind_param("si", $subject_name, $pass_mark);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Subject created successfully.";
            echo "<p><a href='home.php'>Back to Dashboard</a></p>";
        } else {
            echo "Error: " . $stmt->error;
        }



        $stmt->close();
    } else {
        echo "Please fill in all fields.";
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Create Subject created</title>
    <link rel="stylesheet" href="css/styles.css">
</head>