
<?php
require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $date = $_POST['attendance_date'] ?? '';
    $status = $_POST['status'] ?? '';
    $remarks = trim($_POST['remarks'] ?? '');

    if (
        $student_id > 0 &&
        $date !== '' &&
        in_array($status, ['Present', 'Absent', 'Late'], true)
    ) {
        $stmt = $conn->prepare(
    "INSERT INTO attendance
    (student_id, attendance_date, attendance_status)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE
    attendance_status = VALUES(attendance_status)"
);

        if ($stmt) {
            $stmt->bind_param(
                "isss",
                $student_id,
                $attendance_date,
                $attendance_status
            );

            if ($stmt->execute()) {
                $message = "Attendance saved successfully.";
            } else {
                $message = "Error saving attendance: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = "SQL error: " . $conn->error;
        }
    } else {
        $message = "Please fill in all required fields.";
    }
}

$students = $conn->query(
    "SELECT student_id, names
     FROM students ORDER BY names"
);

if (!$students) {
    die("Students query error: " . $conn->error);
}

$records = $conn->query(
    "SELECT a.*, s.names
     FROM attendance a
     JOIN students s ON s.student_id = a.student_id
     ORDER BY a.attendance_date DESC"
);

if (!$records) {
    die("Attendance query error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2>Student Attendance</h2>

    <?php if ($message !== ''): ?>
        <div class="alert alert-info">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="card card-body mb-4">
        <label class="form-label">Student</label>
        <select name="student_id" class="form-select mb-3" required>
            <option value="">Select student</option>

            <?php while ($s = $students->fetch_assoc()): ?>
                <option value="<?= (int)$s['student_id'] ?>">
                   <?= htmlspecialchars($s['names']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label class="form-label">Date</label>
        <input type="date" name="attendance_date"
               class="form-control mb-3"
               value="<?= date('Y-m-d') ?>" required>

        <label class="form-label">Status</label>
        <select name="status" class="form-select mb-3" required>
            <option value="Present">Present</option>
            <option value="Absent">Absent</option>
            <option value="Late">Late</option>
        </select>

        <label class="form-label">Remarks</label>
        <input type="text" name="remarks"
               class="form-control mb-3" maxlength="255">

        <button type="submit" class="btn btn-primary">
            Save Attendance
        </button>
    </form>

    <h3>Attendance Records</h3>

   
<h3>Attendance Records</h3>

<div class="table-responsive">
    <table class="table table-bordered table-striped bg-white">
        <thead>
            <tr>
                <th>Student</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($r = $records->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($r['names']) ?></td>
                    <td><?= htmlspecialchars($r['attendance_date']) ?></td>
                    <td><?= htmlspecialchars($r['attendance_status']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

    </div>
</div>
</body>
</html>
