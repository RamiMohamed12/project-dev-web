<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/user.php';

$user = new User($conn);

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'add') {
        $userType = $_POST['user_type'];
        $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
        $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $location = isset($_POST['location']) ? htmlspecialchars(trim($_POST['location'])) : '';
        $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';

        // Additional fields for student
        $dob = isset($_POST['dob']) ? $_POST['dob'] : '';
        $year = isset($_POST['year']) ? $_POST['year'] : '';
        $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description'])) : '';

        // Validate phone number
        if (!empty($phone) && !preg_match('/^\d+$/', $phone)) {
            echo "Error: Phone number must contain only numbers.";
            exit();
        }

        $success = false;
        switch($userType) {
            case 'student':
                $success = $user->createStudent($name, $email, $password, $location, $phone, $dob, $year, $description);
                break;
            case 'pilote':
                $success = $user->createPilote($name, $email, $password, $location, $phone);
                break;
            case 'admin':
                $success = $user->createAdmin($name, $email, $password);
                break;
        }

        if ($success) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error: Could not create user. " . $user->getError();
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script>
        function toggleFields() {
            const userType = document.getElementById('user_type').value;
            const studentFields = document.getElementById('student_fields');
            const locationPhoneFields = document.getElementById('location_phone_fields');

            studentFields.style.display = 'none';
            locationPhoneFields.style.display = 'none';

            if (userType === 'student') {
                studentFields.style.display = 'block';
                locationPhoneFields.style.display = 'block';
            } else if (userType === 'pilote') {
                locationPhoneFields.style.display = 'block';
            }
        }
    </script>
</head>
<body>
    <div class="form-container">
        <h2>Create User Account</h2>
        <form method="post" action="">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="user_type">User Type:</label>
                <select name="user_type" id="user_type" onchange="toggleFields()" required>
                    <option value="student">Student</option>
                    <option value="pilote">Pilote</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" required>
            </div>

            <div id="location_phone_fields">
                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" name="location">
                </div>

                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" name="phone">
                </div>
            </div>

            <div id="student_fields">
                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" name="dob">
                </div>

                <div class="form-group">
                    <label for="year">Year:</label>
                    <select name="year">
                        <option value="1st">1st Year</option>
                        <option value="2nd">2nd Year</option>
                        <option value="3rd">3rd Year</option>
                        <option value="4th">4th Year</option>
                        <option value="5th">5th Year</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea name="description"></textarea>
                </div>
            </div>

            <button type="submit">Create User</button>
        </form>
    </div>
    

<div class="table-container">
    <h2>User List</h2>
    <div class="filter-section">
        <label for="filter_type">Filter by User Type:</label>
        <select id="filter_type" onchange="filterUsers(this.value)">
            <option value="student">Students</option>
            <option value="pilote">Pilotes</option>
            <option value="admin">Administrators</option>
        </select>
    </div>
    
    <div id="student_table" class="user-table">
        <h3>Students</h3>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Location</th>
                    <th>Phone</th>
                    <th>Year</th>
                    <th>Date of Birth</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $students = $user->getAllStudents();
                if (!empty($students)):
                    foreach ($students as $student):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($student['id_student']) ?></td>
                        <td><?= htmlspecialchars($student['name']) ?></td>
                        <td><?= htmlspecialchars($student['email']) ?></td>
                        <td><?= htmlspecialchars($student['location']) ?></td>
                        <td><?= htmlspecialchars($student['phone_number']) ?></td>
                        <td><?= htmlspecialchars($student['year']) ?></td>
                        <td><?= htmlspecialchars($student['date_of_birth']) ?></td>
                        <td>
                            <a href="editUser.php?id=<?= $student['id_student'] ?>&type=student">Edit</a>
                            <form method="post" action="" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_type" value="student">
                                <input type="hidden" name="id" value="<?= $student['id_student'] ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this student?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php 
                    endforeach;
                else:
                ?>
                    <tr><td colspan="8">No students found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="pilote_table" class="user-table" style="display: none;">
        <h3>Pilotes</h3>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Location</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $pilotes = $user->getAllPilotes();
                if (!empty($pilotes)):
                    foreach ($pilotes as $pilote):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($pilote['id_pilote']) ?></td>
                        <td><?= htmlspecialchars($pilote['name']) ?></td>
                        <td><?= htmlspecialchars($pilote['email']) ?></td>
                        <td><?= htmlspecialchars($pilote['location']) ?></td>
                        <td><?= htmlspecialchars($pilote['phone_number']) ?></td>
                        <td>
                            <a href="editUser.php?id=<?= $pilote['id_pilote'] ?>&type=pilote">Edit</a>
                            <form method="post" action="" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_type" value="pilote">
                                <input type="hidden" name="id" value="<?= $pilote['id_pilote'] ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this pilote?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php 
                    endforeach;
                else:
                ?>
                    <tr><td colspan="6">No pilotes found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="admin_table" class="user-table" style="display: none;">
        <h3>Administrators</h3>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $admins = $user->getAllAdmins();
                if (!empty($admins)):
                    foreach ($admins as $admin):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($admin['id_admin']) ?></td>
                        <td><?= htmlspecialchars($admin['name']) ?></td>
                        <td><?= htmlspecialchars($admin['email']) ?></td>
                        <td>
                            <a href="editUser.php?id=<?= $admin['id_admin'] ?>&type=admin">Edit</a>
                            <form method="post" action="" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_type" value="admin">
                                <input type="hidden" name="id" value="<?= $admin['id_admin'] ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this administrator?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php 
                    endforeach;
                else:
                ?>
                    <tr><td colspan="4">No administrators found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function filterUsers(userType) {
        const tables = document.getElementsByClassName('user-table');
        for (let table of tables) {
            table.style.display = 'none';
        }
        document.getElementById(userType + '_table').style.display = 'block';
    }

    // Initialize the display
    window.onload = function() {
        toggleFields();
        filterUsers('student');
    }
</script>
</body>
</html>