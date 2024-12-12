<!DOCTYPE html>
<html>

<head>
    <title>Trainees Task</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <h1>Welcome to the Trainees Management System</h1>
    <a href="register.php">Register</a>
    <a href="login.php">Login</a>

    <?php
    session_start();
    require 'database.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    if (!isset($_SESSION['role'])) {
        echo "Unauthorized access. Please log in.";
        header("Location: login.php");
        exit();
    }

    $role = $_SESSION['role'];

    if ($role === 'admin') {
        echo "<h1>Admin Dashboard</h1>";

        $users = $conn->query("SELECT id, username, email, is_active FROM users WHERE role = 'student'");

        echo "<table border='1'>";
        echo "<tr><th>Username</th><th>Email</th><th>Status</th><th>Actions</th></tr>";

        while ($user = $users->fetch_assoc()) {
            $status = $user['is_active'] ? 'Active' : 'Inactive';
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . $status . "</td>";
            echo "<td><button class='edit-btn' onclick=\"window.location.href='edit.php?id=" . $user['id'] . "'\">Edit</button> ";
            echo "<button class='delete-btn' onclick=\"window.location.href='delete.php?id=" . $user['id'] . "'\">Delete</button></td>";
            echo "</tr>";
        }
        echo "</table>";

        echo "<button onclick=\"openCreateUserModal()\">Create New User</button>";
        echo "<button onclick=\"openCreateSubjectModal()\">Create New Subject</button>";
        echo "<button onclick=\"openAssignSubjectModal()\">Assign Subject</button>";
    } else {
        $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        echo "<h1>Welcome, " . htmlspecialchars($user['username']) . "</h1>";

        echo "<h2>Your Details</h2>";
        echo "<table border='1'>";
        echo "<tr><th>Username</th><th>Email</th></tr>";
        echo "<tr><td>" . htmlspecialchars($user['username']) . "</td><td>" . htmlspecialchars($user['email']) . "</td></tr>";
        echo "</table>";

        echo "<h2>Your Subjects</h2>";
        echo "<table border='1'>";
        echo "<tr><th>Subject</th><th>Pass Mark</th><th>Mark Obtained</th></tr>";

        $subjects = $conn->prepare("SELECT s.subject_name, s.pass_mark, us.mark FROM subjects s LEFT JOIN user_subjects us ON s.id = us.subject_id WHERE us.user_id = ?");
        $subjects->bind_param("i", $user_id);
        $subjects->execute();
        $result = $subjects->get_result();

        while ($subject = $result->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($subject['subject_name']) . "</td><td>" . $subject['pass_mark'] . "</td><td>" . ($subject['mark'] ?? "-") . "</td></tr>";
        }

        echo "</table>";
    }
    ?>

    <div id="createUserModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeCreateUserModal()">&times;</span>
            <h2>Create New User</h2>
            <form id="createUserForm" action="create_user.php" method="POST">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required><br>

                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required><br>

                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required><br>

                <label for="role">Role:</label>
                <select name="role" id="role" required>
                    <option value="admin">Admin</option>
                    <option value="student">Student</option>
                </select><br>

                <label for="is_active">Active:</label>
                <input type="checkbox" name="is_active" id="is_active" value="1"><br>

                <button type="submit">Create User</button>
            </form>
        </div>
    </div>

    <div id="createSubjectModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeCreateSubjectModal()">&times;</span>
            <h2>Create New Subject</h2>
            <form id="createSubjectForm" action="create_subject.php" method="POST">
                <label for="subject_name">Subject Name:</label>
                <input type="text" name="subject_name" id="subject_name" required><br>

                <label for="pass_mark">Pass Mark:</label>
                <input type="number" name="pass_mark" id="pass_mark" required><br>

                <button type="submit">Create Subject</button>
            </form>
        </div>
    </div>

    <div id="assignSubjectModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeAssignSubjectModal()">&times;</span>
            <h2>Assign Subject to Student</h2>
            <form id="assignSubjectForm" action="assign_subject.php" method="POST">
                <label for="user_id">Student:</label>
                <select name="user_id" id="user_id" required>
                    <?php
                    $students = $conn->query("SELECT id, username FROM users WHERE role = 'student'");
                    while ($student = $students->fetch_assoc()) {
                        echo "<option value='" . $student['id'] . "'>" . htmlspecialchars($student['username']) . "</option>";
                    }
                    ?>
                </select><br>

                <label for="subject_id">Subject:</label>
                <select name="subject_id" id="subject_id" required>
                    <?php
                    $subjects = $conn->query("SELECT id, subject_name FROM subjects");
                    while ($subject = $subjects->fetch_assoc()) {
                        echo "<option value='" . $subject['id'] . "'>" . htmlspecialchars($subject['subject_name']) . "</option>";
                    }
                    ?>
                </select><br>

                <label for="mark">Mark:</label>
                <input type="number" name="mark" id="mark" required><br>

                <button type="submit">Assign Subject</button>
            </form>
        </div>
    </div>

    <script>
        function openCreateUserModal() {
            document.getElementById("createUserModal").style.display = "block";
        }

        function closeCreateUserModal() {
            document.getElementById("createUserModal").style.display = "none";
        }

        function openCreateSubjectModal() {
            document.getElementById("createSubjectModal").style.display = "block";
        }

        function closeCreateSubjectModal() {
            document.getElementById("createSubjectModal").style.display = "none";
        }


        function openAssignSubjectModal() {
            document.getElementById("assignSubjectModal").style.display = "block";
        }

        function closeAssignSubjectModal() {
            document.getElementById("assignSubjectModal").style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == document.getElementById("createUserModal")) {
                closeCreateUserModal();
            } else if (event.target == document.getElementById("createSubjectModal")) {
                closeCreateSubjectModal();
            } else if (event.target == document.getElementById("assignSubjectModal")) {
                closeAssignSubjectModal();
            }
        }
    </script>
</body>

</html>