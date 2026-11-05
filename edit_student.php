
<?php
include 'db.php';

$message = "";

// Get student ID from URL
if (!isset($_GET['id']) && !isset($_POST['student_id'])) {
    die("Student ID is missing!");
}

$student_id = isset($_POST['student_id'])
    ? (int)$_POST['student_id']
    : (int)$_GET['id'];

if ($student_id <= 0) {
    die("Invalid student ID!");
}

// Update student information
if (isset($_POST['update'])) {

    $admission = $_POST['admission_number'];
    $names = $_POST['names'];
    $gender = $_POST['gender'];
    $dob = $_POST['date_of_birth'];
    $class_id = (int)$_POST['class_id'];
    $phone = $_POST['phone_number'];
    $address = $_POST['address'];

    $sql = "UPDATE students SET
            admission_number = ?,
            names = ?,
            gender = ?,
            date_of_birth = ?,
            class_id = ?,
            phone_number = ?,
            address = ?
            WHERE student_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssssissi",
        $admission,
        $names,
        $gender,
        $dob,
        $class_id,
        $phone,
        $address,
        $student_id
    );

    if (mysqli_stmt_execute($stmt)) {
        header("Location: edit_student.php?id=" . $student_id . "&updated=1");
        exit;
    } else {
        $message = "Failed to update student!";
    }
}

// Retrieve student information
$sql = "SELECT * FROM students WHERE student_id = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    die("Student not found!");
}

// Retrieve classes
$classes = mysqli_query($conn, "SELECT * FROM classes");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">
</head>

<body class="container mt-5">

<h2 class="mb-4">Update Student Information</h2>

<?php if (isset($_GET['updated'])) { ?>
    <div class="alert alert-success">
        Student information updated successfully!
    </div>
<?php } ?>

<?php if ($message != "") { ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php } ?>

<form method="POST">

    <input type="hidden" name="student_id"
    value="<?php echo $student['student_id']; ?>">

    <label>Admission Number</label>
    <input type="text" name="admission_number"
    class="form-control mb-3"
    value="<?php echo htmlspecialchars($student['admission_number']); ?>"
    required>

    <label>Student Names</label>
    <input type="text" name="names"
    class="form-control mb-3"
    value="<?php echo htmlspecialchars($student['names']); ?>"
    required>

    <label>Gender</label>
    <select name="gender" class="form-control mb-3">
        <option value="Male"
        <?php if ($student['gender'] == 'Male') echo 'selected'; ?>>
            Male
        </option>

        <option value="Female"
        <?php if ($student['gender'] == 'Female') echo 'selected'; ?>>
            Female
        </option>
    </select>

    <label>Date of Birth</label>
    <input type="date" name="date_of_birth"
    class="form-control mb-3"
    value="<?php echo htmlspecialchars($student['date_of_birth'] ?? ''); ?>">

    <label>Class</label>
    <select name="class_id" class="form-control mb-3" required>

        <?php while ($row = mysqli_fetch_assoc($classes)) { ?>

            <option value="<?php echo $row['class_id']; ?>"
            <?php
            if ($student['class_id'] == $row['class_id']) {
                echo 'selected';
            }
            ?>>
                <?php echo htmlspecialchars($row['class_name']); ?>
            </option>

        <?php } ?>

    </select>

    <label>Phone Number</label>
    <input type="text" name="phone_number"
    class="form-control mb-3"
    value="<?php echo htmlspecialchars($student['phone_number'] ?? ''); ?>">

    <label>Address</label>
    <input type="text" name="address"
    class="form-control mb-3"
    value="<?php echo htmlspecialchars($student['address'] ?? ''); ?>">

    <button type="submit" name="update"
    class="btn btn-success">
        Update Student
    </button>

    <a href="students.php" class="btn btn-secondary">
        Back
    </a>

</form>

</body>
</html>
