
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

$sql = "SELECT books.*, reading_history.read_at
        FROM reading_history
        INNER JOIN books
        ON reading_history.book_id = books.id
        WHERE reading_history.student_id = ?
        ORDER BY reading_history.read_at DESC";

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

    <title>Reading History - Readify</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .history-container {
            margin-left: 290px;
            padding: 40px;
        }

        .history-card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            margin-bottom: 18px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        }

        .history-card h3 {
            color: #172554;
            margin-bottom: 10px;
        }

        .history-card p {
            margin-bottom: 8px;
        }

        .read-btn {
            display: inline-block;
            background: #172554;
            color: white;
            padding: 9px 16px;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .history-container {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="history-container">

    <h1>📖 Reading History</h1>
    <p>Books you have opened recently.</p>

    <br>

    <?php if (mysqli_num_rows($result) > 0): ?>

        <?php while ($book = mysqli_fetch_assoc($result)): ?>

            <div class="history-card">

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

                <p>
                    Last Read:
                    <?php echo date("d M Y, h:i A", strtotime($book["read_at"])); ?>
                </p>

                <a class="read-btn"
                   href="read.php?id=<?php echo (int)$book["id"]; ?>">
                    Read Again →
                </a>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>Your reading history is empty. Open a book to see it here!</p>

    <?php endif; ?>

    <br>

    <a href="books.php">← Browse Books</a>

</div>

</body>
</html>
