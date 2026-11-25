
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

include "../config/db.php";

$studentId = $_SESSION["student_id"];
// Remove favourite book
if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["remove_bookmark"])) {

    $bookmarkId = (int)$_POST["bookmark_id"];

    $deleteSql = "DELETE FROM bookmarks
                  WHERE id = ? AND student_id = ?";

    $deleteStmt = mysqli_prepare($conn, $deleteSql);
    mysqli_stmt_bind_param($deleteStmt, "ii", $bookmarkId, $studentId);
    mysqli_stmt_execute($deleteStmt);

    header("Location: favorites.php?removed=1");
    exit();
}

// Fetch student's favourite books
$sql = "SELECT books.*, bookmarks.id AS bookmark_id
        FROM bookmarks
        INNER JOIN books ON bookmarks.book_id = books.id
        WHERE bookmarks.student_id = ?
        ORDER BY bookmarks.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $studentId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - Readify</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .favorites-container {
            margin-left: 290px;
            padding: 40px;
        }

        .favorite-card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            margin-bottom: 18px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        }

        .favorite-card h3 {
            color: #172554;
            margin-bottom: 8px;
        }

        .favorite-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .read-btn, .remove-btn {
            display: inline-block;
            padding: 9px 16px;
            border-radius: 6px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .read-btn {
            background: #172554;
            color: white;
        }

        .remove-btn {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 768px) {
            .favorites-container {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="favorites-container">

    <h1>⭐ My Favorite Books</h1>
    <p>Your saved books are listed here.</p>

    <br>

    <?php if (mysqli_num_rows($result) > 0): ?>

        <?php while ($book = mysqli_fetch_assoc($result)): ?>

            <div class="favorite-card">

                <h3>
                    <?php echo htmlspecialchars($book["title"]); ?>
                </h3>

                <p>
                    Author:
                    <?php echo htmlspecialchars($book["author"] ?? "Unknown"); ?>
                </p>

                <p>
                    Category:
                    <?php echo htmlspecialchars($book["category"] ?? "Uncategorized"); ?>
                </p>

                
<div class="favorite-actions">

    <?php if (!empty($book["pdf_file"])): ?>

        <a class="read-btn"
           href="read.php?id=<?php echo (int)$book["id"]; ?>">
            Read Online →
        </a>

    <?php else: ?>

        <span>PDF not available</span>

    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="bookmark_id"
               value="<?php echo (int)$book["bookmark_id"]; ?>">

        <button type="submit"
                name="remove_bookmark"
                class="remove-btn">
            Remove from Favorites
        </button>
    </form>

</div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>You haven't added any favourite books yet.</p>

    <?php endif; ?>

    <br>

    <a href="books.php">← Browse Books</a>

</div>

</body>
</html>
