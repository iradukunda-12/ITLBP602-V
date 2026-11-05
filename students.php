

<?php
session_start();
require_once "db.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "success";

if (isset($_GET["deleted"])) {
    $message = "Student deleted successfully!";
}

if (isset($_GET["delete_error"])) {
    $messageType = "danger";

    $errors = [
        "invalid" => "Invalid student ID.",
        "missing" => "Student was not found.",
        "related" => "Cannot delete this student because related records may exist."
    ];

    $message = $errors[$_GET["delete_error"]]
        ?? "Unable to delete student.";
}

// Continue with the existing student registration code below.

require_once "db.php";

$message = "";
$messageType = "success";

// Add a new student
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $admission = trim($_POST["admission_number"] ?? "");
    $names = trim($_POST["names"] ?? "");
    $gender = $_POST["gender"] ?? "";
    $dob = $_POST["date_of_birth"] ?? "";
    $classId = $_POST["class_id"] ?? "";
    $phone = trim($_POST["phone_number"] ?? "");
    $address = trim($_POST["address"] ?? "");

    if (
        $admission === "" || $names === "" ||
        !in_array($gender, ["Male", "Female"], true) ||
        $dob === "" || !ctype_digit($classId) ||
        (int)$classId < 1
    ) {
        $message = "Please complete all required fields correctly.";
        $messageType = "danger";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO students
            (admission_number, names, gender, date_of_birth,
             class_id, phone_number, address)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $classId = (int)$classId;
        $stmt->bind_param(
            "ssssiss",
            $admission,
            $names,
            $gender,
            $dob,
            $classId,
            $phone,
            $address
        );

        if ($stmt->execute()) {
            header("Location: students.php?added=1");
            exit;
        } else {
            if ($stmt->errno == 1062) {
                $message = "This admission number already exists.";
            } else {
                $message = "Unable to save student. Check your data and database.";
            }
            $messageType = "danger";
        }

        $stmt->close();
    }
}

if (isset($_GET["added"])) {
    $message = "Student added successfully!";
}

// Get classes for the dropdown
$classes = $conn->query(
    "SELECT class_id, class_name FROM classes ORDER BY class_name"
);

// Get students and their class names
$students = $conn->query(
    "SELECT s.student_id, s.admission_number, s.names,
            s.gender, s.date_of_birth, s.phone_number,
            s.address, c.class_name
     FROM students s
     INNER JOIN classes c ON s.class_id = c.class_id
     ORDER BY s.student_id DESC"
);

if (!$classes || !$students) {
    die("Database query failed. Check your table and column names.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - LOLSchool</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">LOLSchool</a>
        <a href="index.php" class="btn btn-light btn-sm">Dashboard</a>
    </div>
</nav>

<div class="container py-4">

    <h2 class="mb-4">Student Management</h2>

    <?php if ($message !== ""): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Student registration form -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            Register New Student
        </div>

        <div class="card-body">
            <form method="POST" action="students.php">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Admission Number *</label>
                        <input type="text" name="admission_number"
                               class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Full Names *</label>
                        <input type="text" name="names"
                               class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Gender *</label>
                        <select name="gender" class="form-select" required>
                            <option value="">Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Date of Birth *</label>
                        <input type="date" name="date_of_birth"
                               class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Class *</label>
                        <select name="class_id" class="form-select" required>
                            <option value="">Select class</option>
                            <?php while ($class = $classes->fetch_assoc()): ?>
                                <option value="<?php echo (int)$class["class_id"]; ?>">
                                    <?php echo htmlspecialchars($class["class_name"]); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone_number"
                               class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" name="address"
                               class="form-control">
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            Save Student
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Students table -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            Registered Students
        </div>

        <div class="card-body">
            <p>
                Total students:
                <strong><?php echo $students->num_rows; ?></strong>
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Admission No.</th>
                            <th>Names</th>
                            <th>Gender</th>
                            <th>Date of Birth</th>
                            <th>Class</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    <tbody>
<?php if ($students->num_rows > 0): ?>
    <?php while ($student = $students->fetch_assoc()): ?>

        <tr>
            <td><?php echo (int)$student["student_id"]; ?></td>

            <td><?php echo htmlspecialchars($student["admission_number"]); ?></td>

            <td><?php echo htmlspecialchars($student["names"]); ?></td>

            <td><?php echo htmlspecialchars($student["gender"]); ?></td>

            <td><?php echo htmlspecialchars($student["date_of_birth"]); ?></td>

            <td><?php echo htmlspecialchars($student["class_name"]); ?></td>

            <td><?php echo htmlspecialchars($student["phone_number"] ?? ""); ?></td>

            <td><?php echo htmlspecialchars($student["address"] ?? ""); ?></td>

            <td>
                <!-- Edit Button -->
                <a href="edit_student.php?id=<?php echo (int)$student['student_id']; ?>"
                   class="btn btn-warning btn-sm">
                    Edit
                </a>

                <!-- Delete Button -->
                <form method="POST" action="delete_student.php"
                      style="display:inline;"
                      onsubmit="return confirm('Are you sure you want to delete this student?');">

                    <input type="hidden" name="student_id"
                           value="<?php echo (int)$student['student_id']; ?>">

                    <input type="hidden" name="csrf_token"
                           value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <button type="submit" class="btn btn-danger btn-sm">
                        Delete
                    </button>
                </form>
            </td>
        </tr>

    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="9" class="text-center">
            No students registered yet.
        </td>
    </tr>
<?php endif; ?>
</tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</body>
</html>
