
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: manage_books.php");
    exit;
}

// CSRF verification
if (
    !isset($_POST["csrf_token"]) ||
    !isset($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    die("Invalid request. Please go back and try again.");
}

$bookId = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$bookId || $bookId < 1) {
    header("Location: manage_books.php");
    exit;
}

// Find the book
$stmt = mysqli_prepare(
    $conn,
    "SELECT pdf_file FROM books WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $bookId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$book = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($book) {

    // Delete database record
    $deleteStmt = mysqli_prepare(
        $conn,
        "DELETE FROM books WHERE id = ?"
    );
    mysqli_stmt_bind_param($deleteStmt, "i", $bookId);

    if (mysqli_stmt_execute($deleteStmt)) {

        // Delete only files inside uploads/books
        $fileName = basename($book["pdf_file"] ?? "");
        $filePath = realpath(__DIR__ . "/../uploads/books/" . $fileName);
        $uploadPath = realpath(__DIR__ . "/../uploads/books");

        if (
            $filePath !== false &&
            $uploadPath !== false &&
            dirname($filePath) === $uploadPath &&
            is_file($filePath)
        ) {
            unlink($filePath);
        }
    }

    mysqli_stmt_close($deleteStmt);
}

header("Location: manage_books.php");
exit;
?>
