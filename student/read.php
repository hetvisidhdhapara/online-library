<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../config/db.php";

$bookId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$bookId || $bookId < 1) {
    die("Invalid book ID.");
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT title, pdf_file FROM books WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $bookId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$book = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$book || empty($book["pdf_file"])) {
    die("Book or PDF not found.");
}

// Validate stored PDF path
$storedPath = str_replace("\\", "/", $book["pdf_file"]);
$prefix = "uploads/books/";

if (strpos($storedPath, $prefix) !== 0) {
    die("Invalid PDF file path.");
}

$fileName = substr($storedPath, strlen($prefix));

if (!preg_match('/^[a-f0-9]{32}\.pdf$/', $fileName)) {
    die("Invalid PDF filename.");
}

$pdfPath = realpath(__DIR__ . "/../uploads/books/" . $fileName);
$uploadPath = realpath(__DIR__ . "/../uploads/books");

if (
    $pdfPath === false ||
    $uploadPath === false ||
    dirname($pdfPath) !== $uploadPath ||
    !is_file($pdfPath) ||
    mime_content_type($pdfPath) !== "application/pdf"
) {
    die("PDF file is not available.");
}

$pdfUrl = "../uploads/books/" . rawurlencode($fileName);
// Save Reading History for logged-in student
if (isset($_SESSION["student_id"])) {

    $studentId = $_SESSION["student_id"];

    $historySql = "INSERT INTO reading_history
                   (student_id, book_id)
                   VALUES (?, ?)
                   ON DUPLICATE KEY UPDATE
                   read_at = CURRENT_TIMESTAMP";

    $historyStmt = mysqli_prepare($conn, $historySql);

    mysqli_stmt_bind_param(
        $historyStmt,
        "ii",
        $studentId,
        $bookId
    );

    mysqli_stmt_execute($historyStmt);
    mysqli_stmt_close($historyStmt);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($book["title"]); ?> | Readify
    </title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6fb;
        }

        .navbar {
            background: #14213d;
            color: white;
            padding: 18px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .navbar h2 {
            color: #ffad60;
        }

        .navbar a {
            color: white;
            text-decoration: none;
        }

        .reader-heading {
            padding: 20px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .reader-heading h1 {
            color: #14213d;
            font-size: 23px;
        }

        .pdf-viewer {
            width: 90%;
            height: 80vh;
            margin: 0 auto 30px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .pdf-viewer iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .download-btn {
            background: #ffad60;
            color: #14213d;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 7px;
            font-weight: bold;
        }

        @media(max-width: 600px) {
            .pdf-viewer {
                width: 95%;
                height: 75vh;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <h2>Readify.</h2>
        <a href="books.php">← Back to Books</a>
    </nav>

    <div class="reader-heading">

        <h1>
            <?php echo htmlspecialchars($book["title"]); ?>
        </h1>

        <a class="download-btn"
           href="<?php echo htmlspecialchars($pdfUrl); ?>"
           target="_blank" rel="noopener">
            Open PDF
        </a>

    </div>

    <div class="pdf-viewer">
        <iframe
            src="<?php echo htmlspecialchars($pdfUrl); ?>"
            title="Book PDF Reader">
        </iframe>
    </div>

</body>
</html>
