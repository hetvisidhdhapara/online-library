
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

// Fetch All Books
$query = mysqli_query(
    $conn,
    "SELECT id, title, author, category, description, pdf_file, created_at
     FROM books
     ORDER BY id DESC"
);

if (!$query) {
    die("Unable to export books.");
}

// CSV Download Headers
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=readify_books.csv");

// Open Output
$output = fopen("php://output", "w");

// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// CSV Headings
fputcsv($output, [
    "Book ID",
    "Book Title",
    "Author",
    "Category",
    "Description",
    "PDF File",
    "Added Date"
]);

// Export Book Data
while ($book = mysqli_fetch_assoc($query)) {

    $row = [
        $book["id"],
        $book["title"],
        $book["author"],
        $book["category"],
        $book["description"],
        $book["pdf_file"],
        $book["created_at"]
    ];

    // Protect spreadsheet cells from formula injection
    foreach ($row as &$value) {
        $value = (string)($value ?? "");

        if (preg_match('/^[\s]*[=+\-@]/', $value)) {
            $value = "'" . $value;
        }
    }
    unset($value);

    fputcsv($output, $row);
}

fclose($output);
mysqli_close($conn);
exit;
?>
