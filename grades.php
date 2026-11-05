
<?php
session_start();
require_once "db.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "success";

// Save student marks
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        http_response_code(403);
        exit("Invalid request. Please return to the Grades page.");
    }

    $studentId = filter_input(INPUT_POST, "student_id", FILTER_VALIDATE_INT);
    $subjectId = filter_input(INPUT_POST, "subject_id", FILTER_VALIDATE_INT);
    $assessment = trim($_POST["assessment_type"] ?? "");
    $marksInput = $_POST["marks"] ?? "";
    $academicYear = trim($_POST["academic_year"] ?? "");

    if (
        !$studentId || $studentId < 1 ||
        !$subjectId || $subjectId < 1 ||
        $assessment === "" ||
        !is_numeric($marksInput) ||
        $marksInput < 0 || $marksInput > 100 ||
        !preg_match('/^\d{4}-\d{4}$/', $academicYear)
    ) {
        $message = "Please enter valid information. Marks must be 0-100 and academic year like 2026-2027.";
        $messageType = "danger";
    } else {
        // Check that the student and subject exist
        $check = $conn->prepare(
            "SELECT
                (SELECT COUNT(*) FROM students WHERE student_id = ?),
                (SELECT COUNT(*) FROM subjects WHERE subject_id = ?)"
        );
        $check->bind_param("ii", $studentId, $subjectId);
        $check->execute();
        $check->bind_result($studentExists, $subjectExists);
        $check->fetch();
        $check->close();

        if (!$studentExists || !$subjectExists) {
            $message = "The selected student or subject does not exist.";
            $messageType = "danger";
        } else {
            $marks = (float)$marksInput;

            $stmt = $conn->prepare(
                "INSERT INTO grades
                (student_id, subject_id, assessment_type, marks, academic_year)
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iisds",
                $studentId,
                $subjectId,
                $assessment,
                $marks,
                $academicYear
            );

            if ($stmt->execute()) {
                header("Location: grades.php?saved=1");
                exit;
            } else {
                $message = "Unable to save marks. Please check the database.";
                $messageType = "danger";
            }

            $stmt->close();
        }
    }
}

if (isset($_GET["saved"])) {
    $message = "Student marks saved successfully!";
}

// Get students for dropdown
$students = $conn->query(
    "SELECT student_id, names, admission_number
     FROM students ORDER BY names"
);

// Get subjects for dropdown
$subjects = $conn->query(
    "SELECT subject_id, subject_name, subject_code
     FROM subjects ORDER BY subject_name"
);

// Get saved marks
$grades = $conn->query(
    "SELECT g.grade_id, s.names, s.admission_number,
            sub.subject_name, g.assessment_type,
            g.marks, g.academic_year
     FROM grades g
     INNER JOIN students s ON g.student_id = s.student_id
     INNER JOIN subjects sub ON g.subject_id = sub.subject_id
     ORDER BY g.grade_id DESC"
);

if (!$students || !$subjects || !$grades) {
    die("Database query failed. Check your table and column names.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades - LOLSchool</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a href="index.php" class="navbar-brand">LOLSchool</a>
        <a href="index.php" class="btn btn-light btn-sm">Dashboard</a>
    </div>
</nav>

<div class="container py-4">

    <h2 class="mb-4">Grade Management</h2>

    <?php if ($message !== ""): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Form for entering marks -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            Enter Student Marks
        </div>

        <div class="card-body">
            <form method="POST" action="grades.php">
                <input type="hidden" name="csrf_token"
                       value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Student *</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select student</option>
                            <?php while ($student = $students->fetch_assoc()): ?>
                                <option value="<?php echo (int)$student["student_id"]; ?>">
                                    <?php echo htmlspecialchars(
                                        $student["admission_number"] . " - " . $student["names"]
                                    ); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Subject *</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select subject</option>
                            <?php while ($subject = $subjects->fetch_assoc()): ?>
                                <option value="<?php echo (int)$subject["subject_id"]; ?>">
                                    <?php echo htmlspecialchars(
                                        $subject["subject_code"] . " - " . $subject["subject_name"]
                                    ); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Assessment Type *</label>
                        <select name="assessment_type" class="form-select" required>
                            <option value="">Select assessment</option>
                            <option value="Test">Test</option>
                            <option value="Exam">Exam</option>
                            <option value="Assignment">Assignment</option>
                            <option value="Practical">Practical</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Marks (0-100) *</label>
                        <input type="number" name="marks"
                               class="form-control"
                               min="0" max="100" step="0.01" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Academic Year *</label>
                        <input type="text" name="academic_year"
                               class="form-control"
                               placeholder="2026-2027"
                               pattern="[0-9]{4}-[0-9]{4}"
                               required>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-success">
                            Save Marks
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Table displaying saved marks -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            Recorded Student Marks
        </div>

        <div class="card-body">
            <p>
                Total records:
                <strong><?php echo $grades->num_rows; ?></strong>
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Admission No.</th>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Assessment</th>
                            <th>Marks</th>
                            <th>Academic Year</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($grades->num_rows > 0): ?>
                        <?php while ($grade = $grades->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo (int)$grade["grade_id"]; ?></td>
                                <td><?php echo htmlspecialchars($grade["admission_number"]); ?></td>
                                <td><?php echo htmlspecialchars($grade["names"]); ?></td>
                                <td><?php echo htmlspecialchars($grade["subject_name"]); ?></td>
                                <td><?php echo htmlspecialchars($grade["assessment_type"]); ?></td>
                                <td><?php echo htmlspecialchars($grade["marks"]); ?>/100</td>
                                <td><?php echo htmlspecialchars($grade["academic_year"]); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                No marks recorded yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

</body>
</html>
