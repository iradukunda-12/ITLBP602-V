

<?php
session_start();
require_once 'db.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Enter your username and password.';
    } else {
        $stmt = $conn->prepare(
            "SELECT user_id, username, password, role
             FROM users
             WHERE username = ?
             LIMIT 1"
        );

        if (!$stmt) {
            $error = 'Database error: ' . $conn->error;
        } else {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result(
                    $userId,
                    $dbUsername,
                    $dbPassword,
                    $dbRole
                );
                $stmt->fetch();

                $validPassword =
                    password_verify($password, $dbPassword) ||
                    hash_equals((string)$dbPassword, $password);

                if ($validPassword) {
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $dbUsername;
                    $_SESSION['role'] = $dbRole;

                    $stmt->close();
                    header('Location: index.php');
                    exit;
                }

                $error = 'Password is incorrect.';
            } else {
                $error = 'Username was not found.';
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | LOLSchool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h3>LOLSchool</h3>
                    <p class="mb-0">Student Management System</p>
                </div>

                <div class="card-body">
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username"
                                   class="form-control" autocomplete="username"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password"
                                   class="form-control"
                                   autocomplete="current-password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Login
                        </button>
                    </form>
                </div>

                <div class="card-footer text-center text-muted">
                    LOLSchool &copy; 2026
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
