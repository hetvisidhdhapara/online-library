
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

// Only POST requests allowed
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: manage_students.php");
    exit;
}

// Verify CSRF Token
if (
    !isset($_POST["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    die("Invalid request. Please try again.");
}

// Validate Student ID
if (
    !isset($_POST["student_id"]) ||
    !filter_var($_POST["student_id"], FILTER_VALIDATE_INT)
) {
    die("Invalid student ID.");
}

$studentId = (int) $_POST["student_id"];

// Delete Student
$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM students WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $studentId);

if (mysqli_stmt_execute($stmt)) {

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);

        header("Location: manage_students.php");
        exit;
    } else {
        mysqli_stmt_close($stmt);
        die("Student not found.");
    }

} else {

    mysqli_stmt_close($stmt);
    die("Unable to delete student.");

}
?>
