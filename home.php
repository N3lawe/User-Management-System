<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainees Task</title>
    <!-- إضافة Bootstrap CSS من CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Welcome to the Trainees Management System</h1>
        <div class="text-center">
            <a href="register.php" class="btn btn-primary mx-2">Register</a>
            <a href="login.php" class="btn btn-secondary mx-2">Login</a>
        </div>

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
            echo "<h2 class='mt-4'>Admin Dashboard</h2>";

            $users = $conn->query("SELECT id, username, email, is_active FROM users WHERE role = 'student'");

            echo "<table class='table table-bordered mt-3'>";
            echo "<thead><tr><th>Username</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead><tbody>";

            while ($user = $users->fetch_assoc()) {
                $status = $user['is_active'] ? 'Active' : 'Inactive';
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . $status . "</td>";
                echo "<td>
                        <button class='btn btn-warning btn-sm' onclick=\"window.location.href='edit.php?id=" . $user['id'] . "'\">Edit</button>
                        <button class='btn btn-danger btn-sm' onclick=\"window.location.href='delete.php?id=" . $user['id'] . "'\">Delete</button>
                    </td>";
                echo "</tr>";
            }
            echo "</tbody></table>";

            echo "<div class='mt-4'>
                    <button class='btn btn-success' onclick=\"openCreateUserModal()\">Create New User</button>
                    <button class='btn btn-info' onclick=\"openCreateSubjectModal()\">Create New Subject</button>
                    <button class='btn btn-primary' onclick=\"openAssignSubjectModal()\">Assign Subject</button>
                  </div>";
        } else {
            $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            echo "<h2 class='mt-4'>Welcome, " . htmlspecialchars($user['username']) . "</h2>";

            echo "<h3>Your Details</h3>";
            echo "<table class='table table-bordered mt-3'>
                    <tr><th>Username</th><th>Email</th></tr>
                    <tr><td>" . htmlspecialchars($user['username']) . "</td><td>" . htmlspecialchars($user['email']) . "</td></tr>
                  </table>";

            echo "<h3>Your Subjects</h3>";
            echo "<table class='table table-bordered mt-3'>
                    <thead><tr><th>Subject</th><th>Pass Mark</th><th>Mark Obtained</th></tr></thead><tbody>";

            $subjects = $conn->prepare("SELECT s.subject_name, s.pass_mark, us.mark FROM subjects s LEFT JOIN user_subjects us ON s.id = us.subject_id WHERE us.user_id = ?");
            $subjects->bind_param("i", $user_id);
            $subjects->execute();
            $result = $subjects->get_result();

            while ($subject = $result->fetch_assoc()) {
                echo "<tr><td>" . htmlspecialchars($subject['subject_name']) . "</td><td>" . $subject['pass_mark'] . "</td><td>" . ($subject['mark'] ?? "-") . "</td></tr>";
            }

            echo "</tbody></table>";
        }
        ?>

        <!-- Modal Create User -->
        <div id="createUserModal" class="modal fade" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createUserModalLabel">Create New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createUserForm" action="create_user.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username:</label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role:</label>
                                <select name="role" id="role" class="form-select" required>
                                    <option value="admin">Admin</option>
                                    <option value="student">Student</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="is_active" class="form-check-label">Active:</label>
                                <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input">
                            </div>
                            <button type="submit" class="btn btn-success">Create User</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Create Subject -->
        <div id="createSubjectModal" class="modal fade" tabindex="-1" aria-labelledby="createSubjectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createSubjectModalLabel">Create New Subject</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createSubjectForm" action="create_subject.php" method="POST">
                            <div class="mb-3">
                                <label for="subject_name" class="form-label">Subject Name:</label>
                                <input type="text" name="subject_name" id="subject_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="pass_mark" class="form-label">Pass Mark:</label>
                                <input type="number" name="pass_mark" id="pass_mark" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success">Create Subject</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Assign Subject -->
        <div id="assignSubjectModal" class="modal fade" tabindex="-1" aria-labelledby="assignSubjectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="assignSubjectModalLabel">Assign Subject to Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="assignSubjectForm" action="assign_subject.php" method="POST">
                            <div class="mb-3">
                                <label for="user_id" class="form-label">Student:</label>
                                <select name="user_id" id="user_id" class="form-select" required>
                                    <?php
                                    $students = $conn->query("SELECT id, username FROM users WHERE role = 'student'");
                                    while ($student = $students->fetch_assoc()) {
                                        echo "<option value='" . $student['id'] . "'>" . htmlspecialchars($student['username']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="subject_id" class="form-label">Subject:</label>
                                <select name="subject_id" id="subject_id" class="form-select" required>
                                    <?php
                                    $subjects = $conn->query("SELECT id, subject_name FROM subjects");
                                    while ($subject = $subjects->fetch_assoc()) {
                                        echo "<option value='" . $subject['id'] . "'>" . htmlspecialchars($subject['subject_name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="mark" class="form-label">Mark:</label>
                                <input type="number" name="mark" id="mark" class="form-control" placeholder="Enter Mark" required>
                            </div>
                            <button type="submit" class="btn btn-success">Assign Subject</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- إضافة Bootstrap JS من CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openCreateUserModal() {
            new bootstrap.Modal(document.getElementById('createUserModal')).show();
        }

        function openCreateSubjectModal() {
            new bootstrap.Modal(document.getElementById('createSubjectModal')).show();
        }

        function openAssignSubjectModal() {
            new bootstrap.Modal(document.getElementById('assignSubjectModal')).show();
        }
    </script>
</body>

</html>