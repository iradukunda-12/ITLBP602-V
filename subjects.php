
<?php
require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name'] ?? '');
    $subject_code = trim($_POST['subject_code'] ?? '');

    if ($subject_name !== '' && $subject_code !== '') {
        $stmt = $conn->prepare(
            "INSERT INTO subjects (subject_name, subject_code)
             VALUES (?, ?)"
        );

        if ($stmt) {
            $stmt->bind_param("ss", $subject_name, $subject_code);

            if ($stmt->execute()) {
                $message = "Subject added successfully.";
            } else {
                $message = "Could not add subject: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = "Database error: " . $conn->error;
        }
    } else {
        $message = "Please fill in all fields.";
    }
}

$subjects = $conn->query(
    "SELECT subject_id, subject_name, subject_code
     FROM subjects ORDER BY subject_id DESC"
);

if (!$subjects) {
    die("Subjects query error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Subjects - LOLSchool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2>Manage Subjects</h2>

    <?php if ($message !== ''): ?>
        <div class="alert alert-info">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="card card-body mb-4">
        <label class="form-label">Subject Name</label>
        <input type="text" name="subject_name"
               class="form-control mb-3" required>

        <label class="form-label">Subject Code</label>
        <input type="text" name="subject_code"
               class="form-control mb-3" required>

        <button type="submit" class="btn btn-primary">
            Add Subject
        </button>
    </form>

    <h3>Existing Subjects</h3>

    <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Subject Name</th>
                    <th>Subject Code</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($s = $subjects->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int)$s['subject_id'] ?></td>
                        <td><?= htmlspecialchars($s['subject_name']) ?></td>
                        <td><?= htmlspecialchars($s['subject_code']) ?></td>
                    </tr>
                <?php endwhile; ?>

                <?php if ($subjects->num_rows === 0): ?>
                    <tr>
                        <td colspan="3">No subjects found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="index.php" class="btn btn-secondary">Back to Home</a>
</div>

</body>
</html>
