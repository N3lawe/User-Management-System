<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $stmt = $conn->prepare("SELECT id, username, email, password, is_active, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "User not found.";
        exit();
    }
} else {
    echo "No user selected.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $is_active = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;
    $role = $_POST['role'];

    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, is_active = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssisi", $username, $email, $password, $is_active, $role, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, is_active = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssisi", $username, $email, $is_active, $role, $user_id);
    }

    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: home.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <h1>Edit User</h1>
    <form method="POST" action="">
        <label for="username">Username</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        <br>

        <label for="email">Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        <br>

        <label for="password">Password</label>
        <input type="password" name="password" placeholder="Leave blank to keep current password">
        <br>

        <label for="is_active">Status</label>
        <select name="is_active">
            <option value="1" <?php echo $user['is_active'] == 1 ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo $user['is_active'] == 0 ? 'selected' : ''; ?>>Inactive</option>
        </select>
        <br>

        <label for="role">Role</label>
        <select name="role">
            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
            <option value="student" <?php echo $user['role'] == 'student' ? 'selected' : ''; ?>>Student</option>
        </select>
        <br>

        <button type="submit">Update</button>
    </form>
</body>

</html>