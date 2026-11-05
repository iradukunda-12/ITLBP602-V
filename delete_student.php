
<?php
session_start();
require_once "db.php";

// Allow deletion only through POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: students.php");
    exit;
}

// Verify security token
$token = $_POST["csrf_token"] ?? "";

if (
    !isset($_SESSION["csrf_token"]) ||
    !is_string($token) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(403);
    exit("Invalid request. Return to Students and try again.");
}

// Validate student ID
$studentId = filter_input(INPUT_POST, "student_id", FILTER_VALIDATE_INT);

if (!$studentId || $studentId < 1) {
    header("Location: students.php?delete_error=invalid");
    exit;
}

// Delete using a prepared statement
$stmt = $conn->prepare(
    "DELETE FROM students WHERE student_id = ?"
);
$stmt->bind_param("i", $studentId);

if ($stmt->execute()) {
    if ($stmt->affected_rows === 1) {
        header("Location: students.php?deleted=1");
    } else {
        header("Location: students.php?delete_error=missing");
    }
} else {
    // Related grades or attendance may prevent deletion
    header("Location: students.php?delete_error=related");
}

$stmt->close();
$conn->close();
exit;
?>
