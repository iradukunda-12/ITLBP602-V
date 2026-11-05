
<?php
require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = trim($_POST['class_name'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '');

    if ($class_name !== '' && $academic_year !== '') {
        $stmt = $conn->prepare(
            "INSERT INTO classes (class_name, academic_year)
             VALUES (?, ?)"
        );

        if ($stmt) {
            $stmt->bind_param("ss", $class_name, $academic_year);

            if ($stmt->execute()) {
                $message = "Class added successfully.";
            } else {
                $message = "Could not add class: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = "Database error: " . $conn->error;
        }
    } else {
        $message = "Please fill in all fields.";
    }
}

$classes = $conn->query(
    "SELECT class_id, class_name, academic_year
     FROM classes ORDER BY class_id DESC"
);

if (!$classes) {
    die("Classes query error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Classes - LOLSchool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2>Manage Classes</h2>

    <?php if ($message !== ''): ?>
        <div class="alert alert-info">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="card card-body mb-4">
        <label class="form-label">Class Name</label>
        <input type="text" name="class_name"
               class="form-control mb-3" required>

        <label class="form-label">Academic Year</label>
        <input type="text" name="academic_year"
               class="form-control mb-3"
               placeholder="e.g. 2026" required>

        <button type="submit" class="btn btn-primary">
            Add Class
        </button>
    </form>

    <h3>Existing Classes</h3>

    <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Class Name</th>
                    <th>Academic Year</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($c = $classes->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int)$c['class_id'] ?></td>
                        <td><?= htmlspecialchars($c['class_name']) ?></td>
                        <td><?= htmlspecialchars($c['academic_year']) ?></td>
                    </tr>
                <?php endwhile; ?>

                <?php if ($classes->num_rows === 0): ?>
                    <tr>
                        <td colspan="3">No classes found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="index.php" class="btn btn-secondary">Back to Home</a>
</div>

</body>
</html>
