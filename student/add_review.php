
<?php
session_start();

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit;
}

include "../config/db.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$studentId = (int)$_SESSION["student_id"];
$bookId = (int)($_GET["book_id"] ?? $_POST["book_id"] ?? 0);

$error = "";

// Fetch Book Details
$bookStmt = mysqli_prepare(
    $conn,
    "SELECT id, title, author FROM books WHERE id = ?"
);

mysqli_stmt_bind_param($bookStmt, "i", $bookId);
mysqli_stmt_execute($bookStmt);

$bookResult = mysqli_stmt_get_result($bookStmt);
$book = mysqli_fetch_assoc($bookResult);

if (!$book) {
    header("Location: books.php");
    exit;
}

// Handle Review Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    $rating = filter_var(
        $_POST["rating"] ?? "",
        FILTER_VALIDATE_INT
    );

    $review = trim($_POST["review"] ?? "");

    if (!hash_equals($_SESSION["csrf_token"], $token)) {
        $error = "Invalid request. Please try again.";
    } elseif ($rating === false || $rating < 1 || $rating > 5) {
        $error = "Please select a rating between 1 and 5.";
    } elseif ($review === "") {
        $error = "Please write your review.";
    } elseif (strlen($review) > 2000) {
        $error = "Review must be 2000 characters or less.";
    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO book_reviews
            (student_id, book_id, rating, review)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            rating = VALUES(rating),
            review = VALUES(review),
            created_at = CURRENT_TIMESTAMP"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "iiis",
            $studentId,
            $bookId,
            $rating,
            $review
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: books.php?review=success");
            exit;
        } else {
            $error = "Unable to submit review. Please try again.";
        }

        mysqli_stmt_close($stmt);
    }
}

// Fetch Existing Review
$existingStmt = mysqli_prepare(
    $conn,
    "SELECT rating, review
     FROM book_reviews
     WHERE student_id = ? AND book_id = ?"
);

mysqli_stmt_bind_param(
    $existingStmt,
    "ii",
    $studentId,
    $bookId
);

mysqli_stmt_execute($existingStmt);
$existingResult = mysqli_stmt_get_result($existingStmt);
$existingReview = mysqli_fetch_assoc($existingResult);

// Form Values
$selectedRating = isset($_POST["rating"])
    ? (int)$_POST["rating"]
    : (int)($existingReview["rating"] ?? 0);

$reviewText = $_SERVER["REQUEST_METHOD"] === "POST"
    ? ($_POST["review"] ?? "")
    : ($existingReview["review"] ?? "");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate & Review | Readify</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            color: #202b45;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .review-container {
            width: 100%;
            max-width: 550px;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        }

        h1 {
            color: #14213d;
            margin-bottom: 10px;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 25px;
        }

        .book-info {
            background: #fff3e8;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .book-info h3 {
            color: #14213d;
            margin-bottom: 8px;
        }

        .book-info p {
            color: #64748b;
            font-size: 14px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 10px;
            color: #14213d;
        }

        select,
        textarea {
            width: 100%;
            padding: 13px;
            border: 1px solid #dbe1ea;
            border-radius: 8px;
            font-size: 15px;
            margin-bottom: 22px;
            font-family: Arial, sans-serif;
        }

        textarea {
            resize: vertical;
            min-height: 130px;
        }

        select:focus,
        textarea:focus {
            outline: none;
            border-color: #f4a261;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: #14213d;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .submit-btn:hover {
            background: #f4a261;
            color: #14213d;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #14213d;
            text-decoration: none;
            font-weight: bold;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="review-container">

        <h1>Rate & Review ⭐</h1>

        <p class="subtitle">
            Share your experience with this book
        </p>

        <div class="book-info">
            <h3>
                <?php echo htmlspecialchars($book["title"]); ?>
            </h3>

            <p>
                Author:
                <?php echo htmlspecialchars($book["author"]); ?>
            </p>
        </div>

        <?php if ($error !== ""): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input
                type="hidden"
                name="book_id"
                value="<?php echo $bookId; ?>"
            >

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>"
            >

            <label for="rating">Your Rating</label>

            <select name="rating" id="rating" required>
                <option value="">Select Rating</option>

                <option value="5" <?php echo $selectedRating === 5 ? "selected" : ""; ?>>
                    ⭐⭐⭐⭐⭐ - Excellent
                </option>

                <option value="4" <?php echo $selectedRating === 4 ? "selected" : ""; ?>>
                    ⭐⭐⭐⭐ - Very Good
                </option>

                <option value="3" <?php echo $selectedRating === 3 ? "selected" : ""; ?>>
                    ⭐⭐⭐ - Good
                </option>

                <option value="2" <?php echo $selectedRating === 2 ? "selected" : ""; ?>>
                    ⭐⭐ - Average
                </option>

                <option value="1" <?php echo $selectedRating === 1 ? "selected" : ""; ?>>
                    ⭐ - Poor
                </option>
            </select>

            <label for="review">Write Your Review</label>

            <textarea
                name="review"
                id="review"
                maxlength="2000"
                placeholder="Write your experience about this book..."
                required
            ><?php echo htmlspecialchars($reviewText); ?></textarea>

            <button type="submit" class="submit-btn">
                Submit Review
            </button>

        </form>

        <a href="books.php" class="back-link">
            ← Back to Books
        </a>

    </div>

</body>
</html>
