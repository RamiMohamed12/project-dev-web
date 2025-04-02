<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/Application.php';
require_once __DIR__ . '/../Model/Internship.php';
require_once __DIR__ . '/../Model/Student.php';

$application = new Application($conn);
$internship = new Internship($conn);
$student = new Student($conn);

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'add') {
        $id_student = isset($_POST['id_student']) ? (int)$_POST['id_student'] : 0;
        $id_internship = isset($_POST['id_internship']) ? (int)$_POST['id_internship'] : 0;
        $cv = isset($_POST['cv']) ? htmlspecialchars(trim($_POST['cv'])) : '';
        $cover_letter = isset($_POST['cover_letter']) ? htmlspecialchars(trim($_POST['cover_letter'])) : '';

        // Validate student exists
        if (!$student->readStudent($id_student)) {
            echo "Error: Student not found.";
            exit();
        }

        // Validate internship exists
        if (!$internship->readInternship($id_internship)) {
            echo "Error: Internship not found.";
            exit();
        }

        if ($application->createApplication($id_student, $id_internship, $cv, $cover_letter)) {
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page
            exit();
        } else {
            echo "Error: Could not add application. " . $application->getError();
        }
    } elseif ($action == 'delete') {
        $id_app = (int) $_POST['id_app'];

        if ($application->deleteApplication($id_app)) {
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page
            exit();
        } else {
            echo "Error: Could not delete application. " . $application->getError();
        }
    }
}

// Fetch all applications to display in the table
$applications = $application->getAllApplications();

// Get lists for dropdowns
$students = $student->getAllStudents();
$internships = $internship->getAllInternships();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Applications</title>
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="form-container">
        <h2>Add Application</h2>
        <form method="post" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="id_student">Student:</label>
                <select name="id_student" required>
                    <option value="">Select Student</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?= $s['id_student'] ?>">
                            <?= htmlspecialchars($s['name']) ?> (ID: <?= $s['id_student'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_internship">Internship:</label>
                <select name="id_internship" required>
                    <option value="">Select Internship</option>
                    <?php foreach ($internships as $i): ?>
                        <option value="<?= $i['id'] ?>">
                            <?= htmlspecialchars($i['title']) ?> (ID: <?= $i['id'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="cv">CV File Path:</label>
                <input type="text" name="cv" placeholder="Path to CV file" required>
            </div>
            <div class="form-group">
                <label for="cover_letter">Cover Letter Path:</label>
                <input type="text" name="cover_letter" placeholder="Path to cover letter" required>
            </div>
            <button type="submit">Add Application</button>
        </form>
    </div>

    <div class="table-container">
        <h2>Application List</h2>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Internship</th>
                    <th>CV</th>
                    <th>Cover Letter</th>
                    <th>Application Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($applications)): ?>
                    <?php foreach ($applications as $app): 
                        $studentInfo = $student->readStudent($app['id_student']);
                        $internshipInfo = $internship->readInternship($app['id_internship']);
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($app['id_app']) ?></td>
                            <td>
                                <?= $studentInfo ? htmlspecialchars($studentInfo['name']) : 'Unknown' ?>
                                (ID: <?= $app['id_student'] ?>)
                            </td>
                            <td>
                                <?= $internshipInfo ? htmlspecialchars($internshipInfo['title']) : 'Unknown' ?>
                                (ID: <?= $app['id_internship'] ?>)
                            </td>
                            <td><?= htmlspecialchars(basename($app['cv'])) ?></td>
                            <td><?= htmlspecialchars(basename($app['cover_letter'])) ?></td>
                            <td><?= htmlspecialchars($app['application_date']) ?></td>
                            <td>
                                <form method="post" action="" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_app" value="<?= $app['id_app'] ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this application?');">Delete</button>
                                </form>
                                <a href="editApplication.php?id=<?= $app['id_app'] ?>">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No applications found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
