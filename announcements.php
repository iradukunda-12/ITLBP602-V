
<?php
require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $announcement = trim($_POST['message'] ?? '');
    $posted_by = (int)($_POST['posted_by'] ?? 0);

    if ($title !== '' && $announcement !== '' && $posted_by > 0) {
        $stmt = $conn->prepare(
            "INSERT INTO announcements
            (title, message, publication_date, posted_by)
            VALUES (?, ?, CURDATE(), ?)"
        );

        if ($stmt) {
            $stmt->bind_param(
                "ssi",
                $title,
                $announcement,
                $posted_by
            );

            if ($stmt->execute()) {
                $message = "Announcement published successfully.";
            } else {
                $message = "Could not publish announcement: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = "Database error: " . $conn->error;
        }
    } else {
        $message = "Please fill in all fields and select the poster.";
    }
}

$users = $conn->query(
    "SELECT * FROM users"
);

if (!$users) {
    die("Users query error: " . $conn->error);
}

$userRows = $users->fetch_all(MYSQLI_ASSOC);
$userIdColumn = null;
$userNameColumn = null;

if (!empty($userRows)) {
    foreach (['user_id', 'id'] as $candidate) {
        if (array_key_exists($candidate, $userRows[0])) {
            $userIdColumn = $candidate;
            break;
        }
    }

    foreach (['username', 'full_name', 'names', 'name'] as $candidate) {
        if (array_key_exists($candidate, $userRows[0])) {
            $userNameColumn = $candidate;
            break;
        }
    }
}

$announcements = $conn->query(
    "SELECT * FROM announcements ORDER BY announcement_id DESC"
);

if (!$announcements) {
    die("Announcements query error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>School Announcements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2>School Announcements</h2>

    <?php if ($message !== ''): ?>
        <div class="alert alert-info">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($userIdColumn !== null): ?>
        <form method="POST" class="card card-body mb-4">
            <label class="form-label">Announcement Title</label>
            <input type="text" name="title"
                   class="form-control mb-3" required>

            <label class="form-label">Message</label>
            <textarea name="message" class="form-control mb-3"
                      rows="4" required></textarea>

            <label class="form-label">Posted By</label>
            <select name="posted_by" class="form-select mb-3" required>
                <option value="">Select user</option>

                <?php foreach ($userRows as $u): ?>
                    <option value="<?= (int)$u[$userIdColumn] ?>">
                        <?php
                        if (
                            $userNameColumn !== null &&
                            !empty($u[$userNameColumn])
                        ) {
                            echo htmlspecialchars($u[$userNameColumn]);
                        } else {
                            echo 'User #' . (int)$u[$userIdColumn];
                        }
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-primary">
                Publish Announcement
            </button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">
            No users found, or the users table does not have a recognized ID column.
        </div>
    <?php endif; ?>

    <h3>Published Announcements</h3>

    <?php while ($a = $announcements->fetch_assoc()): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">
                    <?= htmlspecialchars($a['title']) ?>
                </h5>

                <p class="card-text">
                    <?= nl2br(htmlspecialchars($a['message'])) ?>
                </p>

                <small class="text-muted">
                    Published: <?= htmlspecialchars($a['publication_date'] ?? '') ?>
                    | Posted by user ID:
                    <?= htmlspecialchars((string)$a['posted_by']) ?>
                </small>
            </div>
        </div>
    <?php endwhile; ?>

    <?php if ($announcements->num_rows === 0): ?>
        <p>No announcements have been published yet.</p>
    <?php endif; ?>

    <a href="index.php" class="btn btn-secondary">Back to Home</a>
</div>
</body>
</html>
